<?php
/**
 * Noticias index (posts page), static-parity with noticias.html.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php revistalogos_breadcrumbs( array( array( 'label' => __( 'Noticias', 'revistalogos' ) ) ) ); ?>

		<header class="archive-header">
			<h1 class="archive-header__title"><?php esc_html_e( 'Noticias', 'revistalogos' ); ?></h1>
			<p class="archive-header__description">
				<?php esc_html_e( 'Noticias, eventos y reflexiones sobre filosofía y la revista académica LOGO ET SPES.', 'revistalogos' ); ?>
			</p>
		</header>

		<?php if ( have_posts() ) : ?>
			<section class="blog-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="blog-post">
						<header class="blog-post__header">
							<div class="blog-post__meta">
								<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> &bull;
								<span><?php esc_html_e( 'Por', 'revistalogos' ); ?> <strong><?php the_author(); ?></strong></span>
							</div>
							<h2 class="blog-post__title">
								<a href="<?php the_permalink(); ?>" class="blog-post__link">
									<?php the_title(); ?>
								</a>
							</h2>
						</header>

						<div class="blog-post__content">
							<div class="blog-post__excerpt">
								<?php the_excerpt(); ?>
							</div>

							<div class="blog-post__footer">
								<a href="<?php the_permalink(); ?>" class="blog-post__read-more"><?php esc_html_e( 'Leer más', 'revistalogos' ); ?> &rarr;</a>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</section>
			<?php revistalogos_pagination(); ?>
		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/content-none',
				null,
				array(
					'title' => __( 'Todavía no hay noticias publicadas.', 'revistalogos' ),
					'text'  => __( 'Vuelva pronto para leer novedades de la revista y del CENFISS.', 'revistalogos' ),
				)
			);
			?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
