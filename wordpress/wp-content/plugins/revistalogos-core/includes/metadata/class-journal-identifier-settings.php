<?php
/**
 * Site-level digital journal identifiers (issue #58).
 *
 * Print-edition ISSN and depósito legal are out of scope. Empty option
 * means empty — the theme shows «Próximamente»; /llms.txt omits the line.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings API fields on Ajustes → LOGO ET SPES.
 */
class Journal_Identifier_Settings {

	const OPTION_ISSN          = 'revistalogos_journal_issn';
	const OPTION_LEGAL_DEPOSIT = 'revistalogos_journal_legal_deposit';
	const OPTION_DOI_PREFIX    = 'revistalogos_journal_doi_prefix';

	/**
	 * Register Settings API hooks. Does not write options.
	 */
	public static function register_hooks() {
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
	}

	/**
	 * ISSN of the digital edition.
	 *
	 * @return string
	 */
	public static function issn() {
		return self::stored( self::OPTION_ISSN );
	}

	/**
	 * Digital legal-deposit number.
	 *
	 * @return string
	 */
	public static function legal_deposit() {
		return self::stored( self::OPTION_LEGAL_DEPOSIT );
	}

	/**
	 * Crossref DOI prefix, inert until Fase 4.
	 *
	 * @return string
	 */
	public static function doi_prefix() {
		return self::stored( self::OPTION_DOI_PREFIX );
	}

	/**
	 * @param string $key issn | legal_deposit | doi_prefix.
	 * @return string
	 */
	public static function value( $key ) {
		if ( 'issn' === $key ) {
			return self::issn();
		}

		if ( 'legal_deposit' === $key ) {
			return self::legal_deposit();
		}

		if ( 'doi_prefix' === $key ) {
			return self::doi_prefix();
		}

		return '';
	}

	/**
	 * Store plain trimmed text. No ISSN/DOI checksum (Fase 4).
	 *
	 * @param mixed $value Raw submitted value.
	 * @return string
	 */
	public static function sanitize( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$text = trim( (string) $value );

		if ( function_exists( 'sanitize_text_field' ) ) {
			return sanitize_text_field( $text );
		}

		return trim( strip_tags( $text ) );
	}

	/**
	 * Fields on the existing LOGO ET SPES settings page.
	 */
	public static function register_setting() {
		foreach ( self::fields() as $option => $field ) {
			register_setting(
				Article_Pdf_Publication_Settings::GROUP,
				$option,
				array(
					'type'              => 'string',
					'sanitize_callback' => array( __CLASS__, 'sanitize' ),
					'default'           => '',
					'show_in_rest'      => false,
				)
			);
		}

		add_settings_section(
			'revistalogos_journal_identifiers',
			__( 'Identificadores de la revista', 'revistalogos-core' ),
			array( __CLASS__, 'render_section' ),
			Article_Pdf_Publication_Settings::PAGE_SLUG
		);

		foreach ( self::fields() as $option => $field ) {
			add_settings_field(
				$option,
				$field['label'],
				array( __CLASS__, 'render_field' ),
				Article_Pdf_Publication_Settings::PAGE_SLUG,
				'revistalogos_journal_identifiers',
				array(
					'option' => $option,
				)
			);
		}
	}

	/**
	 * @return void
	 */
	public static function render_section() {
		echo '<p>' . esc_html__( 'Solo la edición digital. Un campo vacío se muestra como «Próximamente» en el sitio y no aparece en /llms.txt.', 'revistalogos-core' ) . '</p>';
	}

	/**
	 * @param array<string, string> $args Field args from add_settings_field.
	 * @return void
	 */
	public static function render_field( $args ) {
		$option = isset( $args['option'] ) ? (string) $args['option'] : '';
		if ( '' === $option ) {
			return;
		}

		printf(
			'<input type="text" class="regular-text" name="%1$s" value="%2$s">',
			esc_attr( $option ),
			esc_attr( self::stored( $option ) )
		);
	}

	/**
	 * @param string $option Option name.
	 * @return string
	 */
	private static function stored( $option ) {
		if ( ! function_exists( 'get_option' ) ) {
			return '';
		}

		$value = get_option( $option, '' );

		return is_string( $value ) ? $value : '';
	}

	/**
	 * @return array<string, array{label: string}>
	 */
	private static function fields() {
		return array(
			self::OPTION_ISSN          => array(
				'label' => __( 'ISSN', 'revistalogos-core' ),
			),
			self::OPTION_LEGAL_DEPOSIT => array(
				'label' => __( 'Depósito Legal', 'revistalogos-core' ),
			),
			self::OPTION_DOI_PREFIX    => array(
				'label' => __( 'Prefijo DOI', 'revistalogos-core' ),
			),
		);
	}
}
