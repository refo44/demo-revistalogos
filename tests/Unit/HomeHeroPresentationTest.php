<?php
/**
 * Home hero copy and banner contract (issue #40).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * The journal name lives in the HTML title only; the banner stays letter-free
 * at the hero aspect ratio.
 */
class HomeHeroPresentationTest extends TestCase {

	/**
	 * Owner-directed copy: drop only "LOGO ET SPES" from the hero paragraph.
	 *
	 * @ticket 40
	 */
	public function test_home_hero_description_omits_journal_name_already_in_the_title() {
		$front_page = $this->theme_file_contents( 'front-page.php' );
		$copy_sheet = $this->repo_file_contents( 'docs/09-ui-copy-sheet.md' );

		$this->assertStringContainsString( 'LOGO ET SPES', $front_page );
		$this->assertStringContainsString( 'Revista de Filosofía', $front_page );
		$this->assertStringContainsString(
			'La Revista de Filosofía adscrita, auspiciada y editada por el Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS, es una publicación digital venezolana enfocada en el pensamiento filosófico multidisciplinar. Es de acceso abierto; arbitrada bajo la modalidad <doble anónimo o doble ciego>; con periodicidad anual. Sus páginas están disponibles para difundir investigaciones originales -de autores nacionales e internacionales- que coadyuven a promover el desarrollo de todas las áreas de la Filosofía.',
			$front_page
		);
		$this->assertStringNotContainsString( 'La Revista de Filosofía LOGO ET SPES', $front_page );
		$this->assertStringNotContainsString( 'La Revista de Filosofía LOGO ET SPES', $copy_sheet );
		$this->assertStringContainsString(
			'La Revista de Filosofía adscrita, auspiciada y editada',
			$copy_sheet
		);
	}

	/**
	 * CSS `.hero::before` is locked to 1714 × 356; a 16:9 export would break the strip.
	 *
	 * @ticket 40
	 */
	public function test_home_hero_banner_is_jpeg_at_hero_aspect_ratio() {
		$banner = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/assets/img/banner-main.jpg';

		$this->assertFileIsReadable( $banner );

		$info = getimagesize( $banner );

		$this->assertNotFalse( $info );
		$this->assertSame( IMAGETYPE_JPEG, $info[2] );
		$this->assertSame( 1714, $info[0] );
		$this->assertSame( 356, $info[1] );
	}

	/**
	 * @param string $relative Path under the theme root.
	 */
	private function theme_file_contents( $relative ) {
		return $this->repo_file_contents( 'wordpress/wp-content/themes/revistalogos/' . $relative );
	}

	/**
	 * @param string $relative Path from the repository root.
	 */
	private function repo_file_contents( $relative ) {
		$path = dirname( __DIR__, 2 ) . '/' . $relative;
		$this->assertFileIsReadable( $path );

		$contents = file_get_contents( $path );
		$this->assertNotFalse( $contents );

		return $contents;
	}
}
