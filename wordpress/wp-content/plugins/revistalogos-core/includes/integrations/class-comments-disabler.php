<?php
/**
 * Global comment shutdown (ADR 0011 §3): the anonymous-visitor
 * zero-cookie invariant relies on nobody ever being a commenter, and the
 * journal CPTs never declare comment support.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disables comments and pings everywhere.
 */
class Comments_Disabler {

	/**
	 * Hooked on init.
	 */
	public static function register() {
		// Remove support from the native types that declare it.
		foreach ( array( 'post', 'page', 'attachment' ) as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
			}
			if ( post_type_supports( $post_type, 'trackbacks' ) ) {
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}

		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'pings_open', '__return_false', 20 );
		add_filter( 'comments_array', '__return_empty_array', 20 );

		add_action( 'admin_menu', array( __CLASS__, 'remove_admin_menu' ) );
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'remove_admin_bar_node' ) );
	}

	/**
	 * Hide the Comments screen from the admin menu.
	 */
	public static function remove_admin_menu() {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * Hide the Comments node from the admin bar.
	 */
	public static function remove_admin_bar_node() {
		global $wp_admin_bar;

		if ( $wp_admin_bar instanceof \WP_Admin_Bar ) {
			$wp_admin_bar->remove_node( 'comments' );
		}
	}
}
