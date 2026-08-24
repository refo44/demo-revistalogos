<?php
/**
 * Admin-configurable Article PDF publication enforcement
 * (ADR 0017 work unit 6B).
 *
 * One plugin-owned option. Missing option means OFF. Toggling does
 * not scan, generate, backfill, or mutate Articles.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setting contract + Settings API surface for PDF enforcement.
 */
class Article_Pdf_Publication_Settings {

	const OPTION_NAME = 'revistalogos_article_pdf_publication_enforcement';
	const PAGE_SLUG   = 'revistalogos-settings';
	const GROUP       = 'revistalogos_settings';

	/**
	 * Register Settings API hooks. Does not write the option.
	 */
	public static function register_hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
	}

	/**
	 * Whether future Article publish transitions must enforce a PDF.
	 * Absence of the option is OFF.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return 1 === (int) get_option( self::OPTION_NAME, 0 );
	}

	/**
	 * Store only 0 or 1.
	 *
	 * @param mixed $value Raw submitted value.
	 * @return int
	 */
	public static function sanitize( $value ) {
		return ( 1 === (int) $value ) ? 1 : 0;
	}

	/**
	 * Settings → LOGO ET SPES.
	 */
	public static function register_page() {
		add_options_page(
			__( 'LOGO ET SPES', 'revistalogos-core' ),
			__( 'LOGO ET SPES', 'revistalogos-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the option and the PDF section/field.
	 */
	public static function register_setting() {
		register_setting(
			self::GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => 0,
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'revistalogos_article_pdf',
			__( 'PDF de artículos', 'revistalogos-core' ),
			array( __CLASS__, 'render_section' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			self::OPTION_NAME,
			__( 'Exigir/generar PDF al publicar artículos', 'revistalogos-core' ),
			array( __CLASS__, 'render_field' ),
			self::PAGE_SLUG,
			'revistalogos_article_pdf'
		);
	}

	/**
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'LOGO ET SPES', 'revistalogos-core' ) . '</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE_SLUG );
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * @return void
	 */
	public static function render_section() {
		echo '<p>' . esc_html__( 'Esta opción solo afecta a futuras publicaciones de artículos. No genera, sustituye ni borra PDF existentes, y no cambia el estado de los artículos ya guardados.', 'revistalogos-core' ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Desactivada:', 'revistalogos-core' ) . '</strong> ' . esc_html__( 'publicar sigue funcionando como hoy; el PDF puede adjuntarse manualmente.', 'revistalogos-core' ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Activada:', 'revistalogos-core' ) . '</strong> ' . esc_html__( 'al publicar, si no existe un PDF válido, el plugin intenta generarlo; si falla, la publicación se bloquea.', 'revistalogos-core' ) . '</p>';
	}

	/**
	 * @return void
	 */
	public static function render_field() {
		printf(
			'<input type="hidden" name="%1$s" value="0">',
			esc_attr( self::OPTION_NAME )
		);
		printf(
			'<label><input type="checkbox" name="%1$s" value="1"%2$s> %3$s</label>',
			esc_attr( self::OPTION_NAME ),
			checked( self::is_enabled(), true, false ),
			esc_html__( 'Exigir/generar PDF al publicar artículos', 'revistalogos-core' )
		);
	}
}
