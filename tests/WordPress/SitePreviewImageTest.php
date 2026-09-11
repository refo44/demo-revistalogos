<?php
/**
 * Site preview image for Google / Open Graph (homepage fallback to
 * the journal logo; featured image wins on singular views).
 *
 * @package Revistalogos
 */

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc/metadata-output.php';

/**
 * Protects the discoverable preview image that Search Console reads
 * from the front page. The static URL /assets/img/logo-revista.png
 * is a 404 on WordPress; the theme logo is the replacement.
 */
class SitePreviewImageTest extends WP_UnitTestCase {

	/**
	 * Dado: la portada es una página sin imagen destacada.
	 * Cuando: se emiten los metadatos del documento.
	 * Entonces: og:image apunta al logo de la revista.
	 */
	public function test_front_page_without_featured_image_emits_journal_logo_as_og_image() {
		$this->make_static_front_page();

		$this->go_to( home_url( '/' ) );

		$this->assertTrue( is_front_page() );
		$this->assertFalse( has_post_thumbnail() );

		$html = $this->render_head_metadata();
		$logo = $this->journal_logo_url();

		$this->assertStringContainsString( 'property="og:image"', $html );
		$this->assertStringContainsString( $logo, $html );
		$this->assertStringContainsString( 'name="twitter:card"', $html );
		$this->assertStringContainsString( 'content="summary"', $html );
		$this->assertStringNotContainsString( 'summary_large_image', $html );
		$this->assertStringContainsString( 'name="twitter:image"', $html );
		$this->assertSame( 2, substr_count( $html, $logo ) );
	}

	/**
	 * Dado: la portada sin imagen destacada.
	 * Entonces: el Periodical JSON-LD declara la misma imagen.
	 */
	public function test_front_page_schema_includes_journal_logo() {
		$this->make_static_front_page();

		$this->go_to( home_url( '/' ) );

		$html   = $this->render_schema_metadata();
		$schema = $this->json_ld_from( $html );

		$this->assertSame( 'Periodical', $schema['@graph'][0]['@type'] );
		$this->assertSame( $this->journal_logo_url(), $schema['@graph'][0]['image'] );
	}

	/**
	 * Dado: un contenido singular con imagen destacada.
	 * Entonces: og:image es esa imagen, no el logo.
	 */
	public function test_singular_with_featured_image_emits_that_image() {
		$page_id       = $this->make_published_page( 'normas', 'Normas' );
		$attachment_id = $this->make_image_attachment( $page_id, 'portada-preview' );
		set_post_thumbnail( $page_id, $attachment_id );

		$this->go_to( get_permalink( $page_id ) );

		$html     = $this->render_head_metadata();
		$featured = wp_get_attachment_image_url( $attachment_id, 'full' );

		$this->assertNotEmpty( $featured );
		$this->assertStringContainsString( 'property="og:image"', $html );
		$this->assertStringContainsString( 'name="twitter:image"', $html );
		$this->assertStringContainsString( $featured, $html );
		$this->assertStringNotContainsString( $this->journal_logo_url(), $html );
	}

	/**
	 * Dado: una imagen 1:1 (el logo de la revista).
	 * Entonces: X recibe summary, no summary_large_image.
	 */
	public function test_square_preview_uses_summary_card_not_large_image() {
		$this->assertSame( 'summary', revistalogos_twitter_card_type( 1024, 1024 ) );
		$this->assertSame( 'summary', revistalogos_twitter_card_type( 144, 144 ) );
	}

	/**
	 * Dado: una imagen apaisada de al menos 300×157.
	 * Entonces: X puede usar summary_large_image.
	 */
	public function test_landscape_preview_uses_summary_large_image_card() {
		$this->assertSame( 'summary_large_image', revistalogos_twitter_card_type( 1200, 630 ) );
		$this->assertSame( 'summary_large_image', revistalogos_twitter_card_type( 300, 157 ) );
	}

	/**
	 * @return string Absolute theme-logo URL Search Console should fetch.
	 */
	private function journal_logo_url() {
		return get_theme_root_uri() . '/revistalogos/assets/img/logo-revista.png';
	}

	/**
	 * @return void
	 */
	private function make_static_front_page() {
		$front_id = $this->make_published_page( 'inicio', 'Inicio' );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_id );
	}

	/**
	 * @param string $slug  Page slug.
	 * @param string $title Page title.
	 * @return int
	 */
	private function make_published_page( $slug, $title ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $slug,
				'post_title'  => $title,
			)
		);
	}

	/**
	 * @param int    $parent_id Parent post ID.
	 * @param string $slug      Filename stem.
	 * @return int
	 */
	private function make_image_attachment( $parent_id, $slug ) {
		$logo   = get_theme_root() . '/revistalogos/assets/img/logo-revista.png';
		$binary = file_get_contents( $logo );
		$this->assertNotFalse( $binary );

		$upload = wp_upload_bits( $slug . '.png', null, $binary );
		$this->assertEmpty( $upload['error'] ?? '', wp_json_encode( $upload ) );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/png',
				'post_title'     => $slug,
				'post_status'    => 'inherit',
				'post_parent'    => (int) $parent_id,
			),
			$upload['file'],
			(int) $parent_id
		);
		$this->assertFalse( is_wp_error( $attachment_id ) );
		$this->assertGreaterThan( 0, (int) $attachment_id );

		return (int) $attachment_id;
	}

	/**
	 * @return string
	 */
	private function render_head_metadata() {
		ob_start();
		revistalogos_head_metadata();
		return (string) ob_get_clean();
	}

	/**
	 * @return string
	 */
	private function render_schema_metadata() {
		ob_start();
		revistalogos_schema_metadata();
		return (string) ob_get_clean();
	}

	/**
	 * @param string $html Document fragment with JSON-LD.
	 * @return array<string, mixed>
	 */
	private function json_ld_from( $html ) {
		$this->assertSame( 1, preg_match( '/<script type="application\/ld\+json">(.+)<\/script>/s', $html, $matches ) );
		$schema = json_decode( $matches[1], true );
		$this->assertIsArray( $schema );
		return $schema;
	}
}
