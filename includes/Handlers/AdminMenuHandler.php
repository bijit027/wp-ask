<?php
/**
 * Admin Menu Handler
 *
 * @package PollQuest
 */

namespace PollQuest\Handlers;

/**
 * Class AdminMenuHandler
 */
class AdminMenuHandler {

	/**
	 * Register the admin menu page.
	 */
	public function register(): void {
		$hook = add_menu_page(
			__( 'PollQuest', 'pollquest' ),           // Page title
			__( 'PollQuest', 'pollquest' ),           // Menu title
			'pollquest_create_edit_surveys', // Capability required
			'pollquest',                            // Menu slug
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
				wp_enqueue_script( 'pollquest-admin-vite', 'http://localhost:5173/@vite/client', [], null, true );
				wp_enqueue_script( 'pollquest-admin-app', 'http://localhost:5173/src/admin/main.js', [], null, true );
				
				// Add type="module" to these scripts
				add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
					if ( in_array( $handle, [ 'pollquest-admin-vite', 'pollquest-admin-app' ] ) ) {
						$tag = str_replace( ' src=', ' type="module" src=', $tag );
					}
					return $tag;
				}, 10, 3 );
			} else {
				$script_url = POLLQUEST_PLUGIN_URL . 'assets/admin/admin.js';

				if ( file_exists( POLLQUEST_PLUGIN_DIR . 'assets/admin/admin.js' ) ) {
					wp_enqueue_script( 'pollquest-admin-app', $script_url, [], POLLQUEST_VERSION, true );
					
					// Add type="module" to the script
					add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
						if ( $handle === 'pollquest-admin-app' ) {
							$tag = str_replace( ' src=', ' type="module" src=', $tag );
						}
						return $tag;
					}, 10, 3 );
				}
				
				// Load CSS from main directory (where Vite puts it)
				$css_files = glob( POLLQUEST_PLUGIN_DIR . 'assets/main/*.css' );
				if ( ! empty( $css_files ) ) {
					foreach ( $css_files as $css_file ) {
						$css_url = POLLQUEST_PLUGIN_URL . 'assets/main/' . basename( $css_file );
						wp_enqueue_style( 'pollquest-admin-style-' . md5( basename( $css_file ) ), $css_url, [], POLLQUEST_VERSION );
					}
				}
				
				// Also check for CSS in admin directory
				$admin_css = glob( POLLQUEST_PLUGIN_DIR . 'assets/admin/*.css' );
				if ( ! empty( $admin_css ) ) {
					foreach ( $admin_css as $css_file ) {
						$css_url = POLLQUEST_PLUGIN_URL . 'assets/admin/' . basename( $css_file );
						wp_enqueue_style( 'pollquest-admin-style-admin-' . md5( basename( $css_file ) ), $css_url, [], POLLQUEST_VERSION );
					}
				}
			}

			// Localize config
			wp_localize_script( 'pollquest-admin-app', 'PollQuestAdminConfig', [
				'api_url' => esc_url_raw( rest_url( 'pollquest/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			] );
		} );
	}

	/**
	 * Render the Vue app container.
	 */
	public function render_admin_page(): void {
		echo '<div class="wrap"><div id="pollquest-admin-app"></div></div>';
	}
}
