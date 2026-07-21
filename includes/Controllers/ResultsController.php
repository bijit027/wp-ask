<?php
/**
 * Results REST Controller
 *
 * @package WPAsk
 */

namespace InsightPulse\Controllers;

use InsightPulse\Services\AnalyticsService;
use InsightPulse\Repositories\SurveyRepository;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class ResultsController
 */
class ResultsController {

	/**
	 * @var string
	 */
	private $namespace = 'insightpulse/v1';

	/**
	 * @var AnalyticsService
	 */
	private $analytics_service;

	/**
	 * @var SurveyRepository
	 */
	private $survey_repo;

	public function __construct() {
		$this->analytics_service = new AnalyticsService();
		$this->survey_repo       = new SurveyRepository();
	}

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/surveys/(?P<id>[\d]+)/results',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_results' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check permissions.
	 */
	public function permissions_check( $request ): bool {
		return current_user_can( 'insightpulse_view_results' );
	}

	/**
	 * Get aggregated results for charting.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_results( $request ) {
		$survey_id = (int) $request['id'];
		$survey    = $this->survey_repo->find( $survey_id );

		if ( ! $survey ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		global $wpdb;
		$responses_table = $wpdb->prefix . 'ipulse_responses';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_responses = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$responses_table} WHERE survey_id = %d AND status = 'publish'", $survey_id ) );

		$reportable_data = $this->analytics_service->get_reportable_data( $survey_id );

		return rest_ensure_response( [
			'total_impressions' => (int) $survey->impressions,
			'total_responses'   => $total_responses,
			'completion_rate'   => $survey->impressions > 0 ? round( ( $total_responses / $survey->impressions ) * 100, 1 ) : 0,
			'questions_data'    => $reportable_data,
		] );
	}
}
