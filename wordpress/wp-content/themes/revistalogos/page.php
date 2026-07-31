<?php
/**
 * Shared fallback for pages without a slug-specific wrapper.
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
