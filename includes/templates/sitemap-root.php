<?php
/**
 * Sitemap template
 *
 * @package  10up-sitemaps
 */

header( 'Content-type: application/xml; charset=UTF-8' );

echo '<?xml version="1.0" encoding="UTF-8"?>';

$total_pages = get_option( 'tenup_sitemaps_total_pages', 0 );
?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
		<sitemap>
			<loc><?php echo esc_url( home_url( '/sitemap-page-' . $i . '.xml' ) ); ?></loc>
			<lastmod><?php echo esc_html( date( 'Y-m-d' ) ); ?></lastmod>
		</sitemap>
	<?php endfor; ?>
</sitemapindex>
