<?php
/**
 * Create Meta Table Migration.
 *
 * @package PollQuest
 */

namespace PollQuest\Database\Migrations;

/**
 * Class CreateMetaTable
 */
class CreateMetaTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'pollquest_meta';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			object_type varchar(50) NOT NULL,
			object_id bigint(20) UNSIGNED NOT NULL,
			meta_key varchar(100) NOT NULL,
			meta_value longtext,
			updated_at timestamp NULL,
			PRIMARY KEY  (id),
			KEY object_lookup (object_type, object_id, meta_key)
		) $charset_collate;";

		dbDelta( $sql );
	}
}
