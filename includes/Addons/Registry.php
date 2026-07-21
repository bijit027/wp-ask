<?php
/**
 * Add-on registry.
 *
 * @package InsightPulse
 */

namespace InsightPulse\Addons;

use InsightPulse\Utils\UpgradeLink;

/**
 * Class Registry
 *
 * Defines available WPAsk add-ons and their availability state.
 */
class Registry {

	/**
	 * Whether the Pro version is active.
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {
		return (bool) apply_filters( 'wpask_is_pro', defined( 'WPASK_PRO_VERSION' ) );
	}

	/**
	 * Return all add-ons with resolved install/active state.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_all(): array {
		$is_pro = self::is_pro();
		$addons = self::get_definitions();

		foreach ( $addons as &$addon ) {
			$addon = self::resolve_state( $addon, $is_pro );
		}
		unset( $addon );

		return apply_filters( 'wpask_addons', $addons );
	}

	/**
	 * Raw add-on definitions.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_definitions(): array {
		return [
			[
				'id'          => 'email-notifications',
				'name'        => __( 'Email notifications', 'wpask' ),
				'description' => __( 'Get emailed when a new survey response comes in.', 'wpask' ),
				'icon'        => 'mail',
				'tier'        => 'included',
				'feature'     => 'email_notifications',
			],
			[
				'id'          => 'conditional-logic',
				'name'        => __( 'Conditional logic', 'wpask' ),
				'description' => __( 'Show or skip questions based on previous answers.', 'wpask' ),
				'icon'        => 'git-branch',
				'tier'        => 'included',
				'feature'     => 'conditional_logic',
			],
			[
				'id'          => 'csv-export',
				'name'        => __( 'CSV export', 'wpask' ),
				'description' => __( 'Download all survey responses as a CSV file.', 'wpask' ),
				'icon'        => 'download',
				'tier'        => 'included',
				'feature'     => 'csv_export',
			],
			[
				'id'          => 'heatmaps',
				'name'        => __( 'Heatmaps', 'wpask' ),
				'description' => __( 'Visualize where visitors click and how they interact with your pages.', 'wpask' ),
				'icon'        => 'mouse-pointer-click',
				'tier'        => 'included',
				'feature'     => 'heatmaps',
			],
			[
				'id'          => 'webhooks',
				'name'        => __( 'Webhooks', 'wpask' ),
				'description' => __( 'Send new responses to a custom URL in real time.', 'wpask' ),
				'icon'        => 'webhook',
				'tier'        => 'pro',
				'feature'     => 'webhooks',
			],
			[
				'id'          => 'mailchimp',
				'name'        => __( 'Mailchimp sync', 'wpask' ),
				'description' => __( 'Push new contacts into a Mailchimp audience automatically.', 'wpask' ),
				'icon'        => 'send',
				'tier'        => 'pro',
				'feature'     => 'mailchimp',
			],
			[
				'id'          => 'zapier',
				'name'        => __( 'Zapier', 'wpask' ),
				'description' => __( 'Connect WPAsk to 5,000+ apps with automated zaps.', 'wpask' ),
				'icon'        => 'zap',
				'tier'        => 'pro',
				'feature'     => 'zapier',
			],
			[
				'id'          => 'post-ratings',
				'name'        => __( 'Post ratings', 'wpask' ),
				'description' => __( 'Embed star or thumbs ratings on posts via shortcode.', 'wpask' ),
				'icon'        => 'star',
				'tier'        => 'included',
				'feature'     => 'post_ratings',
			],
		];
	}

	/**
	 * Resolve install/active/lock state for a single add-on.
	 *
	 * @param array<string, mixed> $addon  Add-on definition.
	 * @param bool                 $is_pro Whether Pro is active.
	 * @return array<string, mixed>
	 */
	private static function resolve_state( array $addon, bool $is_pro ): array {
		$included_features = [ 'email_notifications', 'conditional_logic', 'csv_export', 'post_ratings', 'heatmaps' ];
		$feature           = $addon['feature'] ?? '';
		$is_included       = 'included' === ( $addon['tier'] ?? '' );

		if ( $is_included ) {
			$addon['installed'] = in_array( $feature, $included_features, true );
			$addon['active']    = $addon['installed'];
			$addon['locked']    = false;
		} else {
			$addon['installed'] = $is_pro && self::is_pro_feature_active( $feature );
			$addon['active']    = $addon['installed'];
			$addon['locked']    = ! $is_pro;
		}

		if ( ! empty( $addon['locked'] ) ) {
			$addon['upgrade_url'] = UpgradeLink::get( 'addons', $addon['id'], 'https://wpask.io/pricing' );
		}

		unset( $addon['feature'] );

		return $addon;
	}

	/**
	 * Check whether a Pro feature module is active.
	 *
	 * @param string $feature Feature slug.
	 * @return bool
	 */
	private static function is_pro_feature_active( string $feature ): bool {
		$active = (array) get_option( 'wpask_active_addons', [] );
		return in_array( $feature, $active, true );
	}
}
