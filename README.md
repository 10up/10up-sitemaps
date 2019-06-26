# 10up Sitemaps

This is a simple sitemap plugin meant to run at scale. Sitemaps are only updated via WP-CLI. Output is saved in an option for fast reading/displaying on the front end.

## Setup/Usage

1. Install the plugin.
2. Run the WP-CLI command: `wp tenup-sitemaps generate`
3. Add WP-CLI command to cron job. For multisite, add a command for each site in the network.

You can pass `--range` to the `generate` command to only index content within a certain age range. `wp tenup-sitemaps generate --range=12` would only index content created/updated within the last 12 months.

The plugin indexes all public posts, post type archives, and public taxonomy term archives. For posts, images are parsed and included. Translated content needs to be manually filtered in via `tenup_sitemaps_term_translations`.
