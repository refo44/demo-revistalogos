<?php
/**
 * Temporary author name backfill (ADR 0022 / #67).
 *
 * Snapshot → Apply → Restore. Removed on the next tagged deploy.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Proposes given/family names from a title without writing citation_surname.
 */
class Author_Name_Backfill {

	const OPTION_SNAPSHOT = 'revistalogos_author_name_backfill_snapshot';
	const ACTION          = 'revistalogos_author_name_backfill';
	const NONCE_ACTION    = 'revistalogos_author_name_backfill';
	const NONCE_FIELD     = 'revistalogos_author_name_backfill_nonce';

	/**
	 * @param array{title:string,given_names:string,family_names:string,citation_surname:string} $row Author row.
	 * @return array{given_names:string,family_names:string,citation_surname:string}
	 */
	public static function propose( $row ) {
		$title    = self::trim_text( isset( $row['title'] ) ? $row['title'] : '' );
		$given    = self::trim_text( isset( $row['given_names'] ) ? $row['given_names'] : '' );
		$family   = self::trim_text( isset( $row['family_names'] ) ? $row['family_names'] : '' );
		$citation = self::trim_text( isset( $row['citation_surname'] ) ? $row['citation_surname'] : '' );

		if ( '' === $family ) {
			$family = '' !== $citation ? $citation : self::last_token( $title );
		}

		if ( '' === $given ) {
			$split_at = '' !== $citation ? $citation : $family;
			$given    = self::given_names_before_surname( $title, $split_at );
		}

		return array(
			'given_names'      => $given,
			'family_names'     => $family,
			'citation_surname' => $citation,
		);
	}

	/**
	 * Wire admin UI and POST handlers. Does not run on upgrade.
	 */
	public static function register_hooks() {
		add_action( 'revistalogos_settings_after_form', array( __CLASS__, 'render_tools' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle_post' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_notice' ) );
	}

	/**
	 * Snapshot current author name metas if none is stored yet.
	 *
	 * @return array<int, array{given_names:string,family_names:string,citation_surname:string}>
	 */
	public static function snapshot() {
		$existing = get_option( self::OPTION_SNAPSHOT, null );

		if ( is_array( $existing ) ) {
			return $existing;
		}

		$snapshot = self::current_rows();
		update_option( self::OPTION_SNAPSHOT, $snapshot, false );

		return $snapshot;
	}

	/**
	 * Apply proposed names. Takes a snapshot first. Restores if a write throws.
	 *
	 * @return int Authors updated.
	 */
	public static function apply() {
		self::snapshot();
		$updated = 0;

		try {
			foreach ( self::author_posts() as $post ) {
				$current = self::row_from_post( $post );
				$next    = self::propose( $current );

				if (
					$current['given_names'] === $next['given_names']
					&& $current['family_names'] === $next['family_names']
					&& $current['citation_surname'] === $next['citation_surname']
				) {
					continue;
				}

				self::write_row( (int) $post->ID, $next );
				++$updated;
			}
		} catch ( \Throwable $e ) {
			self::restore();
			throw $e;
		}

		return $updated;
	}

	/**
	 * Write snapshot metas back. Title is not touched.
	 *
	 * @return int Authors restored.
	 */
	public static function restore() {
		$snapshot = get_option( self::OPTION_SNAPSHOT, null );

		if ( ! is_array( $snapshot ) ) {
			return 0;
		}

		$restored = 0;

		foreach ( $snapshot as $post_id => $row ) {
			self::write_row( (int) $post_id, $row );
			++$restored;
		}

		return $restored;
	}

	/**
	 * @return void
	 */
	public static function handle_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permiso para esta acción.', 'revistalogos-core' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$op = isset( $_POST['revistalogos_backfill_op'] ) ? sanitize_key( wp_unslash( $_POST['revistalogos_backfill_op'] ) ) : '';

		try {
			if ( 'restore' === $op ) {
				$count = self::restore();
				$code  = 'author_backfill_restored';
			} else {
				$count = self::apply();
				$code  = 'author_backfill_applied';
			}
		} catch ( \Throwable $e ) {
			$count = 0;
			$code  = 'author_backfill_failed';
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                    => Article_Pdf_Publication_Settings::PAGE_SLUG,
					'revistalogos_backfill'   => $code,
					'revistalogos_backfill_n' => (int) $count,
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * @return void
	 */
	public static function render_tools() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$has_snapshot = is_array( get_option( self::OPTION_SNAPSHOT, null ) );

		echo '<hr>';
		echo '<h2>' . esc_html__( 'Nombres y apellidos de autores (temporal)', 'revistalogos-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Rellena Nombres y Apellidos desde el título y el apellido para citar ya guardado. No cambia el título ni el apellido para citar. Esta pantalla se retirará en el próximo deploy.', 'revistalogos-core' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p>';
		printf(
			'<button type="submit" name="revistalogos_backfill_op" value="apply" class="button button-primary">%s</button> ',
			esc_html__( 'Aplicar relleno', 'revistalogos-core' )
		);
		printf(
			'<button type="submit" name="revistalogos_backfill_op" value="restore" class="button"%s>%s</button>',
			$has_snapshot ? '' : ' disabled="disabled"',
			esc_html__( 'Restaurar', 'revistalogos-core' )
		);
		echo '</p>';
		echo '</form>';
	}

	/**
	 * @return void
	 */
	public static function render_notice() {
		if ( ! isset( $_GET['revistalogos_backfill'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$code  = sanitize_key( wp_unslash( $_GET['revistalogos_backfill'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$count = isset( $_GET['revistalogos_backfill_n'] ) ? absint( $_GET['revistalogos_backfill_n'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$messages = array(
			'author_backfill_applied'  => __( 'Relleno de nombres aplicado.', 'revistalogos-core' ),
			'author_backfill_restored' => __( 'Nombres de autor restaurados al snapshot.', 'revistalogos-core' ),
			'author_backfill_failed'   => __( 'El relleno falló y se restauró el snapshot.', 'revistalogos-core' ),
		);

		if ( ! isset( $messages[ $code ] ) ) {
			return;
		}

		$type = 'author_backfill_failed' === $code ? 'error' : 'success';

		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s %3$s</p></div>',
			esc_attr( $type ),
			esc_html( $messages[ $code ] ),
			esc_html( (string) $count )
		);
	}

	/**
	 * @param string $title   Display name.
	 * @param string $surname Bibliographic surname.
	 * @return string
	 */
	public static function given_names_before_surname( $title, $surname ) {
		$title   = self::trim_text( $title );
		$surname = self::trim_text( $surname );

		if ( '' === $title || '' === $surname || $title === $surname ) {
			return '';
		}

		$quoted = preg_quote( $surname, '/' );
		if ( preg_match( '/^(.*)\s+' . $quoted . '(?:\s+.*)?$/u', $title, $matches ) ) {
			return self::trim_text( $matches[1] );
		}

		return $title;
	}

	/**
	 * @param string $title Full name.
	 * @return string
	 */
	public static function last_token( $title ) {
		$parts = preg_split( '/\s+/', self::trim_text( $title ) );

		if ( ! is_array( $parts ) || array() === $parts ) {
			return '';
		}

		return (string) array_pop( $parts );
	}

	/**
	 * @param mixed $value Raw text.
	 * @return string
	 */
	private static function trim_text( $value ) {
		return trim( (string) $value );
	}

	/**
	 * @return \WP_Post[]
	 */
	private static function author_posts() {
		return get_posts(
			array(
				'post_type'      => Content_Types::AUTHOR,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
	}

	/**
	 * @param \WP_Post $post Author.
	 * @return array{title:string,given_names:string,family_names:string,citation_surname:string}
	 */
	private static function row_from_post( $post ) {
		return array(
			'title'            => (string) $post->post_title,
			'given_names'      => (string) get_post_meta( $post->ID, 'given_names', true ),
			'family_names'     => (string) get_post_meta( $post->ID, 'family_names', true ),
			'citation_surname' => (string) get_post_meta( $post->ID, 'citation_surname', true ),
		);
	}

	/**
	 * @return array<int, array{given_names:string,family_names:string,citation_surname:string}>
	 */
	private static function current_rows() {
		$rows = array();

		foreach ( self::author_posts() as $post ) {
			$row                 = self::row_from_post( $post );
			$rows[ (int) $post->ID ] = array(
				'given_names'      => $row['given_names'],
				'family_names'     => $row['family_names'],
				'citation_surname' => $row['citation_surname'],
			);
		}

		return $rows;
	}

	/**
	 * @param int                                                                                 $post_id Author ID.
	 * @param array{given_names?:string,family_names?:string,citation_surname?:string} $row     Metas.
	 */
	private static function write_row( $post_id, $row ) {
		foreach ( array( 'given_names', 'family_names', 'citation_surname' ) as $key ) {
			$value = isset( $row[ $key ] ) ? self::trim_text( $row[ $key ] ) : '';
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
				continue;
			}
			update_post_meta( $post_id, $key, $value );
		}
	}
}
