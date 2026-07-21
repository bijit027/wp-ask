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

define( 'INSIGHTPULSE_VERSION', '1.0.0' );
define( 'INSIGHTPULSE_PLUGIN_FILE', __FILE__ );
define( 'INSIGHTPULSE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INSIGHTPULSE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'INSIGHTPULSE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'INSIGHTPULSE_DB_VERSION', '1.0.0' );

/**
 * Autoloader — PSR-4 style for InsightPulse namespace.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'InsightPulse\\';
	$base_dir = INSIGHTPULSE_PLUGIN_DIR . 'includes/';

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
	require_once INSIGHTPULSE_PLUGIN_DIR . 'includes/Capabilities.php';
	\InsightPulse\Capabilities::add();

	require_once INSIGHTPULSE_PLUGIN_DIR . 'includes/Database/Migrator.php';
	\InsightPulse\Database\Migrator::run();

	set_transient( '_insightpulse_activation_redirect', 1, 30 );
} );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'insightpulse_email_summaries_cron' );
} );

/**
 * Boot the plugin on plugins_loaded.
 */
add_action( 'plugins_loaded', function () {
	\InsightPulse\Plugin::boot();
} );
