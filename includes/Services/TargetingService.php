<?php
/**
 * Targeting Service
 *
 * @package PollQuest
 */

namespace PollQuest\Services;

use PollQuest\Models\Survey;

/**
 * Class TargetingService
 * 
 * Evaluates survey targeting rules against the current context.
 */
class TargetingService {

	/**
	 * Evaluate if a survey should be displayed in the current context.
	 *
	 * @param Survey $survey
	 * @return bool
	 */
	public function should_display( Survey $survey ): bool {
		if ( 'publish' !== $survey->status ) {
			return false;
		}

		if ( $survey->publish_at && strtotime( $survey->publish_at ) > time() ) {
			return false;
		}

		$targeting = $survey->targeting;

		if ( empty( $targeting ) || ! is_array( $targeting ) || empty( $targeting['rules'] ) ) {
			// No rules means show everywhere.
			return true;
		}

		$rules      = $targeting['rules'];
		$rule_match = $targeting['rule_match'] ?? 'all'; // 'all' or 'any'
		$is_match   = 'all' === $rule_match ? true : false;

		foreach ( $rules as $rule ) {
			$rule_result = $this->evaluate_rule( $rule );

			if ( 'all' === $rule_match && ! $rule_result ) {
				return false; // One failed, all fail
			}

			if ( 'any' === $rule_match && $rule_result ) {
				return true; // One passed, we're good
			}
		}

		if ( 'any' === $rule_match ) {
			return false; // None passed
		}

		return true; // All passed
	}

	/**
	 * Evaluate a single rule.
	 *
	 * @param array $rule
	 * @return bool
	 */
	private function evaluate_rule( array $rule ): bool {
		$type     = $rule['type'] ?? '';
		$operator = $rule['operator'] ?? 'is';
		$value    = $rule['value'] ?? '';

		switch ( $type ) {
			case 'url':
				return $this->evaluate_url( $operator, $value );
			case 'user_status':
				return $this->evaluate_user_status( $operator, $value );
			case 'post_type':
				return $this->evaluate_post_type( $operator, $value );
			case 'page':
				return $this->evaluate_page( $operator, $value );
			case 'referrer':
				return $this->evaluate_referrer( $operator, $value );
			case 'time_on_page':
				return $this->evaluate_time_on_page( $operator, $value );
			case 'scroll_depth':
				return $this->evaluate_scroll_depth( $operator, $value );
			case 'device':
				return $this->evaluate_device( $operator, $value );
			case 'exit_intent':
				return $this->evaluate_exit_intent( $operator, $value );
			default:
				// If we don't know the rule type, fail safe (don't show).
				return false;
		}
	}

	private function evaluate_url( string $operator, string $value ): bool {
		global $wp;
		$current_url = home_url( add_query_arg( [], $wp->request ) );

		if ( 'is' === $operator || 'equals' === $operator ) {
			return trim( $current_url, '/' ) === trim( $value, '/' );
		}

		if ( 'contains' === $operator ) {
			return false !== strpos( $current_url, $value );
		}

		if ( 'not_contains' === $operator ) {
			return false === strpos( $current_url, $value );
		}

		return false;
	}

	private function evaluate_user_status( string $operator, string $value ): bool {
		$is_logged_in = is_user_logged_in();

		if ( 'logged_in' === $value ) {
			return 'is' === $operator ? $is_logged_in : ! $is_logged_in;
		}

		if ( 'logged_out' === $value ) {
			return 'is' === $operator ? ! $is_logged_in : $is_logged_in;
		}

		return false;
	}

	private function evaluate_post_type( string $operator, string $value ): bool {
		$current_type = get_post_type();

		if ( 'is' === $operator ) {
			return $current_type === $value;
		}

		if ( 'is_not' === $operator ) {
			return $current_type !== $value;
		}

		return false;
	}

	private function evaluate_page( string $operator, string $value ): bool {
		$current_id = get_the_ID();

		if ( 'is' === $operator ) {
			return $current_id == $value;
		}

		if ( 'is_not' === $operator ) {
			return $current_id != $value;
		}

		return false;
	}

	private function evaluate_referrer( string $operator, string $value ): bool {
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

		if ( 'is' === $operator ) {
			return trim( $referrer, '/' ) === trim( $value, '/' );
		}

		if ( 'contains' === $operator ) {
			return false !== strpos( $referrer, $value );
		}

		return false;
	}

	private function evaluate_time_on_page( string $operator, string $value ): bool {
		// This rule type requires frontend JavaScript implementation
		// For now, return true to not block surveys
		return true;
	}

	private function evaluate_scroll_depth( string $operator, string $value ): bool {
		// This rule type requires frontend JavaScript implementation
		// For now, return true to not block surveys
		return true;
	}

	private function evaluate_device( string $operator, string $value ): bool {
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$is_mobile = preg_match( '/(android|iphone|ipad|ipod)/i', $user_agent );
		$is_tablet = preg_match( '/(ipad|tablet)/i', $user_agent );
		
		$current_device = 'desktop';
		if ( $is_mobile && ! $is_tablet ) {
			$current_device = 'mobile';
		} elseif ( $is_tablet ) {
			$current_device = 'tablet';
		}

		if ( 'is' === $operator ) {
			return $current_device === $value;
		}

		if ( 'is_not' === $operator ) {
			return $current_device !== $value;
		}

		return false;
	}

	private function evaluate_exit_intent( string $operator, string $value ): bool {
		// This rule type requires frontend JavaScript implementation
		// For now, return true to not block surveys
		return true;
	}
}
