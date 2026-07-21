<?php
/**
 * Template REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use InsightPulse\Templates\Registry;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class TemplateController
 */
class TemplateController {

	/**
	 * @var string
	 */
	private $namespace = 'insightpulse/v1';

	/**
	 * @var string
	 */
	private $rest_base = 'survey-templates';

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
	 */
	public function permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_create_edit_surveys' );
	}

	/**
	 * Get available templates.
	 */
	public function get_items( $request ) {
		// Template registry will be built in Phase 7
		$templates = class_exists( '\InsightPulse\Templates\Registry' ) ? Registry::get_all() : [];
		return rest_ensure_response( array_values( $templates ) );
	}
}
