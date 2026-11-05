<?php
/**
 * Articles archive, static-parity with archive-article.html. Also
 * renders the section/article_type/keyword taxonomy archives through
 * thin taxonomy-*.php delegates (docs/12 §4.1, option A): the main
 * query arrives already filtered and only the heading adapts.
 *
 * Filters use native query vars only (s, section, year) — no custom
 * rewrites or query code (ADR 0008 KISS).
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
		<?php get_template_part( 'template-parts/view/article-archive' ); ?>
	</div>
</main>
<?php
get_footer();
