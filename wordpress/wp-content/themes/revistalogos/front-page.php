<?php
/**
 * Front page, static-parity with index.html: hero, featured current
 * issue, three recent articles and the sidebar grid. Dynamic data
 * replaces the demo dataset; hero copy is permanent UI copy. Each
 * section is a template part shared with templates/front-page.html.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$revistalogos_current         = revistalogos_current_issue();
$revistalogos_recent_articles = revistalogos_recent_articles();

?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php get_template_part( 'template-parts/front-hero' ); ?>

		<?php get_template_part( 'template-parts/current-issue', null, array( 'post' => $revistalogos_current ) ); ?>

		<?php get_template_part( 'template-parts/recent-articles', null, array( 'articles' => $revistalogos_recent_articles ) ); ?>

		<?php get_template_part( 'template-parts/front-sidebar' ); ?>
	</div>
</main>
<?php
get_footer();
