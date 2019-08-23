<?php
/**
 * Utility functions
 *
 * @package  10up-sitemaps
 */

namespace TenupSitemaps\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cannot access page directly' );
}

/**
 * Determine if sitemap is setup
 *
 * @return boolean
 */
function sitemap_setup() {
	$pages = get_option( 'tenup_sitemaps_total_pages', 0 );

	if ( empty( $pages ) ) {
		return false;
	}

	return true;
}

/**
 * Ensure XML tag content is valud
 *
 * @param  string $string Tag content
 * @since  1.1
 * @return string
 */
function prepare_valid_xml( $string ) {
	$string = html_entity_decode( $string );
	$string = wp_strip_all_tags( $string );

	return trim( $string );
}

/**
 * Prepare an image for sitemap storage
 *
 * @param  int $id Image id
 * @return array
 */
function prepare_sitemap_image( $id ) {
	$src   = get_image_url( $id );
	$alt   = get_post_meta( $id, '_wp_attachment_image_alt', true );
	$title = get_post_field( 'post_title', $id );

	return [
		'ID'    => $id,
		'url'   => $src,
		'alt'   => prepare_valid_xml( $alt ),
		'title' => prepare_valid_xml( $title ),
	];
}

/**
 * Get attached image URL with filters applied. Adapted from core for speed.
 *
 * @param int $post_id ID of the post.
 * @return string
 */
function get_image_url( $post_id ) {
	static $uploads;

	if ( empty( $uploads ) ) {
		$uploads = wp_upload_dir();
	}

	if ( false !== $uploads['error'] ) {
		return '';
	}

	$file = get_post_meta( $post_id, '_wp_attached_file', true );

	if ( empty( $file ) ) {
		return '';
	}

	// Check that the upload base exists in the file location.
	if ( 0 === strpos( $file, $uploads['basedir'] ) ) {
		$src = str_replace( $uploads['basedir'], $uploads['baseurl'], $file );
	} elseif ( false !== strpos( $file, 'wp-content/uploads' ) ) {
		$src = $uploads['baseurl'] . substr( $file, ( strpos( $file, 'wp-content/uploads' ) + 18 ) );
	} else {
		// It's a newly uploaded file, therefore $file is relative to the baseurl.
		$src = $uploads['baseurl'] . '/' . $file;
	}

	return apply_filters( 'wp_get_attachment_url', $src, $post_id );
}

/**
 * Parse `<img />` tags in content.
 *
 * @param string $content Content string to parse.
 *
 * @return array
 */
function parse_html_images( $content ) {
	$images = [];

	if ( empty( $content ) ) {
		return $images;
	}

	// Prevent DOMDocument from bubbling warnings about invalid HTML.
	libxml_use_internal_errors( true );

	$post_dom = new \DOMDocument();
	$post_dom->loadHTML( '<?xml encoding="UTF-8">' . $content );

	// Clear the errors, so they don't get kept in memory.
	libxml_clear_errors();

	foreach ( $post_dom->getElementsByTagName( 'img' ) as $img ) {
		$src = $img->getAttribute( 'src' );

		if ( empty( $src ) ) {
			continue;
		}

		$class = $img->getAttribute( 'class' );

		if (
			! empty( $class )
			&& false === strpos( $class, 'size-full' )
			&& preg_match( '|wp-image-(?P<id>\d+)|', $class, $matches )
			&& get_post_status( $matches['id'] )
		) {
			$images[] = prepare_sitemap_image( $matches['id'] );
			continue;
		}

		$src = get_absolute_url( $src );

		if ( false === strpos( $src, str_replace( 'www.', '', wp_parse_url( home_url(), PHP_URL_HOST ) ) ) ) {
			continue;
		}

		$images[] = array(
			'url'   => $src,
			'title' => $img->getAttribute( 'title' ),
			'alt'   => $img->getAttribute( 'alt' ),
		);
	}

	return $images;
}

/**
 * Retrieves galleries from the passed content.
 *
 * Forked from core to skip executing shortcodes for performance.
 *
 * @param string $content Content to parse for shortcodes.
 * @return array A list of arrays, each containing gallery data.
 */
function get_content_galleries( $content ) {
	if ( ! has_shortcode( $content, 'gallery' ) ) {
		return array();
	}

	$galleries = array();

	if ( ! preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER ) ) {
		return $galleries;
	}

	foreach ( $matches as $shortcode ) {
		if ( 'gallery' === $shortcode[2] ) {
			$attributes = shortcode_parse_atts( $shortcode[3] );

			if ( '' === $attributes ) {
				$attributes = array();
			}

			$galleries[] = $attributes;
		}
	}

	return $galleries;
}

/**
 * Parse gallery shortcodes in a given content.
 *
 * @param string $content Content string.
 * @param int    $post_id ID of post being parsed.
 *
 * @return array Set of attachment objects.
 */
function parse_galleries( $content, $post_id ) {
	$attachments = array();
	$galleries   = get_content_galleries( $content );

	foreach ( $galleries as $gallery ) {
		$id = $post_id;

		if ( ! empty( $gallery['id'] ) ) {
			$id = intval( $gallery['id'] );
		}

		// Forked from core gallery_shortcode() to have exact same logic.
		if ( ! empty( $gallery['ids'] ) ) {
			$gallery['include'] = $gallery['ids'];
		}

		$gallery_attachments = get_gallery_attachments( $id, $gallery );

		$attachments = array_merge( $attachments, $gallery_attachments );
	}

	return array_unique( $attachments );
}

/**


/**
 * Returns the attachments for a gallery.
 *
 * @param int   $id      The post ID.
 * @param array $gallery The gallery config.
 *
 * @return array The selected attachments.
 */
function get_gallery_attachments( $id, $gallery ) {
	// When there are attachments to include.
	if ( ! empty( $gallery['include'] ) ) {
		return wp_parse_id_list( $gallery['include'] );
	}

	return get_gallery_attachments_for_parent( $id, $gallery );
}

/**
 * Returns the attachments for the given ID.
 *
 * @param int   $id      The post ID.
 * @param array $gallery The gallery config.
 *
 * @return array The selected attachments.
 */
function get_gallery_attachments_for_parent( $id, $gallery ) {
	$query = array(
		'post_parent' => $id,
	);

	// When there are posts that should be excluded from result set.
	if ( ! empty( $gallery['exclude'] ) ) {
		$query['post__not_in'] = wp_parse_id_list( $gallery['exclude'] );
	}

	return get_attachments( $query );
}

/**
 * Returns the attachments.
 *
 * @param array $args Array with query args.
 *
 * @return array The found attachments.
 */
function get_attachments( $args ) {
	$default_args = array(
		'post_status'            => 'inherit',
		'post_type'              => 'attachment',
		'post_mime_type'         => 'image',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'fields'                 => 'ids',
		'cache_results'          => false,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	$args = wp_parse_args( $args, $default_args );

	$get_attachments = new \WP_Query( $args );

	return $get_attachments->posts;
}

/**
 * Check if a url is relative
 *
 * @param  string $url Url to check
 * @return boolean
 */
function is_url_relative( $url ) {
	return ( strpos( $url, 'http' ) !== 0 && strpos( $url, '//' ) !== 0 );
}

/**
 * Make absolute URL for domain or protocol-relative one.
 *
 * @param string $src URL to process.
 *
 * @return string
 */
function get_absolute_url( $src ) {
	if ( empty( $src ) || ! is_string( $src ) ) {
		return $src;
	}

	if ( is_url_relative( $src ) === true ) {
		if ( '/' !== $src[0] ) {
			return $src;
		}
		// The URL is relative, we'll have to make it absolute.
		return home_url() . $src;
	}

	if ( 0 !== strpos( $src, 'http' ) ) {
		// Protocol relative URL, we add the scheme as the standard requires a protocol.
		return wp_parse_url( home_url(), PHP_URL_SCHEME ) . ':' . $src;
	}

	return $src;
}
