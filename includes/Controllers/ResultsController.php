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

		register_rest_route(
			$this->namespace,
			'/results-summary',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_summary' ],
					'permission_callback' => [ $this, 'permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/surveys/(?P<id>[\d]+)/export',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'export_csv' ],
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

	/**
	 * Get aggregated results across all surveys.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_summary( $request ) {
		global $wpdb;
		$surveys_table = $wpdb->prefix . 'ipulse_surveys';
		$responses_table = $wpdb->prefix . 'ipulse_responses';

		$total_impressions = (int) $wpdb->get_var( "SELECT SUM(impressions) FROM {$surveys_table} WHERE status != 'trash'" );
		$total_responses = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$responses_table} WHERE status = 'publish'" );
		$total_surveys = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$surveys_table} WHERE status != 'trash'" );

		return rest_ensure_response( [
			'total_impressions' => $total_impressions,
			'total_responses'   => $total_responses,
			'total_surveys'     => $total_surveys,
			'completion_rate'   => $total_impressions > 0 ? round( ( $total_responses / $total_impressions ) * 100, 1 ) : 0,
		] );
	}

	/**
	 * Export survey responses as CSV.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
	public function export_csv( $request ) {
		$survey_id = (int) $request['id'];
		$survey    = $this->survey_repo->find( $survey_id );

		if ( ! $survey ) {
			return new WP_Error( 'not_found', 'Survey not found.', [ 'status' => 404 ] );
		}

		global $wpdb;
		$responses_table = $wpdb->prefix . 'ipulse_responses';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$responses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$responses_table} WHERE survey_id = %d AND status = 'publish' ORDER BY created_at DESC", $survey_id ) );

		if ( empty( $responses ) ) {
			return new WP_Error( 'no_responses', 'No responses to export.', [ 'status' => 404 ] );
		}

		$questions = is_array( $survey->questions ) ? $survey->questions : json_decode( $survey->questions, true );

		// Generate CSV headers
		$headers = [ 'Response ID', 'Date', 'IP Address' ];
		foreach ( $questions as $q ) {
			$headers[] = $q['label'] . ' (' . $q['type'] . ')';
		}

		// Generate CSV rows
		$rows = [];
		$rows[] = $headers;

		foreach ( $responses as $response ) {
			$row = [
				$response->id,
				$response->created_at,
				$response->ip,
			];

			$answers = is_array( $response->answers ) ? $response->answers : json_decode( $response->answers, true );

			foreach ( $questions as $q ) {
				$answer_value = isset( $answers[ $q['id'] ] ) ? $answers[ $q['id'] ]['value'] : '';

				// Format array values (checkbox)
				if ( is_array( $answer_value ) ) {
					$answer_value = implode( ', ', $answer_value );
				}

				$row[] = $answer_value;
			}

			$rows[] = $row;
		}

		// Output CSV
		$filename = 'survey-' . $survey_id . '-responses-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		foreach ( $rows as $row ) {
			fputcsv( $output, $row );
		}
		fclose( $output );

		exit;
	}
}
