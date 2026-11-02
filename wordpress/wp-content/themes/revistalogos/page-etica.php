<?php
/**
 * Ética wrapper (page-etica.html). The body is the literal canonical
 * "Normas de Ética" text imported from content-source (docs/03 §2: the
 * static demo summary must not be used). Sidebar: page-aside/etica.
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
				get_template_part( 'template-parts/page-aside/etica' );
			},
		)
	);
endwhile;

get_footer();
