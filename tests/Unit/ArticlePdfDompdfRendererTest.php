<?php
/**
 * Concrete Dompdf article PDF renderer (ADR 0017, work unit 4).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Article_Pdf_Generation_Orchestrator;
use Revistalogos_Core\Article_Pdf_Publication_Policy;
use Revistalogos_Core\Dompdf_Article_Pdf_Renderer;

/**
 * Protects HTML → real in-memory PDF bytes before WordPress wiring.
 */
class ArticlePdfDompdfRendererTest extends TestCase {

	/**
	 * Self-contained UTF-8 Spanish HTML must become a real PDF artifact.
	 */
	public function test_self_contained_spanish_html_renders_pdf_bytes() {
		$renderer = new Dompdf_Article_Pdf_Renderer();
		$html     = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Filosofía</title></head><body><p>Filosofía, razón y ética en España: ñ á é í ó ú</p></body></html>';
		$artifact = $renderer->render( $html );

		$this->assertIsString( $artifact );
		$this->assertNotSame( '', $artifact );
		$this->assertStringStartsWith( '%PDF-', $artifact );
		$this->assertStringNotContainsString( $html, $artifact );
	}

	/**
	 * WU3 orchestration must accept the real renderer artifact.
	 */
	public function test_orchestrator_allows_publication_with_real_pdf_artifact() {
		$renderer     = new Dompdf_Article_Pdf_Renderer();
		$orchestrator = new Article_Pdf_Generation_Orchestrator( $renderer );
		$html         = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Ética</title></head><body><p>Ética y razón</p></body></html>';
		$result       = $orchestrator->orchestrate(
			Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
			$html
		);

		$this->assertStringStartsWith( '%PDF-', $result['artifact'] );
		$this->assertSame( Article_Pdf_Publication_Policy::GENERATION_SUCCESS, $result['generation_result'] );
		$this->assertSame( Article_Pdf_Publication_Policy::PUBLICATION_ALLOWED, $result['publication_decision'] );
	}
}
