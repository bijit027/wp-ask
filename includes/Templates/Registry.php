<?php
/**
 * Template Registry
 *
 * @package WPAsk
 */

namespace InsightPulse\Templates;

/**
 * Class Registry
 * 
 * Manages the available survey templates.
 */
class Registry {

	/**
	 * @var array
	 */
	private static $templates = [];

	/**
	 * Register a new template.
	 *
	 * @param string $id
	 * @param array  $data
	 */
	public static function register( string $id, array $data ): void {
		self::$templates[ $id ] = $data;
	}

	/**
	 * Get a specific template by ID.
	 *
	 * @param string $id
	 * @return array|null
	 */
	public static function get( string $id ): ?array {
		return self::$templates[ $id ] ?? null;
	}

	/**
	 * Get all registered templates.
	 *
	 * @return array
	 */
	public static function get_all(): array {
		if ( empty( self::$templates ) ) {
			DefaultTemplates::register();
			do_action( 'insightpulse_register_templates' );
		}

		$is_pro = (bool) apply_filters( 'wpask_is_pro', defined( 'WPASK_PRO_VERSION' ) );
		$templates = array_values( self::$templates );

		foreach ( $templates as &$template ) {
			$template['is_pro']       = ! empty( $template['is_pro'] );
			$template['is_available'] = ! $template['is_pro'] || $is_pro;

			if ( $template['is_pro'] && ! $is_pro ) {
				$template['upgrade_url'] = \InsightPulse\Utils\UpgradeLink::get(
					'templates',
					$template['id'] ?? 'pro-template'
				);
			}
		}
		unset( $template );

		return apply_filters( 'wpask_survey_templates', $templates );
	}
}
