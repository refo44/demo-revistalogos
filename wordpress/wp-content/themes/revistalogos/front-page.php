<?php
/**
 * Front page, static-parity with index.html: hero, featured current
 * issue, three recent articles and the sidebar grid. Dynamic data
 * replaces the demo dataset; hero copy is permanent UI copy.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$revistalogos_current = revistalogos_current_issue();

$revistalogos_recent_articles = revistalogos_core_active() ? get_posts(
	array(
		'post_type'      => 'article',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
) : array();

$revistalogos_recent_news = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'no_found_rows'  => true,
	)
);

$revistalogos_article_archive = get_post_type_archive_link( 'article' );
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<!-- Hero Section -->
		<section class="front-page__hero">
			<div class="hero">
				<div class="hero__content">
					<h1 class="hero__title">LOGO ET SPES</h1>
					<p class="hero__subtitle"><?php esc_html_e( 'Revista de Filosofía', 'revistalogos' ); ?></p>
					<p class="hero__description">
						<?php esc_html_e( 'La Revista de Filosofía LOGO ET SPES adscrita, auspiciada y editada por el Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS, es una publicación digital venezolana enfocada en el pensamiento filosófico multidisciplinar. Es de acceso abierto; arbitrada bajo la modalidad <doble anónimo o doble ciego>; con periodicidad anual. Sus páginas están disponibles para difundir investigaciones originales -de autores nacionales e internacionales- que coadyuven a promover el desarrollo de todas las áreas de la Filosofía.', 'revistalogos' ); ?>
					</p>
					<div class="hero__actions">
						<a href="<?php echo esc_url( revistalogos_current_issue_url() ); ?>" class="btn btn--primary btn--large"><?php esc_html_e( 'Ver número actual', 'revistalogos' ); ?></a>
					</div>
				</div>
			</div>
		</section>

		<!-- Número actual destacado -->
		<?php if ( $revistalogos_current ) : ?>
			<section class="front-page__current-issue">
				<h2 class="front-page__section-title"><?php esc_html_e( 'Número actual', 'revistalogos' ); ?></h2>
				<?php
				get_template_part(
					'template-parts/issue-card',
					null,
					array(
						'post'     => $revistalogos_current,
						'heading'  => 'h3',
						'featured' => true,
					)
				);
				?>
			</section>
		<?php endif; ?>

		<!-- Artículos recientes -->
		<?php if ( $revistalogos_recent_articles ) : ?>
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
		<?php endif; ?>

		<!-- Sidebar Section -->
		<section class="front-page__sidebar">
			<div class="sidebar-grid">
				<!-- Noticias Card -->
				<div class="sidebar-card">
					<h3><?php esc_html_e( 'Noticias', 'revistalogos' ); ?></h3>
					<?php if ( $revistalogos_recent_news ) : ?>
						<ul class="news-list">
							<?php foreach ( $revistalogos_recent_news as $revistalogos_news_item ) : ?>
								<li class="news-item">
									<a href="<?php echo esc_url( get_permalink( $revistalogos_news_item ) ); ?>" class="news-link"><?php echo esc_html( get_the_title( $revistalogos_news_item ) ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p><?php esc_html_e( 'Todavía no hay noticias publicadas.', 'revistalogos' ); ?></p>
					<?php endif; ?>
					<a href="<?php echo esc_url( home_url( '/noticias/' ) ); ?>" class="btn btn--small btn--outline mt-4"><?php esc_html_e( 'Ver noticias', 'revistalogos' ); ?></a>
				</div>

				<!-- Enviar colaboración Card -->
				<div class="sidebar-card">
					<h3><?php esc_html_e( 'Enviar colaboración', 'revistalogos' ); ?></h3>
					<p><?php esc_html_e( 'Consulte las normas editoriales, prepare los archivos requeridos y envíe su manuscrito conforme al proceso definido por la revista.', 'revistalogos' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/enviar-colaboracion/' ) ); ?>" class="btn btn--small btn--outline"><?php esc_html_e( 'Enviar colaboración', 'revistalogos' ); ?></a>
				</div>

				<!-- CENFISS Card -->
				<div class="sidebar-card">
					<h3>CENFISS</h3>
					<p><?php esc_html_e( 'Centro de Filosofía para la Investigación Stanislao Strba', 'revistalogos' ); ?></p>
					<p><strong><?php esc_html_e( 'Ubicación:', 'revistalogos' ); ?></strong> <?php esc_html_e( 'Estado de Carabobo, Valencia, Venezuela', 'revistalogos' ); ?></p>
					<a href="https://cenfiss.net" target="_blank" rel="noopener noreferrer" class="btn btn--small"><?php esc_html_e( 'Ir al sitio', 'revistalogos' ); ?><span class="visually-hidden"> (se abre en nueva pestaña)</span></a>
				</div>
			</div>
		</section>
	</div>
</main>
<?php
get_footer();
