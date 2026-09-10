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

	const QUERY_VAR = 'revistalogos_llms';

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
	}

	/**
	 * Pretty /llms.txt. Flushed by Plugin::maybe_upgrade() on version bump.
	 */
	public static function register_rewrite() {
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
	public static function serve() {
		if ( ! self::is_llms_request() ) {
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
			'name'          => self::JOURNAL_NAME,
			'description'   => self::JOURNAL_DESCRIPTION,
			'home_url'      => home_url( '/' ),
			'issues_url'    => get_post_type_archive_link( Content_Types::ISSUE ) ?: home_url( '/revista/numeros/' ),
			'articles_url'  => get_post_type_archive_link( Content_Types::ARTICLE ) ?: home_url( '/revista/articulos/' ),
			'authors_url'   => get_post_type_archive_link( Content_Types::AUTHOR ) ?: home_url( '/revista/autores/' ),
			'sitemap_url'   => home_url( '/wp-sitemap.xml' ),
			'current_issue' => $issue,
		);
	}
}
