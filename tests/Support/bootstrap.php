<?php
/**
 * PHPUnit unit bootstrap. Does not load WordPress.
 *
 * Theme/plugin files guard on ABSPATH; define a dummy path so a pure
 * helper can be included without booting WordPress.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/revistalogos-unit-abspath/' );
}

$citations = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc/citations.php';

require_once $citations;

$plugin_root = dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/revistalogos-core';
$autoload    = $plugin_root . '/vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
}

$article_pdf_dir = $plugin_root . '/includes/article-pdf';

require_once $article_pdf_dir . '/class-article-pdf-publication-policy.php';
require_once $article_pdf_dir . '/interface-article-pdf-renderer.php';
require_once $article_pdf_dir . '/class-article-pdf-generation-orchestrator.php';
require_once $article_pdf_dir . '/class-dompdf-article-pdf-renderer.php';
