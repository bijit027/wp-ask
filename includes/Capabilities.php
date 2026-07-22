<?php
/**
 * Role capability manager.
 *
 * @package InsightPulse
 */

namespace WPAsk;

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
			'wpask_create_edit_surveys',
			'wpask_delete_surveys',
			'wpask_view_results',
			'wpask_save_settings',
		];

		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
		
		// Update option to track that capabilities were added.
		update_option( 'wpask_capabilities_added', true );
	}
}
