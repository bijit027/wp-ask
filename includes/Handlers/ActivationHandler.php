<?php
/**
 * Activation Handler
 *
 * @package PollQuest
 */

namespace PollQuest\Handlers;

/**
 * Class ActivationHandler
 */
class ActivationHandler {

	/**
	 * Handle redirect to onboarding wizard on first activation.
	 */
	public function maybe_redirect(): void {
		if ( ! get_transient( '_pollquest_activation_redirect' ) ) {
			return;
		}

		delete_transient( '_pollquest_activation_redirect' );

		// Do not redirect if activating multiple plugins or on network activate
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) {
			return;
		}

		$is_onboarding_complete = get_option( 'pollquest_onboarding_complete', false );

		if ( $is_onboarding_complete ) {
			wp_safe_redirect( admin_url( 'admin.php?page=pollquest' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=pollquest#/onboarding' ) );
		exit;
	}
}
