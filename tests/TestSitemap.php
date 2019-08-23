<?php
/**
 * Sitemap testing
 *
 * @package  10up-sitemaps
 */

namespace TenupSitemapsTest;

use TenupSitemaps\Sitemap;
use WP_UnitTestCase;

/**
 * Sitemap test class
 */
class TestSitemap extends WP_UnitTestCase {

	public function setUp() {
		global $wp_rewrite;

		$wp_rewrite->set_permalink_structure('/%postname%/');

		update_option( "rewrite_rules", true );

		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Test setting up the sitemap class
	 *
	 * @since 1.2
	 */
	public function testConstruct() {
		$logger = [
			'success' => 'success',
			'debug'   => 'debug',
			'warning' => 'warning',
			'notice'  => 'line',
		];

		$sitemap = new Sitemap( 6, 50, [ 'test' ], $logger );

		$this->assertEquals( date( 'Y-m-d', strtotime( '-6 month' ) ), date( 'Y-m-d', strtotime( $sitemap->get_range() ) ) );
		$this->assertEquals( 50, $sitemap->get_urls_per_page() );
		$this->assertEquals( [ 'test' ], $sitemap->get_urls() );
		$this->assertEquals( $logger, $sitemap->get_logger() );
	}

	/**
	 * Test logger
	 *
	 * @since  1.2
	 */
	public function testLog() {
		$func = function() {
			$this->assertTrue( true );
		};

		$logger = [
			'success' => $func,
		];

		$sitemap = new Sitemap( 6, 50, [ 'test' ], $logger );

		$sitemap->log( 'test', 'success' );
	}

	/**
	 * Test building post types
	 *
	 * @since 1.2
	 */
	public function testBuildPostTypes() {
		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'post',
			'post_title'  => 'Test Post One',
		);

		wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'post',
			'post_title'  => 'Test Post Two',
		);

		$post_id = wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'post',
			'post_title'  => 'Test Post Three',
		);

		wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'tsm_test',
			'post_title'  => 'Test Custom One',
		);

		wp_insert_post( $args );

		$args = array(
			'post_status' => 'publish',
			'post_type'   => 'tsm_test_private',
			'post_title'  => 'Test Custom Two',
		);

		wp_insert_post( $args );

		$sitemap = new Sitemap();
		$sitemap->build_post_types();

		$urls  = $sitemap->get_urls();
		$links = wp_list_pluck( $urls, 'url' );
		$ids   = wp_list_pluck( $urls, 'ID' );

		$this->assertEquals( 4, count( $urls ) );
		$this->assertTrue( in_array( (int) $post_id, $ids, true ) );
		$this->assertTrue( in_array( home_url() . '/test-post-two/', $links, true ) );
	}

	/**
	 * Test building terms
	 *
	 * @since 1.2
	 */
	public function testBuildTerms() {
		$term1 = wp_insert_term( 'Cat 1', 'category' );
		$term2 = wp_insert_term( 'Tag 1', 'post_tag' );

		$args = array(
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_title'    => 'Test Post One',
			'post_category' => [ $term1['term_id'] ],
		);

		wp_insert_post( $args );

		$args = array(
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_title'    => 'Test Post Two',
			'post_category' => [ $term1['term_id'] ],
		);

		$post_id = wp_insert_post( $args );

		wp_set_post_terms( $post_id, [ $term2['term_id'] ], 'post_tag' );

		$sitemap = new Sitemap();
		$sitemap->build_terms();

		$urls  = $sitemap->get_urls();
		$links = wp_list_pluck( $urls, 'url' );

		$this->assertTrue( in_array( get_term_link( $term1['term_id'] ), $links, true ) );
		$this->assertTrue( in_array( get_term_link( $term2['term_id'] ), $links, true ) );
	}

	/**
	 * Test building homepage
	 *
	 * @since 1.2
	 */
	public function testBuildHomepage() {
		$sitemap = new Sitemap();
		$sitemap->build_homepage();

		$urls = $sitemap->get_urls();

		$this->assertEquals( 1, count( $urls ) );
		$this->assertEquals( home_url(), $urls[0]['url'] );
	}
}
