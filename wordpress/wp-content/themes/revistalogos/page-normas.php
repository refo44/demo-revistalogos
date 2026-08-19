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
	get_template_part(
		'template-parts/content/content-institutional-page',
		null,
		array(
			'description' => __( 'Instrucciones para autores sobre formato, estilo y citación APA (7.ª edición) para la elaboración de producción intelectual de LOGO ET SPES.', 'revistalogos' ),
		)
	);
endwhile;

get_footer();
