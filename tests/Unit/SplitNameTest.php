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

	/**
	 * Hispanic both-surnames: the optional bibliographic surname is the
	 * family name; initials are given names only (issue #64, ADR 0021).
	 */
	public function test_citation_surname_keeps_both_hispanic_surnames_and_given_initials() {
		$parts = revistalogos_split_name( 'Ana María Pérez Gómez', 'Pérez Gómez' );

		$this->assertSame( 'Pérez Gómez', $parts['surname'] );
		$this->assertSame( 'Ana María', $parts['given'] );
		$this->assertSame( 'A.M.', $parts['initials'] );
	}

	/**
	 * Empty or whitespace bibliographic surname keeps the last-token heuristic.
	 */
	public function test_empty_citation_surname_keeps_last_token_heuristic() {
		$parts = revistalogos_split_name( 'Ana María Pérez Gómez', '   ' );

		$this->assertSame( 'Gómez', $parts['surname'] );
		$this->assertSame( 'Ana María Pérez', $parts['given'] );
		$this->assertSame( 'A.M.P.', $parts['initials'] );
	}

	/**
	 * A hyphenated bibliographic surname stays one family name.
	 */
	public function test_hyphenated_citation_surname_is_kept_as_one_family_name() {
		$parts = revistalogos_split_name( 'María Pérez-Gómez', 'Pérez-Gómez' );

		$this->assertSame( 'Pérez-Gómez', $parts['surname'] );
		$this->assertSame( 'María', $parts['given'] );
		$this->assertSame( 'M.', $parts['initials'] );
	}

	/**
	 * Particles such as «de la Cruz» are the surname when the editor says so.
	 */
	public function test_particle_citation_surname_strips_from_the_end_of_the_display_name() {
		$parts = revistalogos_split_name( 'Juana Inés de la Cruz', 'de la Cruz' );

		$this->assertSame( 'de la Cruz', $parts['surname'] );
		$this->assertSame( 'Juana Inés', $parts['given'] );
		$this->assertSame( 'J.I.', $parts['initials'] );
	}

	/**
	 * A filled citation surname in the middle of the title is not treated
	 * as extra given names (ADR 0022 / #67).
	 */
	public function test_citation_surname_in_the_middle_omits_trailing_tokens_from_given_names() {
		$parts = revistalogos_split_name( 'Rafael Eduardo Figueredo Oropeza', 'Figueredo' );

		$this->assertSame( 'Figueredo', $parts['surname'] );
		$this->assertSame( 'Rafael Eduardo', $parts['given'] );
		$this->assertSame( 'R.E.', $parts['initials'] );
	}

	/**
	 * Empty citation surname uses stored family names (ADR 0022).
	 */
	public function test_family_names_are_the_surname_when_citation_surname_is_empty() {
		$parts = revistalogos_split_name( 'Sofía Camila León Albino', '', '', 'León Albino' );

		$this->assertSame( 'León Albino', $parts['surname'] );
		$this->assertSame( 'Sofía Camila', $parts['given'] );
		$this->assertSame( 'S.C.', $parts['initials'] );
	}

	/**
	 * A filled citation surname wins over family names.
	 */
	public function test_citation_surname_wins_over_family_names() {
		$parts = revistalogos_split_name(
			'Rafael Eduardo Figueredo Oropeza',
			'Figueredo',
			'Rafael Eduardo',
			'Figueredo Oropeza'
		);

		$this->assertSame( 'Figueredo', $parts['surname'] );
		$this->assertSame( 'Rafael Eduardo', $parts['given'] );
		$this->assertSame( 'R.E.', $parts['initials'] );
	}

	/**
	 * Stored given names are used as-is when present.
	 */
	public function test_stored_given_names_are_not_reparsed_from_the_title() {
		$parts = revistalogos_split_name( 'Ana María Pérez Gómez', 'Pérez Gómez', 'Ana María', 'Pérez Gómez' );

		$this->assertSame( 'Pérez Gómez', $parts['surname'] );
		$this->assertSame( 'Ana María', $parts['given'] );
		$this->assertSame( 'A.M.', $parts['initials'] );
	}
}
