<?php
/**
 * FSE bootstrap contract (ADR 0015 §3/§7.2, Session 2): the theme is a
 * block theme, Estilos → Colores reaches the front through the palette
 * presets, and the classic PHP views keep main.css as layout authority.
 *
 * @package Revistalogos
 */

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc/foreign-styles.php';

/**
 * Locks what Session 2 changes and what it must not unlock.
 */
class BlockThemeBootstrapTest extends WP_UnitTestCase {

	private const THEME = 'revistalogos';

	/**
	 * @var string
	 */
	private $previous_stylesheet;

	public function set_up() {
		parent::set_up();
		$this->previous_stylesheet = get_stylesheet();
		switch_theme( self::THEME );
		$this->reset_theme_caches();
	}

	public function tear_down() {
		switch_theme( $this->previous_stylesheet );
		$this->reset_theme_caches();
		parent::tear_down();
	}

	/**
	 * Dado: el theme revistalogos activo.
	 * Entonces: WordPress lo trata como block theme (abre el Site Editor).
	 */
	public function test_revistalogos_is_a_block_theme() {
		$this->assertTrue( wp_is_block_theme() );
	}

	/**
	 * Dado: el theme activo.
	 * Entonces: Estilos no libera color por bloque, tipografía ni espaciado.
	 */
	public function test_styles_keep_custom_color_typography_and_spacing_locked() {
		$settings = wp_get_global_settings();

		$this->assertFalse( $settings['color']['custom'] );
		$this->assertFalse( $settings['typography']['customFontSize'] );
		$this->assertFalse( $settings['spacing']['customSpacingSize'] );
	}

	/**
	 * Dado: el front encola sus estilos.
	 * Entonces: global-styles llega con el preset primary y la librería
	 * de bloques sigue fuera (main.css manda en el layout).
	 */
	public function test_front_end_keeps_global_styles_and_drops_block_library() {
		wp_enqueue_style( 'wp-block-library' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_style_is( 'global-styles', 'enqueued' ) );
		$this->assertStringContainsString( '--wp--preset--color--primary: #18597c', $this->global_styles_css() );
		$this->assertFalse( wp_style_is( 'wp-block-library', 'enqueued' ) );
	}

	/**
	 * Dado: la paleta de theme.json.
	 * Entonces: cada color tiene su --color-* en tokens.css, alias del
	 * preset con el mismo hex de respaldo (una sola paleta).
	 */
	public function test_tokens_alias_every_palette_color_to_its_preset() {
		$palette = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
		$tokens  = file_get_contents( get_template_directory() . '/assets/css/tokens.css' );

		$this->assertNotEmpty( $palette );
		foreach ( $palette as $color ) {
			$alias = sprintf( '--color-%1$s: var(--wp--preset--color--%1$s, %2$s);', $color['slug'], $color['color'] );
			$this->assertStringContainsString( $alias, $tokens );
		}
	}

	private function global_styles_css() {
		$inline = wp_styles()->get_data( 'global-styles', 'after' );

		return is_array( $inline ) ? implode( "\n", $inline ) : '';
	}

	private function reset_theme_caches() {
		wp_clean_theme_json_cache();
		$GLOBALS['wp_styles'] = null;
	}
}
