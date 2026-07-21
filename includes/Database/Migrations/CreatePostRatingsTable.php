<?php
/**
 * Create Post Ratings Table Migration.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Database\Migrations;

/**
 * Class CreatePostRatingsTable
 */
class CreatePostRatingsTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ipulse_post_ratings';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id bigint(20) UNSIGNED NOT NULL,
			rating tinyint(3) UNSIGNED NOT NULL,
			user_id bigint(20) UNSIGNED NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY post_id (post_id)
		) $charset_collate;";

		dbDelta( $sql );
	}
}
