<?php
/**
 * Public content license notice (all rights reserved).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * The live theme must reserve editorial content. CC BY 4.0 is withdrawn
 * from public notices. Software stays MIT.
 */
class SiteContentLicenseTest extends TestCase {

	/**
	 * Surfaces a reader sees without opening wp-admin.
	 *
	 * @return array<string, string>
	 */
	private function public_license_files() {
		$theme = 'wordpress/wp-content/themes/revistalogos/';

		return array(
			'footer'   => $theme . 'template-parts/footer.php',
			'acerca'   => $theme . 'page-acerca.php',
			'etica'    => $theme . 'page-etica.php',
			'privacidad' => $theme . 'privacy-policy.php',
		);
	}

	/**
	 * Dado: el pie y las fichas institucionales.
	 * Cuando: se lee la licencia del contenido.
	 * Entonces: todos los derechos reservados; sin CC BY.
	 */
	public function test_public_theme_notices_reserve_editorial_content() {
		foreach ( $this->public_license_files() as $surface => $relative ) {
			$html = $this->repo_file_contents( $relative );

			$this->assertStringContainsString(
				'todos los derechos reservados',
				mb_strtolower( $html ),
				$surface . ' must declare all rights reserved'
			);
			$this->assertStringNotContainsString(
				'creativecommons.org/licenses/by',
				$html,
				$surface . ' must not offer a CC BY deed'
			);
			$this->assertDoesNotMatchRegularExpression(
				'/\bCC BY\b/i',
				$html,
				$surface . ' must not name CC BY'
			);
			$this->assertStringNotContainsString(
				'Creative Commons Atribución',
				$html,
				$surface . ' must not name Creative Commons Attribution'
			);
		}
	}

	/**
	 * Dado: el pie del sitio.
	 * Entonces: el copyright es el año en curso, no 2025.
	 */
	public function test_footer_copyright_year_is_current() {
		$footer     = $this->repo_file_contents(
			'wordpress/wp-content/themes/revistalogos/template-parts/footer.php'
		);
		$copy_sheet = $this->repo_file_contents( 'docs/09-ui-copy-sheet.md' );

		$this->assertStringContainsString( '&copy; 2026 CENFISS.', $footer );
		$this->assertStringNotContainsString( '&copy; 2025 CENFISS.', $footer );
		$this->assertStringContainsString( '© 2026 CENFISS.', $copy_sheet );
		$this->assertStringNotContainsString( '© 2025 CENFISS.', $copy_sheet );
	}

	/**
	 * Dado: el pie del sitio.
	 * Entonces: el código sigue bajo MIT.
	 */
	public function test_footer_keeps_software_under_mit() {
		$footer = $this->repo_file_contents(
			'wordpress/wp-content/themes/revistalogos/template-parts/footer.php'
		);

		$this->assertStringContainsString( 'Código del sitio bajo', $footer );
		$this->assertStringContainsString( 'MIT', $footer );
		$this->assertStringContainsString(
			'https://github.com/refo44/demo-revistalogos/blob/main/LICENSE',
			$footer
		);
	}

	/**
	 * Dado: Políticas y Contacto (maqueta y payload de migración).
	 * Entonces: no ofrecen el deed CC BY.
	 */
	public function test_politicas_and_contacto_do_not_offer_cc_by() {
		$surfaces = array(
			'static-contacto'  => 'static/page-contacto.html',
			'static-politicas' => 'static/page-politicas.html',
			'payload'          => 'wordpress/wp-content/plugins/revistalogos-core/resources/content-payload.json',
		);

		foreach ( $surfaces as $surface => $relative ) {
			$html = $this->repo_file_contents( $relative );

			if ( 'payload' === $surface ) {
				$html = $this->payload_entry_html( $html, array( 'contacto', 'politicas' ) );
			}

			$this->assertStringContainsString(
				'todos los derechos reservados',
				mb_strtolower( $html ),
				$surface . ' must reserve editorial content'
			);
			$this->assertStringNotContainsString(
				'creativecommons.org/licenses/by',
				$html,
				$surface . ' must not offer a CC BY deed'
			);
			$this->assertStringNotContainsString(
				'Creative Commons Atribución',
				$html,
				$surface . ' must not name Creative Commons Attribution'
			);
			$this->assertDoesNotMatchRegularExpression(
				'/\bCC BY\b/i',
				$html,
				$surface . ' must not name CC BY'
			);
		}
	}

	/**
	 * @param string   $payload_json Decoded later from file contents.
	 * @param string[] $slugs        Institutional slugs to concatenate.
	 */
	private function payload_entry_html( $payload_json, array $slugs ) {
		$data = json_decode( $payload_json, true );
		$this->assertIsArray( $data );
		$this->assertIsArray( $data['entries'] ?? null );

		$chunks = array();
		foreach ( $data['entries'] as $entry ) {
			if ( in_array( $entry['slug'] ?? '', $slugs, true ) ) {
				$chunks[] = (string) ( $entry['content_html'] ?? '' );
			}
		}

		$this->assertNotSame( array(), $chunks, 'expected contacto and politicas payload entries' );

		return implode( "\n", $chunks );
	}

	public function test_copy_sheet_footer_matches_reserved_rights() {
		$copy_sheet = $this->repo_file_contents( 'docs/09-ui-copy-sheet.md' );

		$this->assertStringContainsString( 'Todos los derechos reservados', $copy_sheet );
		$this->assertStringNotContainsString(
			'Licencia Creative Commons Atribución 4.0 Internacional',
			$copy_sheet
		);
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
