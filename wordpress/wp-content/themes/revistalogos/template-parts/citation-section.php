<?php
/**
 * Cómo Citar: citation formats, copy buttons, RIS export and citation
 * info. Shared by single-article.php and the revistalogos/article-citation
 * block; assets/js/citation.js drives the buttons.
 *
 * Args:
 * - article_id: article ID (required).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_article_id = isset( $args['article_id'] ) ? absint( $args['article_id'] ) : 0;

if ( ! $revistalogos_article_id ) {
	return;
}

$revistalogos_formats   = revistalogos_citation_formats( $revistalogos_article_id );
$revistalogos_doi       = get_post_meta( $revistalogos_article_id, 'doi', true );
$revistalogos_post_name = get_post_field( 'post_name', $revistalogos_article_id );
?>
<section class="single-article__section citation-section">
	<h2><?php esc_html_e( 'Cómo Citar', 'revistalogos' ); ?></h2>

	<div class="citation-formats">
		<?php foreach ( $revistalogos_formats as $revistalogos_format_name => $revistalogos_format_text ) : ?>
			<?php $revistalogos_format_slug = sanitize_title( $revistalogos_format_name ); ?>
			<div class="citation-format">
				<h3 id="citation-format-<?php echo esc_attr( $revistalogos_format_slug ); ?>"><?php echo esc_html( $revistalogos_format_name ); ?></h3>
				<div class="citation-text">
					<?php if ( 'BibTeX' === $revistalogos_format_name ) : ?>
						<pre><?php echo esc_html( $revistalogos_format_text ); ?></pre>
					<?php else : ?>
						<?php echo esc_html( $revistalogos_format_text ); ?>
					<?php endif; ?>
				</div>
				<button id="citation-copy-<?php echo esc_attr( $revistalogos_format_slug ); ?>" class="btn btn--small citation-copy" data-format="<?php echo esc_attr( $revistalogos_format_slug ); ?>" aria-labelledby="citation-format-<?php echo esc_attr( $revistalogos_format_slug ); ?> citation-copy-<?php echo esc_attr( $revistalogos_format_slug ); ?>"><span aria-hidden="true">📋</span> <?php esc_html_e( 'Copiar', 'revistalogos' ); ?></button>
			</div>
		<?php endforeach; ?>
	</div>

	<div id="citation-copy-status" class="visually-hidden" role="status" aria-live="polite"></div>

	<div class="citation-actions">
		<button class="btn btn--primary" id="export-all-citations" data-filename="<?php echo esc_attr( 'citas-' . $revistalogos_post_name . '.txt' ); ?>"><span aria-hidden="true">📄</span> <?php esc_html_e( 'Exportar Todas las Citas', 'revistalogos' ); ?></button>
		<button class="btn btn--secondary" id="download-ris" data-filename="<?php echo esc_attr( $revistalogos_post_name . '.ris' ); ?>">📥 <?php esc_html_e( 'Descargar RIS', 'revistalogos' ); ?></button>
	</div>
	<pre id="ris-data" hidden><?php echo esc_html( revistalogos_citation_ris( $revistalogos_article_id ) ); ?></pre>

	<div class="citation-info">
		<h3><?php esc_html_e( 'Información de Cita', 'revistalogos' ); ?></h3>
		<ul>
			<li><strong>DOI:</strong> <?php echo $revistalogos_doi ? esc_html( $revistalogos_doi ) : esc_html__( 'Próximamente', 'revistalogos' ); ?></li>
			<li><strong>URL:</strong> <?php echo esc_url( get_permalink( $revistalogos_article_id ) ); ?></li>
			<li><strong><?php esc_html_e( 'Fecha de acceso:', 'revistalogos' ); ?></strong> <span id="current-date"></span></li>
		</ul>
	</div>
</section>
