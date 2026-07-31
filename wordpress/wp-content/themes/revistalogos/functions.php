<?php
/**
 * Theme bootstrap: supports, menus, asset enqueueing and presentation
 * helpers. Domain registration (CPTs, taxonomies, roles, meta) lives in
 * the revistalogos-core plugin and is never duplicated here (ADR 0005).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REVISTALOGOS_THEME_VERSION', '0.1.0' );

require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/class-nav-walker.php';
require_once get_template_directory() . '/inc/citations.php';
require_once get_template_directory() . '/inc/metadata-output.php';

/**
 * Theme supports and menu locations.
 */
function revistalogos_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'primary'      => __( 'Navegación principal', 'revistalogos' ),
			'footer-quick' => __( 'Footer: Enlaces Rápidos', 'revistalogos' ),
			'footer-norms' => __( 'Footer: Normas Editoriales', 'revistalogos' ),
		)
	);
}
add_action( 'after_setup_theme', 'revistalogos_setup' );

/**
 * Front-end assets. main.css is the only stylesheet entry point
 * (ADR 0003); main.js loads in the footer like the static site and the
 * primary content works without it.
 */
function revistalogos_enqueue_assets() {
	wp_enqueue_style(
		'revistalogos-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array(),
		REVISTALOGOS_THEME_VERSION
	);

	wp_enqueue_script(
		'revistalogos-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		REVISTALOGOS_THEME_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	// Citation tools only where the citation block renders.
	if ( is_singular( 'article' ) ) {
		wp_enqueue_script(
			'revistalogos-citation',
			get_template_directory_uri() . '/assets/js/citation.js',
			array(),
			REVISTALOGOS_THEME_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'revistalogos_enqueue_assets' );

/**
 * Selective dequeue (ADR 0003 §3): the theme renders no block markup on
 * the public side, so the core block library styles only add weight and
 * specificity wars against main.css. Audited against the templates in
 * this theme (no wp-block-* classes used). Revisit if editor blocks ever
 * render publicly.
 */
function revistalogos_dequeue_foreign_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'revistalogos_dequeue_foreign_styles', 20 );

/**
 * Favicon, mirroring the static <link rel="icon"> when no Site Icon is
 * set in the customizer.
 */
function revistalogos_favicon() {
	if ( has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" type="image/png" href="%s">' . "\n",
		esc_url( get_template_directory_uri() . '/assets/img/favicon.png' )
	);
}
add_action( 'wp_head', 'revistalogos_favicon' );

/**
 * Whether the revistalogos-core plugin is active.
 *
 * @return bool
 */
function revistalogos_core_active() {
	return class_exists( 'Revistalogos_Core\Plugin' );
}

/**
 * Admin notice when the domain plugin is missing: the theme keeps
 * rendering safe fallbacks but journal content types are unavailable.
 */
function revistalogos_core_missing_notice() {
	if ( revistalogos_core_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'El theme Revista LOGO ET SPES necesita el plugin «Revista LOGO ET SPES — Core» para mostrar números, artículos y autores. Actívalo en Plugins.', 'revistalogos' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'revistalogos_core_missing_notice' );
