<?php
/**
 * Response REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use InsightPulse\Repositories\ResponseRepository;
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
	private $namespace = 'insightpulse/v1';

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
		return current_user_can( 'insightpulse_view_results' );
	}
    
	/**
	 * Check if a given request has access to delete results.
	 */
	public function delete_permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_delete_surveys' );
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

		if ( 'all' === $status ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( 
				"SELECT * FROM {$table} WHERE survey_id = %d ORDER BY id DESC LIMIT %d OFFSET %d", 
				$survey_id, 
				$per_page, 
				$offset 
			);
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE survey_id = %d", $survey_id ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( 
				"SELECT * FROM {$table} WHERE survey_id = %d AND status = %s ORDER BY id DESC LIMIT %d OFFSET %d", 
				$survey_id, 
				$status, 
				$per_page, 
				$offset 
			);
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE survey_id = %d AND status = %s", $survey_id, $status ) );
		}
		
		$results = $wpdb->get_results( $query );
		
		// Decode JSON fields and add gravatar
		$items = [];
		foreach ( $results as $row ) {
			$item = (array) $row;
			$item['answers'] = json_decode( $item['answers'], true );
			$item['context'] = json_decode( $item['context'], true );
			
			$email = $item['email'] ?: ( $item['context']['email'] ?? '' );
			$name  = $item['full_name'] ?: ( $item['context']['name'] ?? '' );
			$item['avatar_url'] = \InsightPulse\Utils\GravatarHelper::get_url( $email, $name );
			
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
