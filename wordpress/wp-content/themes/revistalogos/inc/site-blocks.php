<?php
/**
 * Theme blocks for the block templates (ADR 0015 §7). The site header
 * and footer (§7.5) sit in parts/header.html and parts/footer.html; the
 * front-page and institutional-page blocks (Session 5) sit in
 * templates/front-page.html and page*.html. Each block buffers the same
 * template part the classic PHP view prints, so both share one source
 * and Revistalogos_Nav_Walker keeps emitting nav__*. Presentation only:
 * no domain data is registered here (ADR 0005).
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
 * Registers the theme blocks and their editor preview.
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

	$render_callbacks = array(
		'site-header'   => 'revistalogos_render_site_header_block',
		'site-footer'   => 'revistalogos_render_site_footer_block',
		'front-hero'    => 'revistalogos_render_front_hero_block',
		'front-sidebar' => 'revistalogos_render_front_sidebar_block',
		'page-header'   => 'revistalogos_render_page_header_block',
		'page-aside'    => 'revistalogos_render_page_aside_block',
		'contact-form'  => 'revistalogos_render_contact_form_block',
	);

	foreach ( $render_callbacks as $name => $render_callback ) {
		register_block_type( $blocks_dir . '/' . $name, array( 'render_callback' => $render_callback ) );
	}
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
 * Front-page hero with the current-issue call to action.
 *
 * @return string
 */
function revistalogos_render_front_hero_block() {
	return revistalogos_capture_template_part( 'template-parts/front-hero' );
}

/**
 * Front-page sidebar grid: news, Enviar colaboración and CENFISS.
 *
 * @return string
 */
function revistalogos_render_front_sidebar_block() {
	return revistalogos_capture_template_part( 'template-parts/front-sidebar' );
}

/**
 * Breadcrumbs and archive-header of the context page; '' without one.
 *
 * @param array    $attributes Unused.
 * @param string   $content    Unused.
 * @param WP_Block $block      Block instance.
 * @return string
 */
function revistalogos_render_page_header_block( $attributes, $content, $block ) {
	$page = empty( $block->context['postId'] ) ? null : get_post( (int) $block->context['postId'] );

	if ( ! $page ) {
		return '';
	}

	return revistalogos_capture_template_part( 'template-parts/page-header', array( 'post' => $page ) );
}

/**
 * Sidebar of an institutional page from template-parts/page-aside/{slug};
 * '' when the slug is not a plain key or has no part.
 *
 * @param array $attributes Block attributes (slug).
 * @return string
 */
function revistalogos_render_page_aside_block( $attributes ) {
	$slug = isset( $attributes['slug'] ) ? (string) $attributes['slug'] : '';

	if ( '' === $slug || sanitize_key( $slug ) !== $slug ) {
		return '';
	}

	$aside = revistalogos_capture_template_part( 'template-parts/page-aside/' . $slug );

	return '' === trim( $aside ) ? '' : '<aside class="sidebar">' . $aside . '</aside>';
}

/**
 * Contacto "Enviar Mensaje" region (Contact Form 7 or mailto).
 *
 * @return string
 */
function revistalogos_render_contact_form_block() {
	return revistalogos_capture_template_part( 'template-parts/contact-form' );
}

/**
 * Buffers a theme template part into a string.
 *
 * @param string $slug Template part slug.
 * @param array  $args Part args.
 * @return string
 */
function revistalogos_capture_template_part( $slug, $args = array() ) {
	ob_start();
	get_template_part( $slug, null, $args );

	return (string) ob_get_clean();
}
