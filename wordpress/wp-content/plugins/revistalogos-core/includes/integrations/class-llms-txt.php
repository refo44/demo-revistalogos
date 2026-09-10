<?php
/**
 * /llms.txt for AI agents (issue #47). Stable map plus the current
 * published issue from Queries — never a hardcoded catalog.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders and serves text/plain /llms.txt.
 */
class Llms_Txt {

	const QUERY_VAR      = 'revistalogos_llms';
	const PAGE_SLUG      = 'revistalogos-llms-txt';
	const OPTION_NAME    = 'revistalogos_llms_txt_enabled';
	const SETTINGS_GROUP = 'revistalogos_llms_txt';
	const REFRESH_ACTION = 'revistalogos_refresh_llms_txt';
	const REFRESH_NONCE  = 'revistalogos_refresh_llms_txt';

	/**
	 * Published institutional pages (docs/11). No /buscar/ (noindex).
	 * There is no separate lineamientos or reglamentos page.
	 */
	const INSTITUTIONAL_SLUGS = array(
		'acerca',
		'normas',
		'etica',
		'politicas',
		'comite-editorial',
		'enviar-colaboracion',
		'contacto',
		'enlaces',
		'noticias',
		'privacidad',
	);

	/**
	 * Public journal name (content-source / site chrome).
	 */
	const JOURNAL_NAME = 'Revista de Filosofía LOGO ET SPES';

	/**
	 * Hero description — same wording as front-page.php.
	 */
	const JOURNAL_DESCRIPTION = 'La Revista de Filosofía adscrita, auspiciada y editada por el Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS, es una publicación digital venezolana enfocada en el pensamiento filosófico multidisciplinar. Es de acceso abierto; arbitrada bajo la modalidad <doble anónimo o doble ciego>; con periodicidad anual. Sus páginas están disponibles para difundir investigaciones originales -de autores nacionales e internacionales- que coadyuven a promover el desarrollo de todas las áreas de la Filosofía.';

	/**
	 * Wire rewrite and serving. Called from Plugin::boot().
	 */
	public static function register_hooks() {
		add_action( 'init', array( __CLASS__, 'register_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
		add_action( 'template_redirect', array( __CLASS__, 'serve' ), 0 );
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'update_option_' . self::OPTION_NAME, array( __CLASS__, 'flush_after_toggle' ) );
		add_action( 'add_option_' . self::OPTION_NAME, array( __CLASS__, 'flush_after_toggle' ) );
		add_action( 'admin_post_' . self::REFRESH_ACTION, array( __CLASS__, 'handle_refresh' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_refresh_notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( REVISTALOGOS_CORE_FILE ), array( __CLASS__, 'plugin_action_links' ) );
	}

	/**
	 * Pretty /llms.txt. Flushed by Plugin::maybe_upgrade() on version bump.
	 */
	public static function register_rewrite() {
		if ( ! self::is_enabled() ) {
			return;
		}

		add_rewrite_rule( '^llms\.txt$', 'index.php?' . self::QUERY_VAR . '=1', 'top' );
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
	 * Serve text/plain when this is an llms.txt request.
	 */
	/**
	 * Absence of the option is ON. Deploy does not write the option.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return 1 === (int) get_option( self::OPTION_NAME, 1 );
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
	 * @return bool
	 */
	public static function should_serve() {
		return self::is_enabled() && self::is_llms_request();
	}

	/**
	 * Serve text/plain when this is an enabled llms.txt request.
	 */
	public static function serve() {
		if ( ! self::is_llms_request() ) {
			return;
		}

		if ( ! self::is_enabled() ) {
			global $wp_query;
			if ( $wp_query instanceof \WP_Query ) {
				$wp_query->set_404();
			}
			status_header( 404 );
			nocache_headers();
			return;
		}

		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		status_header( 200 );
		echo self::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- text/plain map, values from titles/URLs we control.
		exit;
	}

	/**
	 * @return string
	 */
	public static function render() {
		return self::format_document( self::catalog() );
	}

	/**
	 * Re-register the pretty URL and return the current catalog.
	 *
	 * @return string
	 */
	public static function refresh() {
		self::register_rewrite();
		flush_rewrite_rules();

		return self::render();
	}

	/**
	 * @param string $nonce Submitted nonce.
	 * @return string|\WP_Error Fresh document, or an authorization error.
	 */
	public static function refresh_if_authorized( $nonce ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'forbidden', __( 'No tiene permiso para actualizar llms.txt.', 'revistalogos-core' ) );
		}

		if ( ! wp_verify_nonce( $nonce, self::REFRESH_NONCE ) ) {
			return new \WP_Error( 'invalid_nonce', __( 'La solicitud para actualizar llms.txt no es válida.', 'revistalogos-core' ) );
		}

		return self::refresh();
	}

	/**
	 * Settings → LOGO ET SPES — llms.txt. Same plugin prefix as the PDF page.
	 */
	public static function register_page() {
		add_options_page(
			__( 'LOGO ET SPES — llms.txt', 'revistalogos-core' ),
			__( 'LOGO ET SPES — llms.txt', 'revistalogos-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Dedicated settings screen (nonce + manage_options).
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'LOGO ET SPES', 'revistalogos-core' ) . ' — <code>' . esc_html__( 'llms.txt', 'revistalogos-core' ) . '</code></h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( self::SETTINGS_GROUP );
		do_settings_sections( self::PAGE_SLUG );
		submit_button();
		echo '</form>';
		self::render_admin_panel();
		echo '</div>';
	}

	/**
	 * Settings API: publish toggle. Does not write the option.
	 */
	public static function register_setting() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => 1,
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'revistalogos_llms_txt',
			'',
			'__return_false',
			self::PAGE_SLUG
		);

		add_settings_field(
			self::OPTION_NAME,
			__( 'Publicar llms.txt', 'revistalogos-core' ),
			array( __CLASS__, 'render_enable_field' ),
			self::PAGE_SLUG,
			'revistalogos_llms_txt'
		);
	}

	/**
	 * @return void
	 */
	public static function render_enable_field() {
		printf(
			'<input type="hidden" name="%1$s" value="0">',
			esc_attr( self::OPTION_NAME )
		);
		printf(
			'<label><input type="checkbox" name="%1$s" value="1"%2$s> %3$s</label>',
			esc_attr( self::OPTION_NAME ),
			checked( self::is_enabled(), true, false ),
			esc_html__( 'Publicar llms.txt', 'revistalogos-core' )
		);
		echo '<p class="description">' . esc_html__( 'Activada: la dirección pública responde con el índice. Desactivada: /llms.txt no se publica.', 'revistalogos-core' ) . '</p>';
	}

	/**
	 * Flush pretty permalinks after the publish toggle changes.
	 */
	public static function flush_after_toggle() {
		self::register_rewrite();
		flush_rewrite_rules();
	}

	/**
	 * Settings → LOGO ET SPES — llms.txt refresh button.
	 */
	public static function handle_refresh() {
		$nonce  = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified in refresh_if_authorized().
		$result = self::refresh_if_authorized( $nonce );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), '', array( 'response' => 403 ) );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'           => self::PAGE_SLUG,
					'llms_refreshed' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Manual refresh panel on Settings → LOGO ET SPES — llms.txt.
	 */
	public static function render_admin_panel() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$public_url = home_url( '/llms.txt' );

		echo '<div class="card" id="llms-txt" style="max-width:800px;margin-top:1.5em;padding:1em 1.5em">';
		echo '<p>' . esc_html__( 'Reúne los archivos vivos y las páginas institucionales publicadas. La dirección pública es /llms.txt.', 'revistalogos-core' ) . '</p>';
		echo '<p><a href="' . esc_url( $public_url ) . '">' . esc_html( $public_url ) . '</a></p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::REFRESH_ACTION ) . '">';
		wp_nonce_field( self::REFRESH_NONCE );
		submit_button( __( 'Actualizar llms.txt', 'revistalogos-core' ), 'secondary', 'submit', false );
		echo '</form>';
		echo '<pre style="white-space:pre-wrap;max-height:22em;overflow:auto;background:#f6f7f7;padding:1em">' . esc_html( self::render() ) . '</pre>';
		echo '</div>';
	}

	/**
	 * Success notice after a manual refresh.
	 */
	public static function render_refresh_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_GET['page'] ) || self::PAGE_SLUG !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin screen flag.
			return;
		}

		if ( empty( $_GET['llms_refreshed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin screen flag.
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'llms.txt se actualizó. La dirección pública vuelve a estar registrada.', 'revistalogos-core' ) . '</p></div>';
	}

	/**
	 * @param string[] $links Plugin row actions.
	 * @return string[]
	 */
	public static function plugin_action_links( $links ) {
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
			'<code>' . esc_html__( 'llms.txt', 'revistalogos-core' ) . '</code>'
		);

		return $links;
	}

	/**
	 * @param array<string, mixed> $catalog Prepared public catalog.
	 * @return string
	 */
	public static function format_document( array $catalog ) {
		$lines   = array();
		$lines[] = '# ' . $catalog['name'];
		$lines[] = '';
		$lines[] = '> ' . $catalog['description'];
		$lines[] = '';
		$lines[] = 'Sitio: ' . $catalog['home_url'];
		$lines[] = '';
		$lines[] = '## Contenido vivo';
		$lines[] = '';
		$lines[] = '- [Números](' . $catalog['issues_url'] . ')';
		$lines[] = '- [Artículos](' . $catalog['articles_url'] . ')';
		$lines[] = '- [Autores](' . $catalog['authors_url'] . ')';
		$lines[] = '';

		if ( ! empty( $catalog['institutional_pages'] ) && is_array( $catalog['institutional_pages'] ) ) {
			$lines[] = '## Información institucional';
			$lines[] = '';

			foreach ( $catalog['institutional_pages'] as $page ) {
				if ( empty( $page['title'] ) || empty( $page['url'] ) ) {
					continue;
				}

				$lines[] = '- [' . $page['title'] . '](' . $page['url'] . ')';
			}

			$lines[] = '';
		}

		if ( ! empty( $catalog['current_issue'] ) && is_array( $catalog['current_issue'] ) ) {
			$issue   = $catalog['current_issue'];
			$lines[] = '## Número actual';
			$lines[] = '';
			$lines[] = '- [' . $issue['title'] . '](' . $issue['url'] . ')';

			if ( ! empty( $issue['articles'] ) && is_array( $issue['articles'] ) ) {
				foreach ( $issue['articles'] as $article ) {
					$lines[] = '  - [' . $article['title'] . '](' . $article['url'] . ')';
				}
			}

			$lines[] = '';
		}

		$lines[] = '## Sitemap';
		$lines[] = '';
		$lines[] = $catalog['sitemap_url'];
		$lines[] = '';

		return implode( "\n", $lines );
	}

	/**
	 * @return bool
	 */
	private static function is_llms_request() {
		if ( 1 === (int) get_query_var( self::QUERY_VAR ) ) {
			return true;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );

		return is_string( $path ) && '/llms.txt' === untrailingslashit( $path );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function catalog() {
		$current = Queries::current_issue();
		$issue   = null;

		if ( $current instanceof \WP_Post ) {
			$articles = array();
			foreach ( Queries::issue_articles( $current->ID ) as $article ) {
				$articles[] = array(
					'title' => get_the_title( $article ),
					'url'   => get_permalink( $article ),
				);
			}

			$issue = array(
				'title'    => get_the_title( $current ),
				'url'      => get_permalink( $current ),
				'articles' => $articles,
			);
		}

		return array(
			'name'                 => self::JOURNAL_NAME,
			'description'          => self::JOURNAL_DESCRIPTION,
			'home_url'             => home_url( '/' ),
			'issues_url'           => get_post_type_archive_link( Content_Types::ISSUE ) ?: home_url( '/revista/numeros/' ),
			'articles_url'         => get_post_type_archive_link( Content_Types::ARTICLE ) ?: home_url( '/revista/articulos/' ),
			'authors_url'          => get_post_type_archive_link( Content_Types::AUTHOR ) ?: home_url( '/revista/autores/' ),
			'sitemap_url'          => home_url( '/wp-sitemap.xml' ),
			'current_issue'        => $issue,
			'institutional_pages'  => self::published_institutional_pages(),
		);
	}

	/**
	 * @return array<int, array{title: string, url: string}>
	 */
	private static function published_institutional_pages() {
		$pages = array();

		foreach ( self::INSTITUTIONAL_SLUGS as $slug ) {
			$page = get_page_by_path( $slug );

			if ( ! $page instanceof \WP_Post || 'publish' !== $page->post_status ) {
				continue;
			}

			$pages[] = array(
				'title' => get_the_title( $page ),
				'url'   => get_permalink( $page ),
			);
		}

		return $pages;
	}
}
