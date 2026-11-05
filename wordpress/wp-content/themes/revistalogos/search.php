<?php
/**
 * Native /?s= fallback. inc/search-redirect.php has already sent the
 * request to /buscar/?q= on template_redirect when the buscar page
 * exists; without it, render the shared search page markup directly.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_template_directory() . '/page-buscar.php';
