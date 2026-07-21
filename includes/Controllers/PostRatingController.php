<?php
/**
 * Post Rating REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use InsightPulse\Services\PostRatingService;
use InsightPulse\Utils\IpHelper;
use WP_Error;
use WP_REST_Request;

/**
 * Class PostRatingController
 */
class PostRatingController {

	/**
	 * @var string
	 */
	private $namespace = 'insightpulse/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'post-ratings';

	/**
	 * @var PostRatingService
	 */
	private $service;

	public function __construct() {
		$this->service = new PostRatingService();
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<post_id>[\d]+)',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => '__return_true',
					'args'                => [
						'post_id' => [
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						],
						'type'    => [
							'type'              => 'string',
							'default'           => 'stars',
							'sanitize_callback' => [ $this, 'sanitize_type' ],
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	/**
	 * Sanitize widget type.
	 *
	 * @param string $type Widget type.
	 * @return string
	 */
	public function sanitize_type( string $type ): string {
		return in_array( $type, [ 'stars', 'thumbs' ], true ) ? $type : 'stars';
	}

	/**
	 * Get rating summary for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$post_id = (int) $request['post_id'];
		$type    = $this->sanitize_type( (string) $request->get_param( 'type' ) );
		$result  = $this->service->get_summary( $post_id, $type );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Submit a post rating.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$params  = $request->get_json_params() ?: $request->get_body_params();
		$post_id = (int) ( $params['post_id'] ?? 0 );
		$rating  = isset( $params['rating'] ) ? (int) $params['rating'] : -1;
		$type    = $this->sanitize_type( (string) ( $params['type'] ?? 'stars' ) );

		if ( ! $post_id ) {
			return new WP_Error( 'missing_post_id', __( 'Post ID is required.', 'wpask' ), [ 'status' => 400 ] );
		}

		$ip            = IpHelper::get_ip();
		$transient_key = 'wpask_post_rating_rl_' . md5( $ip . '_' . $post_id );
		$attempts      = (int) get_transient( $transient_key );

		if ( $attempts >= 10 ) {
			return new WP_Error( 'rate_limited', __( 'Too many rating attempts. Please try again later.', 'wpask' ), [ 'status' => 429 ] );
		}

		set_transient( $transient_key, $attempts + 1, HOUR_IN_SECONDS );

		$result = $this->service->submit( $post_id, $rating, $type );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			[
				'success' => true,
				'summary' => $result,
			]
		);
	}
}
