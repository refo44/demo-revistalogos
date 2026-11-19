<?php
/**
 * Single article, static-parity with single-article.html: header,
 * metadata box, abstracts, keywords, body via the_content(), citation
 * tools and PDF actions. The prototype's ORCID rows are deferred to
 * Fase 4 (ADR 0013).
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
			get_template_part( 'template-parts/view/article-single' );
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
