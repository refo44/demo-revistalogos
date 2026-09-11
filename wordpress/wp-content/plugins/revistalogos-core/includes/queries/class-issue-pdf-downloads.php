<?php
/**
 * First-party download count for the issue PDF (ADR 0011: no paid
 * WP Statistics addon). Ver PDF and Descargar PDF share this path.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Counts and serves the complete-issue PDF.
 */
class Issue_Pdf_Downloads {

	const META_KEY  = '_les_issue_pdf_downloads';
	const QUERY_VAR = 'revistalogos_issue_pdf';

	/**
	 * Register the public counted-download endpoint.
	 */
	public static function register_hooks() {
		add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_serve' ) );
	}

	/**
	 * @param string[] $vars Public query vars.
	 * @return string[]
	 */
	public static function register_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;

		return $vars;
	}

	/**
	 * @param int $issue_id Issue post ID.
	 * @return int
	 */
	public static function count( $issue_id ) {
		$issue_id = absint( $issue_id );

		if ( $issue_id < 1 ) {
			return 0;
		}

		return absint( get_post_meta( $issue_id, self::META_KEY, true ) );
	}

	/**
	 * Public URL that increments then redirects to the attachment.
	 * Empty when the issue has no PDF.
	 *
	 * @param int $issue_id Issue post ID.
	 * @return string
	 */
	public static function counted_url( $issue_id ) {
		$issue_id = absint( $issue_id );

		if ( '' === self::attachment_url( $issue_id ) ) {
			return '';
		}

		return add_query_arg( self::QUERY_VAR, $issue_id, home_url( '/' ) );
	}

	/**
	 * Increment and return the attachment URL, or refuse.
	 *
	 * @param int $issue_id Issue post ID.
	 * @return array{ok: bool, url?: string, count?: int}
	 */
	public static function resolve( $issue_id ) {
		$url = self::attachment_url( $issue_id );

		if ( '' === $url ) {
			return array( 'ok' => false );
		}

		$count = self::increment( $issue_id );

		return array(
			'ok'    => true,
			'url'   => $url,
			'count' => $count,
		);
	}

	/**
	 * Serve a counted download when the query var is present.
	 */
	public static function maybe_serve() {
		$issue_id = absint( get_query_var( self::QUERY_VAR ) );

		if ( $issue_id < 1 ) {
			return;
		}

		$resolved = self::resolve( $issue_id );

		if ( ! $resolved['ok'] ) {
			status_header( 404 );
			nocache_headers();
			exit;
		}

		wp_safe_redirect( $resolved['url'], 302 );
		exit;
	}

	/**
	 * @param int $issue_id Issue post ID.
	 * @return int New total.
	 */
	private static function increment( $issue_id ) {
		$next = self::count( $issue_id ) + 1;
		update_post_meta( $issue_id, self::META_KEY, $next );

		return $next;
	}

	/**
	 * @param int $issue_id Issue post ID.
	 * @return string Attachment URL or empty.
	 */
	private static function attachment_url( $issue_id ) {
		$issue_id = absint( $issue_id );
		$issue    = get_post( $issue_id );

		if ( ! $issue || Content_Types::ISSUE !== $issue->post_type || 'publish' !== $issue->post_status ) {
			return '';
		}

		$attachment_id = absint( get_post_meta( $issue_id, 'pdf_file', true ) );

		if ( $attachment_id < 1 ) {
			return '';
		}

		$url = wp_get_attachment_url( $attachment_id );

		return $url ? $url : '';
	}
}
