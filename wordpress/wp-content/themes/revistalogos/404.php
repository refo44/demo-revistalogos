<?php
/**
 * 404 template, static-parity with 404.html.
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
		<?php get_template_part( 'template-parts/view/not-found' ); ?>
	</div>
</main>
<?php
get_footer();
