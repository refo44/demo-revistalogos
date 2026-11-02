<?php
/**
 * Contacto wrapper (page-contacto.html). The canonical prose renders
 * via the_content(); the "Enviar Mensaje" region holds Contact Form 7
 * when available (ADR 0010) with an accessible mailto fallback, keeping
 * form configuration outside the canonical prose.
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
			'after_content' => static function () {
				get_template_part( 'template-parts/contact-form' );
			},
		)
	);
endwhile;

get_footer();
