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
	 * @subcommand generate
	 * @synopsis [--range]
	 * @param array $args Positional CLI args.
	 * @param array $assoc_args Associative CLI args.
	 */
	public function generate( $args, $assoc_args ) {
		global $wpdb;

		WP_CLI::line( 'Creating sitemap...' );

		$per_page = 500;

		$args = [
			'public' => true,
		];

		$range = '0000-00-00 00:00:00';

		if ( ! empty( $assoc_args['range'] ) && 'all' !== $assoc_args['range'] ) {
			$range = date( 'Y-m-d H:i:s', strtotime( '-' . (int) $assoc_args['range'] . ' month' ) );
		}

		$post_types = get_post_types( $args );

		if ( ! empty( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$post_types = apply_filters( 'tenup_sitemaps_post_types', $post_types );

		$urls = [];

		$homepage_url = [
			'url'      => home_url(),
			'modified' => time(),
		];

		$homepage_url = apply_filters( 'tenup_sitemaps_index_homepage', $homepage_url );

		if ( ! empty( $homepage_url ) ) {
			$urls[] = $homepage_url;
		}

		foreach ( $post_types as $post_type ) {
			$offset = 0;

			$post_type_archive_url = [
				'url'      => get_post_type_archive_link( $post_type ),
				'modified' => time(),
			];

			$post_type_archive_url = apply_filters( 'tenup_sitemaps_index_post_type_archive', $post_type_archive_url, $post_type );

			if ( ! empty( $post_type_archive_url ) ) {
				$urls[] = $post_type_archive_url;
			}

			while ( true ) {
				WP_CLI::debug( 'Processing post type `' . $post_type . '` from offset ' . $offset );

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_date_gmt, post_content FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = '%s' AND post_date_gmt >= '%s' ORDER BY post_date_gmt DESC LIMIT %d, %d", $post_type, $range, (int) $offset, (int) $per_page ), ARRAY_A );

				if ( empty( $results ) ) {
					break;
				}

				foreach ( $results as $result ) {
					$url = [
						'ID'       => (int) $result['ID'],
						'url'      => get_permalink( $result['ID'] ),
						'modified' => strtotime( $result['post_date_gmt'] ),
					];

					if ( apply_filters( 'tenup_sitemaps_index_images', true, $result['ID'] ) ) {
						$images = [];

						$thumbnail_id = get_post_thumbnail_id( $result['ID'] );

						if ( $thumbnail_id ) {
							$images[] = Utils\prepare_sitemap_image( $thumbnail_id );
						}

						$images = array_merge( $images, Utils\parse_html_images( $result['post_content'] ) );

						$gallery_images = Utils\parse_galleries( $result['post_content'], $result['ID'] );

						foreach ( $gallery_images as $image_id ) {
							$images[] = Utils\prepare_sitemap_image( $image_id );
						}

						if ( ! empty( $images ) ) {
							// Make sure unique
							$image_urls = [];

							foreach ( $images as $key => $image ) {
								if ( empty( $image['url'] ) || ! empty( $image_urls[ $image['url'] ] ) ) {
									unset( $images[ $key ] );
								}

								$image_urls[ $image['url'] ] = true;
							}

							$url['images'] = array_values( $images );
						}
					}

					/**
					 * Should return an array like so:
					 *
					 * [
					 *  'sp' => 'spanish link',
					 *  'fr' => 'french link,'
					 * ]
					 */
					$translations = apply_filters( 'tenup_sitemaps_post_translations', [], $result['ID'], $post_type );

					if ( ! empty( $translations ) ) {
						$url['translations'] = $translations;
					}

					$url = apply_filters( 'tenup_sitemaps_index_post', $url, $result['ID'], $post_type );

					if ( ! empty( $url ) ) {
						$urls[] = $url;
					}
				}

				$offset += $per_page;
			}
		}

		if ( apply_filters( 'tenup_sitemaps_index_terms', true ) ) {
			$args = [
				'public' => true,
			];

			$taxonomies = get_taxonomies( $args );

			$taxonomies = apply_filters( 'tenup_sitemaps_taxonomies', $taxonomies );

			foreach ( $taxonomies as $taxonomy ) {
				$offset = 0;

				while ( true ) {
					WP_CLI::debug( 'Processing taxonomy `' . $taxonomy . '` from offset ' . $offset );

					$results = $wpdb->get_results( $wpdb->prepare( "SELECT term_taxonomy.term_id as term_id, name, slug FROM {$wpdb->prefix}term_taxonomy as term_taxonomy, {$wpdb->prefix}terms as terms WHERE term_taxonomy.term_id = terms.term_id AND term_taxonomy.taxonomy = '%s' AND term_taxonomy.count > 0 ORDER BY term_taxonomy.term_id DESC LIMIT %d, %d", $taxonomy, (int) $offset, (int) $per_page ), ARRAY_A );

					if ( empty( $results ) ) {
						break;
					}

					foreach ( $results as $result ) {
						$url = [
							'ID'       => (int) $result['term_id'],
							'url'      => get_term_link( (int) $result['term_id'] ),
							'modified' => time(),
						];

						/**
						 * Should return an array like so:
						 *
						 * [
						 *  'sp' => 'spanish link',
						 *  'fr' => 'french link,'
						 * ]
						 */
						$translations = apply_filters( 'tenup_sitemaps_term_translations', [], $result['term_id'], $taxonomy );

						if ( ! empty( $translations ) ) {
							$url['translations'] = $translations;
						}

						$url = apply_filters( 'tenup_sitemaps_index_term', $url, $result['term_id'], $taxonomy );

						if ( ! empty( $url ) ) {
							$urls[] = $url;
						}
					}

					$offset += $per_page;
				}
			}
		}

		/**
		 * Todo: Add author archives
		 */

		/**
		 * Break up content into manageable options that Memcached can handle. Targeting ~100 KB for each option.
		 */

		$urls_per_page = apply_filters( 'tenup_sitemaps_urls_per_page', 200 );

		$total_pages = ceil( count( $urls ) / $urls_per_page );

		update_option( 'tenup_sitemaps_total_pages', (int) $total_pages, false );

		for ( $i = 1; $i <= $total_pages; $i++ ) {
			$data = array_slice( $urls, ( ( $i - 1 ) * $urls_per_page ), $urls_per_page );

			WP_CLI::debug( 'Saving sitemap page ' . $i . '. Total option size is ~' . round( strlen( serialize( $data ) ) / 1024 ) . ' kilobytes.' );

			update_option( 'tenup_sitemaps_page_' . $i, $data, false );
		}

		WP_CLI::success( 'Sitemap generated. ' . count( $urls ) . ' urls included.' );
	}
}
