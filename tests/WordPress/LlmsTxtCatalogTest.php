<?php
/**
 * /llms.txt is generated from published journal objects (issue #47).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Content_Types;
use Revistalogos_Core\Llms_Txt;
use Revistalogos_Core\Roles;
use Revistalogos_Core\Taxonomies;

/**
 * Production editorial rows stay in production. These cases invent
 * published objects in the isolated suite so the file stays dynamic.
 */
class LlmsTxtCatalogTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Content_Types::register();
		Taxonomies::register();
		Roles::install();
		$this->set_permalink_structure( '/%postname%/' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Dado: no hay números publicados.
	 * Entonces: /llms.txt sigue siendo un mapa válido sin inventar un número.
	 */
	public function test_render_without_published_issue_has_no_current_issue_heading() {
		$document = Llms_Txt::render();

		$this->assertStringContainsString( '# Revista de Filosofía LOGO ET SPES', $document );
		$this->assertStringContainsString( 'Stanislao Strba', $document );
		$this->assertStringContainsString( 'doble anónimo', $document );
		$this->assertStringContainsString( '/wp-sitemap.xml', $document );
		$this->assertStringNotContainsString( '## Número actual', $document );
	}

	/**
	 * Dado: un número publicado con artículos, y un borrador.
	 * Entonces: solo el número vigente y sus artículos publicados aparecen.
	 */
	public function test_render_lists_current_published_issue_and_skips_drafts() {
		$current_id = $this->make_issue( 'Volumen vigente de prueba', '2026-09-01', 'publish' );
		$this->make_issue( 'Borrador futuro', '2026-12-01', 'draft' );

		$author_id     = $this->make_published_author( 'Autora de prueba' );
		$live_article  = $this->make_article( 'Artículo publicado del número', $current_id, 'publish', $author_id );
		$draft_article = $this->make_article( 'Artículo en borrador', $current_id, 'draft' );

		$this->assertSame( 'article', get_post_type( $live_article ) );
		$this->assertSame( 'publish', get_post_status( $live_article ) );
		$this->assertSame( (string) $current_id, (string) get_post_meta( $live_article, 'issue', true ) );

		$document = Llms_Txt::render();

		$this->assertStringContainsString( '## Número actual', $document );
		$this->assertStringContainsString( 'Volumen vigente de prueba', $document );
		$this->assertStringContainsString( get_permalink( $current_id ), $document );
		$this->assertStringContainsString( 'Artículo publicado del número', $document );
		$this->assertStringContainsString( get_permalink( $live_article ), $document );
		$this->assertStringNotContainsString( 'Borrador futuro', $document );
		$this->assertStringNotContainsString( 'Artículo en borrador', $document );
		$this->assertStringNotContainsString( get_permalink( $draft_article ), $document );
	}

	/**
	 * @param string $title  Issue title.
	 * @param string $date   date_published Y-m-d.
	 * @param string $status Post status.
	 * @return int
	 */
	private function make_issue( $title, $date, $status ) {
		$issue_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_title'  => $title,
				'post_status' => $status,
			)
		);
		update_post_meta( $issue_id, 'date_published', $date );

		return $issue_id;
	}

	/**
	 * @param string $name Author display name.
	 * @return int
	 */
	private function make_published_author( $name ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => $name,
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Factory publish of an article is demoted to draft unless a
	 * published Author CPT is already assigned (plugin 0.2.5).
	 *
	 * @param string   $title     Article title.
	 * @param int      $issue_id  Issue post ID.
	 * @param string   $status    Desired post status.
	 * @param int|null $author_id Published Author CPT ID when publishing.
	 * @return int
	 */
	private function make_article( $title, $issue_id, $status, $author_id = null ) {
		$article_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'article',
				'post_title'  => $title,
				'post_status' => 'draft',
			)
		);
		update_post_meta( $article_id, 'issue', $issue_id );

		if ( 'publish' === $status ) {
			update_post_meta( $article_id, 'authors', array( absint( $author_id ) ) );
			wp_update_post(
				array(
					'ID'          => $article_id,
					'post_status' => 'publish',
				)
			);
		}

		return $article_id;
	}
}
