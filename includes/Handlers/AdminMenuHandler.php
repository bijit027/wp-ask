<?php
/**
 * Admin Menu Handler
 *
 * @package WPAsk
 */

namespace InsightPulse\Handlers;

/**
 * Class AdminMenuHandler
 */
class AdminMenuHandler {

	/**
	 * Register the admin menu page.
	 */
	public function register(): void {
		add_menu_page(
			__( 'WPAsk', 'wpask' ),           // Page title
			__( 'WPAsk', 'wpask' ),           // Menu title
			'insightpulse_create_edit_surveys', // Capability required
			'wpask',                            // Menu slug
			[ $this, 'render_admin_page' ],     // Callback
			'dashicons-feedback',               // Icon
			30                                  // Position
		);
	}

	/**
	 * Render the Vue app container.
	 */
	public function render_admin_page(): void {
		echo '<div class="wrap"><div id="wpask-admin-app"></div></div>';
	}
}
