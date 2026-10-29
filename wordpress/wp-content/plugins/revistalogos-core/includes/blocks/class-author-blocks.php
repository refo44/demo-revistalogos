<?php
/**
 * Author block family: author card.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author blocks, rendered through the theme's author partial.
 */
class Author_Blocks {

	/**
	 * Registers the author blocks.
	 */
	public static function register() {
		Domain_Blocks::register_block( 'author-card', array( __CLASS__, 'render_author_card' ) );
	}

	/**
	 * Author card for the context author profile.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Unused.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_author_card( $attributes, $content = '', $block = null ) {
		$author = Domain_Blocks::context_post( $block, Content_Types::AUTHOR );

		if ( ! $author ) {
			return '';
		}

		return Domain_Blocks::theme_part( 'template-parts/author-card', array( 'post' => $author->ID ) );
	}
}
