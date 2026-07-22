<?php
/**
 * Heatmap Frontend Handler
 *
 * @package WPAsk
 */

namespace WPAsk\Handlers;

use WPAsk\Services\HeatmapService;
use WPAsk\Utils\AssetLoader;

/**
 * Class HeatmapHandler
 *
 * Enqueues click-tracking script on pages with active heatmaps.
 */
class HeatmapHandler {

	/**
	 * @var HeatmapService
	 */
	private $service;

	/**
	 * @var array<string, mixed>|null
	 */
	private $tracking_config;

	public function __construct() {
		$this->service = new HeatmapService();
	}

	/**
	 * Register hooks.
	 */
	public function register(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_tracker' ] );
		add_action( 'wp_footer', [ $this, 'inject_config' ], 5 );
	}

	/**
	 * Enqueue heatmap tracker when the current page is tracked.
	 */
	public function maybe_enqueue_tracker(): void {
		if ( ! is_singular() ) {
			return;
		}

		$page_id = get_queried_object_id();
		if ( ! $page_id ) {
			return;
		}

		$config = $this->service->get_tracking_config( $page_id );
		if ( ! $config ) {
			return;
		}

		$this->tracking_config = $config;

		AssetLoader::enqueue_frontend_script(
			'wpask-heatmap',
			'src/frontend/heatmap.js',
			'assets/heatmap/heatmap.js',
			'heatmap'
		);
	}

	/**
	 * Inject tracking config in footer.
	 */
	public function inject_config(): void {
		if ( empty( $this->tracking_config ) || ! wp_script_is( 'wpask-heatmap', 'enqueued' ) ) {
			return;
		}

		echo '<script>window.WPAskHeatmapConfig = ' . wp_json_encode( $this->tracking_config ) . ';</script>';
	}
}
