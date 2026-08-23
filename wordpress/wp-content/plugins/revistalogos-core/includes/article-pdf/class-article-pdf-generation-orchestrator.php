<?php
/**
 * Orchestrates article PDF generation from a publication/PDF decision
 * (ADR 0017 work unit 3).
 *
 * KEEP_EXISTING skips the renderer. GENERATE_REQUIRED calls the renderer
 * and maps the outcome through Article_Pdf_Publication_Policy.
 * No WordPress, persistence or real renderer.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF decision → optional renderer call → publication decision.
 */
class Article_Pdf_Generation_Orchestrator {

	/**
	 * @var Article_Pdf_Renderer
	 */
	private $renderer;

	/**
	 * @var Article_Pdf_Publication_Policy
	 */
	private $policy;

	/**
	 * @param Article_Pdf_Renderer $renderer Replaceable renderer.
	 */
	public function __construct( Article_Pdf_Renderer $renderer ) {
		$this->renderer = $renderer;
		$this->policy   = new Article_Pdf_Publication_Policy();
	}

	/**
	 * @param string $pdf_decision KEEP_EXISTING or GENERATE_REQUIRED.
	 * @param mixed  $source       Opaque article/source payload.
	 * @return array
	 */
	public function orchestrate( $pdf_decision, $source ) {
		if ( ! $this->policy->requests_generation( $pdf_decision ) ) {
			return $this->outcome(
				$pdf_decision,
				Article_Pdf_Publication_Policy::GENERATION_SUCCESS,
				''
			);
		}

		$artifact          = $this->renderer->render( $source );
		$generation_result = ( '' !== $artifact )
			? Article_Pdf_Publication_Policy::GENERATION_SUCCESS
			: Article_Pdf_Publication_Policy::GENERATION_FAILURE;

		return $this->outcome( $pdf_decision, $generation_result, $artifact );
	}

	/**
	 * @param string $pdf_decision      KEEP_EXISTING or GENERATE_REQUIRED.
	 * @param string $generation_result GENERATION_SUCCESS or GENERATION_FAILURE.
	 * @param string $artifact          In-memory PDF bytes, or empty.
	 * @return array
	 */
	private function outcome( $pdf_decision, $generation_result, $artifact ) {
		return array(
			'publication_decision' => $this->policy->decide_publication( $pdf_decision, $generation_result ),
			'generation_result'    => $generation_result,
			'artifact'             => $artifact,
		);
	}
}
