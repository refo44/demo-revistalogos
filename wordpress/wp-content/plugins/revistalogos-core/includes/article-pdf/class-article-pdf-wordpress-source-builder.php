<?php
/**
 * Build a self-contained PDF source HTML document from an Article
 * (ADR 0017 work unit 6A).
 *
 * Reads WordPress Article state only. Does not render PDF, persist,
 * decide publication, or register hooks.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Article CPT → local self-contained HTML string.
 */
class Article_Pdf_WordPress_Source_Builder {

	/**
	 * @param mixed $article_id Article post ID.
	 * @return string|\WP_Error Non-empty HTML on success.
	 */
	public function build( $article_id ) {
		$article = $this->validate_article( $article_id );
		if ( is_wp_error( $article ) ) {
			return $article;
		}

		return $this->document(
			get_the_title( $article ),
			$this->render_body( $article ),
			$this->author_names( (int) $article->ID )
		);
	}

	/**
	 * @param mixed $article_id Raw article ID.
	 * @return \WP_Post|\WP_Error
	 */
	private function validate_article( $article_id ) {
		$article_id = absint( $article_id );
		if ( $article_id <= 0 ) {
			return new \WP_Error(
				'article_pdf_invalid_article',
				'Invalid article.'
			);
		}

		$article = get_post( $article_id );
		if ( ! $article || Content_Types::ARTICLE !== $article->post_type ) {
			return new \WP_Error(
				'article_pdf_invalid_article',
				'Invalid article.'
			);
		}

		return $article;
	}

	/**
	 * Render Gutenberg block markup locally. do_blocks() is enough for
	 * current Article content: the plugin registers no the_content
	 * filters, and apply_filters( 'the_content' ) would also run embeds
	 * and shortcodes that can reach the network.
	 *
	 * @param \WP_Post $article Article post.
	 * @return string
	 */
	private function render_body( $article ) {
		$raw = is_string( $article->post_content ) ? $article->post_content : '';
		if ( '' === $raw ) {
			return '';
		}

		$html = function_exists( 'do_blocks' ) ? do_blocks( $raw ) : $raw;

		return wp_kses_post( $html );
	}

	/**
	 * ADR 0017 §5 names authors as part of the generated-PDF source.
	 * Names only; no theme chrome, permalinks or Fase 4 identifiers.
	 *
	 * @param int $article_id Article post ID.
	 * @return string[]
	 */
	private function author_names( $article_id ) {
		$names   = array();
		$authors = Queries::article_authors( $article_id );
		foreach ( $authors as $author ) {
			$name = get_the_title( $author );
			if ( is_string( $name ) && '' !== $name ) {
				$names[] = $name;
			}
		}

		return $names;
	}

	/**
	 * @param string   $title   Article title.
	 * @param string   $body    Already-escaped/kses body HTML.
	 * @param string[] $authors Author display names.
	 * @return string
	 */
	private function document( $title, $body, $authors ) {
		$title = is_string( $title ) ? $title : '';
		$body  = is_string( $body ) ? $body : '';

		$author_html = '';
		if ( $authors ) {
			$safe = array();
			foreach ( $authors as $name ) {
				$safe[] = esc_html( $name );
			}
			$author_html = '<p class="les-pdf-authors">' . implode( ', ', $safe ) . '</p>';
		}

		return '<!DOCTYPE html>' . "\n"
			. '<html lang="es">' . "\n"
			. '<head>' . "\n"
			. '<meta charset="UTF-8">' . "\n"
			. '<title>' . esc_html( $title ) . '</title>' . "\n"
			. '<style>' . $this->document_css() . '</style>' . "\n"
			. '</head>' . "\n"
			. '<body>' . "\n"
			. '<article>' . "\n"
			. '<header>' . "\n"
			. '<h1>' . esc_html( $title ) . '</h1>' . "\n"
			. $author_html
			. '</header>' . "\n"
			. '<main>' . "\n"
			. $body . "\n"
			. '</main>' . "\n"
			. '</article>' . "\n"
			. '</body>' . "\n"
			. '</html>';
	}

	/**
	 * Minimal readable print CSS. Not the theme stylesheet.
	 *
	 * @return string
	 */
	private function document_css() {
		return 'body{font-family:DejaVu Sans,sans-serif;font-size:12pt;line-height:1.45;color:#111;margin:2cm;}'
			. 'h1{font-size:18pt;line-height:1.25;margin:0 0 0.6em;}'
			. 'p{margin:0 0 0.8em;}'
			. 'ul,ol{margin:0 0 0.8em 1.4em;}'
			. 'img{max-width:100%;height:auto;}';
	}
}
