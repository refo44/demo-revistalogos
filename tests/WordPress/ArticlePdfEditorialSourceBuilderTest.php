<?php
/**
 * Article offprint source builder against real WordPress state
 * (issue #10, BACKLOG item 3; ADR 0017 §5, deferred WU6A fields).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Article_Pdf_WordPress_Source_Builder;

/**
 * Protects how the builder gathers editorial context from the Article,
 * its Issue, its Authors and its taxonomies into the offprint source,
 * and how absent data is omitted without blocking generation.
 */
class ArticlePdfEditorialSourceBuilderTest extends WP_UnitTestCase {

	private function make_published_author( $name, $affiliation = '', $orcid = '' ) {
		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => $name,
				'post_status' => 'publish',
			)
		);
		if ( '' !== $affiliation ) {
			update_post_meta( $author_id, 'afiliacion', $affiliation );
		}
		if ( '' !== $orcid ) {
			update_post_meta( $author_id, 'orcid', $orcid );
		}

		return $author_id;
	}

	private function make_published_issue() {
		$issue_id = self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_title'  => 'Vol. 3 N.º 2',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $issue_id, 'volume_number', 3 );
		update_post_meta( $issue_id, 'issue_number', 2 );
		update_post_meta( $issue_id, 'year', 2027 );
		update_post_meta( $issue_id, 'issn', 'ISSN-DE-PRUEBA' );

		return $issue_id;
	}

	public function test_plugin_registers_the_editorial_content_model() {
		$this->assertTrue( post_type_exists( 'article' ) );
		$this->assertTrue( post_type_exists( 'issue' ) );
		$this->assertTrue( post_type_exists( 'author' ) );
		$this->assertTrue( taxonomy_exists( 'section' ) );
		$this->assertTrue( taxonomy_exists( 'keyword' ) );
	}

	public function test_offprint_source_includes_issue_and_article_editorial_context() {
		$author_with_ids = $this->make_published_author(
			'María QA Contreras',
			'Universidad de Prueba, Caracas',
			'ORCID-DE-PRUEBA'
		);
		$author_plain    = $this->make_published_author( 'José QA Peña' );
		$issue_id        = $this->make_published_issue();

		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'La esperanza como categoría ontológica',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:paragraph --><p>Cuerpo filosófico de prueba.</p><!-- /wp:paragraph -->',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_with_ids, $author_plain ) );
		update_post_meta( $article_id, 'issue', $issue_id );
		update_post_meta( $article_id, 'title_en', 'Hope as an Ontological Category' );
		update_post_meta( $article_id, 'abstract', 'Resumen de prueba en español.' );
		update_post_meta( $article_id, 'abstract_en', 'Test abstract in English.' );
		update_post_meta( $article_id, 'doi', 'DOI-DE-PRUEBA' );
		update_post_meta( $article_id, 'pages', '15-34' );
		update_post_meta( $article_id, 'received_date', '2026-03-12' );
		update_post_meta( $article_id, 'accepted_date', '2026-06-05' );
		update_post_meta( $article_id, 'publication_date', '2026-08-19' );
		wp_set_object_terms( $article_id, array( 'Artículos' ), 'section' );
		wp_set_object_terms( $article_id, array( 'esperanza', 'utopía concreta' ), 'keyword' );

		$source_builder = new Article_Pdf_WordPress_Source_Builder();
		$offprint_html  = $source_builder->build( $article_id );

		$this->assertIsString( $offprint_html );
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
		$this->assertStringContainsString( 'Resumen de prueba en español.', $offprint_html );
		$this->assertStringContainsString( 'Test abstract in English.', $offprint_html );
		$this->assertStringContainsString( 'esperanza; utopía concreta.', $offprint_html );
		$this->assertStringContainsString( 'Recibido: 12 de marzo de 2026', $offprint_html );
		$this->assertStringContainsString( 'Aceptado: 5 de junio de 2026', $offprint_html );
		$this->assertStringContainsString( 'Publicado: 19 de agosto de 2026', $offprint_html );
		$this->assertStringContainsString( 'Cuerpo filosófico de prueba.', $offprint_html );
	}

	public function test_minimal_article_still_generates_without_orphan_labels() {
		$author_id  = $this->make_published_author( 'Autora Única' );
		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'Artículo mínimo',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:paragraph --><p>Solo cuerpo.</p><!-- /wp:paragraph -->',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );

		$source_builder = new Article_Pdf_WordPress_Source_Builder();
		$offprint_html  = $source_builder->build( $article_id );

		$this->assertIsString( $offprint_html );
		$this->assertStringContainsString( 'Artículo mínimo', $offprint_html );
		$this->assertStringContainsString( 'Autora Única', $offprint_html );
		$this->assertStringContainsString( 'Solo cuerpo.', $offprint_html );
		$this->assertStringNotContainsString( 'Vol.', $offprint_html );
		$this->assertStringNotContainsString( 'ISSN', $offprint_html );
		$this->assertStringNotContainsString( 'Sección:', $offprint_html );
		$this->assertStringNotContainsString( 'DOI', $offprint_html );
		$this->assertStringNotContainsString( 'ORCID', $offprint_html );
		$this->assertStringNotContainsString( 'Resumen:', $offprint_html );
		$this->assertStringNotContainsString( 'Abstract:', $offprint_html );
		$this->assertStringNotContainsString( 'Palabras clave:', $offprint_html );
		$this->assertStringNotContainsString( 'Recibido:', $offprint_html );
	}

	public function test_unpublished_issue_is_not_cited_in_the_offprint() {
		$author_id = $this->make_published_author( 'Autora Única' );
		$issue_id  = self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_title'  => 'Número en preparación',
				'post_status' => 'draft',
			)
		);
		update_post_meta( $issue_id, 'volume_number', 9 );
		update_post_meta( $issue_id, 'year', 2030 );

		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'Artículo con número no publicado',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:paragraph --><p>Cuerpo.</p><!-- /wp:paragraph -->',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );
		update_post_meta( $article_id, 'issue', $issue_id );

		$source_builder = new Article_Pdf_WordPress_Source_Builder();
		$offprint_html  = $source_builder->build( $article_id );

		$this->assertIsString( $offprint_html );
		$this->assertStringNotContainsString( 'Vol.', $offprint_html );
		$this->assertStringNotContainsString( '2030', $offprint_html );
	}

	public function test_publication_candidate_keeps_stored_editorial_context() {
		$author_id  = $this->make_published_author( 'Autora Única' );
		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'Título anterior',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:paragraph --><p>Contenido anterior.</p><!-- /wp:paragraph -->',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );
		update_post_meta( $article_id, 'abstract', 'Resumen almacenado.' );
		update_post_meta( $article_id, 'doi', 'DOI-DE-PRUEBA' );

		$source_builder = new Article_Pdf_WordPress_Source_Builder();
		$offprint_html  = $source_builder->build_for_publication(
			$article_id,
			'Título nuevo',
			'<!-- wp:paragraph --><p>Contenido nuevo.</p><!-- /wp:paragraph -->'
		);

		$this->assertIsString( $offprint_html );
		$this->assertStringContainsString( 'Título nuevo', $offprint_html );
		$this->assertStringContainsString( 'Contenido nuevo.', $offprint_html );
		$this->assertStringNotContainsString( 'Contenido anterior.', $offprint_html );
		$this->assertStringContainsString( 'Resumen almacenado.', $offprint_html );
		$this->assertStringContainsString( 'DOI DOI-DE-PRUEBA', $offprint_html );
	}

	public function test_offprint_body_is_sanitized_local_html() {
		$author_id  = $this->make_published_author( 'Autora Única' );
		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'Artículo con marcado peligroso',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:paragraph --><p>Texto legítimo.</p><!-- /wp:paragraph --><script>alert(1)</script>',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );

		$source_builder = new Article_Pdf_WordPress_Source_Builder();
		$offprint_html  = $source_builder->build( $article_id );

		$this->assertIsString( $offprint_html );
		$this->assertStringContainsString( 'Texto legítimo.', $offprint_html );
		$this->assertStringNotContainsString( '<!-- wp:', $offprint_html );
		$this->assertStringNotContainsString( '<script', $offprint_html );
	}
}
