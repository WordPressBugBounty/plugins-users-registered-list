<?php

namespace OviUsers\Helpers;

/**
 * Locating and versioning of the plugin assets.
 */
class Assets {

	/**
	 * Get the URL of a file shipped in the plugin assets directory.
	 *
	 * @param string $file File name relative to the /assets/ directory in the plugin.
	 */
	public static function url( string $file ): string {

		return plugins_url( 'assets/' . ltrim( $file, '/\\' ), OVIUSERS_PLUGIN_FILE );
	}

	/**
	 * Version to register an asset under.
	 *
	 * The version changes on every request while SCRIPT_DEBUG is on, so a reload always
	 * serves the file that is on disk instead of the one the browser already cached.
	 *
	 * @param string $current Version to use when not debugging. Plugin version when empty.
	 */
	public static function ver( string $current = '' ): string {

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return (string) time();
		}

		return $current === '' ? OVIUSERS_VERSION : $current;
	}

	/**
	 * Script handles and version that the build recorded for one entry point.
	 *
	 * `wp-scripts build` writes an `<entry>.asset.php` next to every bundle, listing the
	 * WordPress script handles the bundle expects to find as globals and a hash of the
	 * bundle contents. Reading it keeps that list from being maintained by hand, and keeps
	 * the cache busting tied to the code rather than to the plugin version.
	 *
	 * @param string $entry Entry point name, without a path or an extension.
	 *
	 * @return array {
	 *     @type string[] $dependencies Script handles the bundle depends on.
	 *     @type string   $version      Version to register the bundle under.
	 * }
	 */
	public static function build( string $entry ): array {

		$file = OVIUSERS_PLUGIN_DIR . 'assets/build/' . $entry . '.asset.php';

		$asset = is_readable( $file ) ? include $file : [];

		if ( ! is_array( $asset ) ) {
			$asset = [];
		}

		return [
			'dependencies' => isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] ) ? $asset['dependencies'] : [],
			'version'      => self::ver( isset( $asset['version'] ) ? (string) $asset['version'] : '' ),
		];
	}
}
