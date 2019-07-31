<?php
/**
 * Sitemap template
 *
 * @package  10up-sitemaps
 */

header( 'Content-type: application/xml; charset=UTF-8' );

echo '<?xml version="1.0" encoding="UTF-8"?>';

$sitemap_page = get_query_var( 'sitemap-page' );

$links = apply_filters( 'tenup_sitemaps_page_links', get_option( 'tenup_sitemaps_page_' . $sitemap_page ), $sitemap_page );

if ( empty( $links ) ) {
	$links = [];
}
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
	<?php foreach ( $links as $link ) : ?>
		<url>

			<loc><?php echo esc_html( $link['url'] ); ?></loc>

			<?php if ( ! empty( $link['translations'] ) ) : ?>
				<?php foreach ( $link['translations'] as $lang => $translated_url ) : ?>
					<xhtml:link rel="alternate" hreflang="<?php echo esc_attr( $lang ); ?>" href="<?php echo esc_url( $translated_url ); ?>" />
				<?php endforeach; ?>
			<?php endif; ?>

			<lastmod><?php echo esc_html( date( 'Y-m-d', $link['modified'] ) ); ?></lastmod>

			<?php if ( ! empty( $link['images'] ) ) : ?>
				<?php foreach ( $link['images'] as $image ) : ?>
					<image:image>
						<image:loc><?php echo esc_url( $image['url'] ); ?></image:loc>
						<?php if ( ! empty( $image['title'] ) ) : ?>
							<image:title><?php echo esc_html( $image['title'] ); ?></image:title>
						<?php endif; ?>

						<?php if ( ! empty( $image['alt'] ) ) : ?>
							<image:caption><?php echo esc_html( $image['alt'] ); ?></image:caption>
						<?php endif; ?>
					</image:image>
				<?php endforeach; ?>
			<?php endif; ?>

		</url>
	<?php endforeach; ?>
</urlset>
