<?php
/**
 * Optional per-format Cómo Citar overrides (theme helper, no WordPress).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * Protects ADR 0021: empty override leaves the builder; a filled one
 * replaces only that format.
 */
class CitationOverrideMergeTest extends TestCase {

	/**
	 * Absent or blank overrides must not freeze the generated phrase.
	 */
	public function test_empty_override_leaves_the_generated_citation() {
		$generated = array(
			'APA' => 'Pérez Gómez, A.M. (2024). Title.',
			'MLA' => 'Ana María Pérez Gómez. "Title."',
		);

		$merged = revistalogos_merge_citation_overrides(
			$generated,
			array(
				'APA' => '',
				'MLA' => "  \n",
			)
		);

		$this->assertSame( $generated, $merged );
	}

	/**
	 * A filled override replaces only that box; the others stay generated.
	 */
	public function test_filled_override_replaces_only_that_format() {
		$generated = array(
			'APA' => 'Pérez Gómez, A.M. (2024). Title.',
			'MLA' => 'Ana María Pérez Gómez. "Title."',
			'RIS' => "TY  - JOUR\nER  - ",
		);

		$merged = revistalogos_merge_citation_overrides(
			$generated,
			array(
				'MLA' => 'Pérez Gómez, Ana María. "Title." LOGO ET SPES, vol. 1.',
			)
		);

		$this->assertSame( $generated['APA'], $merged['APA'] );
		$this->assertSame( $generated['RIS'], $merged['RIS'] );
		$this->assertSame(
			'Pérez Gómez, Ana María. "Title." LOGO ET SPES, vol. 1.',
			$merged['MLA']
		);
	}
}
