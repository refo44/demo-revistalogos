<?php
/**
 * Single issue, static-parity with single-issue.html. The body lives in
 * template-parts/view/issue-single, shared with
 * templates/single-issue.html (revistalogos/view), which wins when the
 * block templates are active.
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
			get_template_part( 'template-parts/view/issue-single' );
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
