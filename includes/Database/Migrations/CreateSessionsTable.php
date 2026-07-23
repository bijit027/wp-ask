<?php
/**
 * Create Sessions Table Migration.
 *
 * @package PollQuest
 */

namespace PollQuest\Database\Migrations;

/**
 * Class CreateSessionsTable
 */
class CreateSessionsTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'pollquest_sessions';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			uid varchar(40) NOT NULL,
			user_id bigint(20) UNSIGNED NULL,
			email varchar(100) NULL,
			full_name varchar(100) NULL,
			response_count int(11) DEFAULT 0,
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at timestamp NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY uid (uid),
			KEY user_id (user_id)
		) $charset_collate;";

		dbDelta( $sql );
	}
}
