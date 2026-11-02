<?php
/**
 * Acerca wrapper (page-acerca.html): shared institutional renderer plus
 * the page-specific sidebar (journal info + CENFISS cards).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/content/content-institutional-page',
		null,
		array(
			'aside' => static function () {
				get_template_part( 'template-parts/page-aside/acerca' );
			},
		)
	);
endwhile;

get_footer();
