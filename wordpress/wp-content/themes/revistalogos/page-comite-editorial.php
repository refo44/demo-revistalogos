<?php
/**
 * Comité Editorial wrapper (page-comite-editorial.html).
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
			'description' => __( 'Conoce el Comité Editorial y el equipo técnico de LOGO ET SPES, conformado por especialistas en filosofía.', 'revistalogos' ),
		)
	);
endwhile;

get_footer();
