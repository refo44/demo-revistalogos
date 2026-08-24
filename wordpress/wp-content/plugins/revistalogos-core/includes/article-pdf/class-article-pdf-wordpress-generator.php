<?php
/**
 * Explicit Article → PDF composition (ADR 0017 work unit 6A).
 *
 * Composes WU2 adapter, source builder, WU3 orchestrator, WU4 Dompdf
 * renderer and WU5 persister. Does not register hooks, change status,
 * or read a publication-enforcement option.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Article ID → existing attachment ID, or newly persisted attachment ID.
 */
class Article_Pdf_WordPress_Generator {

	/**
	 * @var Article_Pdf_WordPress_Adapter
	 */
	private $adapter;

	/**
	 * @var Article_Pdf_WordPress_Source_Builder
	 */
	private $source_builder;

	/**
	 * @var Article_Pdf_Generation_Orchestrator
	 */
	private $orchestrator;

	/**
	 * @var Article_Pdf_WordPress_Persister
	 */
	private $persister;

	public function __construct() {
		$this->adapter        = new Article_Pdf_WordPress_Adapter();
		$this->source_builder = new Article_Pdf_WordPress_Source_Builder();
		$this->orchestrator   = new Article_Pdf_Generation_Orchestrator(
			new Dompdf_Article_Pdf_Renderer()
		);
		$this->persister      = new Article_Pdf_WordPress_Persister();
	}

	/**
	 * @param mixed $article_id Article post ID.
	 * @return int|\WP_Error Attachment ID on success.
	 */
	public function generate_for_article( $article_id ) {
		return $this->generate_for_publication( $article_id, null, null, null );
	}

	/**
	 * Keep or generate using candidate publication values.
	 *
	 * Null candidate PDF reads stored pdf_file. Null title/content
	 * fall back to the persisted Article (explicit WU6A generation).
	 *
	 * @param mixed $article_id          Article post ID.
	 * @param mixed $candidate_pdf_file  Candidate attachment ID or null.
	 * @param mixed $candidate_title     Publication title or null.
	 * @param mixed $candidate_content   Publication content or null.
	 * @return int|\WP_Error Attachment ID on success.
	 */
	public function generate_for_publication( $article_id, $candidate_pdf_file, $candidate_title, $candidate_content ) {
		$decision = $this->adapter->decide_pdf_action_for_article( $article_id, $candidate_pdf_file );

		if ( Article_Pdf_Publication_Policy::KEEP_EXISTING === $decision ) {
			$candidate = Metadata::sanitize_pdf_attachment_id( $candidate_pdf_file );
			if ( $candidate > 0 ) {
				return $candidate;
			}

			$existing = $this->adapter->existing_valid_pdf_file_id( $article_id );
			if ( $existing > 0 ) {
				return $existing;
			}
		}

		if ( is_string( $candidate_title ) && is_string( $candidate_content ) ) {
			$source = $this->source_builder->build_for_publication(
				$article_id,
				$candidate_title,
				$candidate_content
			);
		} else {
			$source = $this->source_builder->build( $article_id );
		}
		if ( is_wp_error( $source ) ) {
			return $source;
		}

		$result = $this->orchestrator->orchestrate(
			Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
			$source
		);

		if (
			Article_Pdf_Publication_Policy::GENERATION_SUCCESS !== $result['generation_result']
			|| ! is_string( $result['artifact'] )
			|| '' === $result['artifact']
		) {
			return new \WP_Error(
				'article_pdf_generation_failed',
				'Could not generate PDF.'
			);
		}

		return $this->persister->persist( $article_id, $result['artifact'] );
	}
}
