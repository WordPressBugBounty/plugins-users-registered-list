<?php
/**
 * Plugin Name:       Users Registration Date
 * Plugin URI:        https://ovirium.com
 * Description:       New sortable "Registered" date column on the Users page in wp-admin area.
 * Author:            Slava Abakumov
 * Author URI:        https://ovirium.com
 * Version:           1.1.0
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Reg column to users list in admin area
 *
 * @since 1.0.0
 *
 * @param array $column The list of columns.
 */
function url_modify_user_table( $column ) {

	$column['reg_date'] = __( 'Registered', 'users-registered-list' );

	return $column;
}

add_filter( 'manage_users_columns', 'url_modify_user_table' );

/**
 * Display an actual value in a table of users.
 *
 * @since 1.0.0
 *
 * @param string $val         Expected default value.
 * @param string $column_name Column name we should check.
 * @param int    $user_id     ID of a user we are checking.
 */
function url_modify_user_table_row( $val, $column_name, $user_id ) {

	if ( $column_name !== 'reg_date' ) {
		return $val;
	}

	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return $val;
	}

	// The registration date is stored in UTC, so pin the timezone when parsing.
	$timestamp = strtotime( $user->user_registered . ' +0000' );

	if ( ! $timestamp ) {
		return $val;
	}

	$date_format = get_option( 'date_format', 'F j, Y' );
	if ( empty( $date_format ) ) {
		$date_format = 'F j, Y';
	}

	$time_format = get_option( 'time_format', 'g:i a' );
	if ( empty( $time_format ) ) {
		$time_format = 'g:i a';
	}

	// The dotted underline and help cursor hint that the date is hoverable.
	return sprintf(
		'<span title="%1$s" style="text-decoration: underline dotted; text-underline-offset: 2px; cursor: help;">%2$s</span>',
		/* translators: %s - human-readable time difference, e.g. "3 years". */
		esc_attr( sprintf( __( '%s ago', 'users-registered-list' ), human_time_diff( $timestamp ) ) ),
		// wp_date() converts the UTC timestamp into the site timezone from Settings > General.
		esc_html( wp_date( $date_format . ' (' . $time_format . ')', $timestamp ) )
	);
}

add_filter( 'manage_users_custom_column', 'url_modify_user_table_row', 10, 3 );

/**
 * Make the column sortable.
 *
 * @since 1.0.0
 *
 * @param array $sortable The list of sortable columns.
 */
function url_modify_user_table_sortable( $sortable ) {

	$sortable['reg_date'] = 'user_registered';

	return $sortable;
}

add_filter( 'manage_users_sortable_columns', 'url_modify_user_table_sortable' );
