<?php
/**
 * Políticas wrapper (page-politicas.html).
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
			'description' => __( 'Conoce las políticas que rigen la publicación en LOGO ET SPES, incluyendo garantías de autoría, acceso abierto, derechos de autor, antiplagio y más.', 'revistalogos' ),
		)
	);
endwhile;

get_footer();
