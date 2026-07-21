<?php
/**
 * Logic REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Class LogicController
 */
class LogicController {

	/**
	 * @var string
	 */
	private $namespace = 'insightpulse/v1';

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/logic-type',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_logic_types' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/pages',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_pages' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check permissions.
	 */
	public function permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_create_edit_surveys' );
	}

	/**
	 * Get dynamic logic options for the Survey Builder targeting rules.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_logic_types( $request ) {
		// Get all public post types
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$post_type_options = [];
		foreach ( $post_types as $pt ) {
			$post_type_options[] = [
				'value' => $pt->name,
				'label' => $pt->labels->singular_name,
			];
		}

		// Get standard user roles/statuses
		$user_status_options = [
			[ 'value' => 'logged_in', 'label' => 'Logged In' ],
			[ 'value' => 'logged_out', 'label' => 'Logged Out' ],
		];
		
		global $wp_roles;
		if ( isset( $wp_roles ) ) {
			foreach ( $wp_roles->roles as $key => $role ) {
				$user_status_options[] = [
					'value' => 'role_' . $key,
					'label' => 'Role: ' . $role['name'],
				];
			}
		}

		return rest_ensure_response( [
			'post_types'   => $post_type_options,
			'user_status'  => $user_status_options,
		] );
	}

	/**
	 * Get paginated list of pages/posts for specific page targeting.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_pages( $request ) {
		$post_type = $request->get_param( 'post_type' ) ?: 'page';
		$search = $request->get_param( 'search' ) ?: '';
		$page = (int) $request->get_param( 'page' ) ?: 1;
		$per_page = (int) $request->get_param( 'per_page' ) ?: 50;

		$args = [
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'paged' => $page,
			's' => $search,
		];

		$query = new \WP_Query( $args );
		$pages = [];

		foreach ( $query->posts as $post ) {
			$pages[] = [
				'id' => $post->ID,
				'title' => $post->post_title,
				'type' => $post->post_type,
				'url' => get_permalink( $post->ID ),
			];
		}

		return rest_ensure_response( [
			'pages' => $pages,
			'total' => $query->found_posts,
			'total_pages' => $query->max_num_pages,
		] );
	}
}
