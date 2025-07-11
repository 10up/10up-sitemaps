# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/).

## [Unreleased] - TBD

## [1.0.5] - 2025-07-14

**Note that this release bumps the WordPress minimum version from 6.3 to 6.6.**

### Added

- `readme.txt` file (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9) via [#19](https://github.com/10up/10up-sitemaps/pull/19)).

### Changed

- Bump WordPress "tested up to" version 6.8 (props [@jeffpaul](https://github.com/jeffpaul), [@sudip-md](https://github.com/sudip-md), [@colinswinney](https://github.com/colinswinney), [@dkotter](https://github.com/dkotter) via [#22](https://github.com/10up/10up-sitemaps/pull/22), [#24](https://github.com/10up/10up-sitemaps/pull/24), [#28](https://github.com/10up/10up-sitemaps/pull/28), [#31](https://github.com/10up/10up-sitemaps/pull/31)).
- Bump WordPress minimum supported version to 6.6 (props [@jeffpaul](https://github.com/jeffpaul), [@sudip-md](https://github.com/sudip-md), [@colinswinney](https://github.com/colinswinney), [@dkotter](https://github.com/dkotter) via [#22](https://github.com/10up/10up-sitemaps/pull/22), [#24](https://github.com/10up/10up-sitemaps/pull/24), [#28](https://github.com/10up/10up-sitemaps/pull/28), [#31](https://github.com/10up/10up-sitemaps/pull/31)).

### Developer

- Add WordPress.org plugin assets (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#18](https://github.com/10up/10up-sitemaps/pull/18)).
- Replaced `lee-dohm/no-response` with `actions/stale` to help with closing no-response/stale issues (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#21](https://github.com/10up/10up-sitemaps/pull/21)).
- Update `README.md` file to include the plugin banner image and badges (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#26](https://github.com/10up/10up-sitemaps/pull/26)).

## [1.0.4] - 2023-09-07

### Fixed

- Prefix sitemap index in `robots.txt` with line feed.

## [1.0.3] - 2019-08-12

### Fixed

- No empty urls in sitemap.

## [1.0.2] - 2019-08-05

### Added

- WordPress Plugin type.

## [1.0.1] - 2019-08-05

### Changed

- Package name.

### Fixed

- Log url properly.

## [1.0.0] - 2019-08-01

### Added

- Homepage and post type archive.
- Progress estimator.
- `stop_the_insanity()` calls.
- `robots_txt` filter to include the `sitemap.xml` file.
- Page link filter.

### Removed

- `changefrew` and `priority` from template.

## [0.1.0] - 2019-06-26

### Added

- Initial plugin release! 🎉
- Sitemaps are only updated via WP-CLI.
- Output is saved in an option for fast reading/displaying on the front end.

[Unreleased]: https://github.com/10up/10up-sitemaps/compare/1.0.3...trunk
[1.0.5]: https://github.com/10up/10up-sitemaps/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/10up/10up-sitemaps/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/10up/10up-sitemaps/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/10up/10up-sitemaps/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/10up/10up-sitemaps/compare/87eab2e...1.0.1
[1.0.0]: https://github.com/10up/10up-sitemaps/compare/2a62419...87eab2e
[0.1.0]: https://github.com/10up/10up-sitemaps/commit/2a62419
