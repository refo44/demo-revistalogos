<?php
/**
 * Normas wrapper (page-normas.html). The body (download card, table of
 * contents and the norms themselves) renders from WordPress content.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content/content-institutional-page' );
endwhile;

get_footer();
