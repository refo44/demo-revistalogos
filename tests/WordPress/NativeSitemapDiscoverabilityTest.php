<?php
/**
 * Native WordPress sitemap contract for public discoverability:
 * journal CPTs stay listed, wp-admin users do not, /buscar/ is not a
 * landing, and sitemap requests are not 404 when no native posts exist
 * (WordPress 7.1 / Trac #65945).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Content_Types;
use Revistalogos_Core\Native_Sitemap;
use Revistalogos_Core\Taxonomies;

/**
 * Protects what Google and AI crawlers receive from /wp-sitemap.xml
 * after SiteSEO is out of the way.
 */
class NativeSitemapDiscoverabilityTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Content_Types::register();
		Taxonomies::register();
		Taxonomies::insert_initial_terms();
		if ( function_exists( 'wp_sitemaps_get_server' ) ) {
			wp_sitemaps_get_server()->register_rewrites();
		}
		$this->set_permalink_structure( '/%postname%/' );
	}

	/**
	 * Dado: no hay entradas nativas `post` publicadas.
	 * Cuando: se solicita el índice nativo de sitemaps.
	 * Entonces: la petición no es 404 (workaround WP 7.1 / Trac #65945;
	 * retirar cuando el mínimo de WordPress sea 7.1.1 o superior).
	 */
	public function test_sitemap_index_is_not_404_when_no_published_posts() {
		$this->unpublish_native_posts();
		$this->make_published_page( 'acerca', 'Acerca' );

		$this->go_to( home_url( '/?sitemap=index' ) );

		$this->assertNotEmpty( get_query_var( 'sitemap' ), 'the request must be recognized as a sitemap' );
		$this->assertFalse( is_404(), 'WordPress 7.1 must not mark a populated sitemap as 404 when no posts exist' );
		$this->assertTrue(
			Native_Sitemap::keep_sitemap_from_404( false, $GLOBALS['wp_query'] ),
			'pre_handle_404 must short-circuit only for a recognized sitemap request'
		);
	}

	/**
	 * Dado: una URL pública que no existe.
	 * Entonces: el workaround no convierte el 404 en 200.
	 */
	public function test_ordinary_missing_url_stays_404() {
		$this->go_to( home_url( '/esta-ruta-no-existe-les/' ) );

		$this->assertEmpty( get_query_var( 'sitemap' ) );
		$this->assertTrue( is_404() );
		$this->assertFalse(
			Native_Sitemap::keep_sitemap_from_404( false, $GLOBALS['wp_query'] )
		);
	}

	/**
	 * Dado: el sitemap nativo de WordPress.
	 * Entonces: no anuncia el provider de cuentas wp-admin (`users`).
	 */
	public function test_sitemap_index_omits_the_users_provider() {
		$registry = wp_sitemaps_get_server()->registry;
		$this->assertNull( $registry->get_provider( 'users' ) );

		$locs = $this->sitemap_index_locs();

		foreach ( $locs as $loc ) {
			$this->assertStringNotContainsString(
				'wp-sitemap-users',
				$loc,
				'CPT author is the public author surface; wp-admin users must not be in the sitemap'
			);
		}
	}

	/**
	 * Dado: el registro nativo de sitemaps.
	 * Entonces: posts y taxonomies siguen registrados.
	 */
	public function test_posts_and_taxonomies_providers_remain_registered() {
		$registry = wp_sitemaps_get_server()->registry;

		$this->assertInstanceOf( \WP_Sitemaps_Provider::class, $registry->get_provider( 'posts' ) );
		$this->assertInstanceOf( \WP_Sitemaps_Provider::class, $registry->get_provider( 'taxonomies' ) );
	}

	/**
	 * Dado: una página /buscar/ publicada.
	 * Entonces: no aparece en el sitemap de páginas.
	 */
	public function test_pages_sitemap_omits_the_search_page() {
		$acerca_id = $this->make_published_page( 'acerca', 'Acerca' );
		$buscar_id = $this->make_published_page( 'buscar', 'Buscar' );

		$provider = wp_sitemaps_get_server()->registry->get_provider( 'posts' );
		$url_list = $provider->get_url_list( 1, 'page' );
		$locs     = wp_list_pluck( $url_list, 'loc' );

		$this->assertContains( get_permalink( $acerca_id ), $locs );
		$this->assertNotContains( get_permalink( $buscar_id ), $locs );

		$filtered = Native_Sitemap::exclude_search_page( array(), 'page' );
		$this->assertContains( $buscar_id, $filtered['post__not_in'] );
	}

	/**
	 * Dado: query args de article, issue o author.
	 * Entonces: el filtro de páginas no los altera.
	 */
	public function test_non_page_sitemap_query_args_are_unchanged() {
		$args = array(
			'orderby' => 'ID',
			'order'   => 'ASC',
		);

		foreach ( array( 'article', 'issue', 'author' ) as $post_type ) {
			$this->assertSame( $args, Native_Sitemap::exclude_search_page( $args, $post_type ) );
		}
	}

	/**
	 * Dado: la página /buscar/.
	 * Cuando: se renderizan las directivas robots.
	 * Entonces: incluye noindex.
	 */
	public function test_search_page_sends_noindex() {
		$buscar_id = $this->make_published_page( 'buscar', 'Buscar' );

		$this->go_to( get_permalink( $buscar_id ) );

		$robots = apply_filters( 'wp_robots', array( 'index' => true ) );

		$this->assertTrue( is_page( 'buscar' ) );
		$this->assertArrayNotHasKey( 'index', $robots );
		$this->assertTrue( (bool) $robots['noindex'] );
		$this->assertTrue( (bool) $robots['follow'] );
	}

	/**
	 * Dado: una página institucional.
	 * Entonces: no recibe noindex por el filtro de /buscar/.
	 */
	public function test_institutional_page_is_not_forced_noindex() {
		$acerca_id = $this->make_published_page( 'acerca', 'Acerca' );

		$this->go_to( get_permalink( $acerca_id ) );

		$robots = apply_filters( 'wp_robots', array() );

		$this->assertTrue( is_page( 'acerca' ) );
		$this->assertArrayNotHasKey( 'noindex', $robots );
	}

	/**
	 * Dado: los CPT y la taxonomía públicos del journal.
	 * Entonces: el sitemap nativo sigue ofreciendo article, issue, author
	 * y article_type (no se excluyen).
	 */
	public function test_journal_public_types_remain_in_native_sitemap() {
		$posts = wp_sitemaps_get_server()->registry->get_provider( 'posts' );
		$this->assertInstanceOf( \WP_Sitemaps_Provider::class, $posts );
		$post_types = $posts->get_object_subtypes();
		$this->assertArrayHasKey( 'article', $post_types );
		$this->assertArrayHasKey( 'issue', $post_types );
		$this->assertArrayHasKey( 'author', $post_types );

		$taxonomies = wp_sitemaps_get_server()->registry->get_provider( 'taxonomies' );
		$this->assertInstanceOf( \WP_Sitemaps_Provider::class, $taxonomies );
		$this->assertArrayHasKey( Taxonomies::ARTICLE_TYPE, $taxonomies->get_object_subtypes() );
	}

	/**
	 * @return string[]
	 */
	private function sitemap_index_locs() {
		$list = wp_sitemaps_get_server()->index->get_sitemap_list();

		return wp_list_pluck( $list, 'loc' );
	}

	/**
	 * @param string $slug  Page slug.
	 * @param string $title Page title.
	 * @return int
	 */
	private function make_published_page( $slug, $title ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Reproduce the production condition: no published native posts.
	 */
	private function unpublish_native_posts() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);

		foreach ( $posts as $post ) {
			wp_update_post(
				array(
					'ID'          => $post->ID,
					'post_status' => 'private',
				)
			);
		}
	}
}
