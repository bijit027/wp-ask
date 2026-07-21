<?php
/**
 * Uninstall routine. Called when the plugin is deleted from WP admin.
 *
 * @package InsightPulse
 */

// Only run when WordPress calls this directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Remove all plugin options.
$options = [
	'insightpulse_db_version',
	'insightpulse_settings',
	'insightpulse_onboarding_complete',
	'insightpulse_capabilities_added',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// Remove custom capabilities from all roles.
foreach ( wp_roles()->roles as $role_name => $role_info ) {
	$role = get_role( $role_name );
	if ( $role ) {
		$role->remove_cap( 'insightpulse_create_edit_surveys' );
		$role->remove_cap( 'insightpulse_delete_surveys' );
		$role->remove_cap( 'insightpulse_view_results' );
		$role->remove_cap( 'insightpulse_save_settings' );
	}
}

// Check if user selected "Delete all data on uninstall".
$settings = get_option( 'insightpulse_settings', [] );
if ( ! empty( $settings['delete_data_on_uninstall'] ) ) {
	$tables = [
		$wpdb->prefix . 'ipulse_surveys',
		$wpdb->prefix . 'ipulse_responses',
		$wpdb->prefix . 'ipulse_sessions',
		$wpdb->prefix . 'ipulse_meta',
		$wpdb->prefix . 'ipulse_post_ratings',
		$wpdb->prefix . 'ipulse_email_surveys',
		$wpdb->prefix . 'ipulse_email_survey_responses',
	];

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}
}
