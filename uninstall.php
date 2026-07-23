<?php
/**
 * Uninstall routine. Called when the plugin is deleted from WP admin.
 *
 * @package PollQuest
 */

// Only run when WordPress calls this directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Remove all plugin options.
$options = [
	'pollquest_db_version',
	'pollquest_settings',
	'pollquest_onboarding_complete',
	'pollquest_capabilities_added',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// Remove custom capabilities from all roles.
foreach ( wp_roles()->roles as $role_name => $role_info ) {
	$role = get_role( $role_name );
	if ( $role ) {
		$role->remove_cap( 'pollquest_create_edit_surveys' );
		$role->remove_cap( 'pollquest_delete_surveys' );
		$role->remove_cap( 'pollquest_view_results' );
		$role->remove_cap( 'pollquest_save_settings' );
	}
}

// Check if user selected "Delete all data on uninstall".
$settings = get_option( 'pollquest_settings', [] );
if ( ! empty( $settings['delete_data_on_uninstall'] ) ) {
	$tables = [
		$wpdb->prefix . 'pollquest_surveys',
		$wpdb->prefix . 'pollquest_responses',
		$wpdb->prefix . 'pollquest_sessions',
		$wpdb->prefix . 'pollquest_meta',
		$wpdb->prefix . 'pollquest_post_ratings',
		$wpdb->prefix . 'pollquest_email_surveys',
		$wpdb->prefix . 'pollquest_email_survey_responses',
	];

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}
}
