<?php
/**
 * Journal identifier options on Settings → LOGO ET SPES (issue #58).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Article_Pdf_Publication_Settings;
use Revistalogos_Core\Journal_Identifier_Settings;
use Revistalogos_Core\Llms_Txt;

/**
 * Site-level digital identifiers. Missing option is empty, never invented.
 *
 * @ticket 58
 */
class JournalIdentifierSettingsTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		delete_option( Journal_Identifier_Settings::OPTION_ISSN );
		delete_option( Journal_Identifier_Settings::OPTION_LEGAL_DEPOSIT );
		delete_option( Journal_Identifier_Settings::OPTION_DOI_PREFIX );
	}

	/**
	 * Dado: las opciones no existen.
	 * Entonces: los getters quedan vacíos.
	 */
	public function test_missing_options_are_empty() {
		$this->assertSame( '', Journal_Identifier_Settings::issn() );
		$this->assertSame( '', Journal_Identifier_Settings::legal_deposit() );
		$this->assertSame( '', Journal_Identifier_Settings::doi_prefix() );
	}

	/**
	 * Dado: valores oficiales guardados.
	 * Entonces: el plugin los devuelve tal cual.
	 */
	public function test_stored_options_are_returned() {
		update_option( Journal_Identifier_Settings::OPTION_ISSN, '2443-5678' );
		update_option( Journal_Identifier_Settings::OPTION_LEGAL_DEPOSIT, 'DC2026000123' );
		update_option( Journal_Identifier_Settings::OPTION_DOI_PREFIX, '10.12345' );

		$this->assertSame( '2443-5678', Journal_Identifier_Settings::issn() );
		$this->assertSame( 'DC2026000123', Journal_Identifier_Settings::legal_deposit() );
		$this->assertSame( '10.12345', Journal_Identifier_Settings::doi_prefix() );
	}

	/**
	 * Dado: Ajustes → LOGO ET SPES.
	 * Entonces: la pantalla ofrece ISSN, depósito legal y prefijo DOI.
	 */
	public function test_logo_et_spes_settings_page_offers_identifier_fields() {
		Article_Pdf_Publication_Settings::register_setting();
		Journal_Identifier_Settings::register_setting();

		ob_start();
		Article_Pdf_Publication_Settings::render_page();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="' . Journal_Identifier_Settings::OPTION_ISSN . '"', $html );
		$this->assertStringContainsString( 'name="' . Journal_Identifier_Settings::OPTION_LEGAL_DEPOSIT . '"', $html );
		$this->assertStringContainsString( 'name="' . Journal_Identifier_Settings::OPTION_DOI_PREFIX . '"', $html );
		$this->assertStringContainsString( 'ISSN', $html );
		$this->assertStringContainsString( 'Depósito Legal', $html );
		$this->assertStringContainsString( 'Prefijo DOI', $html );
		$this->assertStringNotContainsString( 'Pie, Acerca y /llms.txt.', $html );
		$this->assertStringNotContainsString( 'Inerte hasta Crossref.', $html );
	}

	/**
	 * Dado: identificadores guardados.
	 * Entonces: /llms.txt los incluye; vacío no escribe Próximamente.
	 */
	public function test_llms_txt_includes_stored_identifiers_and_omits_empty_ones() {
		$empty = Llms_Txt::render();
		$this->assertStringNotContainsString( 'ISSN:', $empty );
		$this->assertStringNotContainsString( 'Depósito Legal:', $empty );
		$this->assertStringNotContainsString( 'DOI:', $empty );
		$this->assertStringNotContainsString( 'Próximamente', $empty );

		update_option( Journal_Identifier_Settings::OPTION_ISSN, '2443-5678' );
		update_option( Journal_Identifier_Settings::OPTION_LEGAL_DEPOSIT, 'DC2026000123' );
		update_option( Journal_Identifier_Settings::OPTION_DOI_PREFIX, '10.12345' );

		$document = Llms_Txt::render();
		$this->assertStringContainsString( 'ISSN: 2443-5678', $document );
		$this->assertStringContainsString( 'Depósito Legal: DC2026000123', $document );
		$this->assertStringContainsString( 'DOI: 10.12345', $document );
	}
}
