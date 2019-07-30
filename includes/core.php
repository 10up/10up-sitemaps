<?php
/**
 * Core plugin functionality
 *
 * @package  10up-sitemaps
 */

namespace TenupSitemaps\Core;

use TenupSitemaps\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup hooks
 */
function setup() {
	add_filter( 'template_include', __NAMESPACE__ . '\load_sitemap_template' );
	add_action( 'init', __NAMESPACE__ . '\create_rewrites' );
	add_filter( 'posts_pre_query', __NAMESPACE__ . '\disable_main_query_for_sitemap_xml', 10, 2 );
	add_action( 'wp', __NAMESPACE__ . '\broken_sitemap_404' );
	add_filter( 'robots_txt', __NAMESPACE__ . '\add_sitemap_robots_txt' );
}

/**
 * 404 sitemap.xml if not setup properly
 */
function broken_sitemap_404() {
	global $wp_query;

	if ( 'true' === get_query_var( 'sitemap' ) && ! Utils\sitemap_setup() ) {
		$wp_query->set_404();
		status_header( 404 );
	}
}

/**
 * Render sitemap
 *
 * @param  string $template Template file to use
 * @return string
 */
function load_sitemap_template( $template ) {

	if ( 'true' === get_query_var( 'sitemap' ) ) {
		if ( ! Utils\sitemap_setup() ) {
			return $template;
		}

		if ( ! empty( get_query_var( 'sitemap-page' ) ) ) {
			$template = __DIR__ . '/templates/sitemap-page.php';
		} else {
			$template = __DIR__ . '/templates/sitemap-root.php';
		}
	}

	return $template;
}

/**
 * Add rewrite rules/tags
 */
function create_rewrites() {
	add_rewrite_tag( '%sitemap%', 'true' );

	add_rewrite_tag( '%sitemap-page%', '[0-9]+' );
	add_rewrite_rule( '^sitemap.xml$', 'index.php?sitemap=true', 'top' );
	add_rewrite_rule( '^sitemap-page-([0-9]+).xml$', 'index.php?sitemap=true&sitemap-page=$matches[1]', 'top' );
	add_action( 'redirect_canonical', __NAMESPACE__ . '\disable_canonical_redirects_for_sitemap_xml', 10, 2 );
}

/**
 * Disable Main Query when rendering sitemaps
 *
 * @param array|null $posts array of post data or null
 * @param WP_Query   $query The WP_Query instance.
 * @return  array
 */
function disable_main_query_for_sitemap_xml( $posts, $query ) {

	if ( $query->is_main_query() && ! empty( $query->query_vars['sitemap'] ) ) {
		if ( Utils\sitemap_setup() ) {
			$posts = [];
		}
	}

	return $posts;
}

/**
 * Disable canonical redirects for the sitemap files
 *
 * @param  string $redirect_url URL to redirect to
 * @param  string $requested_url Originally requested url
 * @return string URL to redirect
 */
function disable_canonical_redirects_for_sitemap_xml( $redirect_url, $requested_url ) {
	if ( preg_match( '/sitemap(\-page\-[0-9]+)?\.xml/i', $requested_url ) ) {
		return $requested_url;
	}

	return $redirect_url;
}

/**
 * Add the sitemap URL to robots.txt
 *
 * @param string $output Robots.txt output.
 * @return string
 */
function add_sitemap_robots_txt( $output ) {
	$url = site_url( '/sitemap.xml' );
	$output .= "Sitemap: {$url}\n";
	return $output;
}
