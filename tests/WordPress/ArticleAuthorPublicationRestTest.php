<?php
/**
 * Regression for issue #30: Gutenberg REST publish of a draft Article
 * with a published Author assigned in the same request must succeed
 * and persist authors. Without CPT custom-fields support, REST omits
 * meta and the published-author guard sees an empty assignment.
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Content_Types;
use Revistalogos_Core\Metadata;
use Revistalogos_Core\Relationships;
use Revistalogos_Core\Roles;

/**
 * Protects the publish-with-author REST contract that Gutenberg uses
 * when the classic author picker syncs IDs into edited post meta.
 */
class ArticleAuthorPublicationRestTest extends WP_UnitTestCase {

	/**
	 * WP_UnitTestCase unregisters all meta keys between tests. Re-install
	 * the journal CPT/meta/role surface each case needs for REST.
	 */
	public function set_up() {
		parent::set_up();
		Content_Types::register();
		Metadata::register();
		Roles::install();

		// Drop the cached REST server so CPT meta re-registered above
		// appears in the article item schema for this test.
		global $wp_rest_server;
		$wp_rest_server = null;
	}

	/**
	 * Dado: el CPT article debe exponer meta por REST.
	 * Entonces: soporta custom-fields (requisito de WordPress para
	 * incluir la propiedad meta en el esquema del CPT).
	 */
	public function test_article_cpt_supports_custom_fields_for_rest_meta() {
		$this->assertTrue(
			post_type_supports( 'article', 'custom-fields' ),
			'article must support custom-fields so REST exposes and persists meta.authors'
		);
	}

	/**
	 * Dado: un artículo en borrador sin autores en meta almacenada.
	 * Cuando: se solicita publicar por REST sin meta.authors.
	 * Entonces: la publicación se rechaza y el artículo sigue sin publicar.
	 */
	public function test_rest_publish_of_draft_without_author_is_refused() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'Draft without author',
				'post_status'  => 'draft',
				'post_content' => 'body',
				'post_author'  => $editor,
			)
		);

		$response = $this->rest_update_article(
			$article_id,
			array(
				'status'  => 'publish',
				'content' => 'body',
			)
		);

		$this->assertTrue(
			$response->is_error() || (int) $response->get_status() >= 400,
			'REST publish without a published author must be refused'
		);
		$this->assertSame( 'draft', get_post_status( $article_id ) );
		$this->assertFalse(
			Relationships::has_published_author( get_post_meta( $article_id, 'authors', true ) )
		);
	}

	/**
	 * Dado: un artículo en borrador sin autores almacenados.
	 * Y: un Author CPT publicado cuyo ID viaja en meta.authors del mismo
	 *    request REST (forma Gutenberg tras sincronizar el picker).
	 * Cuando: se solicita publicar.
	 * Entonces: la publicación continúa y el autor permanece asignado.
	 */
	public function test_rest_publish_of_draft_with_author_in_same_request_succeeds_and_persists() {
		$editor = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $editor );

		$author_id = self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Published Author CPT',
				'post_status' => 'publish',
				'post_author' => $editor,
			)
		);

		$article_id = self::factory()->post->create(
			array(
				'post_type'    => 'article',
				'post_title'   => 'Draft awaiting same-request author',
				'post_status'  => 'draft',
				'post_content' => 'body',
				'post_author'  => $editor,
			)
		);

		$this->assertSame(
			array(),
			Relationships::sanitize_author_ids( get_post_meta( $article_id, 'authors', true ) ),
			'draft under test must start without stored authors'
		);

		$response = $this->rest_update_article(
			$article_id,
			array(
				'status' => 'publish',
				'meta'   => array(
					'authors' => array( (int) $author_id ),
				),
			)
		);
		$data = $response->get_data();

		$this->assertFalse( $response->is_error(), wp_json_encode( $data ) );
		$this->assertContains( (int) $response->get_status(), array( 200, 201 ) );
		$this->assertSame( 'publish', get_post_status( $article_id ) );
		$stored = Relationships::sanitize_author_ids( get_post_meta( $article_id, 'authors', true ) );
		$this->assertTrue(
			Relationships::has_published_author( $stored ),
			'same-request meta.authors must persist; stored=' . wp_json_encode( get_post_meta( $article_id, 'authors', true ) ) . ' response_meta=' . wp_json_encode( is_array( $data ) ? ( $data['meta'] ?? null ) : null )
		);
		$this->assertSame( array( (int) $author_id ), $stored );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'meta', $data );
		$this->assertSame(
			array( (int) $author_id ),
			array_map( 'intval', (array) $data['meta']['authors'] )
		);
	}

	/**
	 * Dado: el esquema REST del CPT article.
	 * Entonces: incluye la propiedad meta.authors (sin ella Gutenberg
	 * no puede enviar ni leer la asignación).
	 */
	public function test_article_rest_schema_exposes_authors_meta() {
		$controller = new WP_REST_Posts_Controller( 'article' );
		$schema     = $controller->get_item_schema();
		$meta_props = isset( $schema['properties']['meta']['properties'] ) && is_array( $schema['properties']['meta']['properties'] )
			? array_keys( $schema['properties']['meta']['properties'] )
			: array();

		$this->assertArrayHasKey( 'meta', $schema['properties'] );
		$this->assertSame( 'object', $schema['properties']['meta']['type'] );
		$this->assertContains(
			'authors',
			$meta_props,
			'REST item schema must expose meta.authors; got: ' . wp_json_encode( $meta_props )
		);
		$this->assertSame( 'array', $schema['properties']['meta']['properties']['authors']['type'] );
	}

	/**
	 * @param int   $article_id Article ID.
	 * @param array $params     REST body params.
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function rest_update_article( $article_id, array $params ) {
		$request = new WP_REST_Request( 'POST', '/wp/v2/article/' . (int) $article_id );
		$request->set_header( 'Content-Type', 'application/json' );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		return rest_do_request( $request );
	}
}
