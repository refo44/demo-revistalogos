<?php
/**
 * Site header and footer blocks for the block templates (ADR 0015 §7.5).
 * parts/header.html and parts/footer.html only place these blocks; the
 * markup still comes from template-parts/header.php and footer.php, so
 * the classic PHP views and the block templates share one source and
 * Revistalogos_Nav_Walker keeps emitting nav__*. Presentation only: no
 * domain data is registered here (ADR 0005).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * The header part carries the static skip-link to #main-content. Core's
 * block-template skip link would be a second one, in English, so both of
 * its hooks come off (core skips the insertion when either is missing).
 */
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
remove_action( 'wp_footer', 'the_block_template_skip_link' );

/**
 * Registers the site header and footer blocks and their editor preview.
 */
function revistalogos_register_site_blocks() {
	$blocks_dir = get_template_directory() . '/blocks';

	wp_register_script(
		'revistalogos-site-blocks-editor',
		get_template_directory_uri() . '/assets/js/site-blocks-editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-server-side-render' ),
		REVISTALOGOS_THEME_VERSION,
		true
	);

	register_block_type(
		$blocks_dir . '/site-header',
		array( 'render_callback' => 'revistalogos_render_site_header_block' )
	);
	register_block_type(
		$blocks_dir . '/site-footer',
		array( 'render_callback' => 'revistalogos_render_site_footer_block' )
	);
}
add_action( 'init', 'revistalogos_register_site_blocks' );

/**
 * Header block: skip-link, logo and primary nav (walker or fallback).
 *
 * @return string
 */
function revistalogos_render_site_header_block() {
	return revistalogos_capture_template_part( 'template-parts/header' );
}

/**
 * Footer block: footer-quick / footer-norms menus or their fallbacks.
 *
 * @return string
 */
function revistalogos_render_site_footer_block() {
	return revistalogos_capture_template_part( 'template-parts/footer' );
}

/**
 * Buffers a theme template part into a string.
 *
 * @param string $slug Template part slug.
 * @return string
 */
function revistalogos_capture_template_part( $slug ) {
	ob_start();
	get_template_part( $slug );

	return (string) ob_get_clean();
}
