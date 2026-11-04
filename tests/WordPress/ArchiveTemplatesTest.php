<?php
/**
 * FSE archives, search, news and 404 (ADR 0015 §7, Session 6): one HTML
 * template per classic view (archive-issue, archive-article and its
 * taxonomy delegates, archive-author, home, single, page-buscar, search,
 * 404). Each composes the revistalogos/view block, which renders the
 * same template-parts/view/ part the classic view prints over the main
 * query, so both share one markup.
 *
 * @package Revistalogos
 */

// functions.php is not loaded here; site-blocks.php versions its editor script.
defined( 'REVISTALOGOS_THEME_VERSION' ) || define( 'REVISTALOGOS_THEME_VERSION', 'test' );

if ( ! function_exists( 'revistalogos_core_active' ) ) {
	function revistalogos_core_active() {
		return class_exists( 'Revistalogos_Core\Plugin' );
	}
}

$revistalogos_theme_inc = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc';
require_once $revistalogos_theme_inc . '/template-tags.php';
require_once $revistalogos_theme_inc . '/class-nav-walker.php';
require_once $revistalogos_theme_inc . '/citations.php';
require_once $revistalogos_theme_inc . '/site-blocks.php';
require_once $revistalogos_theme_inc . '/search-redirect.php';

/**
 * Locks the archive / search / news / 404 template contract.
 */
class ArchiveTemplatesTest extends WP_UnitTestCase {

	private const THEME = 'revistalogos';

	/**
	 * Block template slug => view part it renders.
	 */
	private const VIEWS = array(
		'archive-issue'         => 'issue-archive',
		'archive-article'       => 'article-archive',
		'taxonomy-section'      => 'article-archive',
		'taxonomy-article_type' => 'article-archive',
		'taxonomy-keyword'      => 'article-archive',
		'archive-author'        => 'author-archive',
		'home'                  => 'news',
		'single'                => 'news-single',
		'page-buscar'           => 'search',
		'search'                => 'search',
		'404'                   => 'not-found',
	);

	/**
	 * @var string
	 */
	private $previous_stylesheet;

	public function set_up() {
		parent::set_up();
		$this->previous_stylesheet = get_stylesheet();
		switch_theme( self::THEME );
		wp_clean_theme_json_cache();

		register_nav_menus(
			array(
				'primary'      => 'Navegación principal',
				'footer-quick' => 'Footer: Enlaces Rápidos',
				'footer-norms' => 'Footer: Normas Editoriales',
			)
		);

		// Every theme block, so re-registering does not trip "already registered".
		foreach ( glob( get_template_directory() . '/blocks/*', GLOB_ONLYDIR ) as $dir ) {
			$name = 'revistalogos/' . basename( $dir );
			if ( WP_Block_Type_Registry::get_instance()->is_registered( $name ) ) {
				unregister_block_type( $name );
			}
		}
		revistalogos_register_site_blocks();
	}

	public function tear_down() {
		unset( $_GET['q'], $_GET['pg'] );
		delete_option( 'page_for_posts' );
		update_option( 'show_on_front', 'posts' );
		switch_theme( $this->previous_stylesheet );
		wp_clean_theme_json_cache();
		parent::tear_down();
	}

	/**
	 * Dado: el theme activo.
	 * Entonces: registra revistalogos/view con render_callback y solo
	 * script de editor.
	 */
	public function test_theme_registers_the_view_block() {
		$type = WP_Block_Type_Registry::get_instance()->get_registered( 'revistalogos/view' );

		$this->assertNotNull( $type );
		$this->assertIsCallable( $type->render_callback );
		$this->assertSame( array( 'revistalogos-site-blocks-editor' ), $type->editor_script_handles );
		$this->assertSame( array(), $type->view_script_handles );
	}

	/**
	 * Dado: el bloque revistalogos/view en single.html.
	 * Entonces: recibe postId y postType del contexto, para que la vista
	 * previa del editor pida la entrada que se está editando.
	 */
	public function test_view_block_receives_the_post_context() {
		$type = WP_Block_Type_Registry::get_instance()->get_registered( 'revistalogos/view' );

		$this->assertSame( array( 'postId', 'postType' ), $type->uses_context );
	}

	/**
	 * Dado: las partes de vista del theme.
	 * Entonces: ninguna consulta números, artículos ni autores; esos
	 * datos llegan por la consulta principal o por un helper del plugin
	 * (ADR 0015, contrato de conversión §4).
	 */
	public function test_view_parts_do_not_query_journal_post_types() {
		foreach ( glob( get_template_directory() . '/template-parts/view/*.php' ) as $file ) {
			$this->assertDoesNotMatchRegularExpression(
				"/'post_type'\\s*=>\\s*'(issue|article|author)'/",
				(string) file_get_contents( $file ),
				basename( $file )
			);
		}
	}

	/**
	 * Dado: cada vista clásica de la sesión 6.
	 * Entonces: existe la plantilla HTML del mismo slug, porque la PHP
	 * le ganaría a index.html.
	 */
	public function test_every_session_view_has_its_block_template() {
		$theme_dir = get_template_directory();

		foreach ( array_keys( self::VIEWS ) as $slug ) {
			$this->assertFileExists( $theme_dir . '/' . $slug . '.php', $slug );
			$this->assertNotNull( get_block_template( self::THEME . '//' . $slug ), $slug );
		}
	}

	/**
	 * Dado: la plantilla del archivo de números y su vista PHP.
	 * Entonces: el resolvedor de plantillas elige la HTML.
	 */
	public function test_block_template_wins_over_the_php_view() {
		global $_wp_current_template_id;

		// Core adds this for block themes on after_setup_theme, which switch_theme() does not fire.
		add_theme_support( 'block-templates' );

		$theme_dir = get_template_directory();
		locate_block_template( $theme_dir . '/archive-issue.php', 'archive', array( 'archive-issue.php', 'archive.php' ) );

		$this->assertSame( self::THEME . '//archive-issue', $_wp_current_template_id );
	}

	/**
	 * Dado: cada plantilla de la sesión 6.
	 * Entonces: cabecera, main#main-content, container, un solo
	 * revistalogos/view con su vista, y pie, en ese orden.
	 */
	public function test_templates_compose_one_view_inside_main() {
		foreach ( self::VIEWS as $slug => $view ) {
			$blocks = $this->template_blocks( $slug );

			$this->assertSame(
				array( 'core/template-part', 'core/group', 'core/group', 'revistalogos/view', 'core/template-part' ),
				wp_list_pluck( $blocks, 'blockName' ),
				$slug
			);
			$this->assertSame( 'main', $blocks[1]['attrs']['tagName'], $slug );
			$this->assertSame( 'main-content', $blocks[1]['attrs']['anchor'], $slug );
			$this->assertSame( 'container', $blocks[2]['attrs']['className'], $slug );
			$this->assertSame( $view, $blocks[3]['attrs']['view'], $slug );
		}
	}

	/**
	 * Dado: una vista vacía, desconocida o con intento de recorrer rutas.
	 * Entonces: el bloque no emite nada.
	 */
	public function test_view_block_is_empty_for_unknown_views() {
		foreach ( array( '', 'front-page', '../front-page', 'view/news', 'Issue-Archive' ) as $view ) {
			$this->assertSame( '', $this->render_view( $view ), $view );
		}
	}

	/**
	 * Dado: un número publicado y la URL del archivo de números.
	 * Entonces: migas, cabecera de archivo y la tarjeta del número, igual
	 * que la parte de archive-issue.php.
	 */
	public function test_issue_archive_lists_the_main_query() {
		$this->make_post( 'issue', 'Volumen 1, Número 1' );
		$this->go_to( get_post_type_archive_link( 'issue' ) );

		$html = $this->render_view( 'issue-archive' );

		$this->assertStringContainsString( '<nav class="breadcrumbs" aria-label="Migas de pan">', $html );
		$this->assertStringContainsString( '<h1 class="archive-header__title">Archivo de Números</h1>', $html );
		$this->assertStringContainsString( '<section class="archive-grid">', $html );
		$this->assertStringContainsString( 'Volumen 1, Número 1', $html );
		$this->assertStringNotContainsString( 'search-empty', $html );

		rewind_posts();
		$this->assertSame( $this->part( 'template-parts/view/issue-archive' ), $html );
	}

	/**
	 * Dado: ningún número publicado.
	 * Entonces: el archivo de números muestra su estado vacío.
	 */
	public function test_issue_archive_without_issues_shows_the_empty_state() {
		$this->go_to( get_post_type_archive_link( 'issue' ) );

		$html = $this->render_view( 'issue-archive' );

		$this->assertStringContainsString( 'Todavía no hay números publicados.', $html );
		$this->assertStringNotContainsString( 'archive-grid', $html );
	}

	/**
	 * Dado: un artículo publicado y la URL del archivo de artículos.
	 * Entonces: filtros nativos (s, section, year) y la tarjeta del
	 * artículo.
	 */
	public function test_article_archive_renders_filters_and_cards() {
		$this->make_article( 'Sobre la esperanza' );
		$this->go_to( get_post_type_archive_link( 'article' ) );

		$html = $this->render_view( 'article-archive' );

		$this->assertStringContainsString( '<h1 class="archive-header__title">Archivo de Artículos</h1>', $html );
		$this->assertStringContainsString( '<form class="archive-filters__form"', $html );
		$this->assertStringContainsString( 'name="section"', $html );
		$this->assertStringContainsString( 'name="year"', $html );
		$this->assertStringContainsString( 'class="card article-card"', $html );
		$this->assertStringContainsString( 'Sobre la esperanza', $html );
	}

	/**
	 * Dado: artículos publicados en 2024 y 2026 y un borrador de 2020.
	 * Entonces: el filtro de año ofrece 2026, 2025 y 2024, en ese orden.
	 */
	public function test_article_archive_year_filter_spans_the_published_years() {
		$this->make_article( 'Primero', '2024-03-01 10:00:00' );
		$this->make_article( 'Último', '2026-05-01 10:00:00' );
		self::factory()->post->create(
			array(
				'post_type'   => 'article',
				'post_status' => 'draft',
				'post_date'   => '2020-01-01 10:00:00',
			)
		);
		$this->go_to( get_post_type_archive_link( 'article' ) );

		$html = $this->render_view( 'article-archive' );

		preg_match_all( '/<option value="(\\d{4})"/', $html, $years );
		$this->assertSame( array( '2026', '2025', '2024' ), $years[1] );
	}

	/**
	 * Dado: la URL de un término de sección.
	 * Entonces: el archivo de artículos titula con el término.
	 */
	public function test_article_archive_on_a_section_term_uses_the_term_heading() {
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => 'section',
				'name'     => 'Hermenéutica',
			)
		);
		$article = $this->make_article( 'Ser y esperanza' );
		wp_set_object_terms( $article, array( $term_id ), 'section' );
		$this->go_to( get_term_link( $term_id, 'section' ) );

		$html = $this->render_view( 'article-archive' );

		$this->assertStringContainsString( '<h1 class="archive-header__title">Hermenéutica</h1>', $html );
		$this->assertStringContainsString( 'clasificados en «Hermenéutica».', $html );
		$this->assertStringContainsString( 'Ser y esperanza', $html );
	}

	/**
	 * Dado: ningún autor publicado.
	 * Entonces: el archivo de autores conserva el estado vacío estático.
	 */
	public function test_author_archive_without_authors_keeps_the_static_empty_state() {
		$this->go_to( get_post_type_archive_link( 'author' ) );

		$html = $this->render_view( 'author-archive' );

		$this->assertStringContainsString( '<h1 class="archive-header__title">Archivo de Autores</h1>', $html );
		$this->assertStringContainsString( '<section class="empty-state">', $html );
	}

	/**
	 * Dado: una noticia y la página de entradas /noticias/.
	 * Entonces: la lista de noticias con fecha, autor y «Leer más».
	 */
	public function test_news_index_lists_the_posts() {
		$posts_page = $this->make_post( 'page', 'Noticias', 'noticias' );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $posts_page );
		$this->make_post( 'post', 'Convocatoria abierta' );
		$this->go_to( get_permalink( $posts_page ) );

		$this->assertTrue( is_home() );

		$html = $this->render_view( 'news' );

		$this->assertStringContainsString( '<h1 class="archive-header__title">Noticias</h1>', $html );
		$this->assertStringContainsString( '<section class="blog-posts">', $html );
		$this->assertStringContainsString( 'Convocatoria abierta', $html );
		$this->assertStringContainsString( 'class="blog-post__read-more"', $html );
	}

	/**
	 * Dado: dos noticias publicadas y la URL de una.
	 * Entonces: la noticia completa y la otra como entrada relacionada.
	 */
	public function test_news_single_renders_the_post_and_related_posts() {
		$this->make_post( 'post', 'Noticia anterior' );
		$post_id = $this->make_post( 'post', 'Noticia principal' );
		$this->go_to( get_permalink( $post_id ) );

		$html = $this->render_view( 'news-single' );

		$this->assertStringContainsString( 'blog-post--single', $html );
		$this->assertStringContainsString( '<h1 class="blog-post__title">Noticia principal</h1>', $html );
		$this->assertStringContainsString( 'Cuerpo de la entrada.', $html );
		$this->assertStringContainsString( '<section class="related-posts">', $html );
		$this->assertStringContainsString( 'Noticia anterior', $html );
	}

	/**
	 * Dado: /buscar/ sin consulta.
	 * Entonces: solo cabecera y formulario, como la pantalla estática.
	 */
	public function test_search_view_without_a_query_shows_only_the_form() {
		$this->go_to( get_permalink( $this->make_post( 'page', 'Búsqueda', 'buscar' ) ) );

		$html = $this->render_view( 'search' );

		$this->assertStringContainsString( '<header class="search-header">', $html );
		$this->assertStringContainsString( '<form class="search-form" role="search"', $html );
		$this->assertStringNotContainsString( 'search-results', $html );
		$this->assertStringNotContainsString( 'search-empty', $html );
	}

	/**
	 * Dado: /buscar/?q= con un artículo que coincide.
	 * Entonces: la tarjeta del artículo en los resultados.
	 */
	public function test_search_view_lists_matching_results() {
		$this->make_article( 'Esperanza y tiempo' );
		$this->go_to( get_permalink( $this->make_post( 'page', 'Búsqueda', 'buscar' ) ) );
		$_GET['q'] = 'Esperanza';

		$html = $this->render_view( 'search' );

		$this->assertStringContainsString( '<section class="search-results"', $html );
		$this->assertStringContainsString( 'Esperanza y tiempo', $html );
		$this->assertStringContainsString( 'value="Esperanza"', $html );
	}

	/**
	 * Dado: /buscar/?q= sin coincidencias.
	 * Entonces: el estado vacío accesible.
	 */
	public function test_search_view_without_matches_shows_the_empty_state() {
		$this->go_to( get_permalink( $this->make_post( 'page', 'Búsqueda', 'buscar' ) ) );
		$_GET['q'] = 'inexistente';

		$this->assertStringContainsString( '<section class="search-empty"', $this->render_view( 'search' ) );
	}

	/**
	 * Dado: /?s= nativo con la página /buscar/ publicada.
	 * Entonces: la redirección apunta a /buscar/?q=, como hacía search.php.
	 */
	public function test_native_search_redirects_to_buscar() {
		$buscar = $this->make_post( 'page', 'Búsqueda', 'buscar' );
		$this->go_to( home_url( '/?s=hola' ) );

		$this->assertSame( add_query_arg( 'q', 'hola', get_permalink( $buscar ) ), revistalogos_native_search_redirect_url() );
	}

	/**
	 * Dado: sin página /buscar/, o un feed de búsqueda, o una URL que no
	 * es búsqueda.
	 * Entonces: no hay redirección.
	 */
	public function test_native_search_does_not_redirect_without_buscar_or_outside_search() {
		$this->go_to( home_url( '/?s=hola' ) );
		$this->assertNull( revistalogos_native_search_redirect_url() );

		$this->make_post( 'page', 'Búsqueda', 'buscar' );
		$this->go_to( home_url( '/?s=hola&feed=rss2' ) );
		$this->assertNull( revistalogos_native_search_redirect_url() );

		$this->go_to( home_url( '/' ) );
		$this->assertNull( revistalogos_native_search_redirect_url() );
	}

	/**
	 * Dado: una entrada inexistente resuelta con 404.html (la suite usa
	 * enlaces permanentes simples, así que se pide por ?p=).
	 * Entonces: la página completa tiene un solo main y el bloque de error
	 * con los dos enlaces.
	 */
	public function test_not_found_template_renders_the_error_page() {
		$this->go_to( home_url( '/?p=987654321' ) );

		$this->assertTrue( is_404() );

		$html = $this->render_template( '404' );

		$this->assertSame( 1, substr_count( $html, '<main' ) );
		$this->assertStringContainsString( '<main id="main-content" class="wp-block-group main-content', $html );
		$this->assertStringContainsString( '<p class="error-page__code">404</p>', $html );
		$this->assertStringContainsString( 'La página que busca no existe.', $html );
		$this->assertStringContainsString( 'Ver número actual', $html );
	}

	/**
	 * @param string $slug Template slug.
	 * @return string Rendered template, loop started like core does.
	 */
	private function render_template( $slug ) {
		global $_wp_current_template_content, $_wp_current_template_id;

		$template                     = get_block_template( self::THEME . '//' . $slug );
		$_wp_current_template_id      = $template->id;
		$_wp_current_template_content = $template->content;

		return get_the_block_template_html();
	}

	/**
	 * @param string $slug Template slug.
	 * @return array[] Every parsed block in document order, whitespace dropped.
	 */
	private function template_blocks( $slug ) {
		$template = get_block_template( self::THEME . '//' . $slug );
		$this->assertNotNull( $template, $slug );

		$flat  = array();
		$queue = parse_blocks( $template->content );
		while ( $queue ) {
			$block = array_shift( $queue );
			if ( ! $block['blockName'] ) {
				continue;
			}
			$flat[] = $block;
			$queue  = array_merge( $block['innerBlocks'], $queue );
		}

		return $flat;
	}

	/**
	 * @param string $view View attribute.
	 * @return string
	 */
	private function render_view( $view ) {
		return do_blocks( '<!-- wp:revistalogos/view ' . wp_json_encode( array( 'view' => $view ) ) . ' /-->' );
	}

	/**
	 * @param string $slug Theme template part.
	 * @return string
	 */
	private function part( $slug ) {
		ob_start();
		get_template_part( $slug );

		return (string) ob_get_clean();
	}

	/**
	 * Publish needs a published Author already assigned (plugin 0.2.5).
	 *
	 * @param string $title Article title.
	 * @param string $date  Optional post_date.
	 * @return int Published article ID.
	 */
	private function make_article( $title, $date = '' ) {
		$author_id  = $this->make_post( 'author', 'Autor de ' . $title );
		$article_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'article',
				'post_title'  => $title,
				'post_status' => 'draft',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );
		wp_update_post(
			array(
				'ID'          => $article_id,
				'post_status' => 'publish',
			) + ( $date ? array(
				'post_date' => $date,
				'edit_date' => true,
			) : array() )
		);

		return $article_id;
	}

	/**
	 * @param string $type  Post type.
	 * @param string $title Title.
	 * @param string $name  Optional slug.
	 * @return int Published post ID.
	 */
	private function make_post( $type, $title, $name = '' ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'    => $type,
				'post_title'   => $title,
				'post_name'    => $name ? $name : sanitize_title( $title ),
				'post_content' => '<!-- wp:paragraph --><p>Cuerpo de la entrada.</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
			)
		);
	}
}
