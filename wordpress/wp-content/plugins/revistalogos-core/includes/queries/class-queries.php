<?php
/**
 * Domain queries. "Current issue" is always derived at query time from
 * date_published — never stored as a mutable flag (docs/03 §3, ADR 0005).
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only helpers consumed by the theme. All bounded, no N+1 loops.
 */
class Queries {

	/**
	 * The published issue with the most recent date_published.
	 *
	 * @return \WP_Post|null
	 */
	public static function current_issue() {
		$issues = get_posts(
			array(
				'post_type'      => Content_Types::ISSUE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_key'       => 'date_published',
				'orderby'        => 'meta_value',
				'meta_type'      => 'DATE',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		return $issues ? $issues[0] : null;
	}

	/**
	 * Published articles belonging to an issue (derived reverse query;
	 * counts are computed from the result, never stored).
	 *
	 * @param int $issue_id Issue post ID.
	 * @param int $limit    Maximum results; -1 for all.
	 * @return \WP_Post[]
	 */
	public static function issue_articles( $issue_id, $limit = -1 ) {
		$issue_id = absint( $issue_id );

		if ( 0 === $issue_id ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => Content_Types::ARTICLE,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'meta_key'       => 'issue',
				'meta_value'     => $issue_id,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
	}

	/**
	 * Published articles credited to an author profile.
	 *
	 * @param int $author_id Author post ID.
	 * @param int $limit     Maximum results; -1 for all.
	 * @return \WP_Post[]
	 */
	public static function author_articles( $author_id, $limit = -1 ) {
		$author_id = absint( $author_id );

		if ( 0 === $author_id ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => Content_Types::ARTICLE,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'meta_query'     => array(
					array(
						'key'     => 'authors',
						'value'   => sprintf( ':%d;', $author_id ),
						'compare' => 'LIKE',
					),
				),
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);
	}

	/**
	 * Author posts referenced by an article, in stored order.
	 *
	 * @param int $article_id Article post ID.
	 * @return \WP_Post[]
	 */
	public static function article_authors( $article_id ) {
		$ids = get_post_meta( absint( $article_id ), 'authors', true );

		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return array();
		}

		$posts = get_posts(
			array(
				'post_type'      => Content_Types::AUTHOR,
				'post_status'    => 'publish',
				'posts_per_page' => count( $ids ),
				'post__in'       => array_map( 'absint', $ids ),
				'orderby'        => 'post__in',
				'no_found_rows'  => true,
			)
		);

		return $posts;
	}

	/**
	 * Bounded public search for /buscar/?q= (docs/04: result priority
	 * 1) articles, 2) issues, 3) authors, 4) news). Only approved public
	 * content types; stable pagination via paged.
	 *
	 * @param string $search   Search terms (already sanitized).
	 * @param int    $paged    Page number (1-based).
	 * @param int    $per_page Results per page.
	 * @return \WP_Query
	 */
	public static function search_query( $search, $paged = 1, $per_page = 10 ) {
		$orderby_filter = static function ( $orderby, $query ) {
			global $wpdb;

			if ( $query->get( 'revistalogos_search' ) ) {
				// Documented type priority, then recency. No native
				// orderby exists for type priority; posts_orderby is the
				// supported extension point.
				$orderby = "FIELD({$wpdb->posts}.post_type, 'article', 'issue', 'author', 'post'), {$wpdb->posts}.post_date DESC";
			}

			return $orderby;
		};

		add_filter( 'posts_orderby', $orderby_filter, 10, 2 );

		$query = new \WP_Query(
			array(
				's'                   => $search,
				'post_type'           => array( Content_Types::ARTICLE, Content_Types::ISSUE, Content_Types::AUTHOR, 'post' ),
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, absint( $per_page ) ),
				'paged'               => max( 1, absint( $paged ) ),
				'revistalogos_search' => true,
			)
		);

		remove_filter( 'posts_orderby', $orderby_filter, 10 );

		return $query;
	}

	/**
	 * Issue referenced by an article, if published.
	 *
	 * @param int $article_id Article post ID.
	 * @return \WP_Post|null
	 */
	public static function article_issue( $article_id ) {
		$issue_id = absint( get_post_meta( absint( $article_id ), 'issue', true ) );

		if ( 0 === $issue_id ) {
			return null;
		}

		$issue = get_post( $issue_id );

		if ( $issue && Content_Types::ISSUE === $issue->post_type && 'publish' === $issue->post_status ) {
			return $issue;
		}

		return null;
	}
}
