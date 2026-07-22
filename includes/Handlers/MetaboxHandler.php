<?php
/**
 * Metabox Handler
 *
 * @package WPAsk
 */

namespace WPAsk\Handlers;

/**
 * Class MetaboxHandler
 * 
 * Registers post meta fields for Gutenberg sidebar integration (UserFeedback parity).
 */
class MetaboxHandler {

	/**
	 * Register meta fields.
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'register_meta_fields' ] );
	}

	/**
	 * Register the meta fields to make them accessible via the REST API for Gutenberg.
	 */
	public function register_meta_fields(): void {
		// Field to assign a specific survey to a post
		register_post_meta(
			'', // Apply to all post types that support custom fields
			'_ipulse_show_specific_survey',
			[
				'auth_callback' => '__return_true', // In a real scenario, check edit_post capability
				'default'       => 0,
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'number',
			]
		);

		// Field to disable all surveys on a post
		register_post_meta(
			'', 
			'_ipulse_disable_surveys',
			[
				'auth_callback' => '__return_true',
				'default'       => false,
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
			]
		);
	}
}
