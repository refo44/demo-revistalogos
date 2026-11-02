<?php
/**
 * Visitor privacy page (page-privacidad.html), rendered for the page
 * assigned in Settings → Privacy (ADR 0011 §4). Distinct from the
 * editorial confidentiality section of page-politicas.
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
				get_template_part( 'template-parts/page-aside/privacidad' );
			},
		)
	);
endwhile;

get_footer();
