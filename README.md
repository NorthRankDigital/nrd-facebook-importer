# NRD Facebook Importer

**NRD Facebook Importer** is a WordPress plugin developed by North Rank Digital. It connects to Facebook's Graph API to import events from your Facebook page into WordPress as custom post types, with automatic scheduling and media handling.

## Features

- **Facebook OAuth Authentication** — Connect via Facebook Login with long-lived user tokens (60-day expiry).
- **Scheduled Imports** — Automatically sync events on a configurable schedule (hourly, twice daily, daily, weekly).
- **Smart Event Management** — Automatically removes past events, deduplicates overlapping entries, and cleans up canceled/removed events.
- **Media Handling** — Downloads event cover images as featured images with proper alt text. Re-downloads if accidentally removed.
- **Import Log** — Tracks sync activity with retention of the last 5 sync cycles.
- **Admin Table** — Sortable columns (start, end, status), filterable by status and month.
- **Optional Public Pages** — Enable frontend archive (`/events/`) and single event pages with REST API support.
- **Email Notifications** — Optional email alert to the site admin when the Facebook token is expiring or has expired.
- **Date Range Control** — Configure how far ahead to pull events (1–12 months).
- **Default Event Image** — Set a fallback image URL for events without a cover photo.

## Requirements

- WordPress 5.0+
- PHP 8.0+
- A Facebook App (App ID and App Secret)
- SMTP plugin recommended for email notifications (e.g., Fluent SMTP)

## Installation

1. Download the plugin from the GitHub repository.
2. Upload to your WordPress site's `wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.

## Configuration

### FB Connection Tab
1. Enter your **Facebook App ID** and **App Secret**.
2. Save credentials, then click **Authenticate with Facebook**.
3. The connection status card shows token health and days remaining.

**Required for Facebook App setup:**
- Add your **Site URL** and **Redirect URI** (shown on the settings page) to your Facebook App's Valid OAuth Redirect URIs.

### Schedule Tab
1. Enter your **Facebook Page ID** (found at your Facebook Page → About → Page transparency).
2. Optionally set a **Default Event Image URL** for events without cover photos.
3. Choose a **Date Range** for how far ahead to pull events.
4. Set the **Schedule** interval or leave as "Never" for manual imports only.
5. Use **Run Import Now** to trigger an immediate sync.

### Options Tab
- **Email Expiry Alert** — Sends one email when the token enters the 7-day warning window, and another if it expires.
- **Enable Event Pages** — Makes events publicly accessible with archive and single post pages, and enables REST API access.

### Log Tab
- View import activity including created, updated, and deleted events.
- Logs are automatically trimmed to the last 5 sync cycles.

## Frequently Asked Questions

### How do I get my Facebook App ID and App Secret?
Create a Facebook App at [Facebook Developers](https://developers.facebook.com/). The App ID and App Secret are in your app's Settings → Basic.

### Where do I find my Facebook Page ID?
Go to your Facebook Page → About → Page transparency. The Page ID is listed there.

### How often are events updated?
Based on the schedule you set: hourly, twice daily, daily, or weekly. You can also run imports manually at any time.

### What happens to canceled events?
Events that exist in WordPress but are no longer returned by the Facebook API are automatically deleted along with their media attachments.

### What happens to past events?
Past events are automatically removed during each sync. An event is considered past when the current time is beyond its end time, or if there's no end time, one hour after the start time.

### Does the email notification require anything special?
It uses WordPress's built-in `wp_mail()` function. For reliable delivery, use an SMTP plugin like Fluent SMTP.

## License

This project is licensed under the GPL v2 License. See the [LICENSE](LICENSE) file for details.
