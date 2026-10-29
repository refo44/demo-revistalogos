<?php
/**
 * Enlaces wrapper (page-enlaces.html). External links inside the
 * content carry the approved new-tab pattern from the payload.
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
