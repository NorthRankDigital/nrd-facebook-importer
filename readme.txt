=== NRD Facebook Importer ===
Contributors: northrankdigital
Tags: facebook, events, importer, social media
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import Facebook events from a Facebook page into WordPress as custom post types.

== Description ==

NRD Facebook Importer connects to the Facebook Graph API and imports events from your Facebook page into WordPress. Events are stored as a custom post type with full control over scheduling, media handling, and display.

= Features =

* Facebook OAuth authentication with long-lived user tokens
* Scheduled imports (hourly, twice daily, daily, weekly)
* Automatic removal of past and canceled events
* Event deduplication for overlapping entries
* Featured image downloading with alt text
* Import log with sync history
* Sortable and filterable admin event table
* Optional public event pages with REST API support
* Email notifications for token expiry

== Installation ==

1. Upload the plugin to `wp-content/plugins/nrd-facebook-importer/`
2. Activate the plugin through the Plugins menu
3. Go to Facebook Import > FB Connection to enter your App ID and App Secret
4. Authenticate with Facebook
5. Go to Schedule Import to set your Page ID and sync interval

== Changelog ==

= 2.0.1 =
* Added: WordPress-standard readme.txt with changelog
* Added: .gitignore file

= 2.0.0 =
* Added: Options tab with configurable plugin settings
* Added: Enable Event Pages setting — makes events public with archive/single pages and REST API
* Added: Email expiry alert setting — notifies admin when token is expiring or expired
* Added: Event deduplication for events with the same start and end time
* Added: Automatic cleanup of canceled/removed events not returned by the API
* Added: Sortable columns (start, end, status) on the admin event table
* Added: Status and month filters on the admin event table
* Added: Featured image re-download if accidentally removed from a post
* Added: Image alt text automatically set to event name
* Added: Past event cleanup using end time (or start time + 1 hour)
* Added: Log retention — keeps last 5 sync cycles instead of unlimited
* Changed: Renamed Settings tab to FB Connection
* Changed: Page selection changed to manual Page ID text input
* Changed: Events CPT is private by default (no public URLs unless enabled)
* Changed: Meta box fields styled with two-column grid layout
* Changed: First card on each tab connects seamlessly to navigation tabs
* Fixed: Sortable columns filter name for hyphenated post types
* Fixed: Log pagination TypeError on PHP 8.2+
* Fixed: Meta box CSS not loading on CPT edit screens
* Fixed: Removed debug logging of sensitive credentials
* Fixed: All error_log calls gated behind WP_DEBUG
* Fixed: Added wp_unslash to meta box nonce and field sanitization
* Security: Removed logTokenPermissions debug method that exposed app secrets
* Security: Cleaned up uninstall.php to remove all plugin options

= 1.1.9 =
* Fixed: CSS bug

= 1.1.8 =
* Fixed: Update issue

= 1.1.7 =
* Fixed: Date handling in post creation

= 1.1.6 =
* Fixed: Date issues

= 1.1.5 =
* Increased event pull count

== Upgrade Notice ==

= 2.0.0 =
Major update with new options tab, event deduplication, automatic cleanup of canceled events, sortable admin columns, and security improvements. Review the new Options tab after updating.
