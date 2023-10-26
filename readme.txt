=== 10up Sitemaps ===
Contributors: 10up, tlovett1, jeffpaul
Tags: sitemap, wp-cli, cli
Requires at least: 5.9
Tested up to: 5.9
Stable tag: 1.0.4
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple sitemaps plugin that performs at scale.

== Description ==

This is a simple sitemap plugin meant to run at scale. Sitemaps are only updated via WP-CLI. Output is saved in an option for fast reading/displaying on the front end.

== Setup/Usage ==

1. Install the plugin.
2. Run the WP-CLI command: `wp tenup-sitemaps generate`
3. Add WP-CLI command to cron job. For multisite, add a command for each site in the network.

You can pass `--range` to the `generate` command to only index content within a certain age range. `wp tenup-sitemaps generate --range=12` would only index content created/updated within the last 12 months.

The plugin indexes all public posts, post type archives, and public taxonomy term archives. For posts, images are parsed and included. Translated content needs to be manually filtered in via `tenup_sitemaps_term_translations`.

== Changelog ==

= 1.0.4 - 2023-09-07 =
* Prefix sitemap index in `robots.txt` with line feed.

= 1.0.3 - 2019-08-12 =
* **Fixed:** No empty urls in sitemap.

= 1.0.2 - 2019-08-05 =
* **Added:** WordPress Plugin type.

= 1.0.1 - 2019-08-05 =
* **Changed:** Package name.
* **Fixed:** Log url properly.

= 1.0.0 - 2019-08-01 =
* **Added:** Homepage and post type archive.
* **Added:** Progress estimator.
* **Added:** `stop_the_insanity()` calls.
* **Added:** `robots_txt` filter to include the `sitemap.xml` file.
* **Added:** Page link filter.
* **Removed:** `changefrew` and `priority` from template.

= 0.1.0 - 2019-06-26 =
* **Added:** Initial plugin release! 🎉
* **Added:** Sitemaps are only updated via WP-CLI.
* **Added:** Output is saved in an option for fast reading/displaying on the front end.
