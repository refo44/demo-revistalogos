<?php
/**
 * Persist generated article PDF bytes as a Media Library attachment
 * (ADR 0017 work unit 5).
 *
 * Writes a normal application/pdf attachment and stores its ID in
 * Article pdf_file. Does not decide keep/generate, does not publish,
 * and does not register hooks.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generated PDF bytes → Media Library attachment → pdf_file ID.
 */
class Article_Pdf_WordPress_Persister {

	/**
	 * Persist generated PDF bytes against an Article.
	 *
	 * @param mixed $article_id Article post ID.
	 * @param mixed $pdf_bytes  In-memory PDF bytes.
	 * @return int|\WP_Error Attachment ID on success.
	 */
	public function persist( $article_id, $pdf_bytes ) {
		$article = $this->validate_article( $article_id );
		if ( is_wp_error( $article ) ) {
			return $article;
		}

		$artifact_error = $this->validate_artifact( $pdf_bytes );
		if ( is_wp_error( $artifact_error ) ) {
			return $artifact_error;
		}

		$upload = wp_upload_bits(
			$this->filename_for_article( $article ),
			null,
			$pdf_bytes
		);
		if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
			return new \WP_Error(
				'article_pdf_upload_failed',
				'Could not upload generated PDF.'
			);
		}

		$file = $upload['file'];
		if ( ! is_string( $file ) || ! is_readable( $file ) ) {
			if ( is_string( $file ) ) {
				$this->cleanup_uploaded_file( $file );
			}

			return new \WP_Error(
				'article_pdf_upload_failed',
				'Could not upload generated PDF.'
			);
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'application/pdf',
				'post_title'     => $this->title_for_article( $article ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_parent'    => (int) $article->ID,
			),
			$file,
			(int) $article->ID
		);

		if ( is_wp_error( $attachment_id ) || (int) $attachment_id <= 0 ) {
			$this->cleanup_uploaded_file( $file );
			return new \WP_Error(
				'article_pdf_attachment_failed',
				'Could not create PDF attachment.'
			);
		}

		$attachment_id = (int) $attachment_id;
		$this->maybe_store_attachment_metadata( $attachment_id, $file );

		update_post_meta( (int) $article->ID, 'pdf_file', $attachment_id );
		if ( (int) get_post_meta( (int) $article->ID, 'pdf_file', true ) !== $attachment_id ) {
			$this->cleanup_new_attachment( $attachment_id );
			return new \WP_Error(
				'article_pdf_association_failed',
				'Could not associate PDF attachment with the article.'
			);
		}

		return $attachment_id;
	}

	/**
	 * @param mixed $article_id Raw article ID.
	 * @return \WP_Post|\WP_Error
	 */
	private function validate_article( $article_id ) {
		$article_id = absint( $article_id );
		if ( $article_id <= 0 ) {
			return new \WP_Error(
				'article_pdf_invalid_article',
				'Invalid article.'
			);
		}

		$article = get_post( $article_id );
		if ( ! $article || Content_Types::ARTICLE !== $article->post_type ) {
			return new \WP_Error(
				'article_pdf_invalid_article',
				'Invalid article.'
			);
		}

		return $article;
	}

	/**
	 * @param mixed $pdf_bytes Raw renderer output.
	 * @return \WP_Error|null
	 */
	private function validate_artifact( $pdf_bytes ) {
		if ( ! is_string( $pdf_bytes ) || '' === $pdf_bytes ) {
			return new \WP_Error(
				'article_pdf_invalid_artifact',
				'Invalid PDF artifact.'
			);
		}

		if ( 0 !== strpos( $pdf_bytes, '%PDF-' ) ) {
			return new \WP_Error(
				'article_pdf_invalid_artifact',
				'Invalid PDF artifact.'
			);
		}

		return null;
	}

	/**
	 * @param \WP_Post $article Article post.
	 * @return string
	 */
	private function filename_for_article( $article ) {
		$base = '';
		if ( ! empty( $article->post_name ) ) {
			$base = $article->post_name;
		} elseif ( ! empty( $article->post_title ) ) {
			$base = $article->post_title;
		}

		$base = sanitize_file_name( $base );
		if ( '' === $base ) {
			$base = 'article-' . (int) $article->ID;
		}

		return $base . '.pdf';
	}

	/**
	 * @param \WP_Post $article Article post.
	 * @return string
	 */
	private function title_for_article( $article ) {
		$title = is_string( $article->post_title ) ? trim( $article->post_title ) : '';
		if ( '' === $title ) {
			$title = 'article-' . (int) $article->ID;
		}

		return $title;
	}

	/**
	 * Preview metadata is optional. Persistence must not fail if WordPress
	 * cannot generate a PDF thumbnail.
	 *
	 * @param int    $attachment_id New attachment ID from this call.
	 * @param string $file          Absolute uploaded path from this call.
	 */
	private function maybe_store_attachment_metadata( $attachment_id, $file ) {
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		try {
			$metadata = wp_generate_attachment_metadata( $attachment_id, $file );
		} catch ( \Throwable $e ) {
			return;
		}

		if ( ! is_array( $metadata ) || array() === $metadata ) {
			return;
		}

		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	/**
	 * Remove only the file created by this failed upload attempt.
	 *
	 * @param string $file Absolute path written by wp_upload_bits.
	 */
	private function cleanup_uploaded_file( $file ) {
		if ( ! is_string( $file ) || '' === $file || ! is_file( $file ) ) {
			return;
		}

		wp_delete_file( $file );
	}

	/**
	 * Remove only the attachment created by this failed persist attempt.
	 *
	 * @param int $attachment_id Attachment created in this call.
	 */
	private function cleanup_new_attachment( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( $attachment_id <= 0 ) {
			return;
		}

		wp_delete_attachment( $attachment_id, true );
	}
}
