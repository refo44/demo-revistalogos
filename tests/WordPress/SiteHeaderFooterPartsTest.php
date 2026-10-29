<?php
/**
 * FSE header and footer parts (ADR 0015 §7.5, Session 3): parts/header.html
 * and parts/footer.html are thin shells around theme blocks that keep the
 * static markup. The header nav still comes from Revistalogos_Nav_Walker,
 * never from core Navigation; the footer keeps footer-quick / footer-norms.
 *
 * @package Revistalogos
 */

// functions.php is not loaded here; site-blocks.php versions its editor script.
defined( 'REVISTALOGOS_THEME_VERSION' ) || define( 'REVISTALOGOS_THEME_VERSION', 'test' );

$revistalogos_theme_inc = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc';
require_once $revistalogos_theme_inc . '/template-tags.php';
require_once $revistalogos_theme_inc . '/class-nav-walker.php';
require_once $revistalogos_theme_inc . '/site-blocks.php';

/**
 * Locks the header/footer part contract for block templates.
 */
class SiteHeaderFooterPartsTest extends WP_UnitTestCase {

	private const THEME = 'revistalogos';

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
		remove_theme_mod( 'nav_menu_locations' );
		switch_theme( $this->previous_stylesheet );
		wp_clean_theme_json_cache();
		parent::tear_down();
	}

	/**
	 * Dado: el theme activo.
	 * Entonces: el Site Editor ve las partes header y footer en sus áreas.
	 */
	public function test_header_and_footer_parts_are_registered_in_their_areas() {
		$header = get_block_template( self::THEME . '//header', 'wp_template_part' );
		$footer = get_block_template( self::THEME . '//footer', 'wp_template_part' );

		$this->assertNotNull( $header );
		$this->assertSame( 'header', $header->area );
		$this->assertNotNull( $footer );
		$this->assertSame( 'footer', $footer->area );
	}

	/**
	 * Dado: las partes del theme.
	 * Entonces: cada una es un cascarón con un solo bloque del theme, sin
	 * core/navigation ni marcado propio.
	 */
	public function test_parts_are_thin_shells_around_the_theme_blocks() {
		$this->assertSame( array( 'revistalogos/site-header' ), $this->part_block_names( 'header' ) );
		$this->assertSame( array( 'revistalogos/site-footer' ), $this->part_block_names( 'footer' ) );
	}

	/**
	 * Dado: un menú asignado a primary con un submenú.
	 * Entonces: el bloque de cabecera emite skip-link, header__* y el nav
	 * del walker (nav__item--has-submenu, nav__submenu, nav__link).
	 */
	public function test_header_block_renders_skip_link_and_walker_nav_for_assigned_menu() {
		$menu_id = wp_create_nav_menu( 'Principal' );
		$parent  = $this->add_menu_link( $menu_id, 'Revista', 'https://example.org/numeros/' );
		$this->add_menu_link( $menu_id, 'Autores', 'https://example.org/autores/', $parent );
		set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id ) );

		$html = do_blocks( '<!-- wp:revistalogos/site-header /-->' );

		$this->assertStringContainsString( '<a href="#main-content" class="skip-link">', $html );
		$this->assertStringContainsString( '<header class="header" role="banner">', $html );
		$this->assertStringContainsString( 'class="header__logo"', $html );
		$this->assertStringContainsString( 'class="nav__toggle"', $html );
		$this->assertStringContainsString( '<ul id="main-nav" class="nav__list nav__list--main">', $html );
		$this->assertStringContainsString( '<li class="nav__item nav__item--has-submenu">', $html );
		$this->assertStringContainsString( '<ul class="nav__submenu">', $html );
		$this->assertStringContainsString( 'class="nav__link" aria-haspopup="true" aria-expanded="false">Revista</a>', $html );
		$this->assertStringContainsString( '>Autores</a>', $html );
		$this->assertStringNotContainsString( 'wp-block-navigation', $html );
		$this->assertLessThan( strpos( $html, '<header' ), strpos( $html, 'skip-link' ) );
	}

	/**
	 * Dado: ningún menú en primary.
	 * Entonces: el bloque usa el menú de respaldo ya publicado, sin fatal.
	 */
	public function test_header_block_falls_back_without_primary_menu() {
		$html = do_blocks( '<!-- wp:revistalogos/site-header /-->' );

		$this->assertStringContainsString( '<ul class="nav__list nav__list--main" id="main-nav">', $html );
		$this->assertStringContainsString( '>Inicio</a>', $html );
		$this->assertStringContainsString( '<ul class="nav__submenu">', $html );
	}

	/**
	 * Dado: footer-quick asignado y footer-norms sin menú.
	 * Entonces: el pie usa el walker del footer en uno y el respaldo en el
	 * otro, ambos con footer__list / footer__link.
	 */
	public function test_footer_block_renders_assigned_menu_and_fallback() {
		$menu_id = wp_create_nav_menu( 'Enlaces rápidos' );
		$this->add_menu_link( $menu_id, 'Números publicados', 'https://example.org/numeros/' );
		set_theme_mod( 'nav_menu_locations', array( 'footer-quick' => $menu_id ) );

		$html = do_blocks( '<!-- wp:revistalogos/site-footer /-->' );

		$this->assertStringContainsString( '<footer class="footer" role="contentinfo">', $html );
		$this->assertStringContainsString( 'class="footer__link">Números publicados</a>', $html );
		$this->assertStringContainsString( '>Normas de Publicación</a>', $html );
		$this->assertSame( 2, substr_count( $html, 'class="footer__list"' ) );
	}

	/**
	 * Dado: la plantilla index.html.
	 * Entonces: compone cabecera, main#main-content y pie en ese orden, sin
	 * envolver la cabecera en un segundo <header>.
	 */
	public function test_index_template_composes_parts_around_main_content() {
		$template = get_block_template( self::THEME . '//index' );
		$html     = do_blocks( $template->content );

		$header = strpos( $html, '<header class="header"' );
		$main   = strpos( $html, '<main id="main-content"' );
		$footer = strpos( $html, '<footer class="footer"' );

		$this->assertNotFalse( $header );
		$this->assertNotFalse( $main );
		$this->assertNotFalse( $footer );
		$this->assertLessThan( $main, $header );
		$this->assertLessThan( $footer, $main );
		$this->assertSame( 1, substr_count( $html, '<header' ) );
		$this->assertSame( 1, substr_count( $html, '<footer' ) );
		$this->assertSame( 1, substr_count( $html, 'skip-link' ) );
	}

	/**
	 * Dado: el theme carga sus bloques.
	 * Entonces: el skip-link de núcleo queda fuera; el de la cabecera manda.
	 */
	public function test_core_block_template_skip_link_is_unhooked() {
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' ) );
	}

	/**
	 * @param string $slug Part slug.
	 * @return string[] Top-level block names, whitespace dropped.
	 */
	private function part_block_names( $slug ) {
		$part = get_block_template( self::THEME . '//' . $slug, 'wp_template_part' );
		$this->assertNotNull( $part );

		return array_values( array_filter( wp_list_pluck( parse_blocks( $part->content ), 'blockName' ) ) );
	}

	/**
	 * @param int    $menu_id   Menu term ID.
	 * @param string $title     Item title.
	 * @param string $url       Item URL.
	 * @param int    $parent_id Parent item ID.
	 * @return int Menu item ID.
	 */
	private function add_menu_link( $menu_id, $title, $url, $parent_id = 0 ) {
		return wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-url'       => $url,
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'custom',
				'menu-item-parent-id' => $parent_id,
			)
		);
	}
}
