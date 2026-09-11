<?php
/**
 * First-party download count for the issue PDF only (ADR 0011:
 * no paid WP Statistics addon). Ver and Descargar both count.
 * Article PDFs are out of scope.
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Issue_Pdf_Downloads;

/**
 * Protects increment and resolve of the numbered issue PDF.
 */
class IssuePdfDownloadsTest extends WP_UnitTestCase {

	/**
	 * Dado: un número publicado con PDF.
	 * Cuando: se resuelve una descarga (Ver o Descargar).
	 * Entonces: el conteo sube y la URL es la del adjunto.
	 */
	public function test_resolve_increments_and_returns_attachment_url() {
		$issue_id = $this->make_published_issue_with_pdf();

		$this->assertSame( 0, Issue_Pdf_Downloads::count( $issue_id ) );

		$resolved = Issue_Pdf_Downloads::resolve( $issue_id );

		$this->assertTrue( $resolved['ok'] );
		$this->assertSame( 1, $resolved['count'] );
		$this->assertSame( 1, Issue_Pdf_Downloads::count( $issue_id ) );
		$this->assertNotSame( '', $resolved['url'] );
	}

	/**
	 * Dado: un número publicado sin PDF.
	 * Entonces: no hay descarga y el conteo no cambia.
	 */
	public function test_resolve_without_pdf_does_not_increment() {
		$issue_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_status' => 'publish',
				'post_title'  => 'Número sin PDF',
			)
		);

		$resolved = Issue_Pdf_Downloads::resolve( $issue_id );

		$this->assertFalse( $resolved['ok'] );
		$this->assertSame( 0, Issue_Pdf_Downloads::count( $issue_id ) );
	}

	/**
	 * @return int Issue ID.
	 */
	private function make_published_issue_with_pdf() {
		$issue_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_status' => 'publish',
				'post_title'  => 'Número con PDF',
			)
		);

		$upload = wp_upload_bits( 'numero-qa.pdf', null, '%PDF-1.4 qa' );
		$this->assertEmpty( $upload['error'] ?? '', wp_json_encode( $upload ) );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'application/pdf',
				'post_title'     => 'numero-qa',
				'post_status'    => 'inherit',
				'post_parent'    => $issue_id,
			),
			$upload['file'],
			$issue_id
		);
		$this->assertNotWPError( $attachment_id );
		update_post_meta( $issue_id, 'pdf_file', $attachment_id );

		return $issue_id;
	}
}
