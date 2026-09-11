<?php
/**
 * Single issue, static-parity with single-issue.html: header with
 * cover/meta/actions, table of contents, inline editorial (the article
 * with article_type=editorial of this issue) and derived stats.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$revistalogos_issue_id      = get_the_ID();
	$revistalogos_label         = revistalogos_issue_label( $revistalogos_issue_id );
	$revistalogos_issn          = get_post_meta( $revistalogos_issue_id, 'issn', true );
	$revistalogos_doi           = get_post_meta( $revistalogos_issue_id, 'doi', true );
	$revistalogos_published     = get_post_meta( $revistalogos_issue_id, 'date_published', true );
	$revistalogos_pdf_url       = revistalogos_meta_attachment_url( $revistalogos_issue_id );
	$revistalogos_issue_archive = get_post_type_archive_link( 'issue' );

	$revistalogos_articles = revistalogos_issue_articles( $revistalogos_issue_id );

	// Inline editorial: the linked article typed "editorial".
	$revistalogos_editorial = null;
	foreach ( $revistalogos_articles as $revistalogos_item ) {
		if ( has_term( 'editorial', 'article_type', $revistalogos_item ) ) {
			$revistalogos_editorial = $revistalogos_item;
			break;
		}
	}

	// Derived stats (never stored: ADR 0005). Section count is assigned
	// terms only; the Secciones card is omitted when that count is 0.
	$revistalogos_section_count = revistalogos_issue_section_count( $revistalogos_issue_id );
	$revistalogos_author_ids    = array();
	foreach ( $revistalogos_articles as $revistalogos_item ) {
		$revistalogos_ids = get_post_meta( $revistalogos_item->ID, 'authors', true );
		if ( is_array( $revistalogos_ids ) ) {
			foreach ( $revistalogos_ids as $revistalogos_author_id ) {
				$revistalogos_author_ids[ absint( $revistalogos_author_id ) ] = true;
			}
		}
	}
	?>
	<main id="main-content" class="main-content" tabindex="-1">
		<div class="container">
			<?php
			revistalogos_breadcrumbs(
				array(
					array(
						'label' => __( 'Revista', 'revistalogos' ),
						'url'   => $revistalogos_issue_archive ? $revistalogos_issue_archive : home_url( '/' ),
					),
					array( 'label' => $revistalogos_label ),
				)
			);
			?>

			<!-- Issue Header -->
			<header class="single-issue__header">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php
					the_post_thumbnail(
						'large',
						array(
							'class' => 'single-issue__cover',
							/* translators: %s: issue label. */
							'alt'   => sprintf( __( 'Portada %s', 'revistalogos' ), $revistalogos_label ),
						)
					);
					?>
				<?php endif; ?>
				<h1 class="single-issue__title"><?php echo esc_html( $revistalogos_label ); ?></h1>
				<div class="single-issue__meta">
					<p><strong>ISSN:</strong> <?php echo $revistalogos_issn ? esc_html( $revistalogos_issn ) : esc_html__( 'Próximamente', 'revistalogos' ); ?> | <strong>DOI:</strong> <?php echo $revistalogos_doi ? esc_html( $revistalogos_doi ) : esc_html__( 'Próximamente', 'revistalogos' ); ?></p>
					<?php if ( $revistalogos_published ) : ?>
						<p><strong><?php esc_html_e( 'Publicado:', 'revistalogos' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $revistalogos_published ) ) ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( '' !== trim( get_the_content() ) ) : ?>
					<div class="single-issue__description">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
				<?php if ( $revistalogos_pdf_url ) : ?>
					<div class="single-issue__actions">
						<a href="<?php echo esc_url( $revistalogos_pdf_url ); ?>" class="btn btn--primary btn--large"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %s: issue label. */ __( 'Descargar PDF completo del %s', 'revistalogos' ), $revistalogos_label ) ); ?>">
							<span aria-hidden="true">📄</span> <?php esc_html_e( 'Descargar PDF del número completo', 'revistalogos' ); ?>
						</a>
						<a href="<?php echo esc_url( $revistalogos_pdf_url ); ?>" class="btn btn--secondary btn--large" target="_blank" rel="noopener noreferrer">
							<span aria-hidden="true">👁️</span> <?php esc_html_e( 'Ver PDF', 'revistalogos' ); ?><span class="visually-hidden"> (se abre en nueva pestaña)</span>
						</a>
					</div>
				<?php endif; ?>
			</header>

			<!-- Tabla de contenidos -->
			<?php get_template_part( 'template-parts/toc', null, array( 'issue_id' => $revistalogos_issue_id ) ); ?>

			<!-- Editorial -->
			<?php if ( $revistalogos_editorial ) : ?>
				<section class="single-issue__editorial">
					<h2><?php esc_html_e( 'Editorial', 'revistalogos' ); ?></h2>
					<?php echo wp_kses_post( apply_filters( 'the_content', $revistalogos_editorial->post_content ) ); ?>
				</section>
			<?php endif; ?>

			<!-- Estadísticas del número -->
			<?php
			get_template_part(
				'template-parts/issue-stats',
				null,
				array(
					'article_count' => count( $revistalogos_articles ),
					'section_count' => $revistalogos_section_count,
					'author_count'  => count( $revistalogos_author_ids ),
				)
			);
			?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
