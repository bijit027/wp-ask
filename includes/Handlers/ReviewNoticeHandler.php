<?php
/**
 * Review Notice Handler
 *
 * @package WPAsk
 */

namespace InsightPulse\Handlers;

/**
 * Class ReviewNoticeHandler
 *
 * Prompts admins to leave a 5-star review 14 days after activation.
 */
class ReviewNoticeHandler {

	/**
	 * Transient key for the activation timestamp.
	 */
	const ACTIVATION_KEY = 'wpask_activation_date';

	/**
	 * Option key to track if notice was dismissed.
	 */
	const DISMISSED_KEY = 'wpask_review_dismissed';

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_notices', [ $this, 'maybe_show_notice' ] );
		add_action( 'admin_init', [ $this, 'handle_dismissal' ] );
	}

	/**
	 * Set the activation date if not already set.
	 * Call this on plugin activation.
	 */
	public static function set_activation_date(): void {
		if ( ! get_option( self::ACTIVATION_KEY ) ) {
			update_option( self::ACTIVATION_KEY, time(), false );
		}
	}

	/**
	 * Show the review notice if 14 days have passed and it hasn't been dismissed.
	 */
	public function maybe_show_notice(): void {
		// Only show to admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if dismissed.
		if ( get_option( self::DISMISSED_KEY ) ) {
			return;
		}

		$activation_date = get_option( self::ACTIVATION_KEY );
		if ( ! $activation_date ) {
			return;
		}

		// Check if 14 days have passed.
		$days_since = ( time() - (int) $activation_date ) / DAY_IN_SECONDS;
		if ( $days_since < 14 ) {
			return;
		}

		$dismiss_url = wp_nonce_url(
			add_query_arg( 'wpask_dismiss_review', '1' ),
			'wpask_dismiss_review'
		);

		$review_url = 'https://wordpress.org/support/plugin/wpask/reviews/#new-post';

		echo '<div class="notice notice-info is-dismissible wpask-review-notice">';
		echo '<p>';
		echo '<strong>Enjoying WPAsk?</strong> You\'ve been using it for 14 days — we\'d love a ⭐⭐⭐⭐⭐ review to help others discover it!';
		echo '</p>';
		echo '<p>';
		printf( '<a href="%s" target="_blank" rel="noopener noreferrer" class="button button-primary">Leave a Review 🎉</a>&nbsp;&nbsp;', esc_url( $review_url ) );
		printf( '<a href="%s">No thanks, dismiss</a>', esc_url( $dismiss_url ) );
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Handle the dismissal action.
	 */
	public function handle_dismissal(): void {
		if ( isset( $_GET['wpask_dismiss_review'] ) && check_admin_referer( 'wpask_dismiss_review' ) ) {
			update_option( self::DISMISSED_KEY, true );
			wp_safe_redirect( remove_query_arg( 'wpask_dismiss_review' ) );
			exit;
		}
	}
}
