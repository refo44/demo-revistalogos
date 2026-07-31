<?php
/**
 * Issue table of contents grouped by section, static-parity with
 * partials/toc.html. Articles are fetched once and grouped in memory
 * (no N+1 term queries thanks to get_posts term cache priming).
 *
 * Args:
 * - issue_id: issue post ID (required).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_issue_id = isset( $args['issue_id'] ) ? absint( $args['issue_id'] ) : 0;

if ( 0 === $revistalogos_issue_id ) {
	return;
}

$revistalogos_articles = revistalogos_issue_articles( $revistalogos_issue_id );

if ( ! $revistalogos_articles ) {
	return;
}

// Group by primary section, preserving article order inside each group.
$revistalogos_groups = array();

foreach ( $revistalogos_articles as $revistalogos_article_item ) {
	$revistalogos_terms = get_the_terms( $revistalogos_article_item, 'section' );
	$revistalogos_group = ( is_array( $revistalogos_terms ) && $revistalogos_terms ) ? $revistalogos_terms[0]->name : __( 'Otros', 'revistalogos' );

	if ( ! isset( $revistalogos_groups[ $revistalogos_group ] ) ) {
		$revistalogos_groups[ $revistalogos_group ] = array();
	}

	$revistalogos_groups[ $revistalogos_group ][] = $revistalogos_article_item;
}
?>
<section class="toc" aria-labelledby="toc-title">
	<h2 id="toc-title" class="toc__title"><?php esc_html_e( 'Tabla de Contenidos', 'revistalogos' ); ?></h2>

	<?php foreach ( $revistalogos_groups as $revistalogos_group_name => $revistalogos_group_articles ) : ?>
		<div class="toc__section">
			<h3 class="toc__section-title"><?php echo esc_html( $revistalogos_group_name ); ?></h3>
			<ol class="toc__list">
				<?php foreach ( $revistalogos_group_articles as $revistalogos_toc_article ) : ?>
					<?php
					$revistalogos_toc_authors = revistalogos_article_author_names( $revistalogos_toc_article->ID );
					$revistalogos_toc_pages   = get_post_meta( $revistalogos_toc_article->ID, 'pages', true );
					$revistalogos_toc_doi     = get_post_meta( $revistalogos_toc_article->ID, 'doi', true );
					?>
					<li class="toc__item">
						<a href="<?php echo esc_url( get_permalink( $revistalogos_toc_article ) ); ?>" class="toc__link">
							<?php echo esc_html( get_the_title( $revistalogos_toc_article ) ); ?>
						</a>
						<div class="toc__meta">
							<?php if ( $revistalogos_toc_authors ) : ?>
								<span><?php echo esc_html( $revistalogos_toc_authors ); ?></span>
							<?php endif; ?>
							<?php if ( $revistalogos_toc_authors && $revistalogos_toc_pages ) : ?>
								&bull;
							<?php endif; ?>
							<?php if ( $revistalogos_toc_pages ) : ?>
								<span><?php echo esc_html( sprintf( /* translators: %s: page range. */ __( 'pp. %s', 'revistalogos' ), $revistalogos_toc_pages ) ); ?></span>
							<?php endif; ?>
							<?php if ( $revistalogos_toc_doi ) : ?>
								&bull;
								<span>DOI: <?php echo esc_html( $revistalogos_toc_doi ); ?></span>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endforeach; ?>
</section>
