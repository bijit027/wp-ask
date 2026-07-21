<?php
/**
 * Role capability manager.
 *
 * @package InsightPulse
 */

namespace InsightPulse;

/**
 * Class Capabilities
 */
class Capabilities {

	/**
	 * Add plugin capabilities to the administrator role.
	 */
	public static function add(): void {
		$role = get_role( 'administrator' );
		
		if ( ! $role ) {
			return;
		}

		$caps = [
			'insightpulse_create_edit_surveys',
			'insightpulse_delete_surveys',
			'insightpulse_view_results',
			'insightpulse_save_settings',
		];

		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
		
		// Update option to track that capabilities were added.
		update_option( 'insightpulse_capabilities_added', true );
	}
}
