<?php
/**
 * Role capability manager.
 *
 * @package InsightPulse
 */

namespace PollQuest;

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
			'pollquest_create_edit_surveys',
			'pollquest_delete_surveys',
			'pollquest_view_results',
			'pollquest_save_settings',
		];

		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
		
		// Update option to track that capabilities were added.
		update_option( 'pollquest_capabilities_added', true );
	}
}
