=== Users Registration Date - Sortable Users Column ===
Contributors: slaFFik
Tags: registration date, users list, user management, admin columns, recently registered
Requires at least: 5.3
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

See when each user registered: a sortable "Registered" date column on the Users page. Sort users by registration date, shown in your site timezone.

== Description ==

**Users Registration Date** adds a sortable "Registered" date column to the Users page in the wp-admin area, so you can see when each user registered on your site and sort users by registration date.

WordPress stores the registration date for every user account, but the users list never shows it. Without a plugin, checking it means digging into the database or reaching for WP-CLI. This plugin surfaces that date right on the screen you already use for user management. Activate it, open the Users page, and the "Registered" column is there.

= Key Features =

* **Sortable "Registered" column**: click the column header to sort users by registration date, ascending (oldest users first) or descending (newest users first).
* **Human-readable age on hover**: hover over any date to see how long ago the user registered, like "5 days ago" or "3 years ago".
* **Respects your site settings**: the column uses the date format, time format, and timezone you set on the Settings > General page.
* **Zero configuration**: there is no settings page and nothing to set up, and the plugin stores nothing in your database.
* **Lightweight**: a single small file built on the native WordPress admin columns hooks. It loads no JavaScript, adds no database queries, and does nothing on the front end of your site.

= Find Recently Registered Users =

Sort the "Registered" column in descending order to bring recently registered users to the top of the users list. This is handy for spotting spam registrations, checking that new user registration works, or keeping an eye on how your audience grows.

= Works With Any WordPress Users =

The column shows the registration date for every WordPress user account, no matter how it was created:

* Customers created by WooCommerce and other eCommerce plugins.
* Members registered through membership, community, or LMS plugins.
* Subscribers added by newsletter and form plugins.
* Users created manually in wp-admin or via WP-CLI.

= Get Started =

Install and activate the plugin, and the next time you open the Users page you will know exactly when every user joined your site. Thousands of WordPress sites already run it.

If the plugin helps you, please leave a review. Reviews help other WordPress users find it.

== Installation ==

**Backend installation:**

1. Install the plugin via Plugins > Add New Plugin. Search for "users registration date".
2. Click the "Install Now" button, and then "Activate".

**Manual installation:**

1. Download and unzip the plugin.
2. Upload the `users-registered-list` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the "Plugins" menu in WordPress.

Now open the Users page: the "Registered" column is there.

== Frequently Asked Questions ==

= How do I see when a user registered? =

Install and activate the plugin, then go to the Users page in wp-admin. A new "Registered" column shows the registration date and time for each user.

= How do I find recently registered users? =

Click the "Registered" column header to sort users by registration date. Descending order shows the most recently registered users first.

= What date format and timezone are used? =

The date and time follow the format and timezone settings from your Settings > General page. Hover over a date and you will also see a human-readable age, like "5 days ago".

= Does it work with WooCommerce or Easy Digital Downloads customers? =

Yes. WooCommerce and Easy Digital Downloads customers are regular WordPress users, so their registration dates appear in the column like everyone else's.

= Does it work on WordPress multisite? =

Yes. The "Registered" column appears on the Users page of every subsite where the plugin is active. The Network Admin users screen already has its own built-in Registered column provided by WordPress.

= Does the plugin change my database? =

No. It is display-only: it reads the registration date WordPress already stores and adds nothing to your database. When you deactivate it, the column disappears and nothing is left behind.

== Screenshots ==

1. Users lists column

== Languages ==

You can translate the plugin into your language at [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/users-registered-list/). Every completed translation makes the plugin easier to find for WordPress users in that language.

== Changelog ==

= 1.1.0 (Aug 16, 2026) =

* IMPORTANT: The minimum required PHP version is now 7.4.
* Added: Hover over the registration date to see how long ago the user registered in a human readable format ("5 days ago").
* Changed: Confirmed compatibility with WordPress 7.1.
* Changed: The minimum required WordPress version is now 5.3.
* Fixed: Slightly improved performance of the plugin on sites (realistically visible on busy sites with a lot of users).
* Fixed: The registration date and time are now displayed in the site timezone instead of UTC.

= 1.0.1 (Jan 20, 2024) =

* Changed: Confirmed compatibility with WordPress 6.4.
* Fixed: There were several localization issues
* Fixed: Cleaned up the code.

= 1.0.0 (Nov 11th, 2013) =

* Initial release

== Upgrade Notice ==

= 1.1.0 =
Requires PHP 7.4+ and WordPress 5.3+. Registration dates now display in your site timezone, and hovering a date shows how long ago the user registered.
