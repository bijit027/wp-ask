<?php
/**
 * Add-ons REST Controller
 *
 * @package WPAsk
 */

namespace WPAsk\Controllers;

use WPAsk\Addons\Registry;
use WP_REST_Request;

/**
 * Class AddonsController
 */
class AddonsController {

	/**
	 * @var string
	 */
	private $namespace = 'wpask/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'addons';

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check permissions.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permissions_check( $request ): bool {
		return current_user_can( 'wpask_save_settings' );
	}

	/**
	 * Get available add-ons.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		return rest_ensure_response(
			[
				'addons' => Registry::get_all(),
			]
		);
	}
}
