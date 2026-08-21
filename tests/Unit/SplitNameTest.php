<?php
/**
 * Citation name-splitting heuristic (theme helper, no WordPress).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * Protects the surname/given split used by APA, Harvard and related formats.
 */
class SplitNameTest extends TestCase {

	/**
	 * Multi-token display names treat the last token as the surname.
	 */
	public function test_last_token_is_the_surname_used_in_citation_formats() {
		$parts = revistalogos_split_name( 'Rafael Eduardo Figueredo Oropeza' );

		$this->assertSame( 'Oropeza', $parts['surname'] );
		$this->assertSame( 'Rafael Eduardo Figueredo', $parts['given'] );
		$this->assertSame( 'R.E.F.', $parts['initials'] );
	}

	/**
	 * A mononym must not invent a given name or initials.
	 */
	public function test_single_token_name_has_no_given_names_or_initials() {
		$parts = revistalogos_split_name( 'Platón' );

		$this->assertSame( 'Platón', $parts['surname'] );
		$this->assertSame( '', $parts['given'] );
		$this->assertSame( '', $parts['initials'] );
	}
}
