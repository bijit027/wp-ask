=== PollQuest – Surveys & Feedback Forms ===
Contributors: bijit027
Tags: survey, poll, feedback form, ratings, forms
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Source Code: https://github.com/bijit027/pollquest

Create surveys, polls, and feedback forms for WordPress.

== Development ==

The JavaScript and CSS assets are built using Vite. Source code is available in the `src/` directory.

To build the assets:
1. Install Node.js dependencies: `npm install`
2. Build for production: `npm run build`

The built assets are output to the `assets/` directory.

== Description ==

Create surveys, polls, and feedback forms for WordPress with multiple question types, advanced targeting, conditional logic, and real-time analytics.

= Features =

* Multiple question types (text, radio, checkbox, star ratings, NPS, email, number, dropdown, date, yes/no, file upload)
* Advanced targeting rules (page, referrer, time on page, scroll depth, device, exit intent)
* Conditional logic for skip/show questions
* Real-time analytics with per-question charts
* CSV export functionality
* Date range filtering for responses
* Survey templates
* Heatmaps module
* Post ratings with shortcodes
* File upload support with WordPress media library integration

== Installation ==

1. Install the PollQuest plugin for WordPress via the WordPress.org plugin repository or by uploading the files to your server.
2. Activate the PollQuest plugin.
3. Navigate to PollQuest → Surveys → Add New Survey.
4. Create your first survey and start collecting feedback!

== Source Code ==

The full source code including all build files is available on GitHub:
https://github.com/bijit027/pollquest

Build requirements:
- Node.js 18+
- npm install
- npm run build

== Frequently Asked Questions ==

= How does PollQuest help me? =

PollQuest allows you to collect user feedback to understand visitor behavior and improve your website.

= Where can I find support? =

Support is available on WordPress.org forums.

= How do I create a survey? =

Go to PollQuest → Surveys → Add New Survey. You can choose from pre-built templates or start from scratch.

= Can I customize the survey appearance? =

Yes, you can customize the widget color, position, and confirmation message in the survey settings.

= Does this plugin support file uploads? =

Yes, PollQuest supports file uploads with configurable file type restrictions and size limits.

= Can I export survey responses? =

Yes, you can export all responses as a CSV file from the Results page.

= Does this work with page builders? =

Yes, PollQuest works with all page builders including Elementor, Divi, and Gutenberg using the shortcode.

= Is this plugin GDPR compliant? =

PollQuest is designed with privacy in mind. All survey and response data is stored locally in your WordPress database. Optionally, if the site owner enables the Gravatar/avatar setting, respondent email hashes are sent to Gravatar.com to display profile photos — this feature is disabled by default and must be explicitly enabled. No other data is sent to third-party services. GDPR compliance also depends on your own site's data retention and consent practices.

== Screenshots ==

1. Report overview screen to analyze results and responses
2. Select a prebuilt survey template or create your own
3. Create questions with the survey builder
4. Customize colors and branding
5. Set up display rules and targeting
6. View results in detail and export

== Changelog ==

= 1.0.0: July 23, 2026 =
* New: Initial release
* Multiple question types (rating, NPS, text, email, number, radio, checkbox, dropdown, date, yes/no, file upload)
* Advanced targeting rules (page, referrer, time on page, scroll depth, device, exit intent)
* Conditional logic for skip/show questions
* Real-time analytics with per-question charts
* CSV export functionality
* Date range filtering for responses
* Survey templates
* Heatmaps module
* Post ratings with shortcodes
* File upload support with WordPress media library integration

== Upgrade Notice ==

No upgrade notice required for initial release.

== Credits ==

Developed by Bijit Deb

== License ==

This plugin is released under the GPLv2 or later license.

== Privacy Policy ==

This plugin stores all survey and response data in your WordPress database. No data is sent to external services by default. If the site owner enables the optional Gravatar avatar setting, a hash of respondent email addresses is sent to gravatar.com to load profile photos — this is off by default and opt-in only. You can delete all survey data at any time from the plugin settings.
