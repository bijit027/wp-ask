<?php
/**
 * Database Migrator.
 *
 * @package PollQuest
 */

namespace PollQuest\Database;

use PollQuest\Database\Migrations\CreateEmailSurveysTable;
use PollQuest\Database\Migrations\CreateHeatmapsTable;
use PollQuest\Database\Migrations\CreateMetaTable;
use PollQuest\Database\Migrations\CreatePostRatingsTable;
use PollQuest\Database\Migrations\CreateResponsesTable;
use PollQuest\Database\Migrations\CreateSessionsTable;
use PollQuest\Database\Migrations\CreateSurveysTable;

/**
 * Class Migrator
 */
class Migrator {

	/**
	 * Run all migrations.
	 */
	public static function run(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Rename old ipulse_* tables to pollquest_* if they exist (safety migration)
		self::rename_legacy_tables();

		$migrations = [
			new CreateSurveysTable(),
			new CreateResponsesTable(),
			new CreateSessionsTable(),
			new CreateMetaTable(),
			new CreatePostRatingsTable(),
			new CreateEmailSurveysTable(),
			new CreateHeatmapsTable(),
		];

		foreach ( $migrations as $migration ) {
			$migration->up();
		}

		update_option( 'pollquest_db_version', POLLQUEST_DB_VERSION );
	}

	/**
	 * Rename legacy ipulse_* tables to pollquest_* if they exist.
	 * This is a safety migration for installations that may have old tables.
	 */
	private static function rename_legacy_tables(): void {
		global $wpdb;

		$tables = [
			'ipulse_surveys' => 'pollquest_surveys',
			'ipulse_responses' => 'pollquest_responses',
			'ipulse_sessions' => 'pollquest_sessions',
			'ipulse_meta' => 'pollquest_meta',
			'ipulse_post_ratings' => 'pollquest_post_ratings',
			'ipulse_email_surveys' => 'pollquest_email_surveys',
			'ipulse_email_survey_responses' => 'pollquest_email_survey_responses',
			'ipulse_heatmaps' => 'pollquest_heatmaps',
			'ipulse_heatmap_recordings' => 'pollquest_heatmap_recordings',
		];

		foreach ( $tables as $old_name => $new_name ) {
			$old_table = $wpdb->prefix . $old_name;
			$new_table = $wpdb->prefix . $new_name;

			// Check if old table exists and new table doesn't
			$old_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $old_table ) );
			$new_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $new_table ) );

			if ( $old_exists && ! $new_exists ) {
				$wpdb->query( "RENAME TABLE {$old_table} TO {$new_table}" );
			}
		}
	}
}
