<?php
/**
 * Survey REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use InsightPulse\Repositories\SurveyRepository;
use InsightPulse\Validators\SurveyValidator;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class SurveyController
 */
class SurveyController {

	/**
	 * @var string
	 */
	private $namespace = 'insightpulse/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'surveys';

	/**
	 * @var SurveyRepository
	 */
	private $repository;

	public function __construct() {
		$this->repository = new SurveyRepository();
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
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
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function get_items_permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_create_edit_surveys' );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;

		$table    = $wpdb->prefix . 'ipulse_surveys';
		$status   = $request->get_param( 'status' ) ?: 'publish';
		$page     = (int) $request->get_param( 'page' ) ?: 1;
		$per_page = (int) $request->get_param( 'per_page' ) ?: 20;
		$offset   = ( $page - 1 ) * $per_page;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY id DESC LIMIT %d OFFSET %d", $status, $per_page, $offset );
		
		$results = $wpdb->get_results( $query );
		
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE status = %s", $status ) );

		// Decode JSON fields
		$items = [];
		foreach ( $results as $row ) {
			$item = (array) $row;
			$item['questions']     = json_decode( $item['questions'], true );
			$item['settings']      = json_decode( $item['settings'], true );
			$item['targeting']     = json_decode( $item['targeting'], true );
			$item['notifications'] = json_decode( $item['notifications'], true );
			$items[] = $item;
		}

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Check if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function create_item_permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_create_edit_surveys' );
	}

	/**
	 * Create one item from the request.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		$valid  = SurveyValidator::validate( $params );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$id = $this->repository->create( $valid );

		if ( ! $id ) {
			return new WP_Error( 'db_error', 'Failed to create survey.' );
		}

		$survey = $this->repository->find( $id );
		return rest_ensure_response( $survey );
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function get_item_permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_create_edit_surveys' );
	}

	/**
	 * Get one item from the request.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$id     = (int) $request['id'];
		$survey = $this->repository->find( $id );

		if ( ! $survey ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		return rest_ensure_response( $survey );
	}

	/**
	 * Check if a given request has access to update a specific item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function update_item_permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_create_edit_surveys' );
	}

	/**
	 * Update one item from the request.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$id = (int) $request['id'];
		
		if ( ! $this->repository->find( $id ) ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		$params = $request->get_json_params() ?: $request->get_body_params();
		$valid  = SurveyValidator::validate( $params );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$this->repository->update( $id, $valid );
		
		$survey = $this->repository->find( $id );
		return rest_ensure_response( $survey );
	}

	/**
	 * Check if a given request has access to delete a specific item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function delete_item_permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_delete_surveys' );
	}

	/**
	 * Delete one item from the request.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id = (int) $request['id'];
		
		if ( ! $this->repository->find( $id ) ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ipulse_surveys';
		
		$deleted = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		if ( ! $deleted ) {
			return new WP_Error( 'db_error', 'Failed to delete survey.' );
		}

		return rest_ensure_response( [ 'deleted' => true ] );
	}
}
