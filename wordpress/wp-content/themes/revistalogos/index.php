<?php
/**
 * Global fallback template. Real screens resolve to their specific
 * templates; this renders a plain list so nothing ever fatals.
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
		<?php if ( have_posts() ) : ?>
			<div class="news-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'card' ); ?>>
						<div class="card__body">
							<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<?php the_excerpt(); ?>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php revistalogos_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content-none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
