<?php
/**
 * Author profile. The static screen (single-author.html) is a
 * deliberate empty-state placeholder; the dynamic profile shows name,
 * affiliation, biography and published articles using existing
 * components. ORCID display is deferred to Fase 4 (ADR 0013).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$revistalogos_author_id  = get_the_ID();
	$revistalogos_afiliacion = get_post_meta( $revistalogos_author_id, 'afiliacion', true );
	$revistalogos_bio        = get_post_meta( $revistalogos_author_id, 'bio', true );
	$revistalogos_articles   = revistalogos_author_articles( $revistalogos_author_id );

	$revistalogos_issue_archive  = get_post_type_archive_link( 'issue' );
	$revistalogos_author_archive = get_post_type_archive_link( 'author' );
	$revistalogos_article_archive = get_post_type_archive_link( 'article' );
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
					array(
						'label' => __( 'Autores', 'revistalogos' ),
						'url'   => $revistalogos_author_archive ? $revistalogos_author_archive : home_url( '/' ),
					),
					array( 'label' => get_the_title() ),
				)
			);
			?>

			<header class="archive-header">
				<h1 class="archive-header__title"><?php the_title(); ?></h1>
				<?php if ( $revistalogos_afiliacion ) : ?>
					<p class="archive-header__description"><?php echo esc_html( $revistalogos_afiliacion ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( $revistalogos_bio ) : ?>
				<section class="content-main">
					<div class="card">
						<h2><?php esc_html_e( 'Biografía', 'revistalogos' ); ?></h2>
						<?php echo wp_kses_post( wpautop( $revistalogos_bio ) ); ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $revistalogos_articles ) : ?>
				<section class="archive-grid">
					<?php foreach ( $revistalogos_articles as $revistalogos_article_item ) : ?>
						<?php
						get_template_part(
							'template-parts/article-card',
							null,
							array(
								'post'    => $revistalogos_article_item,
								'heading' => 'h2',
							)
						);
						?>
					<?php endforeach; ?>
				</section>
			<?php else : ?>
				<section class="empty-state">
					<h2><?php esc_html_e( 'Este perfil todavía no tiene artículos publicados.', 'revistalogos' ); ?></h2>
					<div class="error-page__actions">
						<?php if ( $revistalogos_article_archive ) : ?>
							<a href="<?php echo esc_url( $revistalogos_article_archive ); ?>" class="btn btn--secondary"><?php esc_html_e( 'Ver artículos', 'revistalogos' ); ?></a>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
