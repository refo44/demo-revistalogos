<?php
/**
 * Contact Form 7 integration (ADR 0010): honeypot antispam with no
 * cookies, no third parties and no database storage. Contact Form 7 does
 * not ship an approved honeypot, so this is the smallest maintainable
 * first-party implementation (prompt §14 Phase 8.2). Every hook is
 * guarded: with CF7 inactive nothing runs and nothing fails.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Injects a honeypot field into CF7 forms and marks submissions that
 * fill it as spam. reCAPTCHA and Flamingo remain forbidden (ADR 0010);
 * Cloudflare Turnstile is an owner-gated fallback documented in
 * docs/operations/third-party-plugins.md and is not implemented here.
 */
class Contact_Form_Integration {

	/**
	 * Field name. Deliberately attractive to naive bots, invisible and
	 * inert for people and assistive tech (aria-hidden + tabindex -1).
	 */
	const FIELD = 'les_website_url';

	/**
	 * Wire hooks; they no-op unless Contact Form 7 is active.
	 */
	public static function register_hooks() {
		add_filter( 'wpcf7_form_elements', array( __CLASS__, 'inject_honeypot' ) );
		add_filter( 'wpcf7_spam', array( __CLASS__, 'check_honeypot' ), 10, 2 );
	}

	/**
	 * Append the honeypot input to the rendered form.
	 *
	 * @param string $elements Form HTML.
	 * @return string
	 */
	public static function inject_honeypot( $elements ) {
		if ( ! class_exists( '\WPCF7_Submission' ) ) {
			return $elements;
		}

		$honeypot = sprintf(
			'<p class="les-hp-wrap" aria-hidden="true" style="position:absolute !important;left:-9999px !important;">' .
			'<label for="%1$s">%2$s</label>' .
			'<input type="text" id="%1$s" name="%1$s" value="" tabindex="-1" autocomplete="off"></p>',
			esc_attr( self::FIELD ),
			esc_html__( 'No rellenar este campo', 'revistalogos-core' )
		);

		return $elements . $honeypot;
	}

	/**
	 * Flag the submission as spam when the honeypot arrives non-empty.
	 *
	 * @param bool  $spam       Current spam verdict.
	 * @param mixed $submission CF7 submission object (unused).
	 * @return bool
	 */
	public static function check_honeypot( $spam, $submission = null ) {
		if ( $spam ) {
			return $spam;
		}

		if ( isset( $_POST[ self::FIELD ] ) && '' !== trim( (string) wp_unslash( $_POST[ self::FIELD ] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		return $spam;
	}
}
