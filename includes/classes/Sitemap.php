<?php
/**
 * Sitemap class
 *
 * @package  10up-sitemaps
 */

namespace TenupSitemaps;

use TenupSitemaps\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Represents the entire sitemap
 */
class Sitemap {

	/**
	 * URLs to be included in the sitemap
	 *
	 * @var array
	 */
	private $urls;

	/**
	 * Range of URLS to include in the sitemap e.g. 6 months
	 *
	 * @var string
	 */
	private $range;

	/**
	 * Assoc array of callables for logging. Must take the form of:
	 *
	 * [
	 * 'success' => ...,
	 * 'debug'   => ...,
	 * 'notice'  => ...,
	 * 'warning' => ...
	 * ]
	 *
	 * @var array
	 * @since 1.2
	 */
	private $logger;

	/**
	 * URLs to include in each sitemap page
	 *
	 * @var int
	 * @since 1.2
	 */
	private $urls_per_page;

	/**
	 * URLs to process in each DB cycle
	 *
	 * @var int
	 * @since 1.2
	 */
	private $process_page_size = 500;

	/**
	 * Create a new empty sitemap
	 *
	 * @param string  $range         Range of content
	 * @param integer $urls_per_page URLs per sitemap page
	 * @param array   $urls          Preexisting URLs to use
	 * @param array   $logger        Logged to use
	 * @since  1.2
	 */
	public function __construct( $range = 'all', $urls_per_page = 200, $urls = [], $logger = null ) {
		if ( ! empty( $range ) && 'all' !== $range ) {
			$this->range = date( 'Y-m-d H:i:s', strtotime( '-' . (int) $range . ' month' ) );
		} else {
			$this->range = '0000-00-00 00:00:00';
		}

		$this->urls          = $urls;
		$this->logger        = $logger;
		$this->urls_per_page = $urls_per_page;
	}

	/**
	 * Log a message
	 *
	 * @param  string $message Message to log
	 * @param  string $type    Type of message
	 * @since  1.2
	 */
	public function log( $message, $type = 'notice' ) {
		if ( ! empty( $this->logger ) && ! empty( $this->logger[ $type ] ) ) {
			call_user_func( $this->logger[ $type ], $message );
		}
	}

	/**
	 * Build entire sitemap
	 *
	 * @since 1.2
	 */
	public function build() {
		$this->build_homepage();

		if ( apply_filters( 'tenup_sitemaps_index_post_types', true ) ) {
			$this->build_post_types();
		}

		if ( apply_filters( 'tenup_sitemaps_index_terms', true ) ) {
			$this->build_terms();
		}

		if ( apply_filters( 'tenup_sitemaps_index_authors', false ) ) {
			$this->build_authors();
		}
	}

	/**
	 * Add post types to URLs
	 *
	 * @since  1.2
	 */
	public function build_post_types() {
		global $wpdb;

		$args = [
			'public' => true,
		];

		$post_types = get_post_types( $args );

		if ( ! empty( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$post_types = apply_filters( 'tenup_sitemaps_post_types', $post_types );

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
				$this->log( 'Processing post type `' . $post_type . '` from offset ' . $offset, 'debug' );

				// phpcs:disable
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_date_gmt, post_content FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = '%s' AND post_date_gmt >= '%s' ORDER BY post_date_gmt DESC LIMIT %d, %d", $post_type, $this->range, (int) $offset, (int) $this->process_page_size ), ARRAY_A );
				// phpcs:enable

				if ( empty( $results ) ) {
					break;
				}

				foreach ( $results as $result ) {
					$permalink = get_permalink( $result['ID'] );

					$url = [
						'ID'       => (int) $result['ID'],
						'url'      => $permalink,
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

					if ( ! empty( $url ) && ! empty( $url['url'] ) ) {
						$this->urls[] = $url;

						$this->log(
							sprintf(
								'%s (%d) added to page %d.',
								$permalink,
								count( $this->urls ),
								ceil( count( $this->urls ) / $this->urls_per_page )
							),
							'notice'
						);
					} else {
						$this->log(
							sprintf(
								'Could not add %s.',
								$permalink
							),
							'warning'
						);
					}

					$this->stop_the_insanity();
				}

				$offset += $this->process_page_size;
			}
		}
	}

	/**
	 * Add terms to URLs
	 *
	 * @since 1.2
	 */
	public function build_terms() {
		global $wpdb;

		$args = [
			'public' => true,
		];

		$taxonomies = get_taxonomies( $args );

		$taxonomies = apply_filters( 'tenup_sitemaps_taxonomies', $taxonomies );

		foreach ( $taxonomies as $taxonomy ) {
			$offset = 0;

			while ( true ) {
				$this->log( 'Processing taxonomy `' . $taxonomy . '` from offset ' . $offset, 'debug' );

				// phpcs:disable
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT term_taxonomy.term_id as term_id, name, slug FROM {$wpdb->prefix}term_taxonomy as term_taxonomy, {$wpdb->prefix}terms as terms WHERE term_taxonomy.term_id = terms.term_id AND term_taxonomy.taxonomy = '%s' AND term_taxonomy.count > 0 ORDER BY term_taxonomy.term_id DESC LIMIT %d, %d", $taxonomy, (int) $offset, (int) $this->process_page_size ), ARRAY_A );
				// phpcs:enable

				if ( empty( $results ) ) {
					break;
				}

				foreach ( $results as $result ) {
					$permalink = get_term_link( (int) $result['term_id'] );

					$url = [
						'ID'       => (int) $result['term_id'],
						'url'      => $permalink,
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

					if ( ! empty( $url ) && ! empty( $url['url'] ) ) {
						$this->urls[] = $url;

						$this->log(
							sprintf(
								'%s (%d) added to page %d.',
								$permalink,
								count( $this->urls ),
								ceil( count( $this->urls ) / $this->urls_per_page )
							),
							'notice'
						);
					} else {
						$this->log(
							sprintf(
								'Could not add %s.',
								$permalink
							),
							'warning'
						);
					}

					$this->stop_the_insanity();
				}

				$offset += $this->process_page_size;
			}
		}
	}

	/**
	 * Add authors to urls
	 *
	 * @since 1.2
	 */
	public function build_authors() {
		global $wpdb;

		$offset = 0;

		$roles = [
			'administrator',
			'editor',
			'author',
		];

		$roles = apply_filters( 'tenup_sitemaps_user_roles', $roles );

		if ( empty( $roles ) ) {
			return;
		}

		$roles_like = '';

		foreach ( $roles as $role ) {
			if ( ! empty( $roles_like ) ) {
				$roles_like .= ' OR ';
			}

			$roles_like .= "usermeta.meta_value LIKE '%%\"" . esc_sql( $role ) . "\"%%'";
		}

		$roles_like = '(' . $roles_like . ')';

		$capability_col = $wpdb->prefix . 'capabilities';

		if ( is_multisite() && 1 < (int) get_current_blog_id() ) {
			$capability_col = $wpdb->prefix . get_current_blog_id() . '_capabilities';
		}

		while ( true ) {
			// phpcs:disable
			$results = $wpdb->get_results( sprintf( "SELECT * FROM {$wpdb->prefix}users as users, {$wpdb->prefix}usermeta as usermeta WHERE users.ID = usermeta.user_id AND usermeta.meta_key = '{$capability_col}' AND {$roles_like} ORDER BY user_login ASC LIMIT %d, %d", (int) $offset, (int) $this->process_page_size ), ARRAY_A );
			// phpcs:enable

			if ( empty( $results ) ) {
				break;
			}

			foreach ( $results as $result ) {
				$permalink = get_author_posts_url( (int) $result['term_id'] );

				$url = [
					'ID'       => (int) $result['ID'],
					'url'      => $permalink,
					'modified' => time(),
				];

				$url = apply_filters( 'tenup_sitemaps_index_author', $url, $result['ID'] );

				if ( ! empty( $url ) && ! empty( $url['url'] ) ) {
					$this->urls[] = $url;

					$this->log(
						sprintf(
							'%s (%d) added to page %d.',
							$permalink,
							count( $this->urls ),
							ceil( count( $this->urls ) / $this->urls_per_page )
						),
						'notice'
					);
				} else {
					$this->log(
						sprintf(
							'Could not add %s.',
							$permalink
						),
						'warning'
					);
				}

				$this->stop_the_insanity();
			}

			$offset += $this->process_page_size;
		}
	}

	/**
	 * Add homepage url
	 *
	 * @since 1.2
	 */
	public function build_homepage() {
		$homepage_url = [
			'url'      => home_url(),
			'modified' => time(),
		];

		$homepage_url = apply_filters( 'tenup_sitemaps_index_homepage', $homepage_url );

		if ( ! empty( $homepage_url ) ) {
			$this->urls[] = $homepage_url;
		}
	}

	/**
	 * Write sitemap to options
	 *
	 * @since 1.2
	 */
	public function write() {
		$total_pages = ceil( count( $this->urls ) / $this->urls_per_page );

		update_option( 'tenup_sitemaps_total_pages', (int) $total_pages, false );

		for ( $i = 1; $i <= $total_pages; $i++ ) {
			$data = array_slice( $this->urls, ( ( $i - 1 ) * $this->urls_per_page ), $this->urls_per_page );

			// phpcs:disable
			$this->log( 'Saving sitemap page ' . $i . '. Total option size is ~' . round( strlen( serialize( $data ) ) / 1024 ) . ' kilobytes.', 'debug' );
			// phpcs:enable

			update_option( 'tenup_sitemaps_page_' . $i, $data, false );
		}

		$this->log( 'Sitemap generated. ' . count( $this->urls ) . ' urls included. ' . $total_pages . ' pages created.', 'success' );
	}

	/**
	 * Get urls
	 *
	 * @since  1.2
	 * @return array
	 */
	public function get_urls() {
		return $this->urls;
	}

	/**
	 * Get urls per page
	 *
	 * @since  1.2
	 * @return int
	 */
	public function get_urls_per_page() {
		return $this->urls_per_page;
	}

	/**
	 * Get range
	 *
	 * @since  1.2
	 * @return string
	 */
	public function get_range() {
		return $this->range;
	}

	/**
	 * Get logger
	 *
	 * @since  1.2
	 * @return array
	 */
	public function get_logger() {
		return $this->logger;
	}


	/**
	 * Clear all of the caches for memory management
	 *
	 * @since 1.2
	 */
	private function stop_the_insanity() {
		global $wpdb, $wp_object_cache;

		$one_hundred_mb = 104857600;
		if ( memory_get_usage() <= $one_hundred_mb ) {
			return;
		}

		$wpdb->queries = array();

		if ( is_object( $wp_object_cache ) ) {
			$wp_object_cache->group_ops      = array();
			$wp_object_cache->stats          = array();
			$wp_object_cache->memcache_debug = array();
			$wp_object_cache->cache          = array();

			if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
				$wp_object_cache->__remoteset(); // important
			}
		}

		gc_collect_cycles();
	}
}
