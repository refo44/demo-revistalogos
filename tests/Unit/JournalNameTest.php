<?php
/**
 * The journal's display name in shipped code (ADR 0023).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * Readers and citation tools must see LOGOS ET SPES. The technical slug
 * `logo-et-spes` (domain, URLs) stays and does not match these patterns.
 */
class JournalNameTest extends TestCase {

	/**
	 * Dado: el código que se despliega (theme y plugin).
	 * Entonces: ningún archivo conserva el nombre anterior. El payload de
	 * migración queda fuera: se genera desde content-source (ADR 0023).
	 */
	public function test_shipped_code_does_not_carry_the_previous_name() {
		$root  = dirname( __DIR__, 2 ) . '/wordpress/wp-content/';
		$stale = array();

		foreach ( array( 'themes/revistalogos', 'plugins/revistalogos-core' ) as $component ) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $root . $component, FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $files as $file ) {
				$path = $file->getPathname();

				if ( ! preg_match( '/\.(php|css|js|json|txt|html)$/', $path ) ) {
					continue;
				}

				if ( preg_match( '#/(vendor|node_modules)/#', $path ) || str_ends_with( $path, 'resources/content-payload.json' ) ) {
					continue;
				}

				if ( preg_match( '/\blogo[ _]et[ _]spes\b/i', (string) file_get_contents( $path ) ) ) {
					$stale[] = substr( $path, strlen( $root ) );
				}
			}
		}

		$this->assertSame( array(), $stale );
	}

	/**
	 * Dado: los metadatos que leen los indexadores académicos.
	 * Entonces: Highwire y BibTeX nombran la revista con la S.
	 */
	public function test_citation_metadata_uses_the_current_name() {
		$theme = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc/';

		$this->assertStringContainsString(
			"'citation_journal_title' => 'Revista de Filosofía LOGOS ET SPES'",
			(string) file_get_contents( $theme . 'metadata-output.php' )
		);
		$this->assertStringContainsString(
			'journal = {LOGOS ET SPES}',
			(string) file_get_contents( $theme . 'citations.php' )
		);
	}
}
