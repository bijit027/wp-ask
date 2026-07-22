<?php
/**
 * Plugin Name:       WPAsk
 * Plugin URI:        https://wpask.io
 * Description:       Premium WordPress feedback & survey plugin. Understand why your visitors behave the way they do — not just what they do.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            WPAsk
 * Author URI:        https://wpask.io
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpask
 * Domain Path:       /languages
 *
 * @package WPAsk
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPASK_VERSION', '1.0.0' );
define( 'WPASK_PLUGIN_FILE', __FILE__ );
define( 'WPASK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPASK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPASK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPASK_DB_VERSION', '1.0.0' );

/**
 * Autoloader — PSR-4 style for WPAsk namespace.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'WPAsk\\';
	$base_dir = WPASK_PLUGIN_DIR . 'includes/';

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
	require_once WPASK_PLUGIN_DIR . 'includes/Capabilities.php';
	\WPAsk\Capabilities::add();

	require_once WPASK_PLUGIN_DIR . 'includes/Database/Migrator.php';
	\WPAsk\Database\Migrator::run();

	// Set activation date for review prompt.
	require_once WPASK_PLUGIN_DIR . 'includes/Handlers/ReviewNoticeHandler.php';
	\WPAsk\Handlers\ReviewNoticeHandler::set_activation_date();

	set_transient( '_wpask_activation_redirect', 1, 30 );
} );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'wpask_email_summaries_cron' );
} );

/**
 * Boot the plugin on plugins_loaded.
 */
add_action( 'plugins_loaded', function () {
	\WPAsk\Plugin::boot();
} );
