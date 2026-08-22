<?php
/**
 * Pure publication/PDF policy for academic articles (ADR 0017 work unit 1).
 *
 * Decides whether to keep an existing valid PDF or require generation,
 * and whether publication may continue after a generation result.
 * No WordPress, filesystem, Media Library or renderer dependency.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep / generate / block outcomes for article PDF at publish time.
 */
class Article_Pdf_Publication_Policy {

	const KEEP_EXISTING       = 'keep_existing';
	const GENERATE_REQUIRED   = 'generate_required';
	const GENERATION_SUCCESS  = 'generation_success';
	const GENERATION_FAILURE  = 'generation_failure';
	const PUBLICATION_ALLOWED = 'publication_allowed';
	const PUBLICATION_BLOCKED = 'publication_blocked';

	/**
	 * Decide the PDF action from whether a valid PDF is already assigned.
	 *
	 * @param bool $has_valid_pdf Whether a valid PDF already exists.
	 * @return string KEEP_EXISTING or GENERATE_REQUIRED.
	 */
	public function decide_pdf_action( $has_valid_pdf ) {
		if ( $has_valid_pdf ) {
			return self::KEEP_EXISTING;
		}

		return self::GENERATE_REQUIRED;
	}

	/**
	 * Whether the PDF decision requests generation.
	 *
	 * @param string $decision Outcome of decide_pdf_action().
	 * @return bool
	 */
	public function requests_generation( $decision ) {
		return self::GENERATE_REQUIRED === $decision;
	}

	/**
	 * Decide whether publication may continue after a generation result.
	 *
	 * Generation result is only meaningful when generation is required.
	 *
	 * @param string $pdf_decision       KEEP_EXISTING or GENERATE_REQUIRED.
	 * @param string $generation_result  GENERATION_SUCCESS or GENERATION_FAILURE.
	 * @return string PUBLICATION_ALLOWED or PUBLICATION_BLOCKED.
	 */
	public function decide_publication( $pdf_decision, $generation_result ) {
		if ( self::KEEP_EXISTING === $pdf_decision ) {
			return self::PUBLICATION_ALLOWED;
		}

		if ( self::GENERATE_REQUIRED === $pdf_decision && self::GENERATION_SUCCESS === $generation_result ) {
			return self::PUBLICATION_ALLOWED;
		}

		return self::PUBLICATION_BLOCKED;
	}
}
