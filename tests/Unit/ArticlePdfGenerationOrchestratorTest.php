<?php
/**
 * Article PDF generation orchestration (ADR 0017, work unit 3).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Article_Pdf_Generation_Orchestrator;
use Revistalogos_Core\Article_Pdf_Publication_Policy;
use Revistalogos_Core\Article_Pdf_Renderer;

/**
 * In-test renderer: records invocation and returns a configured artifact.
 */
class Recording_Article_Pdf_Renderer implements Article_Pdf_Renderer {

	/**
	 * @var int
	 */
	public $invocations = 0;

	/**
	 * @var mixed
	 */
	public $last_source;

	/**
	 * @var string
	 */
	public $output = "%PDF-fake\n";

	/**
	 * @param mixed $source Opaque article/source payload.
	 * @return string
	 */
	public function render( $source ) {
		$this->invocations++;
		$this->last_source = $source;

		return $this->output;
	}
}

/**
 * Protects keep / generate / block orchestration before WordPress wiring.
 */
class ArticlePdfGenerationOrchestratorTest extends TestCase {

	/**
	 * A valid PDF already assigned must not invoke generation.
	 */
	public function test_existing_pdf_skips_renderer_and_allows_publication() {
		$renderer     = new Recording_Article_Pdf_Renderer();
		$orchestrator = new Article_Pdf_Generation_Orchestrator( $renderer );
		$result       = $orchestrator->orchestrate(
			Article_Pdf_Publication_Policy::KEEP_EXISTING,
			'article-source'
		);

		$this->assertSame( 0, $renderer->invocations );
		$this->assertSame( Article_Pdf_Publication_Policy::PUBLICATION_ALLOWED, $result['publication_decision'] );
		$this->assertSame( '', $result['artifact'] );
	}

	/**
	 * Missing PDF must generate once and allow publication on success.
	 */
	public function test_missing_pdf_invokes_renderer_and_returns_artifact() {
		$renderer           = new Recording_Article_Pdf_Renderer();
		$renderer->output   = "%PDF-generated\n";
		$orchestrator       = new Article_Pdf_Generation_Orchestrator( $renderer );
		$result             = $orchestrator->orchestrate(
			Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
			'article-source-42'
		);

		$this->assertSame( 1, $renderer->invocations );
		$this->assertSame( 'article-source-42', $renderer->last_source );
		$this->assertSame( "%PDF-generated\n", $result['artifact'] );
		$this->assertSame( Article_Pdf_Publication_Policy::GENERATION_SUCCESS, $result['generation_result'] );
		$this->assertSame( Article_Pdf_Publication_Policy::PUBLICATION_ALLOWED, $result['publication_decision'] );
	}

	/**
	 * Empty renderer output is generation failure and must block publication.
	 */
	public function test_empty_renderer_output_blocks_publication() {
		$renderer         = new Recording_Article_Pdf_Renderer();
		$renderer->output = '';
		$orchestrator     = new Article_Pdf_Generation_Orchestrator( $renderer );
		$result           = $orchestrator->orchestrate(
			Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
			'article-source-fail'
		);

		$this->assertSame( 1, $renderer->invocations );
		$this->assertSame( Article_Pdf_Publication_Policy::GENERATION_FAILURE, $result['generation_result'] );
		$this->assertSame( Article_Pdf_Publication_Policy::PUBLICATION_BLOCKED, $result['publication_decision'] );
		$this->assertSame( '', $result['artifact'] );
	}
}
