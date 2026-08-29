<?php
/**
 * Build a self-contained PDF source HTML document from an Article
 * (ADR 0017 work unit 6A; editorial offprint design: issue #10,
 * BACKLOG item 3).
 *
 * Reads WordPress Article state only. Does not render PDF, persist,
 * decide publication, or register hooks. Presentation lives in
 * Article_Pdf_Editorial_Template; this class only gathers fields.
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

		return $this->build_for_publication(
			$article_id,
			get_the_title( $article ),
			is_string( $article->post_content ) ? $article->post_content : ''
		);
	}

	/**
	 * Build source HTML from the Article being published in this request.
	 * Editorial context (authors, issue, abstracts, identifiers, terms)
	 * still comes from stored state.
	 *
	 * @param mixed  $article_id        Article post ID.
	 * @param mixed  $candidate_title   Title of the publication request.
	 * @param mixed  $candidate_content Raw post_content of the request.
	 * @return string|\WP_Error
	 */
	public function build_for_publication( $article_id, $candidate_title, $candidate_content ) {
		$article = $this->validate_article( $article_id );
		if ( is_wp_error( $article ) ) {
			return $article;
		}

		$title = is_string( $candidate_title ) ? $candidate_title : get_the_title( $article );
		$raw   = is_string( $candidate_content ) ? $candidate_content : '';

		$fields              = $this->editorial_fields( (int) $article->ID );
		$fields['title']     = $title;
		$fields['body_html'] = $this->render_body_from_raw( $raw );

		$template = new Article_Pdf_Editorial_Template();

		return $template->render( $fields );
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
	 * @param string $raw Raw post_content.
	 * @return string
	 */
	private function render_body_from_raw( $raw ) {
		if ( ! is_string( $raw ) || '' === $raw ) {
			return '';
		}

		$html = function_exists( 'do_blocks' ) ? do_blocks( $raw ) : $raw;

		return wp_kses_post( $html );
	}

	/**
	 * Stored editorial context for the offprint (ADR 0017 §5 and the
	 * WU6A deferred fields): journal identity, issue citation data,
	 * article metadata, authors, and taxonomy terms. Values are passed
	 * as stored; the template omits what is empty. DOI/ORCID/ISSN stay
	 * inert strings (ADR 0013).
	 *
	 * @param int $article_id Article post ID.
	 * @return array
	 */
	private function editorial_fields( $article_id ) {
		$fields = array(
			'journal_name'     => (string) get_bloginfo( 'name' ),
			'title_en'         => (string) get_post_meta( $article_id, 'title_en', true ),
			'abstract'         => (string) get_post_meta( $article_id, 'abstract', true ),
			'abstract_en'      => (string) get_post_meta( $article_id, 'abstract_en', true ),
			'doi'              => (string) get_post_meta( $article_id, 'doi', true ),
			'pages'            => (string) get_post_meta( $article_id, 'pages', true ),
			'received_date'    => (string) get_post_meta( $article_id, 'received_date', true ),
			'accepted_date'    => (string) get_post_meta( $article_id, 'accepted_date', true ),
			'publication_date' => (string) get_post_meta( $article_id, 'publication_date', true ),
			'authors'          => $this->author_entries( $article_id ),
			'section'          => $this->first_term_name( $article_id, Taxonomies::SECTION ),
			'keywords'         => $this->term_names( $article_id, Taxonomies::KEYWORD ),
		);

		return array_merge( $fields, $this->issue_fields( $article_id ) );
	}

	/**
	 * ADR 0017 §5 names authors as part of the generated-PDF source;
	 * the offprint adds their affiliation and inert ORCID when stored.
	 *
	 * @param int $article_id Article post ID.
	 * @return array[]
	 */
	private function author_entries( $article_id ) {
		$entries = array();
		$authors = Queries::article_authors( $article_id );
		foreach ( $authors as $author ) {
			$name = get_the_title( $author );
			if ( ! is_string( $name ) || '' === $name ) {
				continue;
			}
			$entries[] = array(
				'name'        => $name,
				'affiliation' => (string) get_post_meta( $author->ID, 'afiliacion', true ),
				'orcid'       => (string) get_post_meta( $author->ID, 'orcid', true ),
			);
		}

		return $entries;
	}

	/**
	 * Citation context from the published Issue the Article belongs to.
	 * An unpublished or missing Issue contributes nothing.
	 *
	 * @param int $article_id Article post ID.
	 * @return array
	 */
	private function issue_fields( $article_id ) {
		$issue = Queries::article_issue( $article_id );
		if ( ! $issue ) {
			return array();
		}

		return array(
			'volume' => absint( get_post_meta( $issue->ID, 'volume_number', true ) ),
			'number' => absint( get_post_meta( $issue->ID, 'issue_number', true ) ),
			'year'   => absint( get_post_meta( $issue->ID, 'year', true ) ),
			'issn'   => (string) get_post_meta( $issue->ID, 'issn', true ),
		);
	}

	/**
	 * @param int    $article_id Article post ID.
	 * @param string $taxonomy   Taxonomy name.
	 * @return string[] Term names in the order returned by WordPress.
	 */
	private function term_names( $article_id, $taxonomy ) {
		$terms = get_the_terms( $article_id, $taxonomy );
		if ( ! is_array( $terms ) ) {
			return array();
		}

		$names = array();
		foreach ( $terms as $term ) {
			if ( isset( $term->name ) && is_string( $term->name ) && '' !== $term->name ) {
				$names[] = $term->name;
			}
		}

		return $names;
	}

	/**
	 * @param int    $article_id Article post ID.
	 * @param string $taxonomy   Taxonomy name.
	 * @return string First term name or ''.
	 */
	private function first_term_name( $article_id, $taxonomy ) {
		$names = $this->term_names( $article_id, $taxonomy );

		return $names ? $names[0] : '';
	}
}
