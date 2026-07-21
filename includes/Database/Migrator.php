<?php
/**
 * Database Migrator.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Database;

use InsightPulse\Database\Migrations\CreateEmailSurveysTable;
use InsightPulse\Database\Migrations\CreateHeatmapsTable;
use InsightPulse\Database\Migrations\CreateMetaTable;
use InsightPulse\Database\Migrations\CreatePostRatingsTable;
use InsightPulse\Database\Migrations\CreateResponsesTable;
use InsightPulse\Database\Migrations\CreateSessionsTable;
use InsightPulse\Database\Migrations\CreateSurveysTable;

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

		update_option( 'insightpulse_db_version', INSIGHTPULSE_DB_VERSION );
	}
}
