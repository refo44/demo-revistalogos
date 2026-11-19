<?php
/**
 * Site Editor canvas: the template preview carries the front layout
 * (main.css and its imports) and pages open with their template visible.
 *
 * @package Revistalogos
 */

if ( ! defined( 'REVISTALOGOS_THEME_VERSION' ) ) {
	define( 'REVISTALOGOS_THEME_VERSION', 'test' );
}

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc/site-blocks.php';

class SiteEditorCanvasStylesTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		switch_theme( 'revistalogos' );
		remove_editor_styles();
	}

	public function tear_down() {
		remove_editor_styles();
		parent::tear_down();
	}

	public function test_editor_style_files_follow_main_css_imports_in_order() {
		$files = revistalogos_editor_style_files();

		$this->assertSame( 'assets/css/tokens.css', $files[0] );
		$this->assertSame( 'assets/css/utilities.css', end( $files ) );
		$this->assertContains( 'assets/css/pages/home.css', $files );

		foreach ( $files as $file ) {
			$this->assertStringNotContainsString( '?', $file );
			$this->assertFileExists( get_template_directory() . '/' . $file );
		}
	}

	public function test_editor_styles_load_each_imported_file() {
		revistalogos_add_editor_styles();

		$stylesheets = get_editor_stylesheets();

		$this->assertNotEmpty( $stylesheets );
		$this->assertStringEndsWith( '/assets/css/tokens.css', $stylesheets[0] );
		foreach ( $stylesheets as $stylesheet ) {
			$this->assertStringNotContainsString( '@import', (string) file_get_contents( get_template_directory() . substr( $stylesheet, strlen( get_template_directory_uri() ) ) ) );
		}
	}

	public function test_pages_open_with_their_template_visible() {
		revistalogos_page_editor_default_mode();

		$supports = get_all_post_type_supports( 'page' );

		$this->assertIsArray( $supports['editor'] );
		$this->assertSame( 'template-locked', $supports['editor'][0]['default-mode'] );
	}
}
