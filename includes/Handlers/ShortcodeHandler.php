<?php
/**
 * Shortcode Handler
 *
 * @package WPAsk
 */

namespace InsightPulse\Handlers;

use InsightPulse\Models\Survey;

/**
 * Class ShortcodeHandler
 *
 * Registers the [wpask] shortcode for embedding surveys inside post content.
 */
class ShortcodeHandler {

	/**
	 * Register shortcode hooks.
	 */
	public function register(): void {
		add_shortcode( 'wpask', [ $this, 'render_shortcode' ] );
	}

	/**
	 * Render the [wpask id="X"] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ): string {
		$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'wpask' );
		$id   = (int) $atts['id'];

		if ( ! $id ) {
			return '';
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ipulse_surveys';
		$now   = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d AND status = 'publish' AND (publish_at IS NULL OR publish_at <= %s)",
				$id,
				$now
			)
		);

		if ( ! $row ) {
			return '';
		}

		$survey = new Survey( $row );

		// Enqueue the frontend script
		$is_dev = true; // Toggle for dev mode
		if ( $is_dev ) {
			wp_enqueue_script( 'vite-client-shortcode', 'http://localhost:5173/@vite/client', [], null );
			wp_enqueue_script( 'wpask-frontend', 'http://localhost:5173/src/frontend/wpask.js', [], null );
			add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
				if ( in_array( $handle, [ 'wpask-frontend', 'vite-client-shortcode' ], true ) ) {
					return '<script type="module" src="' . esc_url( $src ) . '"></script>';
				}
				return $tag;
			}, 10, 3 );
		} else {
			$script_url = INSIGHTPULSE_PLUGIN_URL . 'assets/frontend/wpask.js';
			if ( file_exists( INSIGHTPULSE_PLUGIN_DIR . 'assets/frontend/wpask.js' ) ) {
				wp_enqueue_script( 'wpask-frontend', $script_url, [], INSIGHTPULSE_VERSION, true );
			}
		}

		// Build inline config for this specific survey
		$config = [
			'api_url'   => esc_url_raw( rest_url( 'insightpulse/v1' ) ),
			'survey'    => [
				'id'        => $survey->id,
				'title'     => $survey->title,
				'type'      => $survey->type,
				'questions' => $survey->questions,
				'settings'  => $survey->settings,
				'targeting' => $survey->targeting,
			],
		];

		// Output the container and config.
		// The unique ID allows multiple shortcodes on one page.
		$container_id = 'wpask-widget-' . $id;

		return sprintf(
			'<script>window.WPAskConfig_%1$d = %2$s; window.WPAskShortcodeTargets = window.WPAskShortcodeTargets || {}; window.WPAskShortcodeTargets[%1$d] = document.getElementById && document.getElementById("%3$s");</script><div id="%3$s" class="wpask-shortcode-widget" data-survey-id="%1$d"></div>',
			$id,
			wp_json_encode( $config ),
			esc_attr( $container_id )
		);
	}
}
