<?php
/**
 * Journal-level digital identifiers (issue #58).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Journal_Identifier_Settings;

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/revistalogos-core/includes/metadata/class-journal-identifier-settings.php';

/**
 * Stored values stay inert text. Empty is empty — never a invented ISSN.
 *
 * @ticket 58
 */
class JournalIdentifierSettingsTest extends TestCase {

	/**
	 * Dado: un valor con espacios o markup.
	 * Entonces: queda texto plano recortado.
	 */
	public function test_sanitize_keeps_plain_trimmed_text() {
		$this->assertSame( '2443-5678', Journal_Identifier_Settings::sanitize( '  2443-5678  ' ) );
		$this->assertSame( '2443-5678', Journal_Identifier_Settings::sanitize( '<b>2443-5678</b>' ) );
		$this->assertSame( '10.12345', Journal_Identifier_Settings::sanitize( '10.12345' ) );
	}

	/**
	 * Dado: un valor vacío o no textual.
	 * Entonces: se guarda cadena vacía.
	 */
	public function test_sanitize_rejects_empty_and_non_scalar_values() {
		$this->assertSame( '', Journal_Identifier_Settings::sanitize( '' ) );
		$this->assertSame( '', Journal_Identifier_Settings::sanitize( '   ' ) );
		$this->assertSame( '', Journal_Identifier_Settings::sanitize( array( '2443-5678' ) ) );
		$this->assertSame( '', Journal_Identifier_Settings::sanitize( null ) );
	}
}
