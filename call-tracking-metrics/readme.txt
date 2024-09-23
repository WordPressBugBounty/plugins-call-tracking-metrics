=== CallTrackingMetrics ===
Contributors: CallTrackingMetrics
Tags: Call tracking, Conversation analytics, Marketing Attribution, Google Ads, Advertising, SEO
Requires at least: 3.5
Tested up to: 6.4.1
Stable tag: 1.2.12

Discover which marketing campaigns, website pages, and search keywords drive phone calls and conversions. Integrate with popular form plugins.

== Description ==

CallTrackingMetrics is a conversation analytics platform that enables marketers to drive data-backed advertising strategies, track every conversion, and optimize ad spend. Discover which marketing campaigns are generating leads and conversions, and use that data to automate lead flows and create better buyer experiences—-across all communication channels.

Start tracking your conversations through our convenient WordPress plugin that includes a dashboard widget which automatically displays your CallTrackingMetrics activities by day. As an added value, you can also integrate with Contact Form 7 and Gravity Forms to see online form data alongside your conversation activities.

Through this integration, you’ll discover exactly which marketing campaigns, site content, and keywords are driving conversions, allowing you to optimize campaigns around true ROI.

== Installation ==

To use our call tracking plugin, you will need an active CallTrackingMetrics account, and will need to enable API Integration on your account. You can then copy the Access Key and Secret Key into the plugin and enable the features you want to use. The plugin has more information on how to do this.

= Features =

* Easy to configure
* Call tracking from a full range of sources
* Know how many of your phone calls are repeat
* Discover which marketing sources provide the best ROI
* Contact Form 7 integration
* Gravity Forms integration

== Changelog ==

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
