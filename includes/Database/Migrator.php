<?php
/**
 * Database Migrator.
 *
 * @package WPAsk
 */

namespace WPAsk\Database;

use WPAsk\Database\Migrations\CreateEmailSurveysTable;
use WPAsk\Database\Migrations\CreateHeatmapsTable;
use WPAsk\Database\Migrations\CreateMetaTable;
use WPAsk\Database\Migrations\CreatePostRatingsTable;
use WPAsk\Database\Migrations\CreateResponsesTable;
use WPAsk\Database\Migrations\CreateSessionsTable;
use WPAsk\Database\Migrations\CreateSurveysTable;

/**
 * Class Migrator
 */
class Migrator {

	/**
	 * Run all migrations.
	 */
	public static function run(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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

		update_option( 'wpask_db_version', WPASK_DB_VERSION );
	}
}
