<?php
/**
 * Create Responses Table Migration.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Database\Migrations;

/**
 * Class CreateResponsesTable
 */
class CreateResponsesTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ipulse_responses';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			survey_id bigint(20) UNSIGNED NOT NULL,
			session_id bigint(20) UNSIGNED NULL,
			user_id bigint(20) UNSIGNED NULL,
			serial bigint(20) UNSIGNED NULL,
			page_id bigint(20) UNSIGNED NULL,
			page_url text,
			answers longtext,
			context longtext,
			email varchar(100) NULL,
			full_name varchar(100) NULL,
			ip_address varchar(45) NULL,
			country varchar(5) NULL,
			browser varchar(100) NULL,
			platform varchar(100) NULL,
			device varchar(50) NULL,
			status enum('publish', 'trash') DEFAULT 'publish',
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY survey_id (survey_id),
			KEY session_id (session_id),
			KEY user_id (user_id),
			KEY created_at (created_at),
			KEY page_id (page_id)
		) $charset_collate;";

		dbDelta( $sql );
	}
}
