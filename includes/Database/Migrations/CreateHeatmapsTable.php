<?php
/**
 * Create Heatmaps Table Migration.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Database\Migrations;

/**
 * Class CreateHeatmapsTable
 */
class CreateHeatmapsTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ipulse_heatmaps';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			page_id bigint(20) UNSIGNED NOT NULL,
			status enum('publish', 'draft', 'trash') DEFAULT 'publish',
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY page_id (page_id)
		) $charset_collate;";

		dbDelta( $sql );

		$table_name_recordings = $wpdb->prefix . 'ipulse_heatmap_recordings';

		$sql_recordings = "CREATE TABLE $table_name_recordings (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			heatmap_id bigint(20) UNSIGNED NOT NULL,
			click_data longtext,
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY heatmap_id (heatmap_id)
		) $charset_collate;";

		dbDelta( $sql_recordings );
	}
}
