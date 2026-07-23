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
}
