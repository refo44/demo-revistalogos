<?php
/**
 * Native /?s= redirect to the canonical /buscar/?q= route, so no
 * competing indexable variant exists (prompt §9.5). It runs on
 * template_redirect instead of inside search.php because a block theme
 * resolves templates/search.html, which cannot redirect. Without the
 * buscar page there is no redirect and the search template renders the
 * shared search view.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect target for a native search request; null when the request is
 * not a search, is a search feed, or the buscar page is missing.
 *
 * @return string|null
 */
function revistalogos_native_search_redirect_url() {
	if ( ! is_search() || is_feed() ) {
		return null;
	}

	$buscar = get_page_by_path( 'buscar' );

	if ( ! $buscar instanceof WP_Post ) {
		return null;
	}

	return add_query_arg( 'q', rawurlencode( get_search_query() ), get_permalink( $buscar ) );
}

/**
 * Sends the 301 to /buscar/?q= for a native search request.
 */
function revistalogos_redirect_native_search() {
	$url = revistalogos_native_search_redirect_url();

	if ( null === $url ) {
		return;
	}

	wp_safe_redirect( $url, 301 );
	exit;
}
add_action( 'template_redirect', 'revistalogos_redirect_native_search' );
