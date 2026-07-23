<?php
/**
 * Settings REST Controller
 *
 * @package PollQuest
 */

namespace PollQuest\Controllers;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Class SettingsController
 */
class SettingsController {

	/**
	 * @var string
	 */
	private $namespace = 'pollquest/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'settings';

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
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE, // Using POST for updates to avoid CORS issues with PUT
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check permissions.
	 */
	public function permissions_check( $request ): bool {
		return current_user_can( 'pollquest_save_settings' );
	}

	/**
	 * Get settings.
	 */
	public function get_settings( $request ) {
		$settings = get_option( 'pollquest_settings', [] );
		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings.
	 */
	public function update_settings( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		
		// Sanitize settings deeply
		$sanitized = \PollQuest\Utils\Sanitizer::sanitize_array( $params );
		
		update_option( 'pollquest_settings', $sanitized );

		return rest_ensure_response( [ 'success' => true, 'settings' => $sanitized ] );
	}
}
