<?php
/**
 * FSE front page and institutional pages (ADR 0015 §7, Session 5):
 * templates/front-page.html, page.html and one HTML template per
 * page-{slug}.php wrapper. The body is core/post-content; the regions
 * outside the canonical prose are theme blocks that render the same
 * template-parts the classic wrappers use, so both share one markup.
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

/**
 * Locks the front-page / institutional template contract.
 */
class FrontAndInstitutionalTemplatesTest extends WP_UnitTestCase {

	private const THEME = 'revistalogos';

	private const THEME_BLOCKS = array(
		'revistalogos/site-header',
		'revistalogos/site-footer',
		'revistalogos/front-hero',
		'revistalogos/front-sidebar',
		'revistalogos/page-header',
		'revistalogos/page-aside',
		'revistalogos/contact-form',
	);

	/**
	 * Wrappers whose classic view has a sidebar, by template slug.
	 */
	private const ASIDES = array(
		'page-acerca'               => 'acerca',
		'page-etica'                => 'etica',
		'page-enviar-colaboracion'  => 'enviar-colaboracion',
		'privacy-policy'            => 'privacidad',
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

		foreach ( self::THEME_BLOCKS as $name ) {
			if ( WP_Block_Type_Registry::get_instance()->is_registered( $name ) ) {
				unregister_block_type( $name );
			}
		}
		revistalogos_register_site_blocks();
	}

	public function tear_down() {
		delete_option( 'wp_page_for_privacy_policy' );
		delete_option( 'revistalogos_contact_form_id' );
		switch_theme( $this->previous_stylesheet );
		wp_clean_theme_json_cache();
		parent::tear_down();
	}

	/**
	 * Dado: el theme activo.
	 * Entonces: registra los bloques de portada y de páginas
	 * institucionales con render_callback y solo script de editor.
	 */
	public function test_theme_registers_the_front_and_page_blocks() {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( self::THEME_BLOCKS as $name ) {
			$type = $registry->get_registered( $name );
			$this->assertNotNull( $type, $name );
			$this->assertIsCallable( $type->render_callback, $name );
			$this->assertSame( array( 'revistalogos-site-blocks-editor' ), $type->editor_script_handles, $name );
			$this->assertSame( array(), $type->view_script_handles, $name );
		}
	}

	/**
	 * Dado: cada vista clásica de página (page-{slug}.php y
	 * privacy-policy.php), salvo la búsqueda (Sesión 6).
	 * Entonces: existe la plantilla HTML del mismo slug, porque una PHP
	 * más específica le ganaría a page.html.
	 */
	public function test_every_page_wrapper_has_its_block_template() {
		$theme_dir = get_template_directory();
		$wrappers  = array_merge( glob( $theme_dir . '/page-*.php' ), array( $theme_dir . '/privacy-policy.php' ) );
		$slugs     = array_diff( array_map( static fn( $path ) => basename( $path, '.php' ), $wrappers ), array( 'page-buscar' ) );

		$this->assertContains( 'page-acerca', $slugs );

		foreach ( array_merge( array( 'front-page', 'page' ), $slugs ) as $slug ) {
			$this->assertNotNull( get_block_template( self::THEME . '//' . $slug ), $slug );
		}
	}

	/**
	 * Dado: la plantilla de una página institucional y su vista PHP.
	 * Entonces: el resolvedor de plantillas elige la HTML.
	 */
	public function test_block_template_wins_over_the_php_wrapper() {
		global $_wp_current_template_id;

		// Core adds this for block themes on after_setup_theme, which switch_theme() does not fire.
		add_theme_support( 'block-templates' );

		$theme_dir = get_template_directory();
		locate_block_template( $theme_dir . '/page-acerca.php', 'page', array( 'page-acerca.php', 'page-8.php', 'page.php' ) );

		$this->assertSame( self::THEME . '//page-acerca', $_wp_current_template_id );
	}

	/**
	 * Dado: front-page.html.
	 * Entonces: compone cabecera, héroe, número actual, artículos
	 * recientes, la rejilla lateral y el pie, en ese orden.
	 */
	public function test_front_page_composes_its_sections_in_order() {
		$this->assertSame(
			array(
				'core/template-part',
				'core/group',
				'core/group',
				'revistalogos/front-hero',
				'revistalogos/current-issue',
				'revistalogos/recent-articles',
				'revistalogos/front-sidebar',
				'core/template-part',
			),
			$this->template_block_names( 'front-page' )
		);
	}

	/**
	 * Dado: page.html y cada plantilla institucional.
	 * Entonces: cabecera de página, cuerpo desde core/post-content y, solo
	 * donde la vista clásica lo tiene, el lateral del mismo slug.
	 */
	public function test_page_templates_compose_header_content_and_aside() {
		$slugs = array( 'page', 'page-acerca', 'page-etica', 'page-contacto', 'page-enlaces', 'page-enviar-colaboracion', 'page-normas', 'page-politicas', 'page-comite-editorial', 'privacy-policy' );

		foreach ( $slugs as $slug ) {
			$blocks = $this->template_blocks( $slug );
			$names  = wp_list_pluck( $blocks, 'blockName' );

			$this->assertContains( 'revistalogos/page-header', $names, $slug );
			$this->assertSame( 1, count( array_keys( $names, 'core/post-content', true ) ), $slug );
			$this->assertSame( 'page-contacto' === $slug, in_array( 'revistalogos/contact-form', $names, true ), $slug );

			$asides = array_values( wp_list_filter( $blocks, array( 'blockName' => 'revistalogos/page-aside' ) ) );
			if ( isset( self::ASIDES[ $slug ] ) ) {
				$this->assertCount( 1, $asides, $slug );
				$this->assertSame( self::ASIDES[ $slug ], $asides[0]['attrs']['slug'], $slug );
			} else {
				$this->assertCount( 0, $asides, $slug );
			}
		}
	}

	/**
	 * Dado: el bloque del héroe.
	 * Entonces: emite la sección del héroe con el enlace al número actual,
	 * igual que template-parts/front-hero.
	 */
	public function test_front_hero_matches_the_theme_part() {
		$html = do_blocks( '<!-- wp:revistalogos/front-hero /-->' );

		$this->assertStringContainsString( '<section class="front-page__hero">', $html );
		$this->assertStringContainsString( '<h1 class="hero__title">LOGO ET SPES</h1>', $html );
		$this->assertStringContainsString( 'class="btn btn--primary btn--large">Ver número actual</a>', $html );
		$this->assertSame( $this->part( 'template-parts/front-hero' ), $html );
	}

	/**
	 * Dado: ninguna noticia publicada.
	 * Entonces: la rejilla lateral muestra el aviso en la tarjeta Noticias
	 * y conserva las tarjetas de colaboración y CENFISS.
	 */
	public function test_front_sidebar_without_news_shows_the_notice() {
		$html = do_blocks( '<!-- wp:revistalogos/front-sidebar /-->' );

		$this->assertStringContainsString( '<section class="front-page__sidebar">', $html );
		$this->assertSame( 3, substr_count( $html, '<div class="sidebar-card">' ) );
		$this->assertStringContainsString( 'Todavía no hay noticias publicadas.', $html );
		$this->assertStringNotContainsString( 'news-list', $html );
		$this->assertStringContainsString( 'href="https://cenfiss.net"', $html );
		$this->assertSame( $this->part( 'template-parts/front-sidebar' ), $html );
	}

	/**
	 * Dado: cinco noticias publicadas.
	 * Entonces: la tarjeta Noticias lista las cuatro más recientes.
	 */
	public function test_front_sidebar_lists_the_four_latest_news() {
		foreach ( range( 1, 5 ) as $n ) {
			self::factory()->post->create(
				array(
					'post_title' => 'Noticia ' . $n,
					'post_date'  => '2026-10-0' . $n . ' 10:00:00',
				)
			);
		}

		$html = do_blocks( '<!-- wp:revistalogos/front-sidebar /-->' );

		$this->assertSame( 4, substr_count( $html, '<li class="news-item">' ) );
		$this->assertStringContainsString( 'class="news-link">Noticia 5</a>', $html );
		$this->assertStringNotContainsString( 'Noticia 1<', $html );
		$this->assertStringNotContainsString( 'Todavía no hay noticias', $html );
	}

	/**
	 * Dado: una página institucional sin extracto.
	 * Entonces: la cabecera de página emite migas, título y la descripción
	 * de su slug.
	 */
	public function test_page_header_uses_the_slug_description() {
		$page_id = $this->make_page( 'acerca', 'Acerca de la Revista' );

		$html = $this->render( 'page-header', array(), $page_id );

		$this->assertStringContainsString( '<nav class="breadcrumbs" aria-label="Migas de pan">', $html );
		$this->assertStringContainsString( '<span class="breadcrumbs__current">Acerca de la Revista</span>', $html );
		$this->assertStringContainsString( '<h1 class="archive-header__title">Acerca de la Revista</h1>', $html );
		$this->assertStringContainsString( '<p class="archive-header__description">Conoce el enfoque, alcance y objetivos de LOGO ET SPES', $html );
		$this->assertSame(
			$this->part(
				'template-parts/page-header',
				array( 'post' => $page_id )
			),
			$html
		);
	}

	/**
	 * Dado: una página con extracto, y otra sin descripción conocida.
	 * Entonces: el extracto reemplaza la descripción; sin ninguna, no hay
	 * párrafo de descripción.
	 */
	public function test_page_header_excerpt_wins_and_unknown_slug_has_no_description() {
		$with_excerpt = $this->make_page( 'normas', 'Normas', 'Resumen editado en wp-admin.' );
		$unknown      = $this->make_page( 'donaciones', 'Donaciones' );

		$this->assertStringContainsString( '<p class="archive-header__description">Resumen editado en wp-admin.</p>', $this->render( 'page-header', array(), $with_excerpt ) );
		$this->assertStringNotContainsString( 'archive-header__description', $this->render( 'page-header', array(), $unknown ) );
	}

	/**
	 * Dado: la página asignada como política de privacidad.
	 * Entonces: usa la descripción de privacidad, como privacy-policy.php.
	 */
	public function test_page_header_privacy_page_uses_the_privacy_description() {
		$page_id = $this->make_page( 'aviso', 'Aviso de Privacidad' );
		update_option( 'wp_page_for_privacy_policy', $page_id );

		$this->assertStringContainsString( 'Qué datos se tratan cuando visitas este sitio web', $this->render( 'page-header', array(), $page_id ) );
	}

	/**
	 * Dado: el bloque sin página en contexto.
	 * Entonces: no emite nada.
	 */
	public function test_page_header_is_empty_without_a_page() {
		$this->assertSame( '', $this->render( 'page-header' ) );
	}

	/**
	 * Dado: el lateral de Acerca.
	 * Entonces: emite aside.sidebar con la ficha de la revista (ISSN,
	 * depósito y DOI como texto inerte) y la tarjeta CENFISS.
	 */
	public function test_page_aside_renders_the_slug_sidebar() {
		$html = $this->render( 'page-aside', array( 'slug' => 'acerca' ) );

		$this->assertStringStartsWith( '<aside class="sidebar">', $html );
		$this->assertStringEndsWith( '</aside>', $html );
		$this->assertStringContainsString( '<h3>Información de la Revista</h3>', $html );
		$this->assertStringContainsString( '<dt>ISSN:</dt>', $html );
		$this->assertStringContainsString( 'Visitar sitio web', $html );
	}

	/**
	 * Dado: un slug sin lateral, vacío o con intento de recorrer rutas.
	 * Entonces: no emite nada.
	 */
	public function test_page_aside_is_empty_for_unknown_slugs() {
		foreach ( array( '', 'normas', '../front-page', 'page-header' ) as $slug ) {
			$this->assertSame( '', $this->render( 'page-aside', array( 'slug' => $slug ) ), $slug );
		}
	}

	/**
	 * Dado: Contact Form 7 sin formulario configurado.
	 * Entonces: la región «Enviar Mensaje» ofrece el correo directo.
	 */
	public function test_contact_form_falls_back_to_mailto() {
		$html = do_blocks( '<!-- wp:revistalogos/contact-form /-->' );

		$this->assertStringContainsString( '<section class="contact-section">', $html );
		$this->assertStringContainsString( '<h2>Enviar Mensaje</h2>', $html );
		$this->assertStringContainsString( 'href="mailto:revista.cenfiss@gmail.com"', $html );
		$this->assertSame( $this->part( 'template-parts/contact-form' ), $html );
	}

	/**
	 * Dado: la URL de Acerca resuelta con su plantilla HTML.
	 * Entonces: la página completa tiene un solo main, la cabecera de
	 * página, el cuerpo de WordPress dentro de content-main y el lateral.
	 */
	public function test_acerca_template_renders_the_full_page() {
		$page_id = $this->make_page( 'acerca', 'Acerca de la Revista' );
		$this->go_to( get_permalink( $page_id ) );

		$html = $this->render_template( 'page-acerca' );

		$this->assertSame( 1, substr_count( $html, '<main' ) );
		$this->assertStringContainsString( '<main id="main-content" class="wp-block-group main-content', $html );
		$this->assertStringContainsString( '<h1 class="archive-header__title">Acerca de la Revista</h1>', $html );
		$this->assertStringContainsString( 'content-main', $html );
		$this->assertStringContainsString( 'Cuerpo canónico de la página.', $html );
		$this->assertStringContainsString( '<aside class="sidebar">', $html );
		$this->assertLessThan( strpos( $html, '<aside class="sidebar">' ), strpos( $html, 'Cuerpo canónico de la página.' ) );
	}

	/**
	 * Dado: la portada resuelta con front-page.html y sin datos.
	 * Entonces: héroe y rejilla lateral sin número actual ni artículos
	 * recientes; la plantilla no crea contenido.
	 */
	public function test_front_page_template_renders_without_data() {
		$front_id = $this->make_page( 'inicio', 'Inicio' );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_id );
		$this->go_to( home_url( '/' ) );

		$html = $this->render_template( 'front-page' );

		$this->assertSame( 1, substr_count( $html, '<main' ) );
		$this->assertStringContainsString( '<section class="front-page__hero">', $html );
		$this->assertStringContainsString( '<section class="front-page__sidebar">', $html );
		$this->assertStringNotContainsString( 'front-page__current-issue', $html );
		$this->assertStringNotContainsString( 'front-page__recent-articles', $html );
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
	 * @param string $slug Template slug.
	 * @return string[] Block names in document order.
	 */
	private function template_block_names( $slug ) {
		return wp_list_pluck( $this->template_blocks( $slug ), 'blockName' );
	}

	/**
	 * @param string   $block   Block name without namespace.
	 * @param array    $attrs   Attributes.
	 * @param int|null $post_id Context post.
	 * @return string
	 */
	private function render( $block, $attrs = array(), $post_id = null ) {
		$parsed  = array(
			'blockName'    => 'revistalogos/' . $block,
			'attrs'        => $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
		$context = null === $post_id ? array() : array(
			'postId'   => $post_id,
			'postType' => get_post_type( $post_id ),
		);

		return ( new WP_Block( $parsed, $context ) )->render();
	}

	/**
	 * @param string $slug Theme template part.
	 * @param array  $args Part args.
	 * @return string
	 */
	private function part( $slug, $args = array() ) {
		ob_start();
		get_template_part( $slug, null, $args );

		return (string) ob_get_clean();
	}

	/**
	 * @param string $name    Page slug.
	 * @param string $title   Page title.
	 * @param string $excerpt Optional excerpt.
	 * @return int Published page ID.
	 */
	private function make_page( $name, $title, $excerpt = '' ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => $name,
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_content' => '<!-- wp:paragraph --><p>Cuerpo canónico de la página.</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
			)
		);
	}
}
