<?php
/**
 * Heatmap REST Controller
 *
 * @package PollQuest
 */

namespace PollQuest\Controllers;

use PollQuest\Services\HeatmapService;
use PollQuest\Utils\IpHelper;
use WP_Error;
use WP_REST_Request;

/**
 * Class HeatmapController
 */
class HeatmapController {

	/**
	 * @var string
	 */
	private $namespace = 'pollquest/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'heatmaps';

	/**
	 * @var HeatmapService
	 */
	private $service;

	public function __construct() {
		$this->service = new HeatmapService();
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'admin_check' ],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'admin_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/record',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'record_clicks' ],
					'permission_callback' => '__return_true',
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'admin_check' ],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'admin_check' ],
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'admin_check' ],
				],
			]
		);
	}

	/**
	 * Admin permission check.
	 *
	 * @return bool
	 */
	public function admin_check(): bool {
		return current_user_can( 'pollquest_view_results' );
	}

	/**
	 * List heatmaps.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$status = sanitize_text_field( $request->get_param( 'status' ) ?: 'all' );
		return rest_ensure_response( $this->service->list_heatmaps( $status ) );
	}

	/**
	 * Get single heatmap with click data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$result = $this->service->get_heatmap( (int) $request['id'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return rest_ensure_response( $result );
	}

	/**
	 * Create heatmap for a page.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$params  = $request->get_json_params() ?: $request->get_body_params();
		$page_id = (int) ( $params['page_id'] ?? 0 );

		if ( ! $page_id ) {
			return new WP_Error( 'missing_page_id', __( 'Page ID is required.', 'pollquest' ), [ 'status' => 400 ] );
		}

		$result = $this->service->create_heatmap( $page_id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Update heatmap status.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		$status = sanitize_text_field( $params['status'] ?? '' );

		if ( ! $status ) {
			return new WP_Error( 'missing_status', __( 'Status is required.', 'pollquest' ), [ 'status' => 400 ] );
		}

		$result = $this->service->update_status( (int) $request['id'], $status );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Delete heatmap.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$result = $this->service->delete_heatmap( (int) $request['id'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( [ 'success' => true ] );
	}

	/**
	 * Record clicks from frontend tracker.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function record_clicks( $request ) {
		$params     = $request->get_json_params() ?: $request->get_body_params();
		$heatmap_id = (int) ( $params['heatmap_id'] ?? 0 );
		$clicks     = $params['clicks'] ?? [];

		if ( ! $heatmap_id || ! is_array( $clicks ) ) {
			return new WP_Error( 'invalid_payload', __( 'Invalid click payload.', 'pollquest' ), [ 'status' => 400 ] );
		}

		$ip            = IpHelper::get_ip();
		$transient_key = 'pollquest_hm_rl_' . md5( $ip . '_' . $heatmap_id );
		$attempts      = (int) get_transient( $transient_key );

		if ( $attempts >= 30 ) {
			return new WP_Error( 'rate_limited', __( 'Too many requests.', 'pollquest' ), [ 'status' => 429 ] );
		}

		set_transient( $transient_key, $attempts + 1, HOUR_IN_SECONDS );

		$result = $this->service->record_clicks( $heatmap_id, $clicks );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( [ 'success' => true ] );
	}
}
