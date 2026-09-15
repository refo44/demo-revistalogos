<?php
/**
 * Contact Form 7 integration (ADR 0010): idempotent form provision,
 * honeypot antispam with no cookies, no third parties and no database
 * storage. Contact Form 7 does not ship an approved honeypot, so this
 * is the smallest maintainable first-party implementation. Every hook
 * is guarded: with CF7 inactive nothing runs and nothing fails.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provisions the public contact form when CF7 is active, injects a
 * honeypot, and flags filled honeypots as spam. reCAPTCHA and Flamingo
 * remain forbidden (ADR 0010); Cloudflare Turnstile is an owner-gated
 * fallback documented in docs/operations/third-party-plugins.md.
 */
class Contact_Form_Integration {

	/**
	 * Field name. Deliberately attractive to naive bots, invisible and
	 * inert for people and assistive tech (aria-hidden + tabindex -1).
	 */
	const FIELD = 'les_website_url';

	/**
	 * Option the theme reads to render the shortcode.
	 */
	const OPTION_NAME = 'revistalogos_contact_form_id';

	/**
	 * Contact Form 7 stores forms as this CPT.
	 */
	const POST_TYPE = 'wpcf7_contact_form';

	/**
	 * Marks the form this plugin created so re-provision can reuse it.
	 */
	const MANAGED_META = '_les_managed_contact_form';

	/**
	 * Wire hooks; they no-op unless Contact Form 7 is active.
	 */
	public static function register_hooks() {
		// After CF7 registers its CPT on init (priority 10).
		add_action( 'init', array( __CLASS__, 'maybe_provision' ), 20 );
		add_filter( 'wpcf7_form_elements', array( __CLASS__, 'inject_honeypot' ) );
		add_filter( 'wpcf7_spam', array( __CLASS__, 'check_honeypot' ), 10, 2 );
	}

	/**
	 * Create or reuse the managed CF7 form and store its ID.
	 *
	 * @return int Form post ID, or 0 when CF7 is absent.
	 */
	public static function maybe_provision() {
		if ( ! post_type_exists( self::POST_TYPE ) ) {
			return 0;
		}

		$stored = absint( get_option( self::OPTION_NAME ) );
		if ( self::form_is_usable( $stored ) ) {
			return $stored;
		}

		$existing = self::find_managed_form();
		if ( $existing ) {
			update_option( self::OPTION_NAME, $existing );
			return $existing;
		}

		$created = self::insert_managed_form();
		if ( $created ) {
			update_option( self::OPTION_NAME, $created );
		}

		return $created;
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

	/**
	 * @param int $form_id Candidate CF7 post ID.
	 * @return bool
	 */
	private static function form_is_usable( $form_id ) {
		if ( $form_id < 1 ) {
			return false;
		}

		$post = get_post( $form_id );
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( self::POST_TYPE !== $post->post_type ) {
			return false;
		}

		return 'trash' !== $post->post_status && 'auto-draft' !== $post->post_status;
	}

	/**
	 * @return int Existing managed form ID, or 0.
	 */
	private static function find_managed_form() {
		$found = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_key'       => self::MANAGED_META,
				'meta_value'     => '1',
				'fields'         => 'ids',
			)
		);

		return isset( $found[0] ) ? absint( $found[0] ) : 0;
	}

	/**
	 * @return int New form ID, or 0 on failure.
	 */
	private static function insert_managed_form() {
		$id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => Contact_Form_Definition::form_title(),
			),
			true
		);

		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}

		$id = absint( $id );
		update_post_meta( $id, '_form', Contact_Form_Definition::form_template() );
		update_post_meta( $id, '_mail', Contact_Form_Definition::mail_template() );
		update_post_meta( $id, '_mail_2', array( 'active' => false ) );
		update_post_meta( $id, '_messages', array() );
		update_post_meta( $id, '_additional_settings', '' );
		update_post_meta( $id, '_locale', get_locale() );
		update_post_meta( $id, self::MANAGED_META, '1' );

		return $id;
	}
}
