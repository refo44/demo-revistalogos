<?php
/**
 * The theme declares its version twice: the style.css header (what
 * wp-admin shows) and REVISTALOGOS_THEME_VERSION (the ?ver= of main.css
 * and main.js). They must move together.
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * Locks the theme version constant to the style.css header.
 */
class ThemeVersionTest extends TestCase {

	/**
	 * Dado: el theme que se despliega.
	 * Entonces: REVISTALOGOS_THEME_VERSION coincide con `Version:` de
	 * style.css. Si no, el CSS nuevo se sirve con el ?ver= viejo y los
	 * navegadores conservan la copia anterior.
	 */
	public function test_version_constant_matches_the_style_css_header() {
		$theme = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/';

		$this->assertSame( 1, preg_match( '/^Version:\s*(\S+)\s*$/m', (string) file_get_contents( $theme . 'style.css' ), $header ) );
		$this->assertSame( 1, preg_match( "/define\(\s*'REVISTALOGOS_THEME_VERSION',\s*'([^']+)'\s*\);/", (string) file_get_contents( $theme . 'functions.php' ), $constant ) );
		$this->assertSame( $header[1], $constant[1] );
	}
}
