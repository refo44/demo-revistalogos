<?php
/**
 * Single news post, static-parity with single-post.html: post body plus
 * related posts (latest other news, derived at query time).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$revistalogos_related = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 2,
			'post__not_in'   => array( get_the_ID() ),
			'no_found_rows'  => true,
		)
	);
	?>
	<main id="main-content" class="main-content" tabindex="-1">
		<div class="container">
			<?php
			revistalogos_breadcrumbs(
				array(
					array(
						'label' => __( 'Noticias', 'revistalogos' ),
						'url'   => home_url( '/noticias/' ),
					),
					array( 'label' => get_the_title() ),
				)
			);
			?>

			<article <?php post_class( 'blog-post blog-post--single' ); ?>>
				<header class="blog-post__header">
					<div class="blog-post__meta">
						<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> &bull;
						<span><?php esc_html_e( 'Por', 'revistalogos' ); ?> <strong><?php the_author(); ?></strong></span>
					</div>
					<h1 class="blog-post__title"><?php the_title(); ?></h1>
				</header>

				<div class="blog-post__content">
					<?php the_content(); ?>
				</div>
			</article>

			<?php if ( $revistalogos_related ) : ?>
				<section class="related-posts">
					<h2><?php esc_html_e( 'Entradas Relacionadas', 'revistalogos' ); ?></h2>
					<div class="grid grid-cols-2 gap-6">
						<?php foreach ( $revistalogos_related as $revistalogos_related_post ) : ?>
							<article class="card">
								<h3><a href="<?php echo esc_url( get_permalink( $revistalogos_related_post ) ); ?>"><?php echo esc_html( get_the_title( $revistalogos_related_post ) ); ?></a></h3>
								<p><?php echo esc_html( get_the_excerpt( $revistalogos_related_post ) ); ?></p>
								<div class="card__footer">
									<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $revistalogos_related_post ) ); ?>"><?php echo esc_html( get_the_date( '', $revistalogos_related_post ) ); ?></time>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
					<div class="front-page__section-footer">
						<a href="<?php echo esc_url( home_url( '/noticias/' ) ); ?>" class="btn btn--outline btn--small"><?php esc_html_e( 'Ver todas las noticias', 'revistalogos' ); ?></a>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
