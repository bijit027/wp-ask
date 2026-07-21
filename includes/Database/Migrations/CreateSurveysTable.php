<?php
/**
 * Create Surveys Table Migration.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Database\Migrations;

/**
 * Class CreateSurveysTable
 */
class CreateSurveysTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ipulse_surveys';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			status enum('publish', 'draft', 'trash') DEFAULT 'draft',
			type varchar(50) DEFAULT 'floating',
			questions longtext,
			settings longtext,
			targeting longtext,
			notifications longtext,
			impressions bigint(20) DEFAULT 0,
			publish_at timestamp NULL,
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY publish_at (publish_at)
		) $charset_collate;";

		dbDelta( $sql );
	}
}
