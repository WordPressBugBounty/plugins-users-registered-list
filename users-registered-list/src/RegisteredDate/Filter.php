<?php

namespace OviUsers\RegisteredDate;

use DateTimeImmutable;
use DateTimeZone;
use OviUsers\Helpers\Assets;

/**
 * Registration date range filter on the users list table.
 */
class Filter {

	/**
	 * Query argument holding the start of the range, as a Y-m-d date in the site timezone.
	 */
	const ARG_AFTER = 'reg_after';

	/**
	 * Query argument holding the end of the range, as a Y-m-d date in the site timezone.
	 */
	const ARG_BEFORE = 'reg_before';

	/**
	 * Name of the built asset bundle backing the control.
	 */
	const ASSET = 'registered-date-filter';

	/**
	 * Format the range is exchanged in, both in the query arguments and in the visible input.
	 *
	 * ISO order keeps the submitted value identical to the validated one, so no locale
	 * aware parsing is needed on either side.
	 */
	const DATE_FORMAT = 'Y-m-d';

	/**
	 * Register the WordPress hooks this feature needs.
	 */
	public static function hooks(): void {

		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue' ] );
		add_action( 'restrict_manage_users', [ self::class, 'render' ] );
		add_filter( 'users_list_table_query_args', [ self::class, 'filter_query_args' ] );
	}

	/**
	 * Load the assets backing the range control.
	 */
	public static function enqueue(): void {

		$asset = Assets::build( self::ASSET );

		// The bundle carries no copy of jQuery or moment. It reads both off the window,
		// from the copies WordPress itself ships, so both handles have to be loaded first.
		// The build detects them too, but naming them here ties the requirement to our own
		// code rather than to whatever the bundler happened to notice.
		$dependencies = array_unique( array_merge( $asset['dependencies'], [ 'jquery', 'moment' ] ) );

		wp_enqueue_style(
			'oviusers-' . self::ASSET,
			Assets::url( 'build/' . self::ASSET . '.css' ),
			[],
			$asset['version']
		);

		// The build emits a mirrored stylesheet next to the stylesheet. This is what makes
		// WordPress reach for it on right to left locales.
		wp_style_add_data( 'oviusers-' . self::ASSET, 'rtl', 'replace' );

		wp_enqueue_script(
			'oviusers-' . self::ASSET,
			Assets::url( 'build/' . self::ASSET . '.js' ),
			$dependencies,
			$asset['version'],
			true
		);

		wp_add_inline_script(
			'oviusers-' . self::ASSET,
			'window.oviusersRegisteredDateFilter = ' . wp_json_encode( self::get_script_data() ) . ';',
			'before'
		);
	}

	/**
	 * Data the range control needs from the server.
	 *
	 * Today, and with it every preset measured back from it, is resolved here rather
	 * than in the browser: `new Date()` reports the timezone of the machine looking at
	 * the screen, which is regularly not the one the site keeps its dates in.
	 */
	private static function get_script_data(): array {

		$today = new DateTimeImmutable( 'now', wp_timezone() );

		$time_format = get_option( 'time_format', 'g:i a' );

		// Saved empty, the option falls back to WordPress's own default, as in the column.
		if ( empty( $time_format ) ) {
			$time_format = 'g:i a';
		}

		// The two clock times are formatted off a fixed UTC day, which has no clock change to
		// bend either of them, so a timezone character in the format would print "UTC" beside
		// the site timezone the popover names. Those characters go; escaped ones are literal.
		$clock_format = trim( preg_replace( '/\\\\.(*SKIP)(*FAIL)|[eIOPpTZ]/s', '', $time_format ) );
		$utc          = new DateTimeZone( 'UTC' );

		return [
			'today'    => $today->format( self::DATE_FORMAT ),
			'after'    => self::get_date( self::ARG_AFTER ),
			'before'   => self::get_date( self::ARG_BEFORE ),
			'presets'  => self::get_presets( $today ),
			// The picker shows dates only, which leaves open whether the last day counts
			// and by whose clock. A range runs from the first second of its start day to the
			// last second of its end day, in the site timezone, and the popover says so.
			'dayStart' => wp_date( $clock_format, 0, $utc ),
			'dayEnd'   => wp_date( $clock_format, DAY_IN_SECONDS - 1, $utc ),
			'timezone' => self::get_timezone_label(),
			// The script builds its own URLs, so it needs the argument names rather
			// than a second copy of them to drift out of step with these.
			'args'     => [
				'after'  => self::ARG_AFTER,
				'before' => self::ARG_BEFORE,
			],
			'i18n'     => [
				'apply'     => __( 'Apply', 'users-registered-list' ),
				'cancel'    => __( 'Cancel', 'users-registered-list' ),
				'custom'    => __( 'Custom Range', 'users-registered-list' ),
				/* translators: %s - start of a range that has no end, formatted as YYYY-MM-DD. */
				'fromOnly'  => __( '%s and later', 'users-registered-list' ),
				/* translators: %s - end of a range that has no start, formatted as YYYY-MM-DD. */
				'untilOnly' => __( '%s and earlier', 'users-registered-list' ),
			],
		];
	}

	/**
	 * The ready-made ranges offered down the side of the popover.
	 *
	 * Every one of them ends today, so only the start is carried. A list rather than a
	 * map of label to start, because the order they are offered in is the order they
	 * appear in and two labels that happened to match would collapse into one.
	 *
	 * The month, quarter and year boundaries are worked out here for the same reason as
	 * today itself: in the browser they would land on whichever month the viewer's own
	 * timezone happens to be in.
	 *
	 * @param DateTimeImmutable $today Today, in the site timezone.
	 *
	 * @return array[] Each entry has a `label` and a Y-m-d `start`.
	 */
	private static function get_presets( DateTimeImmutable $today ): array {

		$year        = (int) $today->format( 'Y' );
		$month_start = $today->modify( 'first day of this month' );

		// Calendar quarters, so the starts are January, April, July and October.
		$quarter = (int) ceil( (int) $today->format( 'n' ) / 3 );

		/* translators: date format of the month start preset, in PHP date() syntax. */
		$month_start_format = _x( 'F jS', 'month start preset', 'users-registered-list' );

		return [
			[
				'label' => __( 'Today', 'users-registered-list' ),
				'start' => $today->format( self::DATE_FORMAT ),
			],
			[
				'label' => __( 'Last 7 Days', 'users-registered-list' ),
				'start' => $today->modify( '-6 days' )->format( self::DATE_FORMAT ),
			],
			[
				'label' => __( 'Last 30 Days', 'users-registered-list' ),
				'start' => $today->modify( '-29 days' )->format( self::DATE_FORMAT ),
			],
			[
				'label' => sprintf(
					/* translators: %s - the first day of the current month, for example "September 1st". */
					__( 'Since %s', 'users-registered-list' ),
					wp_date( $month_start_format, $month_start->getTimestamp() )
				),
				'start' => $month_start->format( self::DATE_FORMAT ),
			],
			[
				'label' => sprintf(
					/* translators: %d - number of the current calendar quarter, 1 through 4. */
					__( 'Since Q%d start', 'users-registered-list' ),
					$quarter
				),
				'start' => $month_start->setDate( $year, ( $quarter - 1 ) * 3 + 1, 1 )->format( self::DATE_FORMAT ),
			],
			[
				'label' => __( 'Year to Date', 'users-registered-list' ),
				'start' => $month_start->setDate( $year, 1, 1 )->format( self::DATE_FORMAT ),
			],
		];
	}

	/**
	 * Name of the site timezone the range is read in.
	 *
	 * @return string A zone such as "Europe/Amsterdam", or "UTC+02:00" for a manual offset.
	 */
	private static function get_timezone_label(): string {

		$timezone = wp_timezone_string();

		// A site set to a manual offset has no zone name, only a bare "+02:00".
		return preg_match( '/^[+-]/', $timezone ) ? 'UTC' . $timezone : $timezone;
	}

	/**
	 * Output the range control next to the bulk role controls above the list table.
	 *
	 * @param string $which Which tablenav is being rendered: "top" or "bottom".
	 */
	public static function render( $which ): void {

		// One popover per screen. Rendering the control twice would duplicate its element
		// ids and give the picker two inputs to attach to.
		if ( $which !== 'top' ) {
			return;
		}

		$after  = self::get_date( self::ARG_AFTER );
		$before = self::get_date( self::ARG_BEFORE );
		?>
		<div class="oviusers-reg-date-filter" x-data="oviusersRegDateFilter">
			<label for="oviusers-reg-date-range" class="screen-reader-text">
				<?php esc_html_e( 'Filter by registration date', 'users-registered-list' ); ?>
			</label>
			<?php
			// Only the field is cloaked, not the whole control: should the script fail to
			// load on some site, an active range still leaves a working "Clear" link
			// rather than a filtered list with nothing to lift the filter with.
			?>
			<input
				type="text"
				id="oviusers-reg-date-range"
				class="oviusers-reg-date-filter__range"
				placeholder="<?php esc_attr_e( 'Registered Dates', 'users-registered-list' ); ?>"
				autocomplete="off"
				readonly
				x-cloak
				x-ref="picker"
				x-bind:value="label"
			>
			<input type="hidden" name="<?php echo esc_attr( self::ARG_AFTER ); ?>" x-bind:value="after">
			<input type="hidden" name="<?php echo esc_attr( self::ARG_BEFORE ); ?>" x-bind:value="before">
			<?php if ( $after !== '' || $before !== '' ) : ?>
				<a class="oviusers-reg-date-filter__clear" href="<?php echo esc_url( self::get_clear_url() ); ?>">
					<?php esc_html_e( 'Clear', 'users-registered-list' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Add the requested range to the query behind the list table.
	 *
	 * @param array $args Arguments passed to WP_User_Query for the current list table.
	 *
	 * @return array
	 */
	public static function filter_query_args( $args ): array {

		$args = (array) $args;

		$clause = self::get_range_clause();

		if ( $clause === [] ) {
			return $args;
		}

		$existing = isset( $args['date_query'] ) && is_array( $args['date_query'] ) ? $args['date_query'] : [];

		// Nest whatever was already there as one group and AND ours against it. Appending
		// into it instead would inherit its relation, so an existing "OR" would turn the
		// range into a third alternative and widen the result set rather than narrow it -
		// and appending a numeric element to a clause that sits flat at the top level stops
		// WP_Date_Query treating it as a clause at all, dropping it from the SQL silently.
		$args['date_query'] = $existing === []
			? [ $clause ]
			: [
				'relation' => 'AND',
				$existing,
				$clause,
			];

		return $args;
	}

	/**
	 * Read one end of the range out of the request.
	 *
	 * @param string $arg Query argument to read.
	 *
	 * @return string Y-m-d date in the site timezone, or an empty string when absent or unusable.
	 */
	private static function get_date( string $arg ): string {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filtering of a screen already gated by the list_users capability.
		$value = isset( $_GET[ $arg ] ) ? sanitize_text_field( wp_unslash( $_GET[ $arg ] ) ) : '';

		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts ) ) {
			return '';
		}

		// Catches dates that fit the shape but do not exist, such as 2026-02-30.
		if ( ! checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * The requested range as a date query clause.
	 *
	 * The two ends need opposite inclusivity - the start is the first second of its day, the
	 * end the first second of the day after - and WP_Date_Query's "inclusive" is one flag
	 * governing both ends of a clause, so each end gets a clause of its own. Naming the
	 * column in each keeps the range pinned to the registration date even when another
	 * plugin has already pointed the shared date query elsewhere.
	 *
	 * @return array A clause, a relation of two, or an empty array when nothing was requested.
	 */
	private static function get_range_clause(): array {

		$ends = [];

		$after = self::to_utc( self::get_date( self::ARG_AFTER ) );

		if ( $after !== [] ) {
			$ends[] = [
				'column'    => 'user_registered',
				'inclusive' => true,
				'after'     => $after,
			];
		}

		$before = self::to_utc( self::get_date( self::ARG_BEFORE ), true );

		if ( $before !== [] ) {
			$ends[] = [
				'column'    => 'user_registered',
				'inclusive' => false,
				'before'    => $before,
			];
		}

		if ( count( $ends ) === 1 ) {
			return $ends[0];
		}

		return $ends === [] ? [] : array_merge( [ 'relation' => 'AND' ], $ends );
	}

	/**
	 * Turn one end of the range into the UTC datetime the column can be compared against.
	 *
	 * Returned as the year/month/day/hour/minute/second parts WP_Date_Query accepts rather
	 * than a datetime string, and that is not cosmetic. Handed a string, its
	 * build_mysql_datetime() re-reads it in the site timezone and formats it back, which is
	 * a passthrough only while that wall clock exists there. Ours is already UTC, so in a
	 * zone that skipped the interval it names - Samoa has no December 30th 2011 - the value
	 * is silently moved forward and the bound stops describing the requested day. The parts
	 * form is assembled with sprintf() and never re-parsed, so it survives verbatim.
	 *
	 * @param string $date     Y-m-d date in the site timezone. An empty string passes through.
	 * @param bool   $next_day Whether to take the start of the following day instead, for use
	 *                         as an exclusive upper bound.
	 *
	 * @return array UTC datetime parts, or an empty array when there is nothing to convert.
	 */
	private static function to_utc( string $date, bool $next_day = false ): array {

		if ( $date === '' ) {
			return [];
		}

		if ( $next_day ) {
			// The day after, as a plain calendar step taken in UTC so no transition in the
			// site timezone can bend it, and then read as local midnight below. Asking for
			// "23:59:59" of the requested day instead would be wrong wherever a timezone
			// skipped that date outright - Samoa has no December 30th 2011 - because PHP
			// normalizes the nonexistent time forward and the range would silently land on
			// the following day. Taking both ends from a local midnight makes such a range
			// start and end at the same instant, which correctly matches nothing.
			$date = ( new DateTimeImmutable( $date, new DateTimeZone( 'UTC' ) ) )
				->modify( '+1 day' )
				->format( self::DATE_FORMAT );
		}

		$local = DateTimeImmutable::createFromFormat(
			self::DATE_FORMAT . ' H:i:s',
			$date . ' 00:00:00',
			wp_timezone()
		);

		if ( ! $local ) {
			return [];
		}

		// user_registered holds UTC, so the shift out of the site timezone happens here.
		$utc = $local->setTimezone( new DateTimeZone( 'UTC' ) );

		// Shifting off the bottom of the representable range leaves a year WP_Date_Query
		// rejects as not describing a valid date: Samoa's historical +12:33:04 puts local
		// midnight of January 1st, year 1 in year 0. No registration can sit down there, so
		// the bound is simply dropped rather than passed on to be complained about. The top
		// of the range needs no equivalent guard, because the day after 9999-12-31 has no
		// four digit year for the parser above to read back and fails there instead.
		if ( (int) $utc->format( 'Y' ) < 1 ) {
			return [];
		}

		return [
			'year'   => (int) $utc->format( 'Y' ),
			'month'  => (int) $utc->format( 'n' ),
			'day'    => (int) $utc->format( 'j' ),
			'hour'   => (int) $utc->format( 'G' ),
			'minute' => (int) $utc->format( 'i' ),
			'second' => (int) $utc->format( 's' ),
		];
	}

	/**
	 * Current screen without the range, for the link that removes the filter.
	 */
	private static function get_clear_url(): string {

		// Paging goes too: the page the range was on rarely exists once it is lifted.
		return remove_query_arg( [ self::ARG_AFTER, self::ARG_BEFORE, 'paged' ] );
	}
}
