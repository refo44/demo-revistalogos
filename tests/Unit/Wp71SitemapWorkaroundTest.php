<?php
/**
 * Version gate for the WordPress 7.1 native sitemap 404 workaround
 * (Trac #65945). Pure: no WordPress boot.
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

use function Revistalogos_Core\needs_wp71_sitemap_workaround;

/**
 * The workaround is only for the 7.1 series, before the 7.1.1 Core fix.
 */
class Wp71SitemapWorkaroundTest extends TestCase {

	/**
	 * @dataProvider versions_that_need_the_workaround
	 *
	 * @param string $version WordPress version string.
	 */
	public function test_workaround_applies_only_to_the_7_1_series_before_7_1_1( $version ) {
		$this->assertTrue( needs_wp71_sitemap_workaround( $version ) );
	}

	/**
	 * @dataProvider versions_that_must_not_use_the_workaround
	 *
	 * @param string $version WordPress version string.
	 */
	public function test_workaround_does_not_apply_outside_7_1_before_patch( $version ) {
		$this->assertFalse( needs_wp71_sitemap_workaround( $version ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function versions_that_need_the_workaround() {
		return array(
			'7.1'   => array( '7.1' ),
			'7.1.0' => array( '7.1.0' ),
		);
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function versions_that_must_not_use_the_workaround() {
		return array(
			'6.4'   => array( '6.4' ),
			'7.0.4' => array( '7.0.4' ),
			'7.1.1' => array( '7.1.1' ),
			'7.2'   => array( '7.2' ),
		);
	}
}
