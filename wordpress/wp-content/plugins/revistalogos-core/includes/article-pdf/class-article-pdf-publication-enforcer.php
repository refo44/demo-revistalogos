<?php
/**
 * Wire Article PDF enforcement to classic and REST publication
 * (ADR 0017 work unit 6B).
 *
 * Composes the setting, adapter and generator. Does not reimplement
 * WU1–WU6A. Author validation remains owned by Meta_Boxes at
 * priority 10; this class runs at priority 11.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apply the shared enforcement option to a publish transition.
 */
class Article_Pdf_Publication_Enforcer {

	const ERROR_CODE   = 'article_pdf_publication_blocked';
	const NOTICE_CODE  = 'cannot_publish_pdf';
	const ERROR_STATUS = 400;

	/**
	 * REST path already performed PDF side effects for this request.
	 *
	 * @var bool
	 */
	private static $handled_in_rest = false;

	/**
	 * Generated pdf_file IDs that later meta save must not clear.
	 *
	 * @var array<int, int>
	 */
	private static $protected_pdf_ids = array();

	/**
	 * Request-local classic notice code.
	 *
	 * @var string
	 */
	private static $notice = '';

	/**
	 * @var Article_Pdf_WordPress_Generator
	 */
	private static $generator;

	/**
	 * Register publication hooks. Does not mutate Articles on load.
	 */
	public static function register_hooks() {
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'guard_classic_publication' ), 11, 2 );
		add_filter( 'rest_pre_insert_' . Content_Types::ARTICLE, array( __CLASS__, 'guard_rest_publication' ), 11, 2 );
		add_action( 'rest_after_insert_' . Content_Types::ARTICLE, array( __CLASS__, 'clear_rest_pdf_guard' ) );
		add_filter( 'redirect_post_location', array( __CLASS__, 'append_notice_query_arg' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );
	}

	/**
	 * Attachment ID generated in this request for an Article, or 0.
	 *
	 * @param int $article_id Article post ID.
	 * @return int
	 */
	public static function protected_pdf_file_id( $article_id ) {
		$article_id = absint( $article_id );

		return isset( self::$protected_pdf_ids[ $article_id ] )
			? (int) self::$protected_pdf_ids[ $article_id ]
			: 0;
	}

	/**
	 * Remember a generated pdf_file so the same-request meta save
	 * cannot clear an empty submitted field.
	 *
	 * @param int $article_id    Article post ID.
	 * @param int $attachment_id Generated attachment ID.
	 */
	public static function remember_protected_pdf( $article_id, $attachment_id ) {
		$article_id    = absint( $article_id );
		$attachment_id = absint( $attachment_id );
		if ( $article_id && $attachment_id ) {
			self::$protected_pdf_ids[ $article_id ] = $attachment_id;
		}
	}

	/**
	 * Classic publish transition. Skips PDF side effects during REST.
	 *
	 * @param array $data    Sanitized post data.
	 * @param array $postarr Raw post array.
	 * @return array
	 */
	public static function guard_classic_publication( $data, $postarr ) {
		if ( self::$handled_in_rest ) {
			return $data;
		}

		if ( ! self::should_enforce_classic( $data, $postarr ) ) {
			return $data;
		}

		$article_id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
		$result     = self::ensure_pdf_for_publication(
			$article_id,
			self::classic_candidate_pdf( $article_id ),
			isset( $data['post_title'] ) ? $data['post_title'] : '',
			isset( $data['post_content'] ) ? $data['post_content'] : ''
		);

		if ( is_wp_error( $result ) ) {
			$previous            = $article_id ? get_post_status( $article_id ) : '';
			$data['post_status'] = self::fallback_unpublish_status( $previous );
			self::$notice        = self::NOTICE_CODE;
		}

		return $data;
	}

	/**
	 * REST/Gutenberg publish transition. Owns PDF side effects.
	 *
	 * @param \stdClass|\WP_Error $prepared Prepared post.
	 * @param \WP_REST_Request    $request  Request.
	 * @return \stdClass|\WP_Error
	 */
	public static function guard_rest_publication( $prepared, $request ) {
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		if ( ! self::should_enforce_rest( $prepared ) ) {
			return $prepared;
		}

		self::$handled_in_rest = true;

		$article_id = ! empty( $prepared->ID ) ? (int) $prepared->ID : 0;
		$result     = self::ensure_pdf_for_publication(
			$article_id,
			self::rest_candidate_pdf( $request, $article_id ),
			self::rest_candidate_title( $prepared, $article_id ),
			self::rest_candidate_content( $prepared, $article_id )
		);

		if ( is_wp_error( $result ) ) {
			self::$handled_in_rest = false;
			return self::publication_blocked_error();
		}

		self::sync_rest_pdf_meta( $request, (int) $result );

		return $prepared;
	}

	/**
	 * Allow a later classic publish in the same PHP process.
	 */
	public static function clear_rest_pdf_guard() {
		self::$handled_in_rest = false;
	}

	/**
	 * Carry the PDF failure notice across the post-save redirect.
	 *
	 * @param string $location Redirect URL.
	 * @return string
	 */
	public static function append_notice_query_arg( $location ) {
		if ( self::$notice ) {
			$location = add_query_arg( 'revistalogos_notice', rawurlencode( self::$notice ), $location );
		}

		return $location;
	}

	/**
	 * Print the PDF publication failure notice.
	 */
	public static function render_admin_notices() {
		$code = isset( $_GET['revistalogos_notice'] ) ? sanitize_key( wp_unslash( $_GET['revistalogos_notice'] ) ) : self::$notice; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( self::NOTICE_CODE !== $code ) {
			return;
		}

		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html( self::editor_message() )
		);
	}

	/**
	 * Spanish editor-facing publication failure message.
	 *
	 * @return string
	 */
	public static function editor_message() {
		return __( 'No se pudo generar el PDF del artículo. El artículo no fue publicado. Vuelve a intentarlo o adjunta un PDF manualmente.', 'revistalogos-core' );
	}

	/**
	 * @return \WP_Error
	 */
	public static function publication_blocked_error() {
		return new \WP_Error(
			self::ERROR_CODE,
			self::editor_message(),
			array( 'status' => self::ERROR_STATUS )
		);
	}

	/**
	 * @param array $data    Sanitized post data.
	 * @param array $postarr Raw post array.
	 * @return bool
	 */
	private static function should_enforce_classic( $data, $postarr ) {
		if ( Relationships::$skip_article_publish_guard ) {
			return false;
		}

		if ( ! Article_Pdf_Publication_Settings::is_enabled() ) {
			return false;
		}

		if ( ! isset( $data['post_type'] ) || Content_Types::ARTICLE !== $data['post_type'] ) {
			return false;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		$post_id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
		if ( $post_id && wp_is_post_revision( $post_id ) ) {
			return false;
		}

		if ( ! isset( $data['post_status'] ) || 'publish' !== $data['post_status'] ) {
			return false;
		}

		$previous = $post_id ? get_post_status( $post_id ) : '';

		return 'publish' !== $previous;
	}

	/**
	 * @param \stdClass $prepared Prepared post.
	 * @return bool
	 */
	private static function should_enforce_rest( $prepared ) {
		if ( Relationships::$skip_article_publish_guard ) {
			return false;
		}

		if ( ! Article_Pdf_Publication_Settings::is_enabled() ) {
			return false;
		}

		$status     = isset( $prepared->post_status ) ? $prepared->post_status : '';
		$article_id = ! empty( $prepared->ID ) ? (int) $prepared->ID : 0;
		$previous   = $article_id ? get_post_status( $article_id ) : '';

		return ( 'publish' === $status && 'publish' !== $previous );
	}

	/**
	 * Keep or generate a valid PDF for this publish transition.
	 *
	 * @param int         $article_id          Article ID (0 if not yet inserted).
	 * @param mixed       $candidate_pdf_file  Candidate attachment ID or null.
	 * @param string      $candidate_title     Title being published.
	 * @param string      $candidate_content   Body being published.
	 * @return int|\WP_Error Attachment ID.
	 */
	private static function ensure_pdf_for_publication( $article_id, $candidate_pdf_file, $candidate_title, $candidate_content ) {
		$article_id = absint( $article_id );
		if ( $article_id <= 0 ) {
			return self::publication_blocked_error();
		}

		$adapter  = new Article_Pdf_WordPress_Adapter();
		$decision = $adapter->decide_pdf_action_for_article( $article_id, $candidate_pdf_file );

		$attachment_id = self::generator()->generate_for_publication(
			$article_id,
			$candidate_pdf_file,
			$candidate_title,
			$candidate_content
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$attachment_id = absint( $attachment_id );
		if ( $attachment_id <= 0 ) {
			return self::publication_blocked_error();
		}

		if ( Article_Pdf_Publication_Policy::GENERATE_REQUIRED === $decision ) {
			self::remember_protected_pdf( $article_id, $attachment_id );
		}

		return $attachment_id;
	}

	/**
	 * @param int $article_id Article ID.
	 * @return mixed Candidate ID or null to read stored meta.
	 */
	private static function classic_candidate_pdf( $article_id ) {
		if ( self::metabox_nonce_is_valid() && isset( $_POST['pdf_file'] ) ) {
			return Metadata::sanitize_pdf_attachment_id( wp_unslash( $_POST['pdf_file'] ) );
		}

		unset( $article_id );

		return null;
	}

	/**
	 * @param \WP_REST_Request $request    Request.
	 * @param int              $article_id Article ID.
	 * @return mixed Candidate ID or null to read stored meta.
	 */
	private static function rest_candidate_pdf( $request, $article_id ) {
		$meta = $request->get_param( 'meta' );
		if ( is_array( $meta ) && array_key_exists( 'pdf_file', $meta ) ) {
			return Metadata::sanitize_pdf_attachment_id( $meta['pdf_file'] );
		}

		unset( $article_id );

		return null;
	}

	/**
	 * @param \stdClass $prepared   Prepared post.
	 * @param int       $article_id Article ID.
	 * @return string
	 */
	private static function rest_candidate_title( $prepared, $article_id ) {
		if ( isset( $prepared->post_title ) && is_string( $prepared->post_title ) ) {
			return $prepared->post_title;
		}

		return $article_id ? (string) get_the_title( $article_id ) : '';
	}

	/**
	 * @param \stdClass $prepared   Prepared post.
	 * @param int       $article_id Article ID.
	 * @return string
	 */
	private static function rest_candidate_content( $prepared, $article_id ) {
		if ( isset( $prepared->post_content ) && is_string( $prepared->post_content ) ) {
			return $prepared->post_content;
		}

		return $article_id ? (string) get_post_field( 'post_content', $article_id ) : '';
	}

	/**
	 * Prevent a later REST meta write from clearing a generated pdf_file.
	 *
	 * @param \WP_REST_Request $request        Request.
	 * @param int              $attachment_id  Attachment ID to keep.
	 */
	private static function sync_rest_pdf_meta( $request, $attachment_id ) {
		$meta = $request->get_param( 'meta' );
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}

		$submitted = array_key_exists( 'pdf_file', $meta )
			? Metadata::sanitize_pdf_attachment_id( $meta['pdf_file'] )
			: 0;

		if ( $submitted > 0 ) {
			return;
		}

		$meta['pdf_file'] = $attachment_id;
		$request->set_param( 'meta', $meta );
	}

	/**
	 * @return bool
	 */
	private static function metabox_nonce_is_valid() {
		return isset( $_POST[ Meta_Boxes::NONCE_FIELD ] )
			&& wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ Meta_Boxes::NONCE_FIELD ] ) ), Meta_Boxes::NONCE_ACTION );
	}

	/**
	 * @param string $previous Previous status.
	 * @return string
	 */
	private static function fallback_unpublish_status( $previous ) {
		if ( in_array( $previous, array( 'draft', 'pending', 'private' ), true ) ) {
			return $previous;
		}

		return 'draft';
	}

	/**
	 * @return Article_Pdf_WordPress_Generator
	 */
	private static function generator() {
		if ( ! self::$generator ) {
			self::$generator = new Article_Pdf_WordPress_Generator();
		}

		return self::$generator;
	}
}
