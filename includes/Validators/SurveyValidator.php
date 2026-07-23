<?php
/**
 * Survey Validator
 *
 * @package PollQuest
 */

namespace PollQuest\Validators;

/**
 * Class SurveyValidator
 */
class SurveyValidator {

	/**
	 * Validate survey creation/update payload.
	 *
	 * @param array $data
	 * @return array|\WP_Error Validated data or error.
	 */
	public static function validate( array $data ) {
		if ( empty( $data['title'] ) ) {
			return new \WP_Error( 'invalid_title', 'Survey title is required.' );
		}

		$valid_data = [
			'title'         => sanitize_text_field( $data['title'] ),
			'status'        => in_array( $data['status'] ?? '', [ 'publish', 'draft', 'trash' ] ) ? $data['status'] : 'draft',
			'type'          => sanitize_text_field( $data['type'] ?? 'floating' ),
			'questions'     => isset( $data['questions'] ) ? wp_json_encode( \PollQuest\Utils\Sanitizer::sanitize_array( $data['questions'] ) ) : '[]',
			'settings'      => isset( $data['settings'] ) ? wp_json_encode( \PollQuest\Utils\Sanitizer::sanitize_array( $data['settings'] ) ) : '{}',
			'targeting'     => isset( $data['targeting'] ) ? wp_json_encode( \PollQuest\Utils\Sanitizer::sanitize_array( $data['targeting'] ) ) : '{}',
			'notifications' => isset( $data['notifications'] ) ? wp_json_encode( \PollQuest\Utils\Sanitizer::sanitize_array( $data['notifications'] ) ) : '{}',
		];

		if ( ! empty( $data['publish_at'] ) ) {
			$valid_data['publish_at'] = gmdate( 'Y-m-d H:i:s', strtotime( $data['publish_at'] ) );
		} else {
			$valid_data['publish_at'] = null;
		}

		return $valid_data;
	}
}
