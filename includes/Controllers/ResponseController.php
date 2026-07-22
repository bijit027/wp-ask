<?php
/**
 * Response REST Controller
 *
 * @package WPAsk
 */

namespace WPAsk\Controllers;

use WPAsk\Repositories\ResponseRepository;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class ResponseController
 */
class ResponseController {

	/**
	 * @var string
	 */
	private $namespace = 'wpask/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'responses';

	/**
	 * @var ResponseRepository
	 */
	private $repository;

	public function __construct() {
		$this->repository = new ResponseRepository();
	}

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/surveys/(?P<survey_id>[\d]+)/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/trash',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE, // Using POST for state change
					'callback'            => [ $this, 'trash_item' ],
					'permission_callback' => [ $this, 'delete_permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/restore',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'restore_item' ],
					'permission_callback' => [ $this, 'delete_permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check if a given request has access to read results.
	 */
	public function permissions_check( $request ): bool {
		return current_user_can( 'wpask_view_results' );
	}
    
	/**
	 * Check if a given request has access to delete results.
	 */
	public function delete_permissions_check( $request ): bool {
		return current_user_can( 'wpask_delete_surveys' );
	}

	/**
	 * Get a paginated list of responses for a survey.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;

		$survey_id = (int) $request['survey_id'];
		$table     = $wpdb->prefix . 'ipulse_responses';
		$status    = sanitize_text_field( $request->get_param( 'status' ) ?: 'publish' );
		$page      = (int) $request->get_param( 'page' ) ?: 1;
		$per_page  = (int) $request->get_param( 'per_page' ) ?: 50;
		$offset    = ( $page - 1 ) * $per_page;
		$from_date = $request->get_param( 'from' );
		$to_date   = $request->get_param( 'to' );

		$where_conditions = [ 'survey_id = %d' ];
		$where_values = [ $survey_id ];

		if ( 'all' !== $status ) {
			$where_conditions[] = 'status = %s';
			$where_values[] = $status;
		}

		if ( $from_date ) {
			$where_conditions[] = 'created_at >= %s';
			$where_values[] = $from_date;
		}

		if ( $to_date ) {
			$where_conditions[] = 'created_at <= %s';
			$where_values[] = $to_date . ' 23:59:59';
		}

		$where_clause = implode( ' AND ', $where_conditions );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE {$where_clause}", $where_values ) );
		
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare( 
				"SELECT * FROM {$table} WHERE {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d", 
				array_merge( $where_values, [ $per_page, $offset ] )
			)
		);
		
		// Decode JSON fields and add gravatar
		$items = [];
		foreach ( $results as $row ) {
			$item = (array) $row;
			$item['answers'] = json_decode( $item['answers'], true );
			$item['context'] = json_decode( $item['context'], true );
			
			$email = $item['email'] ?: ( $item['context']['email'] ?? '' );
			$name  = $item['full_name'] ?: ( $item['context']['name'] ?? '' );
			$item['avatar_url'] = \WPAsk\Utils\GravatarHelper::get_url( $email, $name );
			
			$items[] = $item;
		}

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Trash a response.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function trash_item( $request ) {
		global $wpdb;
		$id    = (int) $request['id'];
		$table = $wpdb->prefix . 'ipulse_responses';

		$updated = $wpdb->update( $table, [ 'status' => 'trash' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );

		if ( false === $updated ) {
			return new WP_Error( 'db_error', 'Failed to trash response.' );
		}

		return rest_ensure_response( [ 'trashed' => true ] );
	}

	/**
	 * Restore a trashed response.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function restore_item( $request ) {
		global $wpdb;
		$id    = (int) $request['id'];
		$table = $wpdb->prefix . 'ipulse_responses';

		$updated = $wpdb->update( $table, [ 'status' => 'publish' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );

		if ( false === $updated ) {
			return new WP_Error( 'db_error', 'Failed to restore response.' );
		}

		return rest_ensure_response( [ 'restored' => true ] );
	}

	/**
	 * Permanently delete a response.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		global $wpdb;
		$id    = (int) $request['id'];
		$table = $wpdb->prefix . 'ipulse_responses';

		$deleted = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		if ( ! $deleted ) {
			return new WP_Error( 'db_error', 'Failed to delete response.' );
		}

		return rest_ensure_response( [ 'deleted' => true ] );
	}
}
