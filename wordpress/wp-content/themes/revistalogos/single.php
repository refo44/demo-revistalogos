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
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/view/news-single' );
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
