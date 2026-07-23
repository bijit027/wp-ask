<?php
/**
 * Frontend asset loader helper.
 *
 * @package PollQuest
 */

namespace PollQuest\Utils;

/**
 * Class AssetLoader
 */
class AssetLoader {

	/**
	 * Whether to load scripts from the Vite dev server.
	 *
	 * @param string $built_relative_path Path relative to plugin root for the built asset.
	 * @return bool
	 */
	public static function use_vite_dev( string $built_relative_path ): bool {
		if ( defined( 'POLLQUEST_VITE_DEV' ) ) {
			return (bool) POLLQUEST_VITE_DEV;
		}

		return ! file_exists( POLLQUEST_PLUGIN_DIR . ltrim( $built_relative_path, '/' ) );
	}

	/**
	 * Enqueue a Vite dev or production frontend script.
	 *
	 * @param string $handle           Script handle.
	 * @param string $dev_entry        Vite dev entry path (e.g. src/frontend/post-rating.js).
	 * @param string $built_relative   Built asset path relative to plugin root.
	 * @param string $vite_client_slug Unique suffix for the vite client handle.
	 * @return bool True when a script URL was enqueued.
	 */
	public static function enqueue_frontend_script(
		string $handle,
		string $dev_entry,
		string $built_relative,
		string $vite_client_slug = 'vite-client'
	): bool {
		$vite_handle = $handle . '-' . $vite_client_slug;

		if ( self::use_vite_dev( $built_relative ) ) {
			wp_enqueue_script( $vite_handle, 'http://localhost:5173/@vite/client', [], null );
			wp_enqueue_script( $handle, 'http://localhost:5173/' . ltrim( $dev_entry, '/' ), [], null );

			add_filter(
				'script_loader_tag',
				function ( $tag, $script_handle, $src ) use ( $handle, $vite_handle ) {
					if ( in_array( $script_handle, [ $handle, $vite_handle ], true ) ) {
						$tag = str_replace( ' src=', ' type="module" src=', $tag );
					}
					return $tag;
				},
				10,
				3
			);

			return true;
		}

		$script_path = POLLQUEST_PLUGIN_DIR . ltrim( $built_relative, '/' );
		if ( ! file_exists( $script_path ) ) {
			return false;
		}

		wp_enqueue_script(
			$handle,
			POLLQUEST_PLUGIN_URL . ltrim( $built_relative, '/' ),
			[],
			POLLQUEST_VERSION,
			true
		);

		return true;
	}
}
