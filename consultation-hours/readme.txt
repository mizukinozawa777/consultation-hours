=== Consultation Hours ===
Contributors: mizukinozawa
Tags: schedule, time, table, business hours, clinic
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin to display a consultation hours table using the [consultation_hours] shortcode.

== Description ==

This plugin generates a table to display the consultation or business hours of a clinic or store using the [consultation_hours] shortcode. Settings can be configured from "Settings > Consultation Hours".

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/consultation-hours` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to "Settings > Consultation Hours" to configure the hours and notes.
4. Use the `[consultation_hours]` shortcode in your posts or pages.

== Changelog ==

= 1.3.1 =
* MAJOR: Replaced all function and option prefixes from `ch_` to `consultation_hours_` to prevent conflicts, as per plugin review team feedback.
* SECURITY: Added direct file access prevention to all PHP files.
* FIX: Added missing `Requires at least` and `Requires PHP` headers to the main plugin file.
* ENHANCEMENT: Implemented internationalization (i18n) for all user-facing strings.

= 1.3.0 =
* SECURITY: Further hardened security in the data saving process by reducing direct references to superglobal variables.

