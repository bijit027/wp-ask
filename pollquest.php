<?php
/**
 * Plugin Name: PollQuest – Surveys & Feedback Forms for WordPress
 * Description: Create surveys, polls, and feedback forms for WordPress.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Bijit Deb
 * Author URI: https://github.com/bijit027
 * Plugin URI: https://github.com/bijit027/pollquest
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pollquest
 * Domain Path: /languages
 *
 * @package PollQuest
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POLLQUEST_VERSION', '1.0.0' );
define( 'POLLQUEST_PLUGIN_FILE', __FILE__ );
define( 'POLLQUEST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'POLLQUEST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'POLLQUEST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'POLLQUEST_DB_VERSION', '1.0.0' );

/**
 * Autoloader — PSR-4 style for PollQuest namespace.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'PollQuest\\';
	$base_dir = POLLQUEST_PLUGIN_DIR . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, function () {
	require_once POLLQUEST_PLUGIN_DIR . 'includes/Capabilities.php';
	\PollQuest\Capabilities::add();

	require_once POLLQUEST_PLUGIN_DIR . 'includes/Database/Migrator.php';
	\PollQuest\Database\Migrator::run();

	// Set activation date for review prompt.
	require_once POLLQUEST_PLUGIN_DIR . 'includes/Handlers/ReviewNoticeHandler.php';
	\PollQuest\Handlers\ReviewNoticeHandler::set_activation_date();

	set_transient( '_pollquest_activation_redirect', 1, 30 );
} );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'pollquest_email_summaries_cron' );
} );

/**
 * Boot the plugin on plugins_loaded.
 */
add_action( 'plugins_loaded', function () {
	\PollQuest\Plugin::boot();
} );
