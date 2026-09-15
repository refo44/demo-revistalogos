<?php
/**
 * Author citation_surname and article citation_override_* (ADR 0021 / #64).
 *
 * @package Revistalogos
 */

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
 * Protects bibliographic surname + optional per-format Cómo Citar override.
 *
 * @ticket 64
 */
class CitationSurnameOverrideTest extends WP_UnitTestCase {

	/**
	 * Override meta keys the article REST schema and metabox must expose.
	 *
	 * @var string[]
	 */
	private static $override_keys = array(
		'citation_override_apa',
		'citation_override_bibtex',
		'citation_override_vancouver',
		'citation_override_chicago',
		'citation_override_mla',
		'citation_override_harvard',
		'citation_override_ris',
	);

	public function set_up() {
		parent::set_up();
		Content_Types::register();
		Metadata::register();
		Roles::install();

		global $wp_rest_server;
		$wp_rest_server = null;

		$theme_inc = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc';
		require_once $theme_inc . '/template-tags.php';
		require_once $theme_inc . '/citations.php';
	}

	/**
	 * Dado: el esquema REST del CPT author.
	 * Entonces: incluye citation_surname (Gutenberg / #30).
	 */
	public function test_author_rest_schema_exposes_citation_surname() {
		$controller = new WP_REST_Posts_Controller( 'author' );
		$schema     = $controller->get_item_schema();
		$meta_props = isset( $schema['properties']['meta']['properties'] ) && is_array( $schema['properties']['meta']['properties'] )
			? array_keys( $schema['properties']['meta']['properties'] )
			: array();

		$this->assertContains(
			'citation_surname',
			$meta_props,
			'REST author schema must expose meta.citation_surname; got: ' . wp_json_encode( $meta_props )
		);
	}

	/**
	 * Dado: el esquema REST del CPT article.
	 * Entonces: incluye las siete citation_override_*.
	 */
	public function test_article_rest_schema_exposes_citation_override_keys() {
		$controller = new WP_REST_Posts_Controller( 'article' );
		$schema     = $controller->get_item_schema();
		$meta_props = isset( $schema['properties']['meta']['properties'] ) && is_array( $schema['properties']['meta']['properties'] )
			? array_keys( $schema['properties']['meta']['properties'] )
			: array();

		foreach ( self::$override_keys as $key ) {
			$this->assertContains(
				$key,
				$meta_props,
				'REST article schema must expose meta.' . $key . '; got: ' . wp_json_encode( $meta_props )
			);
		}
	}

	/**
	 * Dado: un autor publicado.
	 * Cuando: REST guarda citation_surname.
	 * Entonces: el meta persiste.
	 */
	public function test_rest_persists_citation_surname_on_author() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Ana María Pérez Gómez',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);

		$request = new WP_REST_Request( 'POST', '/wp/v2/author/' . (int) $author_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param(
			'meta',
			array(
				'citation_surname' => 'Pérez Gómez',
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertFalse( $response->is_error(), wp_json_encode( $data ) );
		$this->assertSame( 'Pérez Gómez', (string) get_post_meta( $author_id, 'citation_surname', true ) );
	}

	/**
	 * Dado: un artículo.
	 * Cuando: REST guarda un override de MLA.
	 * Entonces: el meta persiste y las demás cajas no se inventan.
	 */
	public function test_rest_persists_mla_override_without_inventing_the_others() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		$request = new WP_REST_Request( 'POST', '/wp/v2/article/' . (int) $article_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param(
			'meta',
			array(
				'citation_override_mla' => 'MLA parcheado a mano.',
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertFalse( $response->is_error(), wp_json_encode( $data ) );
		$this->assertSame( 'MLA parcheado a mano.', (string) get_post_meta( $article_id, 'citation_override_mla', true ) );
		$this->assertSame( '', (string) get_post_meta( $article_id, 'citation_override_apa', true ) );
	}

	/**
	 * Dado: un autor con citation_surname.
	 * Entonces: APA usa ambos apellidos y solo iniciales de pila.
	 */
	public function test_citation_formats_use_author_citation_surname() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		$formats = revistalogos_citation_formats( $article_id );

		$this->assertStringContainsString( 'Pérez Gómez, A.M.', $formats['APA'] );
		$this->assertStringNotContainsString( 'Gómez, A.M.P.', $formats['APA'] );
		$this->assertStringContainsString( 'Pérez Gómez AM', $formats['Vancouver'] );
	}

	/**
	 * Dado: override de MLA relleno y APA vacío.
	 * Entonces: MLA es el texto guardado; APA sigue el builder.
	 */
	public function test_filled_mla_override_replaces_only_mla() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		update_post_meta( $article_id, 'citation_override_mla', 'MLA parcheado a mano.' );

		$formats = revistalogos_citation_formats( $article_id );

		$this->assertSame( 'MLA parcheado a mano.', $formats['MLA'] );
		$this->assertStringContainsString( 'Pérez Gómez, A.M.', $formats['APA'] );
	}

	/**
	 * Dado: override de MLA.
	 * Cuando: REST lo vacía (Regenerar + guardar).
	 * Entonces: MLA vuelve al builder.
	 */
	public function test_rest_empty_mla_override_returns_to_the_builder() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		update_post_meta( $article_id, 'citation_override_mla', 'MLA parcheado a mano.' );

		$request = new WP_REST_Request( 'POST', '/wp/v2/article/' . (int) $article_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param(
			'meta',
			array(
				'citation_override_mla' => '',
			)
		);

		$response = rest_do_request( $request );
		$this->assertFalse( $response->is_error(), wp_json_encode( $response->get_data() ) );

		$formats = revistalogos_citation_formats( $article_id );
		$this->assertNotSame( 'MLA parcheado a mano.', $formats['MLA'] );
		$this->assertStringContainsString( 'Pérez Gómez', $formats['MLA'] );
	}

	/**
	 * Dado: override de RIS.
	 * Entonces: el payload de descarga usa ese texto.
	 */
	public function test_filled_ris_override_replaces_the_ris_payload() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		$ris = implode(
			"\n",
			array(
				'TY  - JOUR',
				'TI  - Parche',
				'ER  -',
			)
		);
		update_post_meta( $article_id, 'citation_override_ris', $ris );

		$this->assertSame( $ris, revistalogos_citation_ris( $article_id ) );
	}

	/**
	 * Dado: la ficha Autores.
	 * Entonces: el metabox ofrece apellido(s) para citar.
	 */
	public function test_author_metabox_includes_citation_surname_field() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Ana María Pérez Gómez',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);

		ob_start();
		Meta_Boxes::render_fields_box( get_post( $author_id ) );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="citation_surname"', $html );
		$this->assertStringContainsString( 'citar', strtolower( $html ) );
	}

	/**
	 * Dado: la ficha Artículo.
	 * Entonces: Cómo Citar ofrece las siete cajas y Regenerar por formato.
	 */
	public function test_article_citation_metabox_includes_overrides_and_regenerate() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		ob_start();
		Meta_Boxes::render_citation_box( get_post( $article_id ) );
		$html = ob_get_clean();

		foreach ( self::$override_keys as $key ) {
			$this->assertStringContainsString( 'name="' . $key . '"', $html );
		}
		$this->assertSame( 7, substr_count( $html, 'revistalogos-citation-regenerate' ) );
	}

	/**
	 * Dado: override de MLA en el artículo.
	 * Cuando: el metabox guarda MLA vacío.
	 * Entonces: el meta se borra (Regenerar).
	 */
	public function test_classic_save_of_empty_override_deletes_that_meta() {
		$editor     = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );
		$article_id = $this->make_article_with_hispanic_author( $editor, 'Pérez Gómez' );

		update_post_meta( $article_id, 'citation_override_mla', 'MLA parcheado a mano.' );
		update_post_meta( $article_id, 'citation_override_apa', 'APA se conserva.' );

		$_POST[ Meta_Boxes::NONCE_FIELD ] = wp_create_nonce( Meta_Boxes::NONCE_ACTION );
		$_POST['citation_override_mla']     = '';
		$_POST['citation_override_apa']     = 'APA se conserva.';

		Meta_Boxes::save( $article_id, get_post( $article_id ) );

		$this->assertSame( '', (string) get_post_meta( $article_id, 'citation_override_mla', true ) );
		$this->assertFalse( metadata_exists( 'post', $article_id, 'citation_override_mla' ) );
		$this->assertSame( 'APA se conserva.', (string) get_post_meta( $article_id, 'citation_override_apa', true ) );

		unset( $_POST[ Meta_Boxes::NONCE_FIELD ], $_POST['citation_override_mla'], $_POST['citation_override_apa'] );
	}

	/**
	 * @param int    $editor_id        User ID.
	 * @param string $citation_surname Bibliographic surname.
	 * @return int Article ID.
	 */
	private function make_article_with_hispanic_author( $editor_id, $citation_surname ) {
		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Ana María Pérez Gómez',
				'post_status' => 'publish',
				'post_author' => $editor_id,
			)
		);
		update_post_meta( $author_id, 'citation_surname', $citation_surname );

		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'El ser y la esperanza',
				'post_status'  => 'publish',
				'post_content' => 'body',
				'post_author'  => $editor_id,
			)
		);
		update_post_meta( $article_id, 'authors', array( (int) $author_id ) );
		update_post_meta( $article_id, 'publication_date', '2024-06-01' );
		update_post_meta( $article_id, 'pages', '15-32' );
		update_post_meta( $article_id, 'doi', '' );

		return (int) $article_id;
	}
}
