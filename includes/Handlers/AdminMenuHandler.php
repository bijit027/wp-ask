<?php
/**
 * Admin Menu Handler
 *
 * @package WPAsk
 */

namespace WPAsk\Handlers;

/**
 * Class AdminMenuHandler
 */
class AdminMenuHandler {

	/**
	 * Register the admin menu page.
	 */
	public function register(): void {
		$hook = add_menu_page(
			__( 'WPAsk', 'wpask' ),           // Page title
			__( 'WPAsk', 'wpask' ),           // Menu title
			'wpask_create_edit_surveys', // Capability required
			'wpask',                            // Menu slug
			[ $this, 'render_admin_page' ],     // Callback
			'dashicons-feedback',               // Icon
			30                                  // Position
		);

		add_action( "admin_enqueue_scripts", function ( $page ) use ( $hook ) {
			if ( $page !== $hook ) {
				return;
			}

			// In development, load from Vite dev server if running
			// In production, load from assets folder
			$is_dev = false; // Set to false for production build
			
			if ( $is_dev ) {
				wp_enqueue_script( 'wpask-admin-vite', 'http://localhost:5173/@vite/client', [], null, true );
				wp_enqueue_script( 'wpask-admin-app', 'http://localhost:5173/src/admin/main.js', [], null, true );
				
				// Add type="module" to these scripts
				add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
					if ( in_array( $handle, [ 'wpask-admin-vite', 'wpask-admin-app' ] ) ) {
						return '<script type="module" src="' . esc_url( $src ) . '"></script>';
					}
					return $tag;
				}, 10, 3 );
			} else {
				$script_url = WPASK_PLUGIN_URL . 'assets/admin/admin.js';

				if ( file_exists( WPASK_PLUGIN_DIR . 'assets/admin/admin.js' ) ) {
					wp_enqueue_script( 'wpask-admin-app', $script_url, [], WPASK_VERSION, true );
					
					// Add type="module" to the script
					add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
						if ( $handle === 'wpask-admin-app' ) {
							return '<script type="module" src="' . esc_url( $src ) . '"></script>';
						}
						return $tag;
					}, 10, 3 );
				}
				
				// Load CSS from main directory (where Vite puts it)
				$css_files = glob( WPASK_PLUGIN_DIR . 'assets/main/*.css' );
				if ( ! empty( $css_files ) ) {
					foreach ( $css_files as $css_file ) {
						$css_url = WPASK_PLUGIN_URL . 'assets/main/' . basename( $css_file );
						wp_enqueue_style( 'wpask-admin-style-' . md5( basename( $css_file ) ), $css_url, [], WPASK_VERSION );
					}
				}
				
				// Also check for CSS in admin directory
				$admin_css = glob( WPASK_PLUGIN_DIR . 'assets/admin/*.css' );
				if ( ! empty( $admin_css ) ) {
					foreach ( $admin_css as $css_file ) {
						$css_url = WPASK_PLUGIN_URL . 'assets/admin/' . basename( $css_file );
						wp_enqueue_style( 'wpask-admin-style-admin-' . md5( basename( $css_file ) ), $css_url, [], WPASK_VERSION );
					}
				}
			}

			// Localize config
			wp_localize_script( 'wpask-admin-app', 'WPAskAdminConfig', [
				'api_url' => esc_url_raw( rest_url( 'wpask/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			] );
		} );
	}

	/**
	 * Render the Vue app container.
	 */
	public function render_admin_page(): void {
		echo '<div class="wrap"><div id="wpask-admin-app"></div></div>';
	}
}
