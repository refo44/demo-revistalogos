<?php
/**
 * Author given_names / family_names and optional citation_surname (ADR 0022 / #67).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Author_Name_Backfill;
use Revistalogos_Core\Content_Types;
use Revistalogos_Core\Meta_Boxes;
use Revistalogos_Core\Metadata;
use Revistalogos_Core\Roles;

if ( ! function_exists( 'revistalogos_core_active' ) ) {
	/**
	 * Theme helpers loaded without bootstrapping functions.php.
	 *
	 * @return bool
	 */
	function revistalogos_core_active() {
		return class_exists( '\Revistalogos_Core\Plugin' );
	}
}

/**
 * @ticket 67
 */
class AuthorGivenFamilyNamesTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Content_Types::register();
		Metadata::register();
		Roles::install();
		Meta_Boxes::register_hooks();
		Author_Name_Backfill::register_hooks();

		global $wp_rest_server;
		$wp_rest_server = null;

		$theme_inc = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc';
		require_once $theme_inc . '/template-tags.php';
		require_once $theme_inc . '/citations.php';
	}

	/**
	 * Dado: el esquema REST del CPT author.
	 * Entonces: incluye given_names y family_names.
	 */
	public function test_author_rest_schema_exposes_given_and_family_names() {
		$controller = new WP_REST_Posts_Controller( 'author' );
		$schema     = $controller->get_item_schema();
		$meta_props = isset( $schema['properties']['meta']['properties'] ) && is_array( $schema['properties']['meta']['properties'] )
			? array_keys( $schema['properties']['meta']['properties'] )
			: array();

		$this->assertContains( 'given_names', $meta_props );
		$this->assertContains( 'family_names', $meta_props );
		$this->assertContains( 'citation_surname', $meta_props );
	}

	/**
	 * Dado: una ficha Autores.
	 * Entonces: Nombres, Apellidos y apellido para citar están en el metabox.
	 */
	public function test_author_metabox_includes_identity_and_citation_fields() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Sofía Camila León Albino',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);

		ob_start();
		Meta_Boxes::render_fields_box( get_post( $author_id ) );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="given_names"', $html );
		$this->assertStringContainsString( 'name="family_names"', $html );
		$this->assertStringContainsString( 'name="citation_surname"', $html );
	}

	/**
	 * Dado: un autor nuevo por REST sin Nombres ni Apellidos.
	 * Entonces: 400.
	 */
	public function test_rest_create_without_identity_names_is_rejected() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$request = new WP_REST_Request( 'POST', '/wp/v2/author' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'title', 'Sofía Camila León Albino' );
		$request->set_param( 'status', 'publish' );

		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Dado: un auto-draft de autor (flujo Gutenberg / post-new).
	 * Cuando: se publica por REST sin Nombres ni Apellidos.
	 * Entonces: 400.
	 */
	public function test_rest_publish_of_autodraft_without_identity_names_is_rejected() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Sofía Camila León Albino',
				'post_status' => 'auto-draft',
				'post_author' => $editor,
			)
		);

		$request = new WP_REST_Request( 'POST', '/wp/v2/author/' . (int) $author_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'status', 'publish' );

		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'auto-draft', get_post_status( $author_id ) );
	}

	/**
	 * Dado: un auto-draft de autor con Nombres y Apellidos en el mismo REST.
	 * Entonces: publica.
	 */
	public function test_rest_publish_of_autodraft_with_identity_names_succeeds() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Sofía Camila León Albino',
				'post_status' => 'auto-draft',
				'post_author' => $editor,
			)
		);

		$request = new WP_REST_Request( 'POST', '/wp/v2/author/' . (int) $author_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'status', 'publish' );
		$request->set_param(
			'meta',
			array(
				'given_names'  => 'Sofía Camila',
				'family_names' => 'León Albino',
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertFalse( $response->is_error(), wp_json_encode( $data ) );
		$this->assertSame( 'publish', get_post_status( $author_id ) );
	}

	/**
	 * Dado: un autor ya publicado sin Nombres ni Apellidos.
	 * Entonces: REST no bloquea el guardado (ADR 0022: solo ficha nueva).
	 */
	public function test_rest_update_of_existing_author_without_identity_names_is_allowed() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Luis Felipe Ramírez',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);

		$request = new WP_REST_Request( 'POST', '/wp/v2/author/' . (int) $author_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'title', 'Luis Felipe Ramírez' );

		$response = rest_do_request( $request );

		$this->assertFalse( $response->is_error(), wp_json_encode( $response->get_data() ) );
		$this->assertSame( 'publish', get_post_status( $author_id ) );
	}

	/**
	 * Dado: Administrador en Ajustes.
	 * Entonces: ve Aplicar relleno y Restaurar.
	 */
	public function test_backfill_tools_are_visible_to_administrator() {
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		ob_start();
		Author_Name_Backfill::render_tools();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'revistalogos_author_name_backfill', $html );
		$this->assertStringContainsString( 'value="apply"', $html );
		$this->assertStringContainsString( 'value="restore"', $html );
	}

	/**
	 * Dado: un Editor.
	 * Entonces: no ve el puente de relleno.
	 */
	public function test_backfill_tools_are_hidden_from_editor() {
		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );

		ob_start();
		Author_Name_Backfill::render_tools();
		$html = ob_get_clean();

		$this->assertSame( '', $html );
	}

	/**
	 * Dado: un autor nuevo por REST con Nombres y Apellidos.
	 * Entonces: persiste; citation_surname puede faltar.
	 */
	public function test_rest_create_with_identity_names_persists_without_citation_surname() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$request = new WP_REST_Request( 'POST', '/wp/v2/author' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'title', 'Sofía Camila León Albino' );
		$request->set_param( 'status', 'publish' );
		$request->set_param(
			'meta',
			array(
				'given_names'  => 'Sofía Camila',
				'family_names' => 'León Albino',
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertFalse( $response->is_error(), wp_json_encode( $data ) );
		$id = isset( $data['id'] ) ? (int) $data['id'] : 0;
		$this->assertGreaterThan( 0, $id );
		$this->assertSame( 'Sofía Camila', (string) get_post_meta( $id, 'given_names', true ) );
		$this->assertSame( 'León Albino', (string) get_post_meta( $id, 'family_names', true ) );
		$this->assertSame( '', (string) get_post_meta( $id, 'citation_surname', true ) );
	}

	/**
	 * Dado: family_names relleno y citation_surname vacío.
	 * Entonces: APA usa family_names.
	 */
	public function test_citation_uses_family_names_when_citation_surname_is_empty() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article(
			$editor,
			'Sofía Camila León Albino',
			'Sofía Camila',
			'León Albino',
			''
		);

		$formats = revistalogos_citation_formats( $article_id );

		$this->assertStringContainsString( 'León Albino, S.C.', $formats['APA'] );
		$this->assertStringNotContainsString( 'Albino, S.C.L.', $formats['APA'] );
	}

	/**
	 * Dado: citation_surname más corto que family_names.
	 * Entonces: APA usa citation_surname.
	 */
	public function test_citation_surname_override_is_shorter_than_family_names() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article(
			$editor,
			'Rafael Eduardo Figueredo Oropeza',
			'Rafael Eduardo',
			'Figueredo Oropeza',
			'Figueredo'
		);

		$formats = revistalogos_citation_formats( $article_id );

		$this->assertStringContainsString( 'Figueredo, R.E.', $formats['APA'] );
		$this->assertStringNotContainsString( 'Figueredo, R.E.F.O.', $formats['APA'] );
	}

	/**
	 * Dado: autores con citation_surname o vacío.
	 * Cuando: Apply y Restore.
	 * Entonces: family_names se rellena y Restore vuelve al snapshot.
	 */
	public function test_backfill_apply_then_restore_returns_empty_family_names() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$sofia = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Sofía Camila León Albino',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);
		update_post_meta( $sofia, 'citation_surname', 'León Albino' );

		$luis = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Luis Felipe Ramírez',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);

		Author_Name_Backfill::apply();

		$this->assertSame( 'Sofía Camila', (string) get_post_meta( $sofia, 'given_names', true ) );
		$this->assertSame( 'León Albino', (string) get_post_meta( $sofia, 'family_names', true ) );
		$this->assertSame( 'León Albino', (string) get_post_meta( $sofia, 'citation_surname', true ) );
		$this->assertSame( 'Luis Felipe', (string) get_post_meta( $luis, 'given_names', true ) );
		$this->assertSame( 'Ramírez', (string) get_post_meta( $luis, 'family_names', true ) );
		$this->assertSame( '', (string) get_post_meta( $luis, 'citation_surname', true ) );

		Author_Name_Backfill::restore();

		$this->assertSame( '', (string) get_post_meta( $sofia, 'given_names', true ) );
		$this->assertSame( '', (string) get_post_meta( $sofia, 'family_names', true ) );
		$this->assertSame( 'León Albino', (string) get_post_meta( $sofia, 'citation_surname', true ) );
		$this->assertSame( '', (string) get_post_meta( $luis, 'given_names', true ) );
		$this->assertSame( '', (string) get_post_meta( $luis, 'family_names', true ) );
		$this->assertSame( '', (string) get_post_meta( $luis, 'citation_surname', true ) );
	}

	/**
	 * @param int    $editor_id Editor user.
	 * @param string $title     Public title.
	 * @param string $given     given_names.
	 * @param string $family    family_names.
	 * @param string $cite      citation_surname.
	 * @return int Article ID.
	 */
	private function make_article( $editor_id, $title, $given, $family, $cite ) {
		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => $title,
				'post_status' => 'publish',
				'post_author' => $editor_id,
			)
		);
		update_post_meta( $author_id, 'given_names', $given );
		update_post_meta( $author_id, 'family_names', $family );
		if ( '' !== $cite ) {
			update_post_meta( $author_id, 'citation_surname', $cite );
		}

		$issue_id = self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_title'  => 'Vol. 1 Nº 1',
				'post_status' => 'publish',
				'post_author' => $editor_id,
			)
		);
		update_post_meta( $issue_id, 'volume_number', 1 );
		update_post_meta( $issue_id, 'issue_number', 1 );

		$article_id = self::factory()->post->create(
			array(
				'post_type'   => 'article',
				'post_title'  => 'Artículo',
				'post_status' => 'publish',
				'post_author' => $editor_id,
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );
		update_post_meta( $article_id, 'issue', $issue_id );
		update_post_meta( $article_id, 'publication_date', '2026-01-01' );
		update_post_meta( $article_id, 'pages', '1-2' );

		return $article_id;
	}
}
