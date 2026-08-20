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
	 * Admin notice code for the current request (survives redirect).
	 *
	 * @var string
	 */
	private static $notice = '';

	/**
	 * Author IDs already accepted by rest_guard_article_publish for the
	 * current insert. rest_do_request does not define REST_REQUEST, so
	 * wp_insert_post_data must reuse this instead of empty post meta.
	 *
	 * @var int[]|null
	 */
	private static $rest_insert_authors = null;

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
				'pdf_file'       => array( __( 'PDF del número', 'revistalogos-core' ), 'attachment' ),
			),
			Content_Types::ARTICLE => array(
				'title_en'         => array( __( 'Título en inglés', 'revistalogos-core' ), 'text' ),
				'abstract'         => array( __( 'Resumen (máx. 180 palabras)', 'revistalogos-core' ), 'textarea' ),
				'abstract_en'      => array( __( 'Resumen en inglés (máx. 180 palabras)', 'revistalogos-core' ), 'textarea' ),
				'doi'              => array( __( 'DOI', 'revistalogos-core' ), 'text' ),
				'pages'            => array( __( 'Paginación (ej. 15-32)', 'revistalogos-core' ), 'text' ),
				'pdf_file'         => array( __( 'PDF del artículo', 'revistalogos-core' ), 'attachment' ),
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
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'guard_article_publish_status' ), 10, 2 );
		add_filter( 'rest_pre_insert_' . Content_Types::ARTICLE, array( __CLASS__, 'rest_guard_article_publish' ), 10, 2 );
		add_filter( 'redirect_post_location', array( __CLASS__, 'append_notice_query_arg' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );
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
					printf(
						'<input type="number" id="%1$s" name="%2$s" value="%3$s" class="small-text" min="0" step="1">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				case 'attachment':
					self::render_pdf_field( $id, $key, $value );
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
	 * Render the article relationships box (author checkboxes, issue
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

		$empty_class = $selected_authors ? 'revistalogos-authors-empty hidden' : 'revistalogos-authors-empty';

		echo '<fieldset class="revistalogos-authors">';
		echo '<legend><strong>' . esc_html__( 'Autores', 'revistalogos-core' ) . '</strong></legend>';
		printf(
			'<p class="%s"><strong>%s</strong></p>',
			esc_attr( $empty_class ),
			esc_html__( 'Ningún autor asignado', 'revistalogos-core' )
		);

		if ( ! $authors ) {
			echo '<p class="description">' . esc_html__( 'No hay perfiles de autor. Créalos en Autores antes de asignarlos.', 'revistalogos-core' ) . '</p>';
		} else {
			foreach ( $authors as $author ) {
				$author_id = (int) $author->ID;
				printf(
					'<p><label><input type="checkbox" name="authors[]" value="%1$d"%2$s> %3$s</label></p>',
					$author_id,
					checked( in_array( $author_id, $selected_authors, true ), true, false ),
					esc_html( get_the_title( $author ) )
				);
			}
		}

		echo '<p class="description">' . esc_html__( 'Marca uno o más autores. Un artículo publicado necesita al menos un autor publicado. No se asigna ninguno por defecto. Guarda el artículo después de asignar autores y luego publícalo.', 'revistalogos-core' ) . '</p>';
		echo '</fieldset>';

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
			self::save_article_authors( $post_id );

			$issue = isset( $_POST['issue'] ) ? absint( wp_unslash( $_POST['issue'] ) ) : 0;
			update_post_meta( $post_id, 'issue', $issue );
		}
	}

	/**
	 * Media Library picker for a PDF attachment-ID field.
	 *
	 * @param string $html_id Input id.
	 * @param string $name    POST key.
	 * @param mixed  $value   Stored attachment ID.
	 */
	private static function render_pdf_field( $html_id, $name, $value ) {
		$attachment_id = absint( $value );
		$url           = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
		$filename      = '';

		if ( $attachment_id && $url ) {
			$file     = get_attached_file( $attachment_id );
			$filename = $file ? wp_basename( $file ) : get_the_title( $attachment_id );
		} else {
			$attachment_id = 0;
			$url           = '';
		}

		$has = ( $attachment_id > 0 && $url );

		echo '<div class="revistalogos-pdf-field">';
		printf(
			'<input type="hidden" id="%1$s" class="revistalogos-pdf-field__id" name="%2$s" value="%3$d">',
			esc_attr( $html_id ),
			esc_attr( $name ),
			$has ? $attachment_id : 0
		);

		printf(
			'<p class="revistalogos-pdf-field__filename%s">%s</p>',
			$has ? '' : ' hidden',
			esc_html( $filename )
		);
		printf(
			'<p class="revistalogos-pdf-field__empty%s"><strong>%s</strong></p>',
			$has ? ' hidden' : '',
			esc_html__( 'Ningún PDF seleccionado', 'revistalogos-core' )
		);

		echo '<p class="revistalogos-pdf-field__actions">';
		printf(
			'<a class="button revistalogos-pdf-field__view%s" href="%s" target="_blank" rel="noopener noreferrer">%s</a> ',
			$has ? '' : ' hidden',
			$has ? esc_url( $url ) : '#',
			esc_html__( 'Ver PDF', 'revistalogos-core' )
		);
		printf(
			'<button type="button" class="button revistalogos-pdf-field__select">%s</button> ',
			esc_html( $has ? __( 'Reemplazar PDF', 'revistalogos-core' ) : __( 'Seleccionar PDF', 'revistalogos-core' ) )
		);
		printf(
			'<button type="button" class="button revistalogos-pdf-field__remove%s">%s</button>',
			$has ? '' : ' hidden',
			esc_html__( 'Quitar PDF', 'revistalogos-core' )
		);
		echo '</p>';
		echo '<p class="description">' . esc_html__( 'Un PDF por artículo o número. Quitar solo desvincula el archivo; no lo borra de la biblioteca.', 'revistalogos-core' ) . '</p>';
		echo '</div>';
	}

	/**
	 * wp.media + author empty-state script, only on issue/article screens.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue_admin( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, array( Content_Types::ARTICLE, Content_Types::ISSUE ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'revistalogos-core-admin-meta',
			REVISTALOGOS_CORE_URL . 'assets/js/admin-meta.js',
			array( 'jquery' ),
			REVISTALOGOS_CORE_VERSION,
			true
		);
		wp_localize_script(
			'revistalogos-core-admin-meta',
			'revistalogosPdfMedia',
			array(
				'title'   => __( 'Seleccionar PDF', 'revistalogos-core' ),
				'button'  => __( 'Usar este PDF', 'revistalogos-core' ),
				'select'  => __( 'Seleccionar PDF', 'revistalogos-core' ),
				'replace' => __( 'Reemplazar PDF', 'revistalogos-core' ),
			)
		);
	}

	/**
	 * Block transitions to publish when no published Author CPT is assigned.
	 * Does not unpublish an already-published article (bootstrap leftovers).
	 *
	 * @param array $data    Sanitized post data.
	 * @param array $postarr Raw post array.
	 * @return array
	 */
	public static function guard_article_publish_status( $data, $postarr ) {
		if ( Relationships::$skip_article_publish_guard ) {
			return $data;
		}

		if ( Content_Types::ARTICLE !== $data['post_type'] ) {
			return $data;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $data;
		}

		$post_id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;

		if ( $post_id && wp_is_post_revision( $post_id ) ) {
			return $data;
		}

		if ( 'publish' !== $data['post_status'] ) {
			return $data;
		}

		$intended = self::intended_author_ids( $post_id );

		if ( Relationships::has_published_author( $intended ) ) {
			return $data;
		}

		$previous = $post_id ? get_post_status( $post_id ) : '';

		if ( 'publish' === $previous ) {
			$existing = $post_id ? get_post_meta( $post_id, 'authors', true ) : array();
			self::$notice = Relationships::has_published_author( $existing ) ? 'keep_authors' : 'published_needs_author';
			return $data;
		}

		$data['post_status'] = self::fallback_unpublish_status( $previous );
		self::$notice        = 'cannot_publish';

		return $data;
	}

	/**
	 * REST equivalent of the publish-without-author rule.
	 *
	 * @param \stdClass        $prepared Prepared post.
	 * @param \WP_REST_Request $request  Request.
	 * @return \stdClass|\WP_Error
	 */
	public static function rest_guard_article_publish( $prepared, $request ) {
		if ( Relationships::$skip_article_publish_guard ) {
			return $prepared;
		}

		$status  = isset( $prepared->post_status ) ? $prepared->post_status : '';
		$post_id = ! empty( $prepared->ID ) ? (int) $prepared->ID : 0;
		$previous = $post_id ? get_post_status( $post_id ) : '';
		$meta     = $request->get_param( 'meta' );
		$ids      = array();

		if ( is_array( $meta ) && array_key_exists( 'authors', $meta ) ) {
			$ids = $meta['authors'];
		} elseif ( $post_id ) {
			$ids = get_post_meta( $post_id, 'authors', true );
		}

		$has_published = Relationships::has_published_author( $ids );

		if ( 'publish' === $status && ! $has_published && 'publish' !== $previous ) {
			self::$rest_insert_authors = null;
			return new \WP_Error(
				'revistalogos_article_requires_author',
				__( 'Un artículo publicado necesita al menos un autor con estado publicado.', 'revistalogos-core' ),
				array( 'status' => 400 )
			);
		}

		if ( 'publish' === $status && is_array( $meta ) && array_key_exists( 'authors', $meta ) && ! $has_published && 'publish' === $previous && Relationships::has_published_author( get_post_meta( $post_id, 'authors', true ) ) ) {
			self::$rest_insert_authors = null;
			return new \WP_Error(
				'revistalogos_article_keep_author',
				__( 'No se puede quitar el último autor publicado de un artículo publicado.', 'revistalogos-core' ),
				array( 'status' => 400 )
			);
		}

		self::$rest_insert_authors = Relationships::sanitize_author_ids( $ids );

		return $prepared;
	}

	/**
	 * Persist article authors unless this would strip the last published
	 * author from a published article.
	 *
	 * @param int $post_id Article ID.
	 */
	private static function save_article_authors( $post_id ) {
		$submitted = isset( $_POST['authors'] ) ? (array) wp_unslash( $_POST['authors'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$submitted = Relationships::sanitize_author_ids( $submitted );
		$status    = get_post_status( $post_id );

		if ( 'publish' === $status && ! Relationships::has_published_author( $submitted ) ) {
			$existing = get_post_meta( $post_id, 'authors', true );

			if ( Relationships::has_published_author( $existing ) ) {
				if ( ! self::$notice ) {
					self::$notice = 'keep_authors';
				}
				return;
			}

			if ( ! self::$notice ) {
				self::$notice = 'published_needs_author';
			}
		}

		update_post_meta( $post_id, 'authors', $submitted );
	}

	/**
	 * Author IDs the editor is trying to store, or existing meta when the
	 * metabox was not submitted (Quick Edit, REST).
	 *
	 * @param int $post_id Article ID.
	 * @return int[]
	 */
	private static function intended_author_ids( $post_id ) {
		if ( self::metabox_nonce_is_valid() ) {
			$raw = isset( $_POST['authors'] ) ? (array) wp_unslash( $_POST['authors'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return Relationships::sanitize_author_ids( $raw );
		}

		if ( null !== self::$rest_insert_authors ) {
			$ids = self::$rest_insert_authors;
			self::$rest_insert_authors = null;
			return $ids;
		}

		if ( $post_id ) {
			return Relationships::sanitize_author_ids( get_post_meta( $post_id, 'authors', true ) );
		}

		return array();
	}

	/**
	 * Whether this request submitted the journal metabox nonce.
	 *
	 * @return bool
	 */
	private static function metabox_nonce_is_valid() {
		return isset( $_POST[ self::NONCE_FIELD ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION );
	}

	/**
	 * Status to keep when refusing publish.
	 *
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
	 * Carry the notice across the post-save redirect.
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
	 * Print the publish-author admin notice.
	 */
	public static function render_admin_notices() {
		$code = isset( $_GET['revistalogos_notice'] ) ? sanitize_key( wp_unslash( $_GET['revistalogos_notice'] ) ) : self::$notice; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $code ) {
			return;
		}

		$messages = array(
			'cannot_publish'          => __( 'Un artículo publicado necesita al menos un autor con estado publicado. El artículo se ha guardado como borrador o pendiente; no se ha publicado.', 'revistalogos-core' ),
			'keep_authors'            => __( 'No se puede quitar el último autor publicado de un artículo publicado. Se conservaron los autores anteriores. El resto de cambios se ha guardado.', 'revistalogos-core' ),
			'published_needs_author'  => __( 'Este artículo publicado no tiene un autor publicado asignado. Asígnale al menos uno. El artículo no se ha despublicado.', 'revistalogos-core' ),
		);

		if ( ! isset( $messages[ $code ] ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html( $messages[ $code ] )
		);
	}
}
