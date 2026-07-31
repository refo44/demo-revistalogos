<?php
/**
 * Article type taxonomy archive: thin delegate reusing the article
 * archive presentation (docs/12 §4.1, option A).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_template_directory() . '/archive-article.php';
