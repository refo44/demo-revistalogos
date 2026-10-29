<?php
/**
 * Enviar colaboración wrapper (page-enviar-colaboracion.html). The
 * public process stays email-based (no portal); documents resolve to
 * Media Library attachments inside the content.
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
				get_template_part( 'template-parts/page-aside/enviar-colaboracion' );
			},
		)
	);
endwhile;

get_footer();
