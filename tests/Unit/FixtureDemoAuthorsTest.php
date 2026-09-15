<?php
/**
 * Demo fixture author catalog used for local Cómo Citar QA.
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Fixtures;

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/revistalogos-core/includes/fixtures/class-fixtures.php';

/**
 * Protects ADR 0021 local QA: demo authors are natural names, not a
 * numbered Spanish placeholder, and mixed scripts/languages.
 */
class FixtureDemoAuthorsTest extends TestCase {

	/**
	 * Dado: el catálogo demo de autores.
	 * Entonces: no usa «Autora de Ejemplo N» y cubre más de un idioma.
	 */
	public function test_demo_authors_are_natural_names_in_more_than_one_language() {
		$authors = Fixtures::demo_authors();

		$this->assertGreaterThanOrEqual( 6, count( $authors ) );

		$titles = array();
		foreach ( $authors as $author ) {
			$this->assertArrayHasKey( 'title', $author );
			$this->assertArrayHasKey( 'citation_surname', $author );
			$this->assertStringNotContainsString( 'Autora de Ejemplo', $author['title'] );
			$titles[] = $author['title'];
		}

		$this->assertContains( 'Ana María Pérez Gómez', $titles );
		$this->assertContains( 'James Alan Whitfield', $titles );
		$this->assertContains( 'Wei Zhang', $titles );
		$this->assertContains( 'Fatima Al-Hassan', $titles );
	}

	/**
	 * Hispanic both-surnames and particles store the bibliographic surname;
	 * a single English surname stays empty (last-token).
	 */
	public function test_demo_authors_set_citation_surname_only_when_the_heuristic_would_fail() {
		$authors = Fixtures::demo_authors();

		$this->assertSame( 'Pérez Gómez', $authors['author-1']['citation_surname'] );
		$this->assertSame( 'de la Cruz', $authors['author-3']['citation_surname'] );
		$this->assertSame( '', $authors['author-2']['citation_surname'] );
		$this->assertSame( '', $authors['author-4']['citation_surname'] );
	}
}
