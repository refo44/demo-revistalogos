<?php
/**
 * Article PDF publication policy (ADR 0017, work unit 1).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Article_Pdf_Publication_Policy;

/**
 * Protects the keep / generate / block decisions before WordPress wiring.
 */
class ArticlePdfPublicationPolicyTest extends TestCase {

	/**
	 * A valid PDF already assigned must be kept; generation must not start.
	 */
	public function test_valid_pdf_is_kept_and_generation_is_not_requested() {
		$policy   = new Article_Pdf_Publication_Policy();
		$decision = $policy->decide_pdf_action( true );

		$this->assertSame( Article_Pdf_Publication_Policy::KEEP_EXISTING, $decision );
		$this->assertFalse( $policy->requests_generation( $decision ) );
	}

	/**
	 * Publishing without a valid PDF must require generation.
	 */
	public function test_missing_valid_pdf_requires_generation() {
		$policy   = new Article_Pdf_Publication_Policy();
		$decision = $policy->decide_pdf_action( false );

		$this->assertSame( Article_Pdf_Publication_Policy::GENERATE_REQUIRED, $decision );
		$this->assertTrue( $policy->requests_generation( $decision ) );
	}

	/**
	 * Required generation that fails must block publication.
	 */
	public function test_failed_generation_blocks_publication() {
		$policy = new Article_Pdf_Publication_Policy();
		$result = $policy->decide_publication(
			Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
			Article_Pdf_Publication_Policy::GENERATION_FAILURE
		);

		$this->assertSame( Article_Pdf_Publication_Policy::PUBLICATION_BLOCKED, $result );
	}

	/**
	 * Required generation that succeeds must allow publication.
	 */
	public function test_successful_generation_allows_publication() {
		$policy = new Article_Pdf_Publication_Policy();
		$result = $policy->decide_publication(
			Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
			Article_Pdf_Publication_Policy::GENERATION_SUCCESS
		);

		$this->assertSame( Article_Pdf_Publication_Policy::PUBLICATION_ALLOWED, $result );
	}
}
