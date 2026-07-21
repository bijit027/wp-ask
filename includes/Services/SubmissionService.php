<?php
/**
 * Submission Service
 *
 * @package InsightPulse
 */

namespace InsightPulse\Services;

use InsightPulse\Models\Survey;
use InsightPulse\Repositories\ResponseRepository;
use InsightPulse\Repositories\SurveyRepository;
use InsightPulse\Services\SessionService;
use InsightPulse\Services\AnalyticsService;

/**
 * Class SubmissionService
 * 
 * Handles validating and saving a survey submission.
 */
class SubmissionService {

	/**
	 * @var ResponseRepository
	 */
	private $response_repo;

	/**
	 * @var SurveyRepository
	 */
	private $survey_repo;

	/**
	 * @var SessionService
	 */
	private $session_service;

	/**
	 * @var AnalyticsService
	 */
	private $analytics_service;

	public function __construct() {
		$this->response_repo     = new ResponseRepository();
		$this->survey_repo       = new SurveyRepository();
		$this->session_service   = new SessionService();
		$this->analytics_service = new AnalyticsService();
	}

	/**
	 * Process a new survey submission.
	 *
	 * @param int   $survey_id
	 * @param array $payload
	 * @return int|\WP_Error
	 */
	public function submit( int $survey_id, array $payload ) {
		$survey = $this->survey_repo->find( $survey_id );

		if ( ! $survey ) {
			return new \WP_Error( 'not_found', 'Survey not found.' );
		}

		if ( 'publish' !== $survey->status ) {
			return new \WP_Error( 'not_active', 'Survey is not active.' );
		}

		// 1. Get or Create Session
		$session = $this->session_service->get_current_session( true, [
			'email'     => $payload['email'] ?? '',
			'full_name' => $payload['full_name'] ?? '',
		] );

		$answers = $payload['answers'] ?? [];
		$context = $payload['context'] ?? [];

		// Ensure arrays are JSON encoded for the DB if passing raw, 
		// but ResponseRepository accepts them as strings since format is %s
		$encoded_answers = is_array( $answers ) ? wp_json_encode( $answers ) : $answers;
		$encoded_context = is_array( $context ) ? wp_json_encode( $context ) : $context;

		// 2. Prepare Response Data
		$data = [
			'survey_id'  => $survey->id,
			'session_id' => $session ? $session->id : null,
			'user_id'    => get_current_user_id() ?: null,
			'page_url'   => esc_url_raw( $context['page_url'] ?? '' ),
			'answers'    => $encoded_answers,
			'context'    => $encoded_context,
			'email'      => sanitize_email( $payload['email'] ?? ( $session->email ?? '' ) ),
			'full_name'  => sanitize_text_field( $payload['full_name'] ?? ( $session->full_name ?? '' ) ),
			'ip_address' => \InsightPulse\Utils\IpHelper::get_ip(),
			'browser'    => sanitize_text_field( $context['browser'] ?? '' ),
			'device'     => sanitize_text_field( $context['device'] ?? '' ),
			'status'     => 'publish',
		];

		// Remove nulls to let DB defaults apply
		$data = array_filter( $data, function ( $value ) {
			return null !== $value;
		} );

		// 3. Save Response
		$response_id = $this->response_repo->create( $data );

		if ( ! $response_id ) {
			return new \WP_Error( 'db_error', 'Failed to save response.' );
		}

		// 4. Update Aggregates
		if ( $session ) {
			$this->session_service->increment_response_count( $session );
		}

		// Analytics mapping requires decoded arrays
		if ( is_array( $answers ) ) {
			$this->analytics_service->record_reportable_data( $survey->id, $answers );
		}

		// Fire generic action for webhooks/emails
		do_action( 'insightpulse_response_saved', $response_id, $survey->id, $answers, $context );

		return $response_id;
	}
}
