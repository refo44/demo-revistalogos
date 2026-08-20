<?php
/**
 * Article relationships (ADR 0005 §2): article↔author many-to-many and
 * article→issue many-to-one, both stored as post meta holding IDs.
 * issue→articles is always a derived reverse query (Queries class);
 * article counts are never stored on issues.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalization, validation and cleanup for relationship meta.
 */
class Relationships {

	/**
	 * When true, article publish-without-author is allowed (Volume 1
	 * bootstrap creates published sample articles with authors=[]).
	 * Never set from wp-admin article saves.
	 *
	 * @var bool
	 */
	public static $skip_article_publish_guard = false;

	/**
	 * Hook cleanup for deleted referenced posts.
	 */
	public static function register_hooks() {
		add_action( 'before_delete_post', array( __CLASS__, 'cleanup_references' ), 10, 2 );
	}

	/**
	 * Normalize an authors value into a unique array of existing author
	 * post IDs (positive integers, duplicates removed, wrong types dropped).
	 *
	 * @param mixed $value Raw meta value.
	 * @return int[]
	 */
	public static function sanitize_author_ids( $value ) {
		if ( ! is_array( $value ) ) {
			$value = ( '' === $value || null === $value ) ? array() : array( $value );
		}

		$clean = array();

		foreach ( $value as $id ) {
			$id = absint( $id );

			if ( $id > 0 && Content_Types::AUTHOR === get_post_type( $id ) && ! in_array( $id, $clean, true ) ) {
				$clean[] = $id;
			}
		}

		return $clean;
	}

	/**
	 * Author CPT IDs that currently exist and are published.
	 *
	 * @param mixed $value Raw or sanitized authors value.
	 * @return int[]
	 */
	public static function published_author_ids( $value ) {
		$published = array();

		foreach ( self::sanitize_author_ids( $value ) as $id ) {
			if ( 'publish' === get_post_status( $id ) && ! in_array( $id, $published, true ) ) {
				$published[] = $id;
			}
		}

		return $published;
	}

	/**
	 * Whether at least one assigned author is a published Author CPT.
	 *
	 * @param mixed $value Raw or sanitized authors value.
	 * @return bool
	 */
	public static function has_published_author( $value ) {
		return ! empty( self::published_author_ids( $value ) );
	}

	/**
	 * Normalize an issue reference into one existing issue post ID (0 = none).
	 *
	 * @param mixed $value Raw meta value.
	 * @return int
	 */
	public static function sanitize_issue_id( $value ) {
		// Accept array-shaped input for registration consistency, keep one ID.
		if ( is_array( $value ) ) {
			$value = reset( $value );
		}

		$id = absint( $value );

		if ( 0 === $id ) {
			return 0;
		}

		return ( Content_Types::ISSUE === get_post_type( $id ) ) ? $id : 0;
	}

	/**
	 * When an author or issue is deleted, drop dangling references from
	 * articles so templates never resolve deleted posts.
	 *
	 * @param int           $post_id Deleted post ID.
	 * @param \WP_Post|null $post    Deleted post object.
	 */
	public static function cleanup_references( $post_id, $post = null ) {
		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		if ( Content_Types::AUTHOR === $post_type ) {
			$articles = get_posts(
				array(
					'post_type'      => Content_Types::ARTICLE,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => 'authors',
							'value'   => sprintf( ':%d;', $post_id ),
							'compare' => 'LIKE',
						),
					),
					'no_found_rows'  => true,
				)
			);

			foreach ( $articles as $article_id ) {
				$authors = get_post_meta( $article_id, 'authors', true );

				if ( is_array( $authors ) && in_array( $post_id, array_map( 'absint', $authors ), true ) ) {
					$remaining = array_values( array_diff( array_map( 'absint', $authors ), array( $post_id ) ) );
					update_post_meta( $article_id, 'authors', $remaining );
				}
			}
		}

		if ( Content_Types::ISSUE === $post_type ) {
			$articles = get_posts(
				array(
					'post_type'      => Content_Types::ARTICLE,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_key'       => 'issue',
					'meta_value'     => $post_id,
					'no_found_rows'  => true,
				)
			);

			foreach ( $articles as $article_id ) {
				delete_post_meta( $article_id, 'issue' );
			}
		}
	}
}
