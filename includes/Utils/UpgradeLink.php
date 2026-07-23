<?php
/**
 * Upgrade / pricing link helper.
 *
 * Mirrors the UserFeedback Lite upgrade-link pattern with UTM tracking.
 *
 * @package PollQuest
 */

namespace PollQuest\Utils;

/**
 * Class UpgradeLink
 */
class UpgradeLink {

	/**
	 * Build a tracked upgrade URL for PollQuest Pro.
	 *
	 * @param string $medium   UTM medium (e.g. addons, floatbar).
	 * @param string $campaign UTM campaign (e.g. heatmaps, upgrade).
	 * @param string $url      Base URL override.
	 * @return string
	 */
	public static function get( string $medium = '', string $campaign = '', string $url = '' ): string {
		$url = $url ?: 'https://pollquest.io/pricing';

		$url = add_query_arg(
			[
				'utm_source'   => apply_filters( 'pollquest_upgrade_utm_source', 'liteplugin' ),
				'utm_medium'   => sanitize_key( $medium ?: 'default' ),
				'utm_campaign' => sanitize_key( $campaign ?: 'upgrade' ),
				'utm_content'  => POLLQUEST_VERSION,
			],
			trailingslashit( $url )
		);

		return esc_url( $url );
	}
}
