<?php
/**
 * X / Twitter large-card asset (1200×630).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;

/**
 * summary_large_image needs ~2:1. The journal logo is 1:1 and X
 * draws no card for that combination.
 */
class TwitterCardImageTest extends TestCase {

	/**
	 * Dado: la tarjeta social de X.
	 * Entonces: JPEG 1200×630.
	 */
	public function test_twitter_card_is_jpeg_at_large_card_ratio() {
		$card = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/assets/img/og-twitter-card.jpg';

		$this->assertFileIsReadable( $card );

		$info = getimagesize( $card );

		$this->assertNotFalse( $info );
		$this->assertSame( IMAGETYPE_JPEG, $info[2] );
		$this->assertSame( 1200, $info[0] );
		$this->assertSame( 630, $info[1] );
	}
}
