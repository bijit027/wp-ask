<?php
/**
 * Frontend REST Controller
 *
 * @package PollQuest
 */

namespace PollQuest\Controllers;

use PollQuest\Services\SubmissionService;
use PollQuest\Repositories\SurveyRepository;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class FrontendController
 * 
 * Handles public-facing endpoints like submitting a survey.
 */
class FrontendController {

	/**
	 * @var string
	 */
	private $namespace = 'pollquest/v1';

	/**
	 * @var SubmissionService
	 */
	private $submission_service;

	/**
	 * @var SurveyRepository
	 */
	private $survey_repository;

	public function __construct() {
		$this->submission_service = new SubmissionService();
		$this->survey_repository = new SurveyRepository();
	}

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/submit',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'submit_survey' ],
					'permission_callback' => [ $this, 'can_submit' ],
					'args'                => [
						'survey_id' => [
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);
	}

	/**
	 * Permission callback for submit endpoint.
	 * Only allows submissions to published surveys.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function can_submit( WP_REST_Request $request ): bool {
		$params = $request->get_json_params() ?: $request->get_body_params();
		$survey_id = absint( $params['survey_id'] ?? 0 );

		if ( ! $survey_id ) {
			return false;
		}

		$survey = $this->survey_repository->find( $survey_id );
		return $survey && $survey->status === 'publish';
	}

	/**
	 * Submit a survey response.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit_survey( $request ) {
		$params    = $request->get_json_params() ?: $request->get_body_params();
		$survey_id = (int) ( $params['survey_id'] ?? 0 );

		// Security: Check for a valid WP Nonce if desired, though we allow public submissions.
		// A nonce check `wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' )` could go here if strict.
		
		// Rate limiting: 5 submissions per IP per hour.
		$ip       = \PollQuest\Utils\IpHelper::get_ip();
		$transient_key = 'pollquest_rl_' . md5( $ip . '_' . $survey_id );
		$attempts = get_transient( $transient_key ) ?: 0;

		if ( $attempts >= 5 ) {
			return new WP_Error( 'rate_limited', 'Too many submissions. Please try again later.', [ 'status' => 429 ] );
		}
		
		set_transient( $transient_key, $attempts + 1, HOUR_IN_SECONDS );

		// Process submission
		$result = $this->submission_service->submit( $survey_id, $params );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( [
			'success'     => true,
			'response_id' => $result,
			'message'     => 'Survey submitted successfully.',
		] );
	}
}
