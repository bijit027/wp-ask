<?php
/**
 * Heatmap Repository
 *
 * @package PollQuest
 */

namespace PollQuest\Repositories;

use PollQuest\Models\Heatmap;

/**
 * Class HeatmapRepository
 */
class HeatmapRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'pollquest_heatmaps';
	}

	/**
	 * Find a heatmap by ID.
	 *
	 * @param int $id Heatmap ID.
	 * @return Heatmap|null
	 */
	public function find( int $id ): ?Heatmap {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );

		return $row ? new Heatmap( $row ) : null;
	}

	/**
	 * Find a published heatmap for a page.
	 *
	 * @param int $page_id Page ID.
	 * @return Heatmap|null
	 */
	public function find_by_page( int $page_id ): ?Heatmap {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE page_id = %d AND status = 'publish' ORDER BY id DESC LIMIT 1",
				$page_id
			)
		);

		return $row ? new Heatmap( $row ) : null;
	}

	/**
	 * Get all heatmaps, optionally filtered by status.
	 *
	 * @param string $status Status filter or 'all'.
	 * @return array<int, Heatmap>
	 */
	public function get_all( string $status = 'all' ): array {
		global $wpdb;

		if ( 'all' === $status ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results( "SELECT * FROM {$this->table} WHERE status != 'trash' ORDER BY id DESC" );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$this->table} WHERE status = %s ORDER BY id DESC", $status )
			);
		}

		return array_map(
			static function ( $row ) {
				return new Heatmap( $row );
			},
			(array) $rows
		);
	}

	/**
	 * Create a heatmap.
	 *
	 * @param array<string, mixed> $data Heatmap data.
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		$result = $wpdb->insert(
			$this->table,
			$data,
			[ '%d', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Update a heatmap.
	 *
	 * @param int                  $id   Heatmap ID.
	 * @param array<string, mixed> $data Heatmap data.
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		return false !== $wpdb->update(
			$this->table,
			$data,
			[ 'id' => $id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Delete a heatmap permanently.
	 *
	 * @param int $id Heatmap ID.
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;

		return false !== $wpdb->delete( $this->table, [ 'id' => $id ], [ '%d' ] );
	}
}
