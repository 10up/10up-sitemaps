# 10up Sitemaps

> This is a simple sitemap plugin meant to run at scale. Sitemaps are only updated via WP-CLI. Output is saved in an option for fast reading/displaying on the front end.

[![Support Level](https://img.shields.io/badge/support-stable-blue.svg)](#support-level) [![Build Status](https://travis-ci.org/10up/10up-sitemaps.svg?branch=master)](https://travis-ci.org/10up/10up-sitemaps) [![Release Version](https://img.shields.io/github/release/10up/10up-sitemaps.svg)](https://github.com/10up/10up-sitemaps/releases/latest) ![WordPress tested up to version](https://img.shields.io/badge/WordPress-v5.8%20tested-success.svg) [![GPLv2 License](https://img.shields.io/github/license/10up/10up-sitemaps.svg)](https://github.com/10up/10up-sitemaps/blob/develop/LICENSE.md) 
## Setup/Usage

1. Install the plugin.
2. Run the WP-CLI command: `wp tenup-sitemaps generate`
3. Add WP-CLI command to cron job. For multisite, add a command for each site in the network.

You can pass `--range` to the `generate` command to only index content within a certain age range. `wp tenup-sitemaps generate --range=12` would only index content created/updated within the last 12 months.

The plugin indexes all public posts, post type archives, and public taxonomy term archives. For posts, images are parsed and included. Translated content needs to be manually filtered in via `tenup_sitemaps_term_translations`.

## Support Level

**Stable:** 10up is not planning to develop any new features for this, but will still respond to bug reports and security concerns. We welcome PRs, but any that include new features should be small and easy to integrate and should not include breaking changes. We otherwise intend to keep this tested up to the most recent version of WordPress.

## Changelog

A complete listing of all notable changes to 10up Sitemaps is documented in [CHANGELOG.md](https://github.com/10up/10up-sitemaps/blob/develop/CHANGELOG.md).

## Like what you see?

<a href="http://10up.com/contact/"><img src="https://10up.com/uploads/2016/10/10up-Github-Banner.png" width="850" alt="Work with us at 10up"></a>
