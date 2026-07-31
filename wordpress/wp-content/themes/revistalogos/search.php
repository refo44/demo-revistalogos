<?php
/**
 * Native /?s= fallback: redirect to the canonical /buscar/?q= route so
 * no competing indexable variant exists (prompt §9.5). If the buscar
 * page is missing, render the shared search page markup directly.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_buscar = get_page_by_path( 'buscar' );

if ( $revistalogos_buscar instanceof WP_Post ) {
	wp_safe_redirect(
		add_query_arg( 'q', rawurlencode( get_search_query() ), get_permalink( $revistalogos_buscar ) ),
		301
	);
	exit;
}

require get_template_directory() . '/page-buscar.php';
