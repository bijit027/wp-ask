<?php
/**
 * Submission Service
 *
 * @package PollQuest
 */

namespace PollQuest\Services;

use PollQuest\Models\Survey;
use PollQuest\Repositories\ResponseRepository;
use PollQuest\Repositories\SurveyRepository;
use PollQuest\Services\SessionService;
use PollQuest\Services\AnalyticsService;

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

		// Process file uploads - save to WordPress media library
		foreach ( $answers as $question_id => $answer ) {
			if ( isset( $answer['type'] ) && 'file_upload' === $answer['type'] && isset( $answer['value']['data'] ) ) {
				$file_data = $answer['value'];
				
				// Decode base64 data
				$upload = $this->upload_base64_file( $file_data );
				
				if ( $upload && ! is_wp_error( $upload ) ) {
					// Replace base64 data with attachment ID and URL
					$answers[ $question_id ]['value'] = [
						'attachment_id' => $upload['id'],
						'url' => $upload['url'],
						'name' => $file_data['name'],
						'size' => $file_data['size'],
						'type' => $file_data['type'],
					];
				} else {
					// If upload failed, remove the file data
					unset( $answers[ $question_id ] );
				}
			}
		}

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
			'ip_address' => \PollQuest\Utils\IpHelper::get_ip(),
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
		do_action( 'pollquest_response_saved', $response_id, $survey->id, $answers, $context );

		return $response_id;
	}

	/**
	 * Upload a base64-encoded file to WordPress media library.
	 *
	 * @param array $file_data File data with 'data', 'name', 'type'.
	 * @return array|WP_Error Array with 'id' and 'url' on success, WP_Error on failure.
	 */
	private function upload_base64_file( $file_data ) {
		// Extract base64 data
		$data = $file_data['data'];
		$name = sanitize_file_name( $file_data['name'] );
		$type = $file_data['type'];

		// Remove data URL prefix if present
		if ( strpos( $data, 'data:' ) === 0 ) {
			$data = substr( $data, strpos( $data, ',' ) + 1 );
		}

		// Decode base64
		$decoded = base64_decode( $data );
		if ( false === $decoded ) {
			return new \WP_Error( 'invalid_base64', 'Failed to decode base64 data.' );
		}

		// Get file extension
		$ext = pathinfo( $name, PATHINFO_EXTENSION );
		if ( ! $ext ) {
			$ext = $this->get_extension_from_mime( $type );
			$name .= '.' . $ext;
		}

		// Upload to WordPress
		$upload = wp_upload_bits( $name, null, $decoded );

		if ( $upload['error'] ) {
			return new \WP_Error( 'upload_failed', $upload['error'] );
		}

		// Create attachment
		$attachment = [
			'post_mime_type' => $type,
			'post_title'     => sanitize_title( pathinfo( $name, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );

		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		// Generate attachment metadata
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return [
			'id'  => $attach_id,
			'url' => $upload['url'],
		];
	}

	/**
	 * Get file extension from MIME type.
	 *
	 * @param string $mime MIME type.
	 * @return string File extension.
	 */
	private function get_extension_from_mime( $mime ) {
		$mime_map = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
			'application/pdf' => 'pdf',
			'application/msword' => 'doc',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
			'text/plain' => 'txt',
		];

		return $mime_map[ $mime ] ?? 'bin';
	}
}
