<?php
/**
 * Frontend Handler
 *
 * @package WPAsk
 */

namespace InsightPulse\Handlers;

use InsightPulse\Models\Survey;
use InsightPulse\Services\TargetingService;
use InsightPulse\Services\SessionService;
use InsightPulse\Utils\AssetLoader;

/**
 * Class FrontendHandler
 */
class FrontendHandler {

	/**
	 * @var TargetingService
	 */
	private $targeting_service;

	/**
	 * @var SessionService
	 */
	private $session_service;

	/**
	 * @var array
	 */
	private $current_config;

	public function __construct() {
		$this->targeting_service = new TargetingService();
		$this->session_service   = new SessionService();
	}

	/**
	 * Register frontend hooks.
	 */
	public function register(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_footer', [ $this, 'inject_widget_container' ] );
	}

	/**
	 * Conditionally enqueue the frontend widget scripts if an active survey matches targeting.
	 */
	public function enqueue_assets(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ipulse_surveys';
		
		$matched_survey = null;

		// 1. Check for Preview Mode
		if ( isset( $_GET['wpask_preview'] ) && current_user_can( 'insightpulse_manage_surveys' ) ) {
			$preview_id = (int) $_GET['wpask_preview'];
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $preview_id ) );
			if ( $row ) {
				$matched_survey = new Survey( $row );
			}
		}

		// 2. Normal flow if not previewing
		if ( ! $matched_survey ) {
			$now_utc = current_time( 'mysql', true );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status = 'publish' AND (publish_at IS NULL OR publish_at <= %s) ORDER BY id DESC", $now_utc ) );

			if ( empty( $results ) ) {
				return;
			}

			// Check targeting rules for each survey
			foreach ( $results as $row ) {
				$survey = new Survey( $row );

				// Check per-post override (UserFeedback feature)
				if ( $this->is_survey_disabled_on_post() ) {
					return;
				}
				$specific_survey_id = $this->get_specific_survey_for_post();
				if ( $specific_survey_id && (int) $specific_survey_id !== (int) $survey->id ) {
					continue;
				}

				if ( $this->targeting_service->should_display( $survey ) ) {
					$matched_survey = $survey;
					break; // Just load the first one that matches for now
				}
			}
		}

		if ( ! $matched_survey ) {
			return;
		}

		// 3. Initialize Session
		// We pass false so it doesn't force create unless they submit,
		// but if they already have one, we want its data.
		$session = $this->session_service->get_current_session( false );

		// 4. Enqueue Assets (Compiled via Vite in Phase 9/10)
		if ( ! AssetLoader::enqueue_frontend_script(
			'wpask-frontend',
			'src/frontend/wpask.js',
			'assets/frontend/frontend.js',
			'frontend'
		) ) {
			return;
		}

		// Inline config avoids page cache issues better than wp_localize_script
		$this->current_config = [
			'api_url'   => esc_url_raw( rest_url( 'insightpulse/v1' ) ),
			'survey'    => [
				'id'        => $matched_survey->id,
				'title'     => $matched_survey->title,
				'type'      => $matched_survey->type,
				'questions' => $matched_survey->questions,
				'settings'  => $matched_survey->settings,
				'targeting' => $matched_survey->targeting,
			],
			'session'   => $session ? [ 'uid' => $session->uid ] : null,
		];
	}

	/**
	 * Inject the mount point for the Vanilla JS widget.
	 */
	public function inject_widget_container(): void {
		// Only inject if script was enqueued (meaning a survey matched)
		if ( wp_script_is( 'wpask-frontend', 'enqueued' ) ) {
			// Get matched survey from class property if we stored it, or just refetch it (simplified)
			// Wait, we need the config. Let's build it here by storing it in a class property.
			if ( ! empty( $this->current_config ) ) {
				echo '<script>window.WPAskConfig = ' . wp_json_encode( $this->current_config ) . ';</script>';
			}
			echo '<!-- WPAsk Widget Container -->';
			echo '<div id="wpask-widget-root"></div>';
		}
	}

	/**
	 * Check if surveys are disabled on the current post via metabox.
	 *
	 * @return bool
	 */
	private function is_survey_disabled_on_post(): bool {
		if ( ! is_singular() ) {
			return false;
		}
		$disabled = get_post_meta( get_queried_object_id(), '_ipulse_disable_surveys', true );
		return ! empty( $disabled );
	}

	/**
	 * Check if a specific survey is forced on the current post via metabox.
	 *
	 * @return int|false
	 */
	private function get_specific_survey_for_post() {
		if ( ! is_singular() ) {
			return false;
		}
		$survey_id = get_post_meta( get_queried_object_id(), '_ipulse_show_specific_survey', true );
		return ! empty( $survey_id ) ? (int) $survey_id : false;
	}
}
