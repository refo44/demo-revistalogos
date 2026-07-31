<?php
/**
 * Single article, static-parity with single-article.html: header,
 * metadata box, abstracts, keywords, body via the_content(), citation
 * tools and PDF actions. The prototype's ORCID rows are deferred to
 * Fase 4 (ADR 0013).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$revistalogos_article_id = get_the_ID();
	$revistalogos_title      = get_the_title();
	$revistalogos_title_en   = get_post_meta( $revistalogos_article_id, 'title_en', true );
	$revistalogos_abstract   = get_post_meta( $revistalogos_article_id, 'abstract', true );
	$revistalogos_abstract_en = get_post_meta( $revistalogos_article_id, 'abstract_en', true );
	$revistalogos_pages      = get_post_meta( $revistalogos_article_id, 'pages', true );
	$revistalogos_doi        = get_post_meta( $revistalogos_article_id, 'doi', true );
	$revistalogos_received   = get_post_meta( $revistalogos_article_id, 'received_date', true );
	$revistalogos_accepted   = get_post_meta( $revistalogos_article_id, 'accepted_date', true );
	$revistalogos_pubdate    = get_post_meta( $revistalogos_article_id, 'publication_date', true );
	$revistalogos_pdf_url    = revistalogos_meta_attachment_url( $revistalogos_article_id );
	$revistalogos_authors    = revistalogos_article_authors( $revistalogos_article_id );
	$revistalogos_issue      = revistalogos_article_issue( $revistalogos_article_id );
	$revistalogos_keywords   = get_the_terms( $revistalogos_article_id, 'keyword' );

	$revistalogos_issue_archive = get_post_type_archive_link( 'issue' );
	$revistalogos_date_format   = get_option( 'date_format' );

	$revistalogos_crumbs = array(
		array(
			'label' => __( 'Revista', 'revistalogos' ),
			'url'   => $revistalogos_issue_archive ? $revistalogos_issue_archive : home_url( '/' ),
		),
	);

	if ( $revistalogos_issue ) {
		$revistalogos_crumbs[] = array(
			'label' => revistalogos_issue_label( $revistalogos_issue->ID ),
			'url'   => get_permalink( $revistalogos_issue ),
		);
	}

	$revistalogos_crumbs[] = array( 'label' => $revistalogos_title );

	$revistalogos_formats = revistalogos_citation_formats( $revistalogos_article_id );
	?>
	<main id="main-content" class="main-content" tabindex="-1">
		<div class="container">
			<?php revistalogos_breadcrumbs( $revistalogos_crumbs ); ?>

			<!-- Article Header -->
			<header class="single-article__header">
				<h1 class="single-article__title"><?php echo esc_html( $revistalogos_title ); ?></h1>
				<?php if ( $revistalogos_title_en ) : ?>
					<p class="single-article__subtitle"><?php echo esc_html( $revistalogos_title_en ); ?></p>
				<?php endif; ?>
				<?php if ( $revistalogos_authors ) : ?>
					<div class="single-article__authors">
						<?php
						$revistalogos_links = array();
						foreach ( $revistalogos_authors as $revistalogos_author ) {
							$revistalogos_links[] = sprintf(
								'<a href="%s"><strong>%s</strong></a>',
								esc_url( get_permalink( $revistalogos_author ) ),
								esc_html( get_the_title( $revistalogos_author ) )
							);
						}
						echo implode( ', ', $revistalogos_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>
				<?php endif; ?>
				<div class="single-article__meta">
					<p>
						<?php if ( $revistalogos_issue ) : ?>
							<strong><?php echo esc_html( revistalogos_issue_label( $revistalogos_issue->ID ) ); ?></strong> &bull;
						<?php endif; ?>
						<?php if ( $revistalogos_pages ) : ?>
							<strong><?php echo esc_html( sprintf( /* translators: %s: page range. */ __( 'pp. %s', 'revistalogos' ), $revistalogos_pages ) ); ?></strong> &bull;
						<?php endif; ?>
						<strong>DOI:</strong> <?php echo $revistalogos_doi ? esc_html( $revistalogos_doi ) : esc_html__( 'Próximamente', 'revistalogos' ); ?>
					</p>
					<?php if ( $revistalogos_received || $revistalogos_accepted || $revistalogos_pubdate ) : ?>
						<p>
							<?php if ( $revistalogos_received ) : ?>
								<strong><?php esc_html_e( 'Recibido:', 'revistalogos' ); ?></strong> <?php echo esc_html( date_i18n( $revistalogos_date_format, strtotime( $revistalogos_received ) ) ); ?>
							<?php endif; ?>
							<?php if ( $revistalogos_accepted ) : ?>
								&bull; <strong><?php esc_html_e( 'Aceptado:', 'revistalogos' ); ?></strong> <?php echo esc_html( date_i18n( $revistalogos_date_format, strtotime( $revistalogos_accepted ) ) ); ?>
							<?php endif; ?>
							<?php if ( $revistalogos_pubdate ) : ?>
								&bull; <strong><?php esc_html_e( 'Publicado:', 'revistalogos' ); ?></strong> <?php echo esc_html( date_i18n( $revistalogos_date_format, strtotime( $revistalogos_pubdate ) ) ); ?>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div>
			</header>

			<!-- Metadata Box -->
			<?php get_template_part( 'template-parts/metadata-box', null, array( 'article_id' => $revistalogos_article_id ) ); ?>

			<!-- Article Content -->
			<article class="single-article__content">
				<?php if ( $revistalogos_abstract ) : ?>
					<section class="single-article__section" aria-labelledby="abs-es">
						<h2 id="abs-es"><?php esc_html_e( 'Resumen', 'revistalogos' ); ?></h2>
						<div class="single-article__abstract">
							<?php echo wp_kses_post( wpautop( $revistalogos_abstract ) ); ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $revistalogos_abstract_en ) : ?>
					<section class="single-article__section" aria-labelledby="abs-en">
						<h2 id="abs-en">Abstract</h2>
						<div class="single-article__abstract" lang="en">
							<?php echo wp_kses_post( wpautop( $revistalogos_abstract_en ) ); ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( is_array( $revistalogos_keywords ) && $revistalogos_keywords ) : ?>
					<section class="single-article__section">
						<h2><?php esc_html_e( 'Palabras clave', 'revistalogos' ); ?></h2>
						<p><strong><?php esc_html_e( 'Español:', 'revistalogos' ); ?></strong> <?php echo esc_html( implode( ', ', wp_list_pluck( $revistalogos_keywords, 'name' ) ) ); ?></p>
					</section>
				<?php endif; ?>

				<?php if ( '' !== trim( get_the_content() ) ) : ?>
					<?php the_content(); ?>
				<?php endif; ?>

				<!-- Cómo Citar -->
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
						<button class="btn btn--primary" id="export-all-citations" data-filename="<?php echo esc_attr( 'citas-' . get_post_field( 'post_name', $revistalogos_article_id ) . '.txt' ); ?>"><span aria-hidden="true">📄</span> <?php esc_html_e( 'Exportar Todas las Citas', 'revistalogos' ); ?></button>
						<button class="btn btn--secondary" id="download-ris" data-filename="<?php echo esc_attr( get_post_field( 'post_name', $revistalogos_article_id ) . '.ris' ); ?>">📥 <?php esc_html_e( 'Descargar RIS', 'revistalogos' ); ?></button>
					</div>
					<pre id="ris-data" hidden><?php echo esc_html( revistalogos_citation_ris( $revistalogos_article_id ) ); ?></pre>

					<div class="citation-info">
						<h3><?php esc_html_e( 'Información de Cita', 'revistalogos' ); ?></h3>
						<ul>
							<li><strong>DOI:</strong> <?php echo $revistalogos_doi ? esc_html( $revistalogos_doi ) : esc_html__( 'Próximamente', 'revistalogos' ); ?></li>
							<li><strong>URL:</strong> <?php echo esc_url( get_permalink() ); ?></li>
							<li><strong><?php esc_html_e( 'Fecha de acceso:', 'revistalogos' ); ?></strong> <span id="current-date"></span></li>
						</ul>
					</div>
				</section>
			</article>

			<!-- Article Actions -->
			<section class="single-article__actions">
				<?php if ( $revistalogos_pdf_url ) : ?>
					<a href="<?php echo esc_url( $revistalogos_pdf_url ); ?>" class="btn btn--pdf btn--large"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: article title. */ __( "Descargar PDF del artículo '%s'", 'revistalogos' ), $revistalogos_title ) ); ?>">
						<span aria-hidden="true">📄</span> <?php esc_html_e( 'Descargar PDF del artículo', 'revistalogos' ); ?>
					</a>
					<a href="<?php echo esc_url( $revistalogos_pdf_url ); ?>" class="btn btn--secondary btn--large" target="_blank" rel="noopener noreferrer">
						<span aria-hidden="true">👁️</span> <?php esc_html_e( 'Ver PDF', 'revistalogos' ); ?><span class="visually-hidden"> (se abre en nueva pestaña)</span>
					</a>
				<?php endif; ?>
				<?php if ( $revistalogos_issue ) : ?>
					<a href="<?php echo esc_url( get_permalink( $revistalogos_issue ) ); ?>" class="btn btn--secondary btn--large">
						<?php esc_html_e( 'Ver número completo', 'revistalogos' ); ?>
					</a>
				<?php endif; ?>
			</section>
		</div>
	</main>
	<?php
endwhile;

get_footer();
