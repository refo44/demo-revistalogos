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
	 * Dado: la hoja de copy del pie.
	 * Entonces: ya no anuncia Creative Commons.
	 */
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
