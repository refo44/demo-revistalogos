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
		<?php get_template_part( 'template-parts/view/news' ); ?>
	</div>
</main>
<?php
get_footer();
