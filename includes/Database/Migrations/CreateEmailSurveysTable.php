<?php
/**
 * Create Email Surveys Table Migration.
 *
 * @package InsightPulse
 */

namespace WPAsk\Database\Migrations;

/**
 * Class CreateEmailSurveysTable
 */
class CreateEmailSurveysTable {

	/**
	 * Run the migration.
	 */
	public function up(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ipulse_email_surveys';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			slug varchar(255) NOT NULL,
			secret_key varchar(64) NOT NULL,
			title varchar(255) NOT NULL,
			link_text varchar(255) DEFAULT 'How would you rate this email?',
			status enum('publish', 'draft', 'trash') DEFAULT 'draft',
			rating_options longtext,
			thank_you_message varchar(255) DEFAULT NULL,
			collect_feedback tinyint(1) DEFAULT 1,
			feedback_field_label varchar(255) DEFAULT NULL,
			settings text,
			notifications text,
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at timestamp NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			UNIQUE KEY secret_key (secret_key)
		) $charset_collate;";

		dbDelta( $sql );

		// Create the responses table for email surveys as well.
		$table_name_responses = $wpdb->prefix . 'ipulse_email_survey_responses';

		$sql_responses = "CREATE TABLE $table_name_responses (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			survey_id bigint(20) UNSIGNED NOT NULL,
			rating tinyint(3) UNSIGNED NOT NULL,
			feedback text NULL,
			ip_address varchar(45) NULL,
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY survey_id (survey_id)
		) $charset_collate;";

		dbDelta( $sql_responses );
	}
}
