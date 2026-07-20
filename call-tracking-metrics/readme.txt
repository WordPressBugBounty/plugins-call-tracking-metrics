=== CallTrackingMetrics ===
Contributors: CallTrackingMetrics
Tags: Call tracking, Conversation analytics, Marketing Attribution, Google Ads, Advertising, SEO
Requires at least: 6.5
Tested up to: 7.0.2
Stable tag: 2.1.10
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

CallTrackingMetrics integrates with your WordPress site to provide powerful call tracking and attribution.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/call-tracking-metrics` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings -> CallTrackingMetrics screen to configure the plugin.

== Changelog ==

= 2.1.10 =
* Compatibility: Tested with WordPress 7.0.2.

= 2.1.9 =
* Fix: Capture Gravity Forms caller names and emails when labels are blank and fields use placeholders.
* Fix: Capture Contact Form 7 caller names from Name-style placeholders when shortcode names are neutral, while preserving type-based email detection.

= 2.1.8 =
* Fix: Restore legacy Gravity Forms country-code behavior for standard phone fields (`country_code = 1`) and avoid sending country names (for example, `United States`) as `country_code`.
* Fix: Sync with CTM now reliably loads all available CTM forms across pages and sorts them alphabetically by name.
* Fix: clean up debug logs.
* Change: duplicate submission prevention is now disabled by default, and existing installs are migrated to disabled with a one-time admin notice.

= 2.1.7 =
* Fix: Gravity Forms phone detection now prioritizes the field type, matching legacy behavior.

= 2.1.6 =
* Fix: Ensure all Gravity Forms and Contact Form 7 forms are visible in the "Manage Forms" tab, not just imported ones.
* Fix: Resolve PHP warning in Contact Form 7 integration caused by invalid regex pattern.

= 2.1.5 =
* Fix: Prevent settings checkboxes from reverting to enabled state on save.
* Fix: Allow manual override of tracking script to correctly save and persist custom edits.
* Fix: Ensure fetching tracking script does not automatically save to database.
* Fix: Granular duplicate prevention options (Session ID vs IP) are now correctly respected.
* UI: Remove non-functional "Refresh Script" button.

= 2.1.4 =
* minor fixes

= 2.1.3 =
* Fix CF7 mail sending

= 2.1.2 =
* Fixed Gravity Forms redirect regression by restoring AJAX response handling.

= 2.1.1 =
* Downgraded PHP requirement to 8.2 for broader environment compatibility.
* Fixed Contact Form 7 integration custom field mapping regression.
* Improved API service to preserve nested form reactor data.

= 2.1.0 =
* Fixed Gravity Forms integration custom field mapping regression.
* Added support for CTM_API_BASE_URL constant for environment-specific API overrides.
* Improved duplicate submission prevention logic.
* Added detailed debug logging system.

= 2.0.3 =

= 2.0 =
* Major Update: Modernized codebase with improved performance and reliability
* Added comprehensive duplicate submission prevention for forms
* Added support for regional API endpoints (Global vs. Europe)
* Improved Contact Form 7 and Gravity Forms integrations
* Enhanced security and logging capabilities
* Updated dependencies and compatibility checks

= 1.2.15 =
* test with wordpress 6.7.2
* verify build

= 1.2.14 =
* update tested up to version
* verify build

= 1.2.13 =
* test with wordpress 6.6.1
* verify build

= 1.2.12 =
* test with wordpress 6.4.1
* verify build

= 1.2.11 =
* test with wordpress 6.1
* verify build

= 1.2.10 =
* update assets
* verify build

= 1.2.9 =
* update assets
* update latest tested version

= 1.2.8 =
* add abort to submit_cf7 allowing easier support for multi-step form submissions

= 1.2.7 =
* add filters for contact form 7 and gravity forms
   - ctm_cf7_formreactor_data and ctm_gf_formreactor_data

= 1.2.6 =
* fixes for contact form 7

= 1.2.5 =
* fixes for gravity forms

= 1.2.4 =
* update latest tested version

= 1.2.3 =
* Update data sent on form submission to account for use case of integrating with a single Gravity Forms license being used on multiple domains

= 1.2.2 =
* Update query to fix potential issue displaying daily stats on WP dashboard
* Update Gravity submissions to avoid potential cross-domain form id conflicts

= 1.2.1 =
* Replace deprecated on_sent_ok in Contact Form 7 integration with wpcf7mailsent
* Add support for intl_tel for Contact Form 7 and international phone numbers
* Document the type=tel requirement for Contact Form 7 and Gravity Forms integrations
* Add plugin recommendation when using Contact Form 7 with international numbers

= 1.2.0 =
* fix for deactivation error on plugin update

= 1.1.9 =
* fix for deactivation error on plugin update

= 1.1.8 =
* include call to plugin.php to avoid error

= 1.1.7 =
* Delete old, unneeded file

= 1.1.6 =
* Fix for potential Rocketscript conflict
* Replace deprecated WP arguments
* Update plugin options handling
* Enqueue scripts in wp-admin when needed
* Update method to manually install CTM tracking code
* Plugin uninstall now cleans CTM plugin options

= 1.1.5 =
* Fix for potential JS variable conflict between Gravity Forms and other plugins

= 1.1.4 =
* No changes

= 1.1.3 =
* Fix potential crash on cf7_enabled undefined

= 1.1.2 =
* Contact Form 7 forms no longer have Additional Settings added to them when the integration is disabled

= 1.1.1 =
* Gravity Forms embedded in an iframe now redirect within the iframe

= 1.1 =
* Fixing a bug with Gravity Forms embedded inside iframes
* Other bug fixes

= 1.0.9 =
* Various bug fixes

= 1.0.8 =
* Fixing another compatibility issue with Gravity Form redirects
* Other various bug fixes

= 1.0.7 =
* Fixing a compatibility issue with Gravity Form redirects

= 1.0.6 =
* Submitted forms should now actually MATCH the visitor details in the call log. Sorry about that.

= 1.0.5 =
* Submitted forms should now have visitor details

= 1.0.4 =
* Fixed a bug that caused the tracking script to not appear on certain WordPress websites

= 1.0.3 =
* Fixed a bug causing the spinner in Contact Form 7 to not disappear after submitting the form
* Fixed a bug with Gravity Form submissions rendering text to the top of the page
* Added Logs for Contact Form 7 and Gravity Forms to help diagnose submission problems

= 1.0.2 =
* Namespacing the CSS classes to not conflict with WordPress

= 1.0.1 =
* Restored support for manually installing the tracking code, without using API keys

= 1.0 =
* Rewritten Contact Form 7 integration
* Improved Gravity Forms integration
* Zero-config tracking code installation
* New user interface
* Other bug fixes and improvements

= 0.5.2 =
* Fixing a bug with submitting Gravity Forms

= 0.5.1 =
* Fixed a syntax error with versions of PHP < 5.4.0

= 0.5 =
* Gravity Forms integration
* Minor UI improvements
* Added a plugin icon

= 0.4.5 =
* fix admin settings link

= 0.4.4 =
* release updated plugin

= 0.4.3 =
* visitor data tracking update

= 0.4.2 =
* contact form 7 integration

= 0.4.1 =
* better handling of user input errors when using the API keys

= 0.3.8 =
* text updates
* silence debug messages

= 0.3.7 =
* upgrade charts library
* only show dashboard to privileged users

= 0.3.6 =
* improved error handling

= 0.3.5 =
* fix trailing quote
* upgrade highcharts

= 0.3.4 =
* update host and secure https

= 0.3.2 =
* minior update to handle no api keys better

= 0.3.1 =
* version bump

= 0.3 =
* Add admin dashboard widget provides a quick snapshot of call reporting data

= 0.2 =
* Don't include tracking script in /wp-admin pages

= 0.1 =
* Initial release
