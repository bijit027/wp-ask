<?php
/**
 * Add-on registry.
 *
 * @package PollQuest
 */

namespace PollQuest\Addons;

use PollQuest\Utils\UpgradeLink;

/**
 * Class Registry
 *
 * Defines available PollQuest add-ons and their availability state.
 */
class Registry {

	/**
	 * Whether the Pro version is active.
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {
		return (bool) apply_filters( 'pollquest_is_pro', defined( 'POLLQUEST_PRO_VERSION' ) );
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

		return apply_filters( 'pollquest_addons', $addons );
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
				'name'        => __( 'Email notifications', 'pollquest' ),
				'description' => __( 'Get emailed when a new survey response comes in.', 'pollquest' ),
				'icon'        => 'mail',
				'installed'  => true,
				'active'     => true,
				'locked'     => false,
			],
			[
				'id'          => 'conditional-logic',
				'name'        => __( 'Conditional logic', 'pollquest' ),
				'description' => __( 'Show or skip questions based on previous answers.', 'pollquest' ),
				'icon'        => 'git-branch',
				'installed'  => true,
				'active'     => true,
				'locked'     => false,
			],
			[
				'id'          => 'csv-export',
				'name'        => __( 'CSV export', 'pollquest' ),
				'description' => __( 'Download all survey responses as a CSV file.', 'pollquest' ),
				'icon'        => 'download',
				'installed'  => true,
				'active'     => true,
				'locked'     => false,
			],
			[
				'id'          => 'heatmaps',
				'name'        => __( 'Heatmaps', 'pollquest' ),
				'description' => __( 'Visualize where visitors click and how they interact with your pages.', 'pollquest' ),
				'icon'        => 'mouse-pointer-click',
				'installed'  => true,
				'active'     => true,
				'locked'     => false,
			],
			[
				'id'          => 'post-ratings',
				'name'        => __( 'Post ratings', 'pollquest' ),
				'description' => __( 'Embed star or thumbs ratings on posts via shortcode.', 'pollquest' ),
				'icon'        => 'star',
				'installed'  => true,
				'active'     => true,
				'locked'     => false,
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
		// All features are now included, no Pro tier
		return $addon;
	}

	/**
	 * Check whether a Pro feature module is active.
	 *
	 * @param string $feature Feature slug.
	 * @return bool
	 */
	private static function is_pro_feature_active( string $feature ): bool {
		return true; // All features are active
	}
}
