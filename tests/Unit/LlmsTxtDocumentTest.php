<?php
/**
 * Markdown shape of /llms.txt (issue #47). No WordPress boot.
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Llms_Txt;

/**
 * The file is a stable map plus an optional current-issue block.
 * It must not invent identifiers or dump the whole catalog.
 */
class LlmsTxtDocumentTest extends TestCase {

	/**
	 * Dado: el catálogo público de la revista.
	 * Entonces: el documento nombra la revista, los archivos vivos y el sitemap.
	 */
	public function test_document_lists_stable_archives_and_sitemap() {
		$document = Llms_Txt::format_document( $this->empty_catalog() );

		$this->assertStringContainsString( '# Revista de Filosofía LOGO ET SPES', $document );
		$this->assertStringContainsString( 'https://example.org/revista/numeros/', $document );
		$this->assertStringContainsString( 'https://example.org/revista/articulos/', $document );
		$this->assertStringContainsString( 'https://example.org/revista/autores/', $document );
		$this->assertStringContainsString( 'https://example.org/wp-sitemap.xml', $document );
		$this->assertStringNotContainsString( 'Número actual', $document );
		$this->assertStringNotContainsString( 'Información institucional', $document );
	}

	/**
	 * Dado: páginas institucionales publicadas.
	 * Entonces: el documento las enlaza por título y URL, sin inventar otras.
	 */
	public function test_document_lists_institutional_pages() {
		$catalog                      = $this->empty_catalog();
		$catalog['institutional_pages'] = array(
			array(
				'title' => 'Normas de Publicación',
				'url'   => 'https://example.org/normas/',
			),
			array(
				'title' => 'Ética',
				'url'   => 'https://example.org/etica/',
			),
			array(
				'title' => 'Políticas',
				'url'   => 'https://example.org/politicas/',
			),
			array(
				'title' => 'Comité Editorial',
				'url'   => 'https://example.org/comite-editorial/',
			),
		);

		$document = Llms_Txt::format_document( $catalog );

		$this->assertStringContainsString( '## Información institucional', $document );
		$this->assertStringContainsString( '[Normas de Publicación](https://example.org/normas/)', $document );
		$this->assertStringContainsString( '[Ética](https://example.org/etica/)', $document );
		$this->assertStringContainsString( '[Políticas](https://example.org/politicas/)', $document );
		$this->assertStringContainsString( '[Comité Editorial](https://example.org/comite-editorial/)', $document );
		$this->assertStringNotContainsString( 'Búsqueda', $document );
		$this->assertStringNotContainsString( 'lineamientos', $document );
	}

	/**
	 * Dado: un número publicado con artículos.
	 * Entonces: el documento enlaza ese número y esos artículos.
	 */
	public function test_document_lists_current_issue_and_its_articles() {
		$catalog = $this->empty_catalog();
		$catalog['current_issue'] = array(
			'title'    => 'Filosofía Contemporánea: Nuevas Perspectivas',
			'url'      => 'https://example.org/revista/numeros/vol-1-n-1/',
			'articles' => array(
				array(
					'title' => 'El perro y la dignidad compartida',
					'url'   => 'https://example.org/revista/articulos/el-perro/',
				),
			),
		);

		$document = Llms_Txt::format_document( $catalog );

		$this->assertStringContainsString( '## Número actual', $document );
		$this->assertStringContainsString( '[Filosofía Contemporánea: Nuevas Perspectivas](https://example.org/revista/numeros/vol-1-n-1/)', $document );
		$this->assertStringContainsString( '[El perro y la dignidad compartida](https://example.org/revista/articulos/el-perro/)', $document );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function empty_catalog() {
		return array(
			'name'          => 'Revista de Filosofía LOGO ET SPES',
			'description'   => 'Publicación digital venezolana de acceso abierto.',
			'home_url'      => 'https://example.org/',
			'issues_url'    => 'https://example.org/revista/numeros/',
			'articles_url'  => 'https://example.org/revista/articulos/',
			'authors_url'   => 'https://example.org/revista/autores/',
			'sitemap_url'          => 'https://example.org/wp-sitemap.xml',
			'current_issue'        => null,
			'institutional_pages'  => array(),
		);
	}
}
