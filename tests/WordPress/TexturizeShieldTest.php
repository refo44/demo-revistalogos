<?php
/**
 * Core runs wptexturize() over every block template and template part
 * (get_the_block_template_html()). Classic views never texturize their
 * own escaped text, so without a shield a theme block turns « - » into
 * an en dash, «--» into an em dash, «...» into an ellipsis and «3x4»
 * into «3×4». The theme shields the text of its own blocks with
 * entities texturize does not match; the browser shows the same text.
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
 * Locks the texturize shield of theme block output.
 */
class TexturizeShieldTest extends WP_UnitTestCase {

	/**
	 * Editorial text that wptexturize() would change.
	 */
	private const SAMPLES = array(
		'Hope - an ontological category',
		'tesis -- argumento',
		'a---b',
		'Espera...',
		'pp. 12-34',
		'3x4 cm',
		"l'esperance \"citada\"",
		'``doble\'\'',
		"'tis",
		'Marca (tm)',
		'xn--caf-dma',
	);

	/**
	 * Dado: texto escapado con guiones, puntos suspensivos, comillas o
	 * «NxN», protegido y luego pasado por wptexturize().
	 * Entonces: al decodificar, el texto es el original.
	 */
	public function test_shielded_text_survives_texturize() {
		foreach ( self::SAMPLES as $sample ) {
			$html = wptexturize( '<p>' . revistalogos_shield_from_texturize( esc_html( $sample ) ) . '</p>' );

			$this->assertSame( '<p>' . $sample . '</p>', html_entity_decode( $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $sample );
		}
	}

	/**
	 * Dado: etiquetas, atributos, comentarios de bloque y los elementos que
	 * wptexturize() no toca (script, style, pre, code anidado).
	 * Entonces: el escudo solo toca el texto que wptexturize() cambiaría.
	 */
	public function test_shield_leaves_markup_scripts_and_styles_alone() {
		$markup = '<a href="/a-b/" class="x-y"><!-- wp:x {"a":"b-c"} --></a>'
			. '<script>var a = 1 - 2; "...";</script><style>.a-b::after{content:"--"}</style>';

		$this->assertSame( $markup, revistalogos_shield_from_texturize( $markup ) );
		$this->assertSame(
			'<p class="a-b">a &#45; b</p><script>x--;</script><pre>TY  - <code>a - b</code> - JOUR</pre><p>c&#45;&#45;d</p>',
			revistalogos_shield_from_texturize( '<p class="a-b">a - b</p><script>x--;</script><pre>TY  - <code>a - b</code> - JOUR</pre><p>c--d</p>' )
		);
	}

	/**
	 * Dado: la salida de un bloque del tema o del plugin, y la de un
	 * bloque de core.
	 * Entonces: render_block protege solo los bloques revistalogos/*.
	 */
	public function test_render_block_shields_only_journal_blocks() {
		$this->assertSame( 10, has_filter( 'render_block', 'revistalogos_shield_block_from_texturize' ) );

		foreach ( array( 'revistalogos/view', 'revistalogos/issue-card' ) as $name ) {
			$this->assertSame( '<p>a &#45; b</p>', revistalogos_shield_block_from_texturize( '<p>a - b</p>', array( 'blockName' => $name ) ), $name );
		}
		foreach ( array( 'core/paragraph', null ) as $name ) {
			$this->assertSame( '<p>a - b</p>', revistalogos_shield_block_from_texturize( '<p>a - b</p>', array( 'blockName' => $name ) ), (string) $name );
		}
	}
}
