<?php
/**
 * Authors archive. The static screen (archive-author.html) is a
 * deliberate empty-state placeholder awaiting editorial records; the
 * dynamic list reuses the existing card/archive-grid components and
 * keeps the static empty state when no author exists.
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
		<?php get_template_part( 'template-parts/view/author-archive' ); ?>
	</div>
</main>
<?php
get_footer();
