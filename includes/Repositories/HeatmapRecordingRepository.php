<?php
/**
 * Heatmap Recording Repository
 *
 * @package InsightPulse
 */

namespace WPAsk\Repositories;

use WPAsk\Models\HeatmapRecording;

/**
 * Class HeatmapRecordingRepository
 */
class HeatmapRecordingRepository {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ipulse_heatmap_recordings';
	}

	/**
	 * Create a recording batch.
	 *
	 * @param array<string, mixed> $data Recording data.
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		if ( isset( $data['click_data'] ) && is_array( $data['click_data'] ) ) {
			$data['click_data'] = wp_json_encode( $data['click_data'] );
		}

		$result = $wpdb->insert(
			$this->table,
			$data,
			[ '%d', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Get all recordings for a heatmap.
	 *
	 * @param int $heatmap_id Heatmap ID.
	 * @return array<int, HeatmapRecording>
	 */
	public function get_by_heatmap( int $heatmap_id ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE heatmap_id = %d ORDER BY id DESC",
				$heatmap_id
			)
		);

		return array_map(
			static function ( $row ) {
				return new HeatmapRecording( $row );
			},
			(array) $rows
		);
	}

	/**
	 * Count recordings for a heatmap.
	 *
	 * @param int $heatmap_id Heatmap ID.
	 * @return int
	 */
	public function count_by_heatmap( int $heatmap_id ): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE heatmap_id = %d",
				$heatmap_id
			)
		);
	}

	/**
	 * Delete all recordings for a heatmap.
	 *
	 * @param int $heatmap_id Heatmap ID.
	 * @return bool
	 */
	public function delete_by_heatmap( int $heatmap_id ): bool {
		global $wpdb;

		return false !== $wpdb->delete( $this->table, [ 'heatmap_id' => $heatmap_id ], [ '%d' ] );
	}
}
