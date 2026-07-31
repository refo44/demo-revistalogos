<?php
/**
 * Native meta boxes for the journal CPT fields — no field builder
 * (ADR 0005 §2). Nonce, capability and sanitization on every save;
 * sanitization itself is enforced by the register_post_meta callbacks.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin editing UI for issue, article and author fields.
 */
class Meta_Boxes {

	const NONCE_ACTION = 'revistalogos_core_meta';
	const NONCE_FIELD  = 'revistalogos_core_meta_nonce';

	/**
	 * Field map per post type: key => array(label, input type).
	 *
	 * @return array<string, array<string, array{0: string, 1: string}>>
	 */
	private static function fields() {
		return array(
			Content_Types::ISSUE   => array(
				'volume_number'  => array( __( 'Volumen', 'revistalogos-core' ), 'number' ),
				'issue_number'   => array( __( 'Número', 'revistalogos-core' ), 'number' ),
				'year'           => array( __( 'Año', 'revistalogos-core' ), 'number' ),
				'date_published' => array( __( 'Fecha de publicación', 'revistalogos-core' ), 'date' ),
				'issn'           => array( __( 'ISSN electrónico (e-ISSN)', 'revistalogos-core' ), 'text' ),
				'doi'            => array( __( 'DOI', 'revistalogos-core' ), 'text' ),
				'pdf_file'       => array( __( 'PDF del número (ID de adjunto)', 'revistalogos-core' ), 'attachment' ),
			),
			Content_Types::ARTICLE => array(
				'title_en'         => array( __( 'Título en inglés', 'revistalogos-core' ), 'text' ),
				'abstract'         => array( __( 'Resumen (máx. 250 palabras)', 'revistalogos-core' ), 'textarea' ),
				'abstract_en'      => array( __( 'Resumen en inglés', 'revistalogos-core' ), 'textarea' ),
				'doi'              => array( __( 'DOI', 'revistalogos-core' ), 'text' ),
				'pages'            => array( __( 'Paginación (ej. 15-32)', 'revistalogos-core' ), 'text' ),
				'pdf_file'         => array( __( 'PDF del artículo (ID de adjunto)', 'revistalogos-core' ), 'attachment' ),
				'language'         => array( __( 'Idioma (es, en)', 'revistalogos-core' ), 'text' ),
				'publication_date' => array( __( 'Fecha de publicación', 'revistalogos-core' ), 'date' ),
				'received_date'    => array( __( 'Fecha de envío', 'revistalogos-core' ), 'date' ),
				'accepted_date'    => array( __( 'Fecha de aceptación', 'revistalogos-core' ), 'date' ),
			),
			Content_Types::AUTHOR  => array(
				'afiliacion' => array( __( 'Institución, afiliación', 'revistalogos-core' ), 'text' ),
				'orcid'      => array( __( 'ORCID iD (NNNN-NNNN-NNNN-NNNK)', 'revistalogos-core' ), 'text' ),
				'bio'        => array( __( 'Biografía breve', 'revistalogos-core' ), 'textarea' ),
				'email'      => array( __( 'Correo (opcional, no público)', 'revistalogos-core' ), 'email' ),
			),
		);
	}

	/**
	 * Wire admin hooks.
	 */
	public static function register_hooks() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Register one metadata box per CPT, plus the relationships box on
	 * articles.
	 */
	public static function add_boxes() {
		foreach ( array_keys( self::fields() ) as $post_type ) {
			add_meta_box(
				'revistalogos-core-fields',
				__( 'Metadatos de la revista', 'revistalogos-core' ),
				array( __CLASS__, 'render_fields_box' ),
				$post_type,
				'normal',
				'high'
			);
		}

		add_meta_box(
			'revistalogos-core-relationships',
			__( 'Relaciones (autores y número)', 'revistalogos-core' ),
			array( __CLASS__, 'render_relationships_box' ),
			Content_Types::ARTICLE,
			'side',
			'default'
		);
	}

	/**
	 * Render the field box for the current post type.
	 *
	 * @param \WP_Post $post Current post.
	 */
	public static function render_fields_box( $post ) {
		$fields = self::fields();

		if ( ! isset( $fields[ $post->post_type ] ) ) {
			return;
		}

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ( $fields[ $post->post_type ] as $key => $config ) {
			list( $label, $type ) = $config;
			$value = get_post_meta( $post->ID, $key, true );
			$id    = 'revistalogos-' . $key;

			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';

			switch ( $type ) {
				case 'textarea':
					printf(
						'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_textarea( $value )
					);
					break;

				case 'number':
				case 'attachment':
					printf(
						'<input type="number" id="%1$s" name="%2$s" value="%3$s" class="small-text" min="0" step="1">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				case 'date':
					printf(
						'<input type="date" id="%1$s" name="%2$s" value="%3$s">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				case 'email':
					printf(
						'<input type="email" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				default:
					printf(
						'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
			}

			echo '</td></tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Render the article relationships box (authors multi-select, issue
	 * select). Bounded lists; an academic journal has few of each.
	 *
	 * @param \WP_Post $post Current article.
	 */
	public static function render_relationships_box( $post ) {
		$selected_authors = get_post_meta( $post->ID, 'authors', true );
		$selected_authors = is_array( $selected_authors ) ? array_map( 'absint', $selected_authors ) : array();
		$selected_issue   = absint( get_post_meta( $post->ID, 'issue', true ) );

		$authors = get_posts(
			array(
				'post_type'      => Content_Types::AUTHOR,
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'posts_per_page' => 500,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		$issues = get_posts(
			array(
				'post_type'      => Content_Types::ISSUE,
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'posts_per_page' => 500,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		echo '<p><label for="revistalogos-authors"><strong>' . esc_html__( 'Autores', 'revistalogos-core' ) . '</strong></label><br>';
		echo '<select id="revistalogos-authors" name="authors[]" multiple size="6" style="width:100%">';

		foreach ( $authors as $author ) {
			printf(
				'<option value="%1$d"%2$s>%3$s</option>',
				(int) $author->ID,
				selected( in_array( (int) $author->ID, $selected_authors, true ), true, false ),
				esc_html( get_the_title( $author ) )
			);
		}

		echo '</select></p>';
		echo '<p class="description">' . esc_html__( 'Mantén pulsado Ctrl/Cmd para seleccionar varios.', 'revistalogos-core' ) . '</p>';

		echo '<p><label for="revistalogos-issue"><strong>' . esc_html__( 'Número', 'revistalogos-core' ) . '</strong></label><br>';
		echo '<select id="revistalogos-issue" name="issue" style="width:100%">';
		echo '<option value="0">' . esc_html__( '— Sin número asignado —', 'revistalogos-core' ) . '</option>';

		foreach ( $issues as $issue ) {
			printf(
				'<option value="%1$d"%2$s>%3$s</option>',
				(int) $issue->ID,
				selected( $selected_issue, (int) $issue->ID, false ),
				esc_html( get_the_title( $issue ) )
			);
		}

		echo '</select></p>';
	}

	/**
	 * Persist submitted fields. register_post_meta sanitize callbacks run
	 * inside update_post_meta; this layer only verifies intent (nonce),
	 * autosave state and capability.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$fields = self::fields();

		if ( ! isset( $fields[ $post->post_type ] ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( array_keys( $fields[ $post->post_type ] ) as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			// Sanitization happens in the registered sanitize_callback.
			update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		if ( Content_Types::ARTICLE === $post->post_type ) {
			$authors = isset( $_POST['authors'] ) ? (array) wp_unslash( $_POST['authors'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			update_post_meta( $post_id, 'authors', $authors );

			$issue = isset( $_POST['issue'] ) ? absint( wp_unslash( $_POST['issue'] ) ) : 0;
			update_post_meta( $post_id, 'issue', $issue );
		}
	}
}
