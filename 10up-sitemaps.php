<?php
/**
 * Plugin Name: 10up Sitemaps
 * Plugin URI:  http://10up.com
 * Description: Simple sitemap plugin
 * Version:     0.1.0
 * Author:      Taylor Lovett, 10up
 * Author URI:  http://10up.com
 * License:     GPLv2+
 * Text Domain: tenup-sitemaps
 *
 * @package  10up-sitemaps
 */

/**
 * Code used from Yoast SEO (https://github.com/Yoast/wordpress-seo) and Metro Sitemap (https://github.com/Automattic/msm-sitemap)
 */

namespace TenupSitemaps;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cannot access page directly' );
}

define( 'TENUPSITEMAPS_VERSION', '1.0' );

/**
 * PSR-4 autoloading
 */
spl_autoload_register(
	function( $class ) {
			// Project-specific namespace prefix.
			$prefix = 'TenupSitemaps\\';
			// Base directory for the namespace prefix.
			$base_dir = __DIR__ . '/includes/classes/';
			// Does the class use the namespace prefix?
			$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}
			$relative_class = substr( $class, $len );
			$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
			// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/core.php';

Core\setup();

/**
 * Flush rewrites on activation and deactivation
 */
register_activation_hook(
	__FILE__,
	function() {
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	function() {
		flush_rewrite_rules();
	}
);

/**
 * WP CLI Commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'tenup-sitemaps', __NAMESPACE__ . '\Command' );
}
