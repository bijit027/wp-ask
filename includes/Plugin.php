<?php
/**
 * Main Plugin bootstrap class. Binds all handlers and registers REST routes.
 *
 * @package WPAsk
 */

namespace WPAsk;

use WPAsk\Handlers\AdminMenuHandler;
use WPAsk\Handlers\ActivationHandler;
use WPAsk\Handlers\FrontendHandler;
use WPAsk\Handlers\MetaboxHandler;
use WPAsk\Handlers\ShortcodeHandler;
use WPAsk\Handlers\ReviewNoticeHandler;
use WPAsk\Handlers\HeatmapHandler;
use WPAsk\Controllers\SurveyController;
use WPAsk\Controllers\ResponseController;
use WPAsk\Controllers\ResultsController;
use WPAsk\Controllers\SettingsController;
use WPAsk\Controllers\TemplateController;
use WPAsk\Controllers\FrontendController;
use WPAsk\Controllers\LogicController;
use WPAsk\Controllers\AddonsController;
use WPAsk\Controllers\PostRatingController;
use WPAsk\Controllers\HeatmapController;
use WPAsk\Database\Migrator;

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
	 * Run migrations (incremental), load textdomain, register all hooks.
	 */
	private function init(): void {
		// Run DB upgrades if version changed.
		$this->maybe_run_migrations();

		// Load translations.
		add_action( 'init', [ $this, 'load_textdomain' ] );

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
		add_action( 'wpask_response_saved', [ $this, 'trigger_email_notifications' ], 10, 4 );
	}

	/**
	 * Trigger email notifications on new response.
	 */
	public function trigger_email_notifications( $response_id, $survey_id, $answers, $context ): void {
		$survey   = ( new \WPAsk\Repositories\SurveyRepository() )->find( $survey_id );
		$response = ( new \WPAsk\Repositories\ResponseRepository() )->find( $response_id );

		if ( $survey && $response && ! empty( $survey->notifications->email->active ) ) {
			$notification = new \WPAsk\Emails\ResponseNotification( $survey, $response );
			$notification->maybe_send();
		}
	}

	/**
	 * Run DB migrations if DB version is behind.
	 */
	private function maybe_run_migrations(): void {
		$current = get_option( 'wpask_db_version', '0.0.0' );
		if ( version_compare( $current, WPASK_DB_VERSION, '<' ) ) {
			Migrator::run();
		}
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'wpask',
			false,
			dirname( WPASK_PLUGIN_BASENAME ) . '/languages'
		);
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
			'wpask_dashboard_widget',
			__( 'WPAsk — Recent Activity', 'wpask' ),
			[ $this, 'render_dashboard_widget' ]
		);
	}

	/**
	 * Render the dashboard widget content.
	 */
	public function render_dashboard_widget(): void {
		$builder_url = admin_url( 'admin.php?page=wpask#/surveys/new' );
		echo '<div id="wpask-dashboard-widget" data-builder-url="' . esc_url( $builder_url ) . '"></div>';
	}
}
