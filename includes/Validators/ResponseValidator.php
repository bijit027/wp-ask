<?php
/**
 * Response Validator
 *
 * @package InsightPulse
 */

namespace PollQuest\Validators;

/**
 * Class ResponseValidator
 */
class ResponseValidator {

	/**
	 * Validate a response payload.
	 *
	 * @param array $data
	 * @return array|\WP_Error
	 */
	public static function validate( array $data ) {
		if ( empty( $data['answers'] ) || ! is_array( $data['answers'] ) ) {
			return new \WP_Error( 'missing_answers', 'Answers are required.' );
		}

		$valid_data = [
			'answers'   => \PollQuest\Utils\Sanitizer::sanitize_array( $data['answers'] ),
			'context'   => isset( $data['context'] ) && is_array( $data['context'] ) ? \PollQuest\Utils\Sanitizer::sanitize_array( $data['context'] ) : [],
			'email'     => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'full_name' => isset( $data['full_name'] ) ? sanitize_text_field( $data['full_name'] ) : '',
		];

		return $valid_data;
	}
}
