<?php
/**
 * Editorial offprint template for the generated article PDF
 * (issue #10, BACKLOG item 3; approved mockup: opción 1
 * «Clásico filológico», grayscale).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Article_Pdf_Editorial_Template;

/**
 * Protects the visual contract of the article offprint source HTML:
 * bibliographic masthead, centered title block, abstracts, keywords,
 * Spanish editorial dates, and omission of every empty field.
 */
class ArticlePdfEditorialTemplateTest extends TestCase {

	private function make_full_editorial_fields() {
		return array(
			'journal_name'     => 'Revista de Filosofía LOGO ET SPES',
			'volume'           => 3,
			'number'           => 2,
			'year'             => 2027,
			'issn'             => 'ISSN-DE-PRUEBA',
			'pages'            => '15-34',
			'section'          => 'Artículos',
			'title'            => 'La esperanza como categoría ontológica',
			'title_en'         => 'Hope as an Ontological Category',
			'authors'          => array(
				array(
					'name'        => 'María QA Contreras',
					'affiliation' => 'Universidad de Prueba, Caracas',
					'orcid'       => 'ORCID-DE-PRUEBA',
				),
				array(
					'name'        => 'José QA Peña',
					'affiliation' => '',
					'orcid'       => '',
				),
			),
			'doi'              => 'DOI-DE-PRUEBA',
			'received_date'    => '2026-03-12',
			'accepted_date'    => '2026-06-05',
			'publication_date' => '2026-08-19',
			'abstract'         => 'Resumen de prueba en español.',
			'abstract_en'      => 'Test abstract in English.',
			'keywords'         => array( 'esperanza', 'utopía concreta' ),
			'body_html'        => '<p>Cuerpo filosófico de prueba: ñ á é í ó ú.</p>',
		);
	}

	public function test_full_editorial_fields_render_the_offprint_first_page() {
		$editorial_template = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render( $this->make_full_editorial_fields() );

		$this->assertStringContainsString( 'Revista de Filosofía LOGO ET SPES', $offprint_html );
		$this->assertStringContainsString( 'Vol. 3, N.º 2 (2027), pp. 15-34', $offprint_html );
		$this->assertStringContainsString( 'ISSN ISSN-DE-PRUEBA', $offprint_html );
		$this->assertStringContainsString( 'Sección: Artículos', $offprint_html );
		$this->assertStringContainsString( 'La esperanza como categoría ontológica', $offprint_html );
		$this->assertStringContainsString( 'Hope as an Ontological Category', $offprint_html );
		$this->assertStringContainsString( 'María QA Contreras', $offprint_html );
		$this->assertStringContainsString( 'Universidad de Prueba, Caracas · ORCID ORCID-DE-PRUEBA', $offprint_html );
		$this->assertStringContainsString( 'José QA Peña', $offprint_html );
		$this->assertStringContainsString( 'DOI DOI-DE-PRUEBA', $offprint_html );
		$this->assertStringContainsString( 'Resumen:', $offprint_html );
		$this->assertStringContainsString( 'Resumen de prueba en español.', $offprint_html );
		$this->assertStringContainsString( 'Abstract:', $offprint_html );
		$this->assertStringContainsString( 'Test abstract in English.', $offprint_html );
		$this->assertStringContainsString( 'Palabras clave:', $offprint_html );
		$this->assertStringContainsString( 'esperanza; utopía concreta.', $offprint_html );
		$this->assertStringContainsString( '<p>Cuerpo filosófico de prueba: ñ á é í ó ú.</p>', $offprint_html );
	}

	public function test_minimal_fields_render_without_orphan_labels() {
		$editorial_template = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render(
			array(
				'title'     => 'Artículo mínimo',
				'authors'   => array( array( 'name' => 'Autora Única' ) ),
				'body_html' => '<p>Solo cuerpo.</p>',
			)
		);

		$this->assertStringContainsString( 'Artículo mínimo', $offprint_html );
		$this->assertStringContainsString( 'Autora Única', $offprint_html );
		$this->assertStringContainsString( '<p>Solo cuerpo.</p>', $offprint_html );
		$this->assertStringNotContainsString( 'Vol.', $offprint_html );
		$this->assertStringNotContainsString( 'N.º', $offprint_html );
		$this->assertStringNotContainsString( 'ISSN', $offprint_html );
		$this->assertStringNotContainsString( 'Sección:', $offprint_html );
		$this->assertStringNotContainsString( 'pp.', $offprint_html );
		$this->assertStringNotContainsString( 'DOI', $offprint_html );
		$this->assertStringNotContainsString( 'ORCID', $offprint_html );
		$this->assertStringNotContainsString( 'Resumen:', $offprint_html );
		$this->assertStringNotContainsString( 'Abstract:', $offprint_html );
		$this->assertStringNotContainsString( 'Palabras clave:', $offprint_html );
		$this->assertStringNotContainsString( 'Recibido:', $offprint_html );
		$this->assertStringNotContainsString( 'Aceptado:', $offprint_html );
		$this->assertStringNotContainsString( 'Publicado:', $offprint_html );
	}

	public function test_document_declares_print_frame_and_grayscale_typography() {
		$editorial_template = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render( $this->make_full_editorial_fields() );

		$this->assertStringContainsString( '<!DOCTYPE html>', $offprint_html );
		$this->assertStringContainsString( 'lang="es"', $offprint_html );
		$this->assertStringContainsString( 'UTF-8', $offprint_html );
		$this->assertStringContainsString( 'DejaVu Serif', $offprint_html );
		$this->assertStringContainsString( '@page', $offprint_html );
		$this->assertStringContainsString( 'counter(page)', $offprint_html );
		$this->assertStringNotContainsString( '#18597c', $offprint_html );
		$this->assertStringNotContainsString( '#ffbf00', $offprint_html );
	}

	public function test_editorial_dates_render_in_spanish() {
		$editorial_template = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render( $this->make_full_editorial_fields() );

		$this->assertStringContainsString( 'Recibido: 12 de marzo de 2026', $offprint_html );
		$this->assertStringContainsString( 'Aceptado: 5 de junio de 2026', $offprint_html );
		$this->assertStringContainsString( 'Publicado: 19 de agosto de 2026', $offprint_html );
	}

	public function test_invalid_editorial_date_is_omitted() {
		$fields                  = $this->make_full_editorial_fields();
		$fields['received_date'] = 'no-es-fecha';
		$fields['accepted_date'] = '';
		$editorial_template      = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render( $fields );

		$this->assertStringNotContainsString( 'Recibido:', $offprint_html );
		$this->assertStringNotContainsString( 'Aceptado:', $offprint_html );
		$this->assertStringContainsString( 'Publicado: 19 de agosto de 2026', $offprint_html );
	}

	public function test_partial_issue_context_omits_missing_segments() {
		$fields            = $this->make_full_editorial_fields();
		$fields['volume']  = 0;
		$fields['number']  = 0;
		$fields['issn']    = '';
		$fields['section'] = '';
		$editorial_template = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render( $fields );

		$this->assertStringContainsString( '2027, pp. 15-34', $offprint_html );
		$this->assertStringNotContainsString( 'Vol.', $offprint_html );
		$this->assertStringNotContainsString( 'N.º', $offprint_html );
		$this->assertStringNotContainsString( 'ISSN', $offprint_html );
		$this->assertStringNotContainsString( 'Sección:', $offprint_html );
	}

	public function test_special_characters_are_escaped_outside_the_body() {
		$fields              = $this->make_full_editorial_fields();
		$fields['title']     = 'Cuerpo & <alma>';
		$fields['authors']   = array( array( 'name' => 'Autora & <Prueba>' ) );
		$editorial_template  = new Article_Pdf_Editorial_Template();

		$offprint_html = $editorial_template->render( $fields );

		$this->assertStringContainsString( 'Cuerpo &amp; &lt;alma&gt;', $offprint_html );
		$this->assertStringContainsString( 'Autora &amp; &lt;Prueba&gt;', $offprint_html );
		$this->assertStringNotContainsString( '<alma>', $offprint_html );
	}
}
