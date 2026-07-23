<?php
/**
 * Main Plugin bootstrap class. Binds all handlers and registers REST routes.
 *
 * @package PollQuest
 */

namespace PollQuest;

use PollQuest\Handlers\AdminMenuHandler;
use PollQuest\Handlers\ActivationHandler;
use PollQuest\Handlers\FrontendHandler;
use PollQuest\Handlers\MetaboxHandler;
use PollQuest\Handlers\ShortcodeHandler;
use PollQuest\Handlers\ReviewNoticeHandler;
use PollQuest\Handlers\HeatmapHandler;
use PollQuest\Controllers\SurveyController;
use PollQuest\Controllers\ResponseController;
use PollQuest\Controllers\ResultsController;
use PollQuest\Controllers\SettingsController;
use PollQuest\Controllers\TemplateController;
use PollQuest\Controllers\FrontendController;
use PollQuest\Controllers\LogicController;
use PollQuest\Controllers\AddonsController;
use PollQuest\Controllers\PostRatingController;
use PollQuest\Controllers\HeatmapController;
use PollQuest\Database\Migrator;

/**
 * Class Plugin
 *
 * Central bootstrapper. Instantiated once via plugins_loaded.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Boot the plugin.
	 *
	 * @return self
	 */
	public static function boot(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Private constructor — use ::boot().
	 */
	private function __construct() {}

	/**
	 * Run migrations (incremental), register all hooks.
	 */
	private function init(): void {
		// Run DB upgrades if version changed.
		$this->maybe_run_migrations();

		// Register REST API routes.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Register admin menu.
		add_action( 'admin_menu', [ new AdminMenuHandler(), 'register' ] );

		// Handle activation redirect.
		add_action( 'admin_init', [ new ActivationHandler(), 'maybe_redirect' ] );

		// Inject widget on frontend.
		( new FrontendHandler() )->register();

		// Shortcode support.
		( new ShortcodeHandler() )->register();

		// Heatmap click tracking.
		( new HeatmapHandler() )->register();

		// Post metabox and admin notices.
		if ( is_admin() ) {
			( new MetaboxHandler() )->register();
			( new ReviewNoticeHandler() )->register();
			add_action( 'wp_dashboard_setup', [ $this, 'register_dashboard_widget' ] );
		}

		// Email Notifications.
		add_action( 'pollquest_response_saved', [ $this, 'trigger_email_notifications' ], 10, 4 );
	}

	/**
	 * Trigger email notifications on new response.
	 */
	public function trigger_email_notifications( $response_id, $survey_id, $answers, $context ): void {
		$survey   = ( new \PollQuest\Repositories\SurveyRepository() )->find( $survey_id );
		$response = ( new \PollQuest\Repositories\ResponseRepository() )->find( $response_id );

		if ( $survey && $response && ! empty( $survey->notifications->email->active ) ) {
			$notification = new \PollQuest\Emails\ResponseNotification( $survey, $response );
			$notification->maybe_send();
		}
	}

	/**
	 * Run DB migrations if DB version is behind.
	 */
	private function maybe_run_migrations(): void {
		$current = get_option( 'pollquest_db_version', '0.0.0' );
		if ( version_compare( $current, POLLQUEST_DB_VERSION, '<' ) ) {
			Migrator::run();
		}
	}

	/**
	 * Register all REST API routes via controllers.
	 */
	public function register_rest_routes(): void {
		( new SurveyController() )->register_routes();
		( new ResponseController() )->register_routes();
		( new ResultsController() )->register_routes();
		( new SettingsController() )->register_routes();
		( new TemplateController() )->register_routes();
		( new FrontendController() )->register_routes();
		( new LogicController() )->register_routes();
		( new AddonsController() )->register_routes();
		( new PostRatingController() )->register_routes();
		( new HeatmapController() )->register_routes();
	}

	/**
	 * Register the WP admin dashboard widget.
	 */
	public function register_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'pollquest_dashboard_widget',
			__( 'PollQuest — Recent Activity', 'pollquest' ),
			[ $this, 'render_dashboard_widget' ]
		);
	}

	/**
	 * Render the dashboard widget content.
	 */
	public function render_dashboard_widget(): void {
		$builder_url = admin_url( 'admin.php?page=pollquest#/surveys/new' );
		echo '<div id="pollquest-dashboard-widget" data-builder-url="' . esc_url( $builder_url ) . '"></div>';
	}
}
