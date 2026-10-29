<?php
/**
 * Article block family: article card, metadata box and Cómo Citar.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Article blocks, rendered through the theme's article partials.
 */
class Article_Blocks {

	/**
	 * Registers the article blocks.
	 */
	public static function register() {
		Domain_Blocks::register_block( 'article-card', array( __CLASS__, 'render_article_card' ) );
		Domain_Blocks::register_block( 'article-metadata', array( __CLASS__, 'render_article_metadata' ) );
		Domain_Blocks::register_block( 'article-citation', array( __CLASS__, 'render_article_citation' ) );
	}

	/**
	 * Article card for the context article.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Unused.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_article_card( $attributes, $content = '', $block = null ) {
		$article = Domain_Blocks::context_post( $block, Content_Types::ARTICLE );

		if ( ! $article ) {
			return '';
		}

		return Domain_Blocks::theme_part(
			'template-parts/article-card',
			array(
				'post'    => $article->ID,
				'heading' => Domain_Blocks::heading( $attributes ),
			)
		);
	}

	/**
	 * Metadata box of the context article.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Unused.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_article_metadata( $attributes, $content = '', $block = null ) {
		$article = Domain_Blocks::context_post( $block, Content_Types::ARTICLE );

		if ( ! $article ) {
			return '';
		}

		return Domain_Blocks::theme_part( 'template-parts/metadata-box', array( 'article_id' => $article->ID ) );
	}

	/**
	 * Cómo Citar of the context article: the existing formats, RIS and
	 * citation info, unchanged (formats belong to ADR 0021/0022).
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Unused.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_article_citation( $attributes, $content = '', $block = null ) {
		$article = Domain_Blocks::context_post( $block, Content_Types::ARTICLE );

		if ( ! $article ) {
			return '';
		}

		return Domain_Blocks::theme_part( 'template-parts/citation-section', array( 'article_id' => $article->ID ) );
	}
}
