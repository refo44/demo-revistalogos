<?php
/**
 * Search page (/buscar/?q=), static-parity with search.html: header,
 * form and results with the documented type priority (docs/04) and an
 * accessible empty state. No custom rewrites; q is a plain GET param.
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
		<?php get_template_part( 'template-parts/view/search' ); ?>
	</div>
</main>
<?php
get_footer();
