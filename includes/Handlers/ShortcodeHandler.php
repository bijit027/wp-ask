<?php
/**
 * Shortcode Handler
 *
 * @package WPAsk
 */

namespace InsightPulse\Handlers;

use InsightPulse\Models\Survey;
use InsightPulse\Utils\AssetLoader;

/**
 * Class ShortcodeHandler
 *
 * Registers shortcodes for embedding surveys and post ratings.
 */
class ShortcodeHandler {

	/**
	 * Register shortcode hooks.
	 */
	public function register(): void {
		add_shortcode( 'wpask', [ $this, 'render_shortcode' ] );
		add_shortcode( 'wpask_rating', [ $this, 'render_rating_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_shortcode_assets' ] );
	}

	/**
	 * Pre-enqueue assets when the current post contains our shortcodes.
	 */
	public function maybe_enqueue_shortcode_assets(): void {
		if ( is_admin() || ! is_singular() ) {
			return;
		}

		global $post;
		if ( ! $post || empty( $post->post_content ) ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'wpask_rating' ) ) {
			$this->enqueue_post_rating_script();
		}
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

		AssetLoader::enqueue_frontend_script(
			'wpask-frontend',
			'src/frontend/wpask.js',
			'assets/frontend/frontend.js',
			'shortcode'
		);

		$config = [
			'api_url' => esc_url_raw( rest_url( 'insightpulse/v1' ) ),
			'survey'  => [
				'id'        => $survey->id,
				'title'     => $survey->title,
				'type'      => $survey->type,
				'questions' => $survey->questions,
				'settings'  => $survey->settings,
				'targeting' => $survey->targeting,
			],
		];

		$container_id = 'wpask-widget-' . $id;

		return sprintf(
			'<div id="%1$s" class="wpask-shortcode-widget" data-survey-id="%2$d" data-wpask-config="%3$s"></div>',
			esc_attr( $container_id ),
			$id,
			esc_attr( wp_json_encode( $config ) )
		);
	}

	/**
	 * Render the [wpask_rating] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_rating_shortcode( $atts ): string {
		$atts = shortcode_atts(
			[
				'id'    => 0,
				'type'  => 'stars',
				'label' => __( 'Rate this post', 'wpask' ),
				'color' => '#6366f1',
			],
			$atts,
			'wpask_rating'
		);

		$post_id = $this->resolve_post_id( (int) $atts['id'] );
		$type    = in_array( $atts['type'], [ 'stars', 'thumbs' ], true ) ? $atts['type'] : 'stars';

		if ( ! $post_id || ! get_post( $post_id ) ) {
			return '';
		}

		$this->enqueue_post_rating_script();

		$config = [
			'api_url' => esc_url_raw( rest_url( 'insightpulse/v1' ) ),
			'post_id' => $post_id,
			'type'    => $type,
			'label'   => sanitize_text_field( $atts['label'] ),
			'color'   => sanitize_hex_color( $atts['color'] ) ?: '#6366f1',
		];

		$container_id = 'wpask-rating-' . $post_id . '-' . wp_unique_id();

		return sprintf(
			'<div id="%1$s" class="wpask-post-rating" data-post-id="%2$d" data-type="%3$s" data-wpask-config="%4$s"><span class="wpask-post-rating-fallback">%5$s</span></div>',
			esc_attr( $container_id ),
			$post_id,
			esc_attr( $type ),
			esc_attr( wp_json_encode( $config ) ),
			esc_html( $config['label'] )
		);
	}

	/**
	 * Resolve the post ID for rating shortcodes.
	 *
	 * @param int $requested_id Explicit ID from shortcode attributes.
	 * @return int
	 */
	private function resolve_post_id( int $requested_id ): int {
		if ( $requested_id > 0 ) {
			return $requested_id;
		}

		$post_id = (int) get_the_ID();
		if ( $post_id > 0 ) {
			return $post_id;
		}

		if ( is_singular() ) {
			return (int) get_queried_object_id();
		}

		global $post;
		return ( $post && ! empty( $post->ID ) ) ? (int) $post->ID : 0;
	}

	/**
	 * Enqueue the post rating frontend script once.
	 */
	private function enqueue_post_rating_script(): void {
		static $enqueued = false;

		if ( $enqueued ) {
			return;
		}

		$this->enqueue_post_rating_styles();

		AssetLoader::enqueue_frontend_script(
			'wpask-post-rating',
			'src/frontend/post-rating.js',
			'assets/post-rating/post-rating.js',
			'rating'
		);

		$enqueued = true;
	}

	/**
	 * Enqueue rating styles once.
	 */
	private function enqueue_post_rating_styles(): void {
		static $styled = false;

		if ( $styled ) {
			return;
		}

		wp_register_style( 'wpask-post-rating', false, [], INSIGHTPULSE_VERSION );
		wp_enqueue_style( 'wpask-post-rating' );
		wp_add_inline_style(
			'wpask-post-rating',
			'.wpask-post-rating{display:block;margin:16px 0;font-family:Inter,system-ui,sans-serif}.wpask-post-rating-fallback{font-size:14px;font-weight:600;color:#1a1d2b}'
		);

		$styled = true;
	}
}
