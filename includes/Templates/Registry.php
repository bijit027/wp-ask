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
		
		return self::$templates;
	}
}
