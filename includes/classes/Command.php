<?php
/**
 * WP-CLI command for plugin
 *
 * @package  10up-sitemaps
 */

namespace TenupSitemaps;

use \WP_CLI_Command as WP_CLI_Command;
use \WP_CLI as WP_CLI;
use TenupSitemaps\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CLI Command
 */
class Command extends WP_CLI_Command {

	/**
	 * Generate sitemap
	 *
	 * ## OPTIONS
	 *
	 * [--type=<range>]
	 * : Range of posts to include. Either 'all' or a number of months.
	 *
	 * [--noprogress]
	 * : Disables the progress list/estimator
	 *
	 * @subcommand generate
	 * @synopsis [--range] [--noprogress]
	 * @param array $args Positional CLI args.
	 * @param array $assoc_args Associative CLI args.
	 */
	public function generate( $args, $assoc_args ) {
		global $wpdb;

		WP_CLI::line( 'Creating sitemap...' );

		$per_page = 500;

		$logger = [
			'success' => [ '\WP_CLI', 'success' ],
			'debug'   => [ '\WP_CLI', 'debug' ],
			'warning' => [ '\WP_CLI', 'warning' ],
			'notice'  => [ '\WP_CLI', 'line' ],
		];

		$urls_per_page = apply_filters( 'tenup_sitemaps_urls_per_page', 200 );

		$sitemap = new Sitemap( $assoc_args['range'], $urls_per_page, [], $logger );

		$sitemap->build();
		$sitemap->write();
	}
}
