<?php

namespace OviUsers;

use OviUsers\RegisteredDate\Column;
use OviUsers\RegisteredDate\Filter;

/**
 * Class Plugin.
 */
final class Plugin {

	/**
	 * Single instance of the plugin.
	 *
	 * @var Plugin
	 */
	private static $instance;

	/**
	 * You shall not pass.
	 */
	private function __construct() {}

	/**
	 * Main instance.
	 *
	 * Only one instance of the plugin exists in memory at any one time.
	 * Also prevent the need to define globals all over the place.
	 */
	public static function instance(): Plugin {

		if (
			self::$instance === null ||
			! self::$instance instanceof self
		) {
			self::$instance = new self();

			self::$instance->init();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Set up whatever the plugin needs before any hook fires.
	 */
	private function init(): void {

		// Nothing to set up yet.
	}

	/**
	 * Register the hooks of every feature.
	 */
	private function hooks(): void {

		// Fires only while wp-admin/users.php is loading, just before that screen builds its
		// list table. Narrower than is_admin() and admin_init, both of which are also true
		// for admin-ajax.php and for every other admin screen.
		add_action( 'load-users.php', [ Column::class, 'hooks' ] );
		add_action( 'load-users.php', [ Filter::class, 'hooks' ] );
	}
}
