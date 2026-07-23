<?php
/**
 * Survey REST Controller
 *
 * @package PollQuest
 */

namespace PollQuest\Controllers;

use PollQuest\Repositories\SurveyRepository;
use PollQuest\Validators\SurveyValidator;
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
	private $namespace = 'pollquest/v1';

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
		// Collection: GET all, POST create
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

		// Single survey: GET, PUT, DELETE
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

		// Trash a survey (soft delete)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/trash',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'trash_item' ],
				'permission_callback' => [ $this, 'delete_item_permissions_check' ],
			]
		);

		// Restore a trashed survey
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/restore',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'restore_item' ],
				'permission_callback' => [ $this, 'delete_item_permissions_check' ],
			]
		);

		// Duplicate a survey
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/duplicate',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'duplicate_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
			]
		);

		// Record an impression from frontend widget
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/impression',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'record_impression' ],
				'permission_callback' => function($request) {
					$survey = $this->repository->find(absint($request['id']));
					return $survey && $survey->status === 'publish';
				},
			]
		);

		// Bulk status update
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-status',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'bulk_status' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
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
		return current_user_can( 'pollquest_create_edit_surveys' );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;

		$table    = $wpdb->prefix . 'pollquest_surveys';
		$status   = sanitize_text_field( $request->get_param( 'status' ) ?: 'all' );
		$page     = (int) $request->get_param( 'page' ) ?: 1;
		$per_page = (int) $request->get_param( 'per_page' ) ?: 50;
		$offset   = ( $page - 1 ) * $per_page;

		// Build WHERE clause based on status
		if ( 'all' === $status ) {
			// "All" tab: show published + draft, but NOT trash
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status != 'trash' ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = $wpdb->get_var( "SELECT COUNT(id) FROM {$table} WHERE status != 'trash'" );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY id DESC LIMIT %d OFFSET %d", $status, $per_page, $offset ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE status = %s", $status ) );
		}

		// Decode JSON fields
		$items = [];
		foreach ( $results as $row ) {
			$item                  = (array) $row;
			$item['questions']     = json_decode( $item['questions'], true );
			$item['settings']      = json_decode( $item['settings'], true );
			$item['targeting']     = json_decode( $item['targeting'], true );
			$item['notifications'] = json_decode( $item['notifications'], true );
			$items[]               = $item;
		}

		// Get per-status counts for tab badges
		$counts = [];
		foreach ( [ 'publish', 'draft', 'trash' ] as $s ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$counts[ $s ] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE status = %s", $s ) );
		}
		$counts['all'] = $counts['publish'] + $counts['draft'];

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) ceil( $total / $per_page ) );
		$response->header( 'X-WP-StatusCounts', wp_json_encode( $counts ) );

		return $response;
	}


	/**
	 * Check if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function create_item_permissions_check( $request ): bool {
		return current_user_can( 'pollquest_create_edit_surveys' );
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
		return current_user_can( 'pollquest_create_edit_surveys' );
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
		return current_user_can( 'pollquest_create_edit_surveys' );
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
		return current_user_can( 'pollquest_delete_surveys' );
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
		$table = $wpdb->prefix . 'pollquest_surveys';
		
		$deleted = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		if ( ! $deleted ) {
			return new WP_Error( 'db_error', 'Failed to delete survey.' );
		}

		return rest_ensure_response( [ 'deleted' => true ] );
	}

	/**
	 * Soft-delete: move survey to trash.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function trash_item( $request ) {
		$id = (int) $request['id'];

		if ( ! $this->repository->find( $id ) ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		global $wpdb;
		$table   = $wpdb->prefix . 'pollquest_surveys';
		$updated = $wpdb->update( $table, [ 'status' => 'trash' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );

		if ( false === $updated ) {
			return new WP_Error( 'db_error', 'Failed to trash survey.' );
		}

		return rest_ensure_response( [ 'trashed' => true ] );
	}

	/**
	 * Restore a trashed survey back to draft.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function restore_item( $request ) {
		$id = (int) $request['id'];

		if ( ! $this->repository->find( $id ) ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		global $wpdb;
		$table   = $wpdb->prefix . 'pollquest_surveys';
		$updated = $wpdb->update( $table, [ 'status' => 'draft' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );

		if ( false === $updated ) {
			return new WP_Error( 'db_error', 'Failed to restore survey.' );
		}

		return rest_ensure_response( [ 'restored' => true, 'status' => 'draft' ] );
	}

	/**
	 * Duplicate a survey (deep copy — title gets "Copy of " prefix, status becomes draft).
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate_item( $request ) {
		$id     = (int) $request['id'];
		$survey = $this->repository->find( $id );

		if ( ! $survey ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'pollquest_surveys';

		$new_data = [
			'title'         => 'Copy of ' . $survey->title,
			'type'          => $survey->type,
			'status'        => 'draft',
			'questions'     => is_string( $survey->questions ) ? $survey->questions : wp_json_encode( $survey->questions ),
			'settings'      => is_string( $survey->settings ) ? $survey->settings : wp_json_encode( $survey->settings ),
			'targeting'     => is_string( $survey->targeting ) ? $survey->targeting : wp_json_encode( $survey->targeting ),
			'notifications' => is_string( $survey->notifications ) ? $survey->notifications : wp_json_encode( $survey->notifications ),
			'created_at'    => current_time( 'mysql', true ),
			'updated_at'    => current_time( 'mysql', true ),
		];

		$inserted = $wpdb->insert( $table, $new_data );

		if ( ! $inserted ) {
			return new WP_Error( 'db_error', 'Failed to duplicate survey.' );
		}

		$new_survey = $this->repository->find( $wpdb->insert_id );
		return rest_ensure_response( $new_survey );
	}

	/**
	 * Record a single impression for a survey (called by frontend widget).
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function record_impression( $request ) {
		$id = (int) $request['id'];

		global $wpdb;
		$table = $wpdb->prefix . 'pollquest_surveys';

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET impressions = impressions + 1 WHERE id = %d", // phpcs:ignore
				$id
			)
		);

		return rest_ensure_response( [ 'recorded' => true ] );
	}

	/**
	 * Bulk update the status of multiple surveys.
	 * Body: { ids: [1,2,3], status: "draft"|"publish"|"trash" }
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function bulk_status( $request ) {
		$params = $request->get_json_params();
		$ids    = array_map( 'intval', $params['ids'] ?? [] );
		$status = sanitize_text_field( $params['status'] ?? '' );

		$allowed_statuses = [ 'draft', 'publish', 'trash' ];

		if ( empty( $ids ) || ! in_array( $status, $allowed_statuses, true ) ) {
			return new WP_Error( 'bad_request', 'Invalid ids or status.', [ 'status' => 400 ] );
		}

		global $wpdb;
		$table       = $wpdb->prefix . 'pollquest_surveys';
		$ids_escaped = implode( ',', $ids );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = %s WHERE id IN ({$ids_escaped})", $status ) );

		return rest_ensure_response( [ 'updated' => count( $ids ), 'status' => $status ] );
	}
}

