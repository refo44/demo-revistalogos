<?php
/**
 * Read-only WordPress adapter for the article PDF publication policy
 * (ADR 0017 work unit 2).
 *
 * Inspects pdf_file and returns the domain decision. Does not generate
 * bytes, create attachments, write meta, or register hooks.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Article → valid-PDF predicate → Article_Pdf_Publication_Policy.
 */
class Article_Pdf_WordPress_Adapter {

	/**
	 * Decide the PDF action for an article from stored or intended pdf_file.
	 *
	 * Passing $pdf_file evaluates a candidate ID without writing. Omitting
	 * it reads the current post meta. The check is read-only.
	 *
	 * @param int   $article_id Article post ID.
	 * @param mixed $pdf_file   Optional candidate attachment ID.
	 * @return string KEEP_EXISTING or GENERATE_REQUIRED.
	 */
	public function decide_pdf_action_for_article( $article_id, $pdf_file = null ) {
		$article_id = absint( $article_id );

		if ( null === $pdf_file && $article_id ) {
			$pdf_file = get_post_meta( $article_id, 'pdf_file', true );
		}

		$has_valid = $this->is_valid_pdf_file( $pdf_file );
		$policy    = new Article_Pdf_Publication_Policy();

		return $policy->decide_pdf_action( $has_valid );
	}

	/**
	 * Read-only reuse of the registered pdf_file contract: existing
	 * attachment with MIME application/pdf. Does not persist.
	 *
	 * @param mixed $pdf_file Raw attachment ID or stored meta.
	 * @return bool
	 */
	public function is_valid_pdf_file( $pdf_file ) {
		return 0 !== Metadata::sanitize_pdf_attachment_id( $pdf_file );
	}
}
