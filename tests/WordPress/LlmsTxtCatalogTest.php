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
		$this->assertStringNotContainsString( '## Información institucional', $document );
	}

	/**
	 * Dado: páginas institucionales publicadas y un borrador.
	 * Entonces: /llms.txt enlaza solo las publicadas del mapa estable.
	 */
	public function test_render_lists_published_institutional_pages_and_skips_drafts() {
		$this->make_page( 'normas', 'Normas de Publicación', 'publish' );
		$this->make_page( 'etica', 'Ética', 'publish' );
		$this->make_page( 'politicas', 'Políticas', 'publish' );
		$this->make_page( 'comite-editorial', 'Comité Editorial', 'publish' );
		$this->make_page( 'acerca', 'Acerca', 'draft' );
		$this->make_page( 'buscar', 'Búsqueda', 'publish' );
		$this->make_page( 'pagina-ajena', 'Página ajena', 'publish' );

		$document = Llms_Txt::render();

		$this->assertStringContainsString( '## Información institucional', $document );
		$this->assertStringContainsString( 'Normas de Publicación', $document );
		$this->assertStringContainsString( home_url( '/normas/' ), $document );
		$this->assertStringContainsString( 'Ética', $document );
		$this->assertStringContainsString( 'Políticas', $document );
		$this->assertStringContainsString( 'Comité Editorial', $document );
		$this->assertStringNotContainsString( 'Acerca', $document );
		$this->assertStringNotContainsString( 'Búsqueda', $document );
		$this->assertStringNotContainsString( 'Página ajena', $document );
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
	 * Dado: un número publicado y permalinks sin la regla de /llms.txt.
	 * Cuando: un administrador actualiza /llms.txt.
	 * Entonces: el documento refleja ese número y la ruta queda registrada.
	 */
	public function test_administrator_refresh_registers_pretty_url_and_returns_current_catalog() {
		$issue_id  = $this->make_issue( 'Número para actualizar llms', '2026-09-10', 'publish' );
		$author_id = $this->make_published_author( 'Autora para actualizar llms' );
		$this->make_article( 'Artículo vivo para llms', $issue_id, 'publish', $author_id );

		delete_option( 'rewrite_rules' );

		$nonce    = wp_create_nonce( Llms_Txt::REFRESH_NONCE );
		$document = Llms_Txt::refresh_if_authorized( $nonce );

		$this->assertIsString( $document );
		$this->assertStringContainsString( 'Número para actualizar llms', $document );
		$this->assertStringContainsString( 'Artículo vivo para llms', $document );

		$rules = get_option( 'rewrite_rules' );
		$this->assertIsArray( $rules );
		$this->assertArrayHasKey( '^llms\.txt$', $rules );
	}

	/**
	 * Dado: un usuario sin manage_options.
	 * Entonces: no puede actualizar /llms.txt.
	 */
	public function test_refresh_is_denied_without_manage_options() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$result = Llms_Txt::refresh_if_authorized( wp_create_nonce( Llms_Txt::REFRESH_NONCE ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'forbidden', $result->get_error_code() );
	}

	/**
	 * Dado: un administrador con nonce inválido.
	 * Entonces: no actualiza /llms.txt.
	 */
	public function test_refresh_is_denied_with_invalid_nonce() {
		$result = Llms_Txt::refresh_if_authorized( 'nonce-invalido' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_nonce', $result->get_error_code() );
	}

	/**
	 * Dado: Ajustes → LOGO ET SPES — llms.txt.
	 * Entonces: la pantalla nombra el plugin, el archivo técnico y
	 * ofrece Actualizar llms.txt con nonce.
	 */
	public function test_settings_page_offers_manual_llms_refresh() {
		ob_start();
		Llms_Txt::render_settings_page();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'LOGO ET SPES', $html );
		$this->assertStringContainsString( '<code>llms.txt</code>', $html );
		$this->assertStringContainsString( '/llms.txt', $html );
		$this->assertStringContainsString( 'Actualizar llms.txt', $html );
		$this->assertStringContainsString( 'name="_wpnonce"', $html );
		$this->assertStringNotContainsString( 'Mapa para IA', $html );
		$this->assertStringNotContainsString( 'Índice público', $html );
	}

	/**
	 * Dado: el menú Ajustes.
	 * Entonces: llms.txt cuelga del mismo prefijo que LOGO ET SPES.
	 */
	public function test_settings_menu_has_a_separate_llms_txt_entry() {
		Llms_Txt::register_page();
		\Revistalogos_Core\Article_Pdf_Publication_Settings::register_page();

		global $submenu;
		$this->assertArrayHasKey( 'options-general.php', $submenu );

		$slugs  = array();
		$labels = array();
		foreach ( $submenu['options-general.php'] as $item ) {
			$slugs[]            = $item[2];
			$labels[ $item[2] ] = wp_strip_all_tags( $item[0] );
		}

		$this->assertContains( Llms_Txt::PAGE_SLUG, $slugs );
		$this->assertContains( \Revistalogos_Core\Article_Pdf_Publication_Settings::PAGE_SLUG, $slugs );
		$this->assertNotSame( Llms_Txt::PAGE_SLUG, \Revistalogos_Core\Article_Pdf_Publication_Settings::PAGE_SLUG );
		$this->assertSame( 'LOGO ET SPES — llms.txt', $labels[ Llms_Txt::PAGE_SLUG ] );
		$this->assertSame( 'LOGO ET SPES', $labels[ \Revistalogos_Core\Article_Pdf_Publication_Settings::PAGE_SLUG ] );
	}

	/**
	 * Dado: Ajustes → LOGO ET SPES (PDF).
	 * Entonces: no incrusta el panel de llms.txt.
	 */
	public function test_pdf_settings_page_does_not_embed_llms_panel() {
		ob_start();
		\Revistalogos_Core\Article_Pdf_Publication_Settings::render_page();
		$html = ob_get_clean();

		$this->assertStringNotContainsString( 'Actualizar llms.txt', $html );
	}

	/**
	 * Dado: la fila del plugin en Plugins.
	 * Entonces: el enlace nombra la pantalla, no finge que actualiza.
	 */
	public function test_plugin_row_links_to_the_llms_txt_screen() {
		$links = Llms_Txt::plugin_action_links( array() );

		$this->assertStringContainsString( '>llms.txt<', implode( ' ', $links ) );
		$this->assertStringContainsString( 'page=' . Llms_Txt::PAGE_SLUG, implode( ' ', $links ) );
		$this->assertStringNotContainsString( 'Actualizar llms.txt', implode( ' ', $links ) );
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

	/**
	 * @param string $slug   Page slug.
	 * @param string $title  Page title.
	 * @param string $status Post status.
	 * @return int
	 */
	private function make_page( $slug, $title, $status ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_title'  => $title,
				'post_status' => $status,
			)
		);
	}
}
