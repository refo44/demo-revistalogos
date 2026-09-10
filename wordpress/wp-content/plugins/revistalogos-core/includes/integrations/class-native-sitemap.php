<?php
/**
 * Native WordPress sitemap and robots for public discoverability.
 * No SiteSEO. CPT author is the public author surface.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the WordPress 7.1 sitemap HTTP 404 workaround should run.
 *
 * Remove when the plugin's minimum WordPress version is 7.1.1 or later.
 *
 * @see https://core.trac.wordpress.org/ticket/65945
 *
 * @param string $wp_version WordPress version string.
 * @return bool
 */
function needs_wp71_sitemap_workaround( $wp_version ) {
	return version_compare( $wp_version, '7.1', '>=' )
		&& version_compare( $wp_version, '7.1.1', '<' );
}

/**
 * Drops wp-admin users from the sitemap, keeps /buscar/ out of the
 * page sitemap and noindex, and works around WordPress 7.1 serving
 * populated sitemaps as HTTP 404 when no native posts are published.
 */
class Native_Sitemap {

	const SEARCH_PAGE_SLUG = 'buscar';

	/**
	 * Wire filters. Called from Plugin::boot() before init so
	 * wp_sitemaps_add_provider runs when Core registers providers.
	 */
	public static function register_hooks() {
		add_filter( 'wp_sitemaps_add_provider', array( __CLASS__, 'omit_users_provider' ), 10, 2 );
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'exclude_search_page' ), 10, 2 );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_search_page' ) );

		if ( needs_wp71_sitemap_workaround( self::wordpress_version() ) ) {
			add_filter( 'pre_handle_404', array( __CLASS__, 'keep_sitemap_from_404' ), 10, 2 );
		}
	}

	/**
	 * @param WP_Sitemaps_Provider|false $provider Provider instance.
	 * @param string                     $name     Provider name.
	 * @return WP_Sitemaps_Provider|false
	 */
	public static function omit_users_provider( $provider, $name ) {
		if ( 'users' === $name ) {
			return false;
		}

		return $provider;
	}

	/**
	 * @param array<string, mixed> $args      WP_Query args.
	 * @param string               $post_type Post type being listed.
	 * @return array<string, mixed>
	 */
	public static function exclude_search_page( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}

		$search_page = get_page_by_path( self::SEARCH_PAGE_SLUG, OBJECT, 'page' );
		if ( ! $search_page instanceof \WP_Post ) {
			return $args;
		}

		$excluded = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$excluded[] = (int) $search_page->ID;
		$args['post__not_in'] = array_values( array_unique( array_map( 'absint', $excluded ) ) );

		return $args;
	}

	/**
	 * Single wp_robots meta. The theme does not emit a separate robots tag.
	 *
	 * @param array<string, mixed> $robots Robots directives.
	 * @return array<string, mixed>
	 */
	public static function noindex_search_page( $robots ) {
		if ( ! is_page( self::SEARCH_PAGE_SLUG ) ) {
			return $robots;
		}

		unset( $robots['index'], $robots['nofollow'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;

		return $robots;
	}

	/**
	 * Work around WordPress 7.1 native sitemap HTTP 404 bug.
	 *
	 * Only recognized sitemap requests. A sitemap that Core later
	 * finds empty can still 404 from WP_Sitemaps::render_sitemaps().
	 * Remove when the minimum supported WordPress version is >= 7.1.1.
	 *
	 * @see https://core.trac.wordpress.org/ticket/65945
	 *
	 * @param bool     $preempt  Whether to short-circuit 404 handling.
	 * @param WP_Query $wp_query The main query.
	 * @return bool
	 */
	public static function keep_sitemap_from_404( $preempt, $wp_query ) {
		if ( ! needs_wp71_sitemap_workaround( self::wordpress_version() ) ) {
			return $preempt;
		}

		if ( ! get_query_var( 'sitemap' ) && ! get_query_var( 'sitemap-stylesheet' ) ) {
			return $preempt;
		}

		if ( $wp_query instanceof \WP_Query ) {
			$wp_query->is_404 = false;
		}
		status_header( 200 );

		return true;
	}

	/**
	 * @return string
	 */
	private static function wordpress_version() {
		return isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : get_bloginfo( 'version' );
	}
}
