<?php
/**
 * Sanitizer Helper
 *
 * @package InsightPulse
 */

namespace PollQuest\Utils;

/**
 * Class Sanitizer
 */
class Sanitizer {

	/**
	 * Deep sanitize an array of data.
	 *
	 * @param array $data
	 * @return array
	 */
	public static function sanitize_array( array $data ): array {
		$sanitized = [];

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ sanitize_key( $key ) ] = self::sanitize_array( $value );
			} elseif ( is_numeric( $value ) ) {
				// Keep numeric values as numeric string or float/int
				$sanitized[ sanitize_key( $key ) ] = $value;
			} elseif ( is_bool( $value ) ) {
				$sanitized[ sanitize_key( $key ) ] = $value;
			} else {
				$sanitized[ sanitize_key( $key ) ] = sanitize_textarea_field( $value );
			}
		}

		return $sanitized;
	}
}
