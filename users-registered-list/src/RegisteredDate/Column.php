<?php

namespace OviUsers\RegisteredDate;

/**
 * Sortable "Registered" column on the users list table.
 */
class Column {

	/**
	 * Key the column is registered under on the users list table.
	 */
	const COLUMN = 'reg_date';

	/**
	 * Register the WordPress hooks this feature needs.
	 */
	public static function hooks(): void {

		add_filter( 'manage_users_columns', [ self::class, 'add_column' ] );
		add_filter( 'manage_users_custom_column', [ self::class, 'render_column' ], 10, 3 );
		add_filter( 'manage_users_sortable_columns', [ self::class, 'add_sortable_column' ] );
	}

	/**
	 * Add the column to the users list table in the admin area.
	 *
	 * @param array $columns The list of columns.
	 *
	 * @return array
	 */
	public static function add_column( $columns ): array {

		$columns[ self::COLUMN ] = __( 'Registered', 'users-registered-list' );

		return $columns;
	}

	/**
	 * Display the registration date in the users list table.
	 *
	 * @param string $value       Expected default value.
	 * @param string $column_name Column name we should check.
	 * @param int    $user_id     ID of a user we are checking.
	 *
	 * @return string
	 */
	public static function render_column( $value, $column_name, $user_id ) {

		if ( $column_name !== self::COLUMN ) {
			return $value;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return $value;
		}

		// The registration date is stored in UTC, so pin the timezone when parsing.
		$timestamp = strtotime( $user->user_registered . ' +0000' );

		if ( ! $timestamp ) {
			return $value;
		}

		// The dotted underline and help cursor hint that the date is hoverable.
		return sprintf(
			'<span title="%1$s" style="text-decoration: underline dotted; text-underline-offset: 2px; cursor: help;">%2$s</span>',
			/* translators: %s - human-readable time difference, e.g. "3 years". */
			esc_attr( sprintf( __( '%s ago', 'users-registered-list' ), human_time_diff( $timestamp ) ) ),
			// wp_date() converts the UTC timestamp into the site timezone from Settings > General.
			esc_html( wp_date( self::get_datetime_format(), $timestamp ) )
		);
	}

	/**
	 * Make the column sortable.
	 *
	 * @param array $columns The list of sortable columns.
	 *
	 * @return array
	 */
	public static function add_sortable_column( $columns ): array {

		$columns[ self::COLUMN ] = 'user_registered';

		return $columns;
	}

	/**
	 * Combined date and time format from Settings > General.
	 *
	 * Both options can be saved empty, in which case WordPress own defaults are used.
	 */
	private static function get_datetime_format(): string {

		$date_format = get_option( 'date_format', 'F j, Y' );

		if ( empty( $date_format ) ) {
			$date_format = 'F j, Y';
		}

		$time_format = get_option( 'time_format', 'g:i a' );

		if ( empty( $time_format ) ) {
			$time_format = 'g:i a';
		}

		return $date_format . ' (' . $time_format . ')';
	}
}
