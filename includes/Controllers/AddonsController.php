<?php
/**
 * Add-ons REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use InsightPulse\Addons\Registry;
use InsightPulse\Utils\UpgradeLink;
use WP_REST_Request;

/**
 * Class AddonsController
 */
class AddonsController {

	/**
	 * @var string
	 */
	private $namespace = 'insightpulse/v1';

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
		return current_user_can( 'insightpulse_save_settings' );
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
				'is_pro'      => Registry::is_pro(),
				'upgrade_url' => UpgradeLink::get( 'addons', 'upgrade' ),
				'addons'      => Registry::get_all(),
			]
		);
	}
}
