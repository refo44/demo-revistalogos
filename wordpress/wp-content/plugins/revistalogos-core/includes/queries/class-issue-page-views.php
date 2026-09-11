<?php
/**
 * Public page-view count for one issue permalink. Reads WP Statistics
 * when the free plugin is active; never sums article views.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adapter around wp_statistics_pages for Estadísticas del Número.
 */
class Issue_Page_Views {

	/**
	 * Total recorded views of the issue URL. 0 when the plugin is
	 * missing, inactive, or has no rows for that ID.
	 *
	 * @param int           $issue_id    Issue post ID.
	 * @param callable|null $hits_reader Optional reader (id): int for tests.
	 * @return int
	 */
	public static function count( $issue_id, $hits_reader = null ) {
		$issue_id = (int) $issue_id;

		if ( $issue_id < 1 ) {
			return 0;
		}

		if ( ! is_callable( $hits_reader ) ) {
			$hits_reader = array( __CLASS__, 'read_installed_hits' );
		}

		$hits = call_user_func( $hits_reader, $issue_id );

		if ( ! is_numeric( $hits ) ) {
			return 0;
		}

		return max( 0, (int) $hits );
	}

	/**
	 * @param int $issue_id Issue post ID.
	 * @return int
	 */
	public static function read_installed_hits( $issue_id ) {
		if ( ! function_exists( 'wp_statistics_pages' ) ) {
			return 0;
		}

		return wp_statistics_pages( 'total', '', $issue_id );
	}
}
