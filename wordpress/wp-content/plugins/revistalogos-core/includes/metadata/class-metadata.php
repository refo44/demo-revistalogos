<?php
/**
 * Post meta registration for the published-content model (docs/03 §3).
 *
 * Fase 3 boundary (ADR 0013, docs/17 Fase 4): issn, doi and orcid are inert
 * base storage only — no normalization, checksum validation, derived URLs,
 * display markers or Crossref export here. article.doi_url and
 * author.orcid_url are derived concepts and are NOT stored.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers every custom field with type, REST schema, sanitization and
 * authorization. Editing UI lives in Meta_Boxes (native, no field builder).
 */
class Metadata {

	/**
	 * Register all fields. Hooked on init after CPTs and taxonomies.
	 */
	public static function register() {
		// issue fields.
		self::register_int( Content_Types::ISSUE, 'volume_number', __( 'Volumen oficial', 'revistalogos-core' ) );
		self::register_int( Content_Types::ISSUE, 'issue_number', __( 'Número oficial', 'revistalogos-core' ) );
		self::register_int( Content_Types::ISSUE, 'year', __( 'Año de publicación', 'revistalogos-core' ) );
		self::register_date( Content_Types::ISSUE, 'date_published', __( 'Fecha de publicación', 'revistalogos-core' ) );
		self::register_text( Content_Types::ISSUE, 'issn', __( 'ISSN electrónico (e-ISSN)', 'revistalogos-core' ) );
		self::register_text( Content_Types::ISSUE, 'doi', __( 'DOI del número', 'revistalogos-core' ) );
		self::register_pdf_attachment( Content_Types::ISSUE, 'pdf_file', __( 'PDF completo del número (Media Library)', 'revistalogos-core' ) );

		// article fields.
		self::register_text( Content_Types::ARTICLE, 'title_en', __( 'Título en inglés', 'revistalogos-core' ) );
		self::register_textarea( Content_Types::ARTICLE, 'abstract', __( 'Resumen en español', 'revistalogos-core' ) );
		self::register_textarea( Content_Types::ARTICLE, 'abstract_en', __( 'Resumen en inglés', 'revistalogos-core' ) );
		self::register_text( Content_Types::ARTICLE, 'doi', __( 'DOI del artículo', 'revistalogos-core' ) );
		self::register_text( Content_Types::ARTICLE, 'pages', __( 'Paginación oficial dentro del número', 'revistalogos-core' ) );
		self::register_pdf_attachment( Content_Types::ARTICLE, 'pdf_file', __( 'PDF del artículo (Media Library)', 'revistalogos-core' ) );
		self::register_text( Content_Types::ARTICLE, 'language', __( 'Idioma principal (es, en)', 'revistalogos-core' ) );
		self::register_date( Content_Types::ARTICLE, 'publication_date', __( 'Fecha de publicación', 'revistalogos-core' ) );
		self::register_date( Content_Types::ARTICLE, 'received_date', __( 'Fecha de envío', 'revistalogos-core' ) );
		self::register_date( Content_Types::ARTICLE, 'accepted_date', __( 'Fecha de aceptación', 'revistalogos-core' ) );

		// article relationships (ADR 0005 §2: post meta storing IDs).
		register_post_meta(
			Content_Types::ARTICLE,
			'authors',
			array(
				'object_subtype'    => Content_Types::ARTICLE,
				'type'              => 'array',
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => array( Relationships::class, 'sanitize_author_ids' ),
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
			)
		);

		register_post_meta(
			Content_Types::ARTICLE,
			'issue',
			array(
				'object_subtype'    => Content_Types::ARTICLE,
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => array( Relationships::class, 'sanitize_issue_id' ),
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);

		// author fields.
		self::register_text( Content_Types::AUTHOR, 'afiliacion', __( 'Institución, afiliación', 'revistalogos-core' ) );
		self::register_text( Content_Types::AUTHOR, 'orcid', __( 'ORCID iD', 'revistalogos-core' ) );
		self::register_textarea( Content_Types::AUTHOR, 'bio', __( 'Biografía breve', 'revistalogos-core' ) );

		register_post_meta(
			Content_Types::AUTHOR,
			'email',
			array(
				'object_subtype'    => Content_Types::AUTHOR,
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'sanitize_email',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				// Not exposed over REST: contact data is not public API surface.
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Meta edit authorization: defer to the mapped edit capability.
	 *
	 * @param bool   $allowed  Unused default.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public static function can_edit( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Sanitize a Y-m-d date string; anything else becomes ''.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function sanitize_date( $value ) {
		$value = trim( (string) $value );

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return '';
		}

		list( $year, $month, $day ) = array_map( 'intval', explode( '-', $value ) );

		return checkdate( $month, $day, $year ) ? $value : '';
	}

	/**
	 * Register a plain text field.
	 *
	 * @param string $subtype Post type.
	 * @param string $key     Meta key.
	 * @param string $label   Field description.
	 */
	private static function register_text( $subtype, $key, $label ) {
		register_post_meta(
			$subtype,
			$key,
			array(
				'object_subtype'    => $subtype,
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'description'       => $label,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register a multi-line text field.
	 *
	 * @param string $subtype Post type.
	 * @param string $key     Meta key.
	 * @param string $label   Field description.
	 */
	private static function register_textarea( $subtype, $key, $label ) {
		register_post_meta(
			$subtype,
			$key,
			array(
				'object_subtype'    => $subtype,
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'description'       => $label,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register a non-negative integer field.
	 *
	 * @param string $subtype Post type.
	 * @param string $key     Meta key.
	 * @param string $label   Field description.
	 */
	private static function register_int( $subtype, $key, $label ) {
		register_post_meta(
			$subtype,
			$key,
			array(
				'object_subtype'    => $subtype,
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'description'       => $label,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register a Y-m-d date field.
	 *
	 * @param string $subtype Post type.
	 * @param string $key     Meta key.
	 * @param string $label   Field description.
	 */
	private static function register_date( $subtype, $key, $label ) {
		register_post_meta(
			$subtype,
			$key,
			array(
				'object_subtype'    => $subtype,
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'description'       => $label,
				'sanitize_callback' => array( __CLASS__, 'sanitize_date' ),
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register a PDF attachment-ID field (Media Library reference;
	 * permanent URLs are never stored — ADR 0005 §5). MIME must be
	 * application/pdf. Generic image attachments (featured images) use
	 * native post thumbnails, not this helper.
	 *
	 * @param string $subtype Post type.
	 * @param string $key     Meta key.
	 * @param string $label   Field description.
	 */
	private static function register_pdf_attachment( $subtype, $key, $label ) {
		register_post_meta(
			$subtype,
			$key,
			array(
				'object_subtype'    => $subtype,
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'description'       => $label,
				'sanitize_callback' => array( __CLASS__, 'sanitize_pdf_attachment_id' ),
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Keep only IDs that reference an existing attachment.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_attachment_id( $value ) {
		$id = absint( $value );

		if ( 0 === $id ) {
			return 0;
		}

		return ( 'attachment' === get_post_type( $id ) ) ? $id : 0;
	}

	/**
	 * Keep only existing application/pdf attachments. Images and other
	 * MIME types become 0 (cleared). Used by article and issue pdf_file.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_pdf_attachment_id( $value ) {
		$id = self::sanitize_attachment_id( $value );

		if ( 0 === $id ) {
			return 0;
		}

		return ( 'application/pdf' === get_post_mime_type( $id ) ) ? $id : 0;
	}
}
