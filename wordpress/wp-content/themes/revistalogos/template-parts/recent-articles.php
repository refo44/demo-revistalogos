<?php
/**
 * Front-page "Artículos recientes" section. Prints nothing without
 * articles. Shared by front-page.php and the plugin's
 * revistalogos/recent-articles block, which resolves the articles.
 *
 * Args:
 * - articles: WP_Post[] most recent published articles.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_recent_articles = isset( $args['articles'] ) ? (array) $args['articles'] : array();

if ( ! $revistalogos_recent_articles ) {
	return;
}

$revistalogos_article_archive = get_post_type_archive_link( 'article' );
?>
<section class="front-page__recent-articles">
	<h2 class="front-page__section-title"><?php esc_html_e( 'Artículos recientes', 'revistalogos' ); ?></h2>
	<div class="archive-grid">
		<?php foreach ( $revistalogos_recent_articles as $revistalogos_recent_article ) : ?>
			<?php
			get_template_part(
				'template-parts/article-card',
				null,
				array(
					'post'    => $revistalogos_recent_article,
					'heading' => 'h3',
				)
			);
			?>
		<?php endforeach; ?>
	</div>
	<div class="front-page__section-footer">
		<?php if ( $revistalogos_article_archive ) : ?>
			<a href="<?php echo esc_url( $revistalogos_article_archive ); ?>" class="btn btn--outline btn--small"><?php esc_html_e( 'Ver todos los artículos', 'revistalogos' ); ?></a>
		<?php endif; ?>
	</div>
</section>
