<?php
/**
 * Article card, static-parity with partials/article-card.html.
 *
 * Args:
 * - post: WP_Post article (required).
 * - heading: h2|h3 title tag (h3 on front page, h2 in archives).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_article = isset( $args['post'] ) ? get_post( $args['post'] ) : null;

if ( ! $revistalogos_article ) {
	return;
}

$revistalogos_heading = isset( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h2';

$revistalogos_article_id = $revistalogos_article->ID;
$revistalogos_title      = get_the_title( $revistalogos_article );
$revistalogos_title_en   = get_post_meta( $revistalogos_article_id, 'title_en', true );
$revistalogos_abstract   = get_post_meta( $revistalogos_article_id, 'abstract', true );
$revistalogos_pub_date   = get_post_meta( $revistalogos_article_id, 'publication_date', true );
$revistalogos_year       = $revistalogos_pub_date ? substr( $revistalogos_pub_date, 0, 4 ) : get_the_date( 'Y', $revistalogos_article );
$revistalogos_pdf_url    = revistalogos_meta_attachment_url( $revistalogos_article_id );
$revistalogos_permalink  = get_permalink( $revistalogos_article );
$revistalogos_authors    = revistalogos_article_authors( $revistalogos_article_id );
$revistalogos_sections   = get_the_terms( $revistalogos_article, 'section' );
?>
<article class="card article-card">
	<header class="card__header">
		<<?php echo $revistalogos_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="card__title article-card__title">
			<a href="<?php echo esc_url( $revistalogos_permalink ); ?>" class="article-card__link">
				<?php echo esc_html( $revistalogos_title ); ?>
			</a>
		</<?php echo $revistalogos_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( $revistalogos_title_en ) : ?>
			<p class="article-card__subtitle"><?php echo esc_html( $revistalogos_title_en ); ?></p>
		<?php endif; ?>
		<?php if ( $revistalogos_authors ) : ?>
			<div class="article-card__authors">
				<strong><?php esc_html_e( 'Autores:', 'revistalogos' ); ?></strong>
				<?php
				$revistalogos_links = array();
				foreach ( $revistalogos_authors as $revistalogos_author ) {
					$revistalogos_links[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( get_permalink( $revistalogos_author ) ),
						esc_html( get_the_title( $revistalogos_author ) )
					);
				}
				echo implode( ', ', $revistalogos_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		<?php endif; ?>
		<div class="article-card__meta">
			<?php if ( is_array( $revistalogos_sections ) && $revistalogos_sections ) : ?>
				<span class="article-card__meta-item">
					<span class="article-card__meta-label"><?php esc_html_e( 'Sección:', 'revistalogos' ); ?></span>
					<span><?php echo esc_html( $revistalogos_sections[0]->name ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( $revistalogos_year ) : ?>
				<span class="article-card__meta-item">
					<span class="article-card__meta-label"><?php esc_html_e( 'Año:', 'revistalogos' ); ?></span>
					<span><?php echo esc_html( $revistalogos_year ); ?></span>
				</span>
			<?php endif; ?>
		</div>
	</header>

	<?php
	$revistalogos_show_keywords = ! empty( $args['show_keywords'] );
	$revistalogos_keywords      = $revistalogos_show_keywords ? get_the_terms( $revistalogos_article, 'keyword' ) : false;
	?>
	<?php if ( $revistalogos_abstract || ( is_array( $revistalogos_keywords ) && $revistalogos_keywords ) ) : ?>
		<div class="card__content">
			<?php if ( $revistalogos_abstract ) : ?>
				<div class="article-card__abstract">
					<p><?php echo esc_html( $revistalogos_abstract ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( is_array( $revistalogos_keywords ) && $revistalogos_keywords ) : ?>
				<div class="article-card__keywords">
					<strong><?php esc_html_e( 'Palabras clave:', 'revistalogos' ); ?></strong> <?php echo esc_html( implode( ', ', wp_list_pluck( $revistalogos_keywords, 'name' ) ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<footer class="card__footer">
		<?php if ( $revistalogos_pdf_url ) : ?>
			<a href="<?php echo esc_url( $revistalogos_pdf_url ); ?>" class="btn btn--pdf btn--small"
				aria-label="<?php echo esc_attr( sprintf( /* translators: %s: article title. */ __( "Descargar PDF del artículo '%s'", 'revistalogos' ), $revistalogos_title ) ); ?>">
				<span aria-hidden="true">📄</span> PDF
			</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( $revistalogos_permalink ); ?>" class="btn btn--secondary btn--small">
			<?php esc_html_e( 'Leer más', 'revistalogos' ); ?>
		</a>
	</footer>
</article>
