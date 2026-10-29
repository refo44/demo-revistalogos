<?php
/**
 * Foreign stylesheets the public front does not load: core block
 * styles and plugin defaults that would fight main.css.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Selective dequeue (ADR 0003 §3, superseded in part by ADR 0015 §3):
 * the PHP views render no block markup on the public side, so the core
 * block library styles only add weight and specificity wars against
 * main.css. Contact Form 7's default sheet is replaced by tokens in
 * static-pages.css. global-styles stays: it carries the Estilos palette
 * presets that tokens.css aliases. Revisit wp-block-library when public
 * views are served by block templates.
 */
function revistalogos_dequeue_foreign_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'contact-form-7' );
}
add_action( 'wp_enqueue_scripts', 'revistalogos_dequeue_foreign_styles', 20 );
