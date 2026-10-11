<?php
/**
 * Plugin Name:       Users Registration Date
 * Plugin URI:        https://ovirium.com
 * Description:       Sortable "Registered" date column and a registration date range filter on the Users page in wp-admin.
 * Author:            Slava Abakumov
 * Author URI:        https://ovirium.com
 * Version:           1.2.0
 * Requires at least: 5.3
 * Requires PHP:      7.4
 * Text Domain:       users-registered-list
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see <https://www.gnu.org/licenses/>.
 */

use OviUsers\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WPForms.Comments.PHPDocDefine.MissPHPDoc
define( 'OVIUSERS_VERSION', '1.2.0' );
define( 'OVIUSERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OVIUSERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OVIUSERS_PLUGIN_FILE', __FILE__ );
// phpcs:enable

/**
 * One function to rule them all.
 */
function oviusers(): Plugin {

	require_once __DIR__ . '/vendor/autoload.php';

	return Plugin::instance();
}

add_action(
	'plugins_loaded',
	static function () {

		oviusers();

		/**
		 * By this time the plugin has been loaded.
		 */
		do_action( 'oviusers_loaded' );
	}
);
