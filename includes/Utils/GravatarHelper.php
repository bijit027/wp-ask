<?php
/**
 * Gravatar Helper
 *
 * @package InsightPulse
 */

namespace PollQuest\Utils;

/**
 * Class GravatarHelper
 */
class GravatarHelper {

	/**
	 * Get the gravatar image URL for an email, with a fallback to ui-avatars.
	 * Mirrors OpinionCraft Utility::getGravatarPhoto().
	 *
	 * @param string $email
	 * @param string $name
	 * @param int    $size
	 * @return string
	 */
	public static function get_url( string $email, string $name = '', int $size = 128 ): string {
		if ( empty( $email ) ) {
			return '';
		}

		// Privacy opt-in: only load external avatars if enabled
		if ( ! get_option( 'pollquest_enable_gravatar', false ) ) {
			return ''; // empty — use local default avatar fallback
		}

		$fallback = '';
		if ( $name ) {
			$fallback = '&d=https%3A%2F%2Fui-avatars.com%2Fapi%2F' . urlencode( $name ) . '/' . $size;
		} else {
			$fallback = '&d=mp'; // Mystery person default
		}

		$hash = md5( strtolower( trim( $email ) ) );

		return "https://www.gravatar.com/avatar/{$hash}?s={$size}" . $fallback;
	}
}
