<?php
/**
 * Nav menu walker emitting the frozen static navigation markup
 * (nav__item / nav__link / nav__submenu classes, submenu ARIA).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders wp_nav_menu items with the static site's class contract.
 */
class Revistalogos_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a submenu list.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Menu args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="nav__submenu">';
	}

	/**
	 * Close a submenu list.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Menu args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	/**
	 * Open a menu item.
	 *
	 * @param string   $output            Output buffer.
	 * @param WP_Post  $data_object       Menu item.
	 * @param int      $depth             Depth.
	 * @param stdClass $args              Menu args.
	 * @param int      $current_object_id Current object ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item         = $data_object;
		$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
		$is_external  = ! empty( $item->target ) && '_blank' === $item->target;

		if ( 0 === $depth ) {
			$li_class = 'nav__item' . ( $has_children ? ' nav__item--has-submenu' : '' );
			$output  .= '<li class="' . esc_attr( $li_class ) . '">';
		} else {
			$output .= '<li>';
		}

		$attributes = ' href="' . esc_url( $item->url ) . '"';

		if ( 0 === $depth ) {
			$attributes .= ' class="nav__link"';
		}

		if ( $has_children && 0 === $depth ) {
			$attributes .= ' aria-haspopup="true" aria-expanded="false"';
		}

		if ( $is_external ) {
			$attributes .= ' target="_blank" rel="noopener noreferrer"';
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$output .= '<a' . $attributes . '>' . esc_html( $title );

		if ( $is_external ) {
			$output .= ' ↗<span class="visually-hidden"> (se abre en nueva pestaña)</span>';
		}

		$output .= '</a>';
	}

	/**
	 * Close a menu item.
	 *
	 * @param string   $output      Output buffer.
	 * @param WP_Post  $data_object Menu item.
	 * @param int      $depth       Depth.
	 * @param stdClass $args        Menu args.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * Footer menu walker: flat list items with footer__link anchors,
 * preserving the static external-link pattern.
 */
class Revistalogos_Footer_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a menu item.
	 *
	 * @param string   $output            Output buffer.
	 * @param WP_Post  $data_object       Menu item.
	 * @param int      $depth             Depth.
	 * @param stdClass $args              Menu args.
	 * @param int      $current_object_id Current object ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item        = $data_object;
		$is_external = ! empty( $item->target ) && '_blank' === $item->target;

		$attributes = ' href="' . esc_url( $item->url ) . '" class="footer__link"';

		if ( $is_external ) {
			$attributes .= ' target="_blank" rel="noopener noreferrer"';
		}

		$output .= '<li><a' . $attributes . '>' . esc_html( $item->title );

		if ( $is_external ) {
			$output .= '<span class="visually-hidden"> (se abre en nueva pestaña)</span>';
		}

		$output .= '</a>';
	}

	/**
	 * Close a menu item.
	 *
	 * @param string   $output      Output buffer.
	 * @param WP_Post  $data_object Menu item.
	 * @param int      $depth       Depth.
	 * @param stdClass $args        Menu args.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
