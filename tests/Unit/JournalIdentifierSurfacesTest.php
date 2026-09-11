<?php
/**
 * Public surfaces read journal identifiers from plugin settings (issue #58).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * Footer and Acerca keep the public labels and do not hardcode the values.
 *
 * @ticket 58
 */
class JournalIdentifierSurfacesTest extends TestCase {

	/**
	 * Dado: el pie del sitio.
	 * Entonces: ISSN y depósito legal salen del ajuste, con las etiquetas actuales.
	 */
	public function test_footer_reads_issn_and_legal_deposit_from_settings() {
		$footer = $this->repo_file_contents(
			'wordpress/wp-content/themes/revistalogos/template-parts/footer.php'
		);

		$this->assertStringContainsString( '<strong>ISSN:</strong>', $footer );
		$this->assertStringContainsString( 'Depósito Legal:', $footer );
		$this->assertStringContainsString( "revistalogos_journal_identifier_text( 'issn' )", $footer );
		$this->assertStringContainsString( "revistalogos_journal_identifier_text( 'legal_deposit' )", $footer );
		$this->assertStringNotContainsString( 'electrónico', $footer );
		$this->assertStringNotContainsString( 'digital', mb_strtolower( $footer ) );
	}

	/**
	 * Dado: la ficha de Acerca.
	 * Entonces: ISSN, depósito legal y DOI salen del ajuste.
	 */
	public function test_acerca_reads_journal_identifiers_from_settings() {
		$acerca = $this->repo_file_contents(
			'wordpress/wp-content/themes/revistalogos/page-acerca.php'
		);

		$this->assertStringContainsString( '<dt>ISSN:</dt>', $acerca );
		$this->assertStringContainsString( 'Depósito Legal:', $acerca );
		$this->assertStringContainsString( '<dt>DOI:</dt>', $acerca );
		$this->assertStringContainsString( "revistalogos_journal_identifier_text( 'issn' )", $acerca );
		$this->assertStringContainsString( "revistalogos_journal_identifier_text( 'legal_deposit' )", $acerca );
		$this->assertStringContainsString( "revistalogos_journal_identifier_text( 'doi_prefix' )", $acerca );
	}

	/**
	 * @param string $relative Path from the repository root.
	 */
	private function repo_file_contents( $relative ) {
		$path = dirname( __DIR__, 2 ) . '/' . $relative;
		$this->assertFileIsReadable( $path );

		$contents = file_get_contents( $path );
		$this->assertNotFalse( $contents );

		return $contents;
	}
}
