<?php
/**
 * Author name backfill proposal (ADR 0022 / #67).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Author_Name_Backfill;

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/revistalogos-core/includes/metadata/class-author-name-backfill.php';

/**
 * Apply copies lastnames from citation_surname or last-token; names are
 * the title remainder; citation_surname is never written.
 *
 * @ticket 67
 */
class AuthorNameBackfillTest extends TestCase {

	/**
	 * Dado: citation_surname relleno.
	 * Entonces: family_names lo copia y given_names es lo anterior del título.
	 */
	public function test_filled_citation_surname_becomes_family_names_and_keeps_the_cite_field() {
		$out = Author_Name_Backfill::propose(
			array(
				'title'            => 'Sofía Camila León Albino',
				'given_names'      => '',
				'family_names'     => '',
				'citation_surname' => 'León Albino',
			)
		);

		$this->assertSame( 'Sofía Camila', $out['given_names'] );
		$this->assertSame( 'León Albino', $out['family_names'] );
		$this->assertSame( 'León Albino', $out['citation_surname'] );
	}

	/**
	 * Dado: citation_surname vacío.
	 * Entonces: family_names es last-token; citation_surname sigue vacío.
	 */
	public function test_empty_citation_surname_uses_last_token_and_does_not_fill_the_cite_field() {
		$out = Author_Name_Backfill::propose(
			array(
				'title'            => 'Luis Felipe Ramírez',
				'given_names'      => '',
				'family_names'     => '',
				'citation_surname' => '',
			)
		);

		$this->assertSame( 'Luis Felipe', $out['given_names'] );
		$this->assertSame( 'Ramírez', $out['family_names'] );
		$this->assertSame( '', $out['citation_surname'] );
	}

	/**
	 * Dado: un apellido de cita en medio del título.
	 * Entonces: given_names omite lo que va después.
	 */
	public function test_middle_citation_surname_does_not_copy_trailing_tokens_into_given_names() {
		$out = Author_Name_Backfill::propose(
			array(
				'title'            => 'Rafael Eduardo Figueredo Oropeza',
				'given_names'      => '',
				'family_names'     => '',
				'citation_surname' => 'Figueredo',
			)
		);

		$this->assertSame( 'Rafael Eduardo', $out['given_names'] );
		$this->assertSame( 'Figueredo', $out['family_names'] );
		$this->assertSame( 'Figueredo', $out['citation_surname'] );
	}

	/**
	 * Dado: metas ya rellenas.
	 * Entonces: Apply no las pisa.
	 */
	public function test_filled_given_and_family_names_are_not_overwritten() {
		$out = Author_Name_Backfill::propose(
			array(
				'title'            => 'Juan Pablo Sequeira Martínez',
				'given_names'      => 'Juan Pablo',
				'family_names'     => 'Sequeira Martínez',
				'citation_surname' => 'Sequeira Martínez',
			)
		);

		$this->assertSame( 'Juan Pablo', $out['given_names'] );
		$this->assertSame( 'Sequeira Martínez', $out['family_names'] );
		$this->assertSame( 'Sequeira Martínez', $out['citation_surname'] );
	}
}
