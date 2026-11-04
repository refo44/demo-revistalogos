<?php
/**
 * Issues archive, static-parity with archive-issue.html.
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
		<?php get_template_part( 'template-parts/view/issue-archive' ); ?>
	</div>
</main>
<?php
get_footer();
