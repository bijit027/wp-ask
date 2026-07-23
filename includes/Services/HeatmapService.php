<?php
/**
 * Heatmap Service
 *
 * @package PollQuest
 */

namespace PollQuest\Services;

use PollQuest\Repositories\HeatmapRecordingRepository;
use PollQuest\Repositories\HeatmapRepository;
use WP_Error;

/**
 * Class HeatmapService
 */
class HeatmapService {

	/**
	 * @var HeatmapRepository
	 */
	private $heatmaps;

	/**
	 * @var HeatmapRecordingRepository
	 */
	private $recordings;

	public function __construct() {
		$this->heatmaps    = new HeatmapRepository();
		$this->recordings  = new HeatmapRecordingRepository();
	}

	/**
	 * List heatmaps with page metadata and stats.
	 *
	 * @param string $status Status filter.
	 * @return array<int, array<string, mixed>>
	 */
	public function list_heatmaps( string $status = 'all' ): array {
		$items = $this->heatmaps->get_all( $status );
		$list  = [];

		foreach ( $items as $heatmap ) {
			$list[] = $this->format_heatmap( $heatmap );
		}

		return $list;
	}

	/**
	 * Get a single heatmap with aggregated click data.
	 *
	 * @param int $id Heatmap ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function get_heatmap( int $id ) {
		$heatmap = $this->heatmaps->find( $id );
		if ( ! $heatmap ) {
			return new WP_Error( 'not_found', __( 'Heatmap not found.', 'pollquest' ), [ 'status' => 404 ] );
		}

		$data = $this->format_heatmap( $heatmap );
		$data['points'] = $this->aggregate_clicks( $id );

		return $data;
	}

	/**
	 * Create a heatmap for a page.
	 *
	 * @param int $page_id Page ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_heatmap( int $page_id ) {
		if ( ! get_post( $page_id ) ) {
			return new WP_Error( 'invalid_page', __( 'Page not found.', 'pollquest' ), [ 'status' => 404 ] );
		}

		$existing = $this->heatmaps->find_by_page( $page_id );
		if ( $existing ) {
			return new WP_Error(
				'duplicate',
				__( 'A heatmap already exists for this page.', 'pollquest' ),
				[ 'status' => 409, 'heatmap_id' => (int) $existing->id ]
			);
		}

		$id = $this->heatmaps->create(
			[
				'page_id'    => $page_id,
				'status'     => 'publish',
				'created_at' => current_time( 'mysql', true ),
			]
		);

		if ( ! $id ) {
			return new WP_Error( 'db_error', __( 'Could not create heatmap.', 'pollquest' ), [ 'status' => 500 ] );
		}

		$heatmap = $this->heatmaps->find( $id );
		return $this->format_heatmap( $heatmap );
	}

	/**
	 * Update heatmap status.
	 *
	 * @param int    $id     Heatmap ID.
	 * @param string $status New status.
	 * @return array<string, mixed>|WP_Error
	 */
	public function update_status( int $id, string $status ) {
		$allowed = [ 'publish', 'draft', 'trash' ];
		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'invalid_status', __( 'Invalid status.', 'pollquest' ), [ 'status' => 400 ] );
		}

		$heatmap = $this->heatmaps->find( $id );
		if ( ! $heatmap ) {
			return new WP_Error( 'not_found', __( 'Heatmap not found.', 'pollquest' ), [ 'status' => 404 ] );
		}

		$this->heatmaps->update( $id, [ 'status' => $status ] );
		$heatmap = $this->heatmaps->find( $id );

		return $this->format_heatmap( $heatmap );
	}

	/**
	 * Delete a heatmap and its recordings.
	 *
	 * @param int $id Heatmap ID.
	 * @return true|WP_Error
	 */
	public function delete_heatmap( int $id ) {
		$heatmap = $this->heatmaps->find( $id );
		if ( ! $heatmap ) {
			return new WP_Error( 'not_found', __( 'Heatmap not found.', 'pollquest' ), [ 'status' => 404 ] );
		}

		$this->recordings->delete_by_heatmap( $id );
		$this->heatmaps->delete( $id );

		return true;
	}

	/**
	 * Record click batch from frontend.
	 *
	 * @param int                  $heatmap_id Heatmap ID.
	 * @param array<int, mixed>    $clicks     Click points.
	 * @return true|WP_Error
	 */
	public function record_clicks( int $heatmap_id, array $clicks ) {
		// Note: Heatmap existence and status validation is now handled by permission_callback

		$sanitized = [];
		foreach ( $clicks as $click ) {
			if ( ! is_array( $click ) ) {
				continue;
			}

			$x = isset( $click['x'] ) ? (float) $click['x'] : null;
			$y = isset( $click['y'] ) ? (float) $click['y'] : null;

			if ( null === $x || null === $y ) {
				continue;
			}

			$sanitized[] = [
				'x'       => max( 0, min( 1, $x ) ),
				'y'       => max( 0, min( 1, $y ) ),
				'scrollY' => isset( $click['scrollY'] ) ? (int) $click['scrollY'] : 0,
				'vw'      => isset( $click['vw'] ) ? (int) $click['vw'] : 0,
				'vh'      => isset( $click['vh'] ) ? (int) $click['vh'] : 0,
			];
		}

		if ( empty( $sanitized ) ) {
			return new WP_Error( 'no_clicks', __( 'No valid clicks provided.', 'pollquest' ), [ 'status' => 400 ] );
		}

		$result = $this->recordings->create(
			[
				'heatmap_id' => $heatmap_id,
				'click_data' => [ 'clicks' => $sanitized ],
				'created_at' => current_time( 'mysql', true ),
			]
		);

		if ( ! $result ) {
			return new WP_Error( 'db_error', __( 'Could not save clicks.', 'pollquest' ), [ 'status' => 500 ] );
		}

		return true;
	}

	/**
	 * Get heatmap config for frontend tracking on a page.
	 *
	 * @param int $page_id Page ID.
	 * @return array<string, mixed>|null
	 */
	public function get_tracking_config( int $page_id ): ?array {
		$heatmap = $this->heatmaps->find_by_page( $page_id );
		if ( ! $heatmap ) {
			return null;
		}

		return [
			'heatmap_id' => (int) $heatmap->id,
			'page_id'    => (int) $heatmap->page_id,
			'api_url'    => esc_url_raw( rest_url( 'pollquest/v1' ) ),
		];
	}

	/**
	 * Format heatmap with page info and stats.
	 *
	 * @param \PollQuest\Models\Heatmap $heatmap Heatmap model.
	 * @return array<string, mixed>
	 */
	private function format_heatmap( $heatmap ): array {
		$post  = get_post( (int) $heatmap->page_id );
		$data  = $heatmap->to_array();
		$stats = $this->aggregate_clicks( (int) $heatmap->id );

		$data['page_title']   = $post ? $post->post_title : __( 'Unknown page', 'pollquest' );
		$data['page_url']     = $post ? get_permalink( $post ) : '';
		$data['page_type']    = $post ? $post->post_type : '';
		$data['click_count']  = $stats['total_clicks'];
		$data['session_count'] = $this->recordings->count_by_heatmap( (int) $heatmap->id );

		return $data;
	}

	/**
	 * Aggregate click points into a density grid and raw points.
	 *
	 * @param int $heatmap_id Heatmap ID.
	 * @return array<string, mixed>
	 */
	private function aggregate_clicks( int $heatmap_id ): array {
		$recordings   = $this->recordings->get_by_heatmap( $heatmap_id );
		$points       = [];
		$grid         = [];
		$grid_size    = 20;
		$total_clicks = 0;

		foreach ( $recordings as $recording ) {
			$clicks = $recording->click_data['clicks'] ?? [];
			if ( ! is_array( $clicks ) ) {
				continue;
			}

			foreach ( $clicks as $click ) {
				if ( ! isset( $click['x'], $click['y'] ) ) {
					continue;
				}

				$x = (float) $click['x'];
				$y = (float) $click['y'];
				$points[] = [ 'x' => $x, 'y' => $y ];
				$total_clicks++;

				$gx = min( $grid_size - 1, (int) floor( $x * $grid_size ) );
				$gy = min( $grid_size - 1, (int) floor( $y * $grid_size ) );
				$key = $gx . ':' . $gy;

				if ( ! isset( $grid[ $key ] ) ) {
					$grid[ $key ] = [
						'x'     => ( $gx + 0.5 ) / $grid_size,
						'y'     => ( $gy + 0.5 ) / $grid_size,
						'count' => 0,
					];
				}

				$grid[ $key ]['count']++;
			}
		}

		$max_count = 0;
		foreach ( $grid as $cell ) {
			$max_count = max( $max_count, $cell['count'] );
		}

		return [
			'total_clicks' => $total_clicks,
			'points'       => $points,
			'grid'         => array_values( $grid ),
			'max_count'    => $max_count,
			'grid_size'    => $grid_size,
		];
	}
}
