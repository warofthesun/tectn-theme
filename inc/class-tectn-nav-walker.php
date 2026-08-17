<?php
/**
 * Header nav walker: split parent link + submenu toggle for mobile accordion.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom nav menu walker.
 */
class Tectn_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Submenu ID for the next start_lvl call (set in start_el for parents).
	 *
	 * @var string
	 */
	private $pending_submenu_id = '';

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );

		$classes = array( 'sub-menu' );
		$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id_attr = '';
		if ( $this->pending_submenu_id !== '' ) {
			$id_attr = ' id="' . esc_attr( $this->pending_submenu_id ) . '"';
			$this->pending_submenu_id = '';
		}

		$output .= "{$n}{$indent}<ul{$class_names}{$id_attr}>{$n}";
	}

	/**
	 * Starts the element output.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$has_children = in_array( 'menu-item-has-children', $classes, true );
		if ( ! $has_children && isset( $args->has_children ) ) {
			$has_children = (bool) $args->has_children;
		}

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . '>';

		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		if ( '_blank' === $item->target && empty( $item->xfn ) ) {
			$atts['rel'] = 'noopener noreferrer';
		} else {
			$atts['rel'] = ! empty( $item->xfn ) ? $item->xfn : '';
		}
		$atts['href']         = ! empty( $item->url ) ? $item->url : '';
		$atts['aria-current'] = $item->current ? 'page' : '';

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && $value !== '' && $value !== false ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$item_output  = isset( $args->before ) ? $args->before : '';
		$link_output  = '<a' . $attributes . '>';
		$link_output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . $title . ( isset( $args->link_after ) ? $args->link_after : '' );
		$link_output .= '</a>';

		if ( $has_children ) {
			$submenu_id = 'submenu-' . $item->ID;
			$this->pending_submenu_id = $submenu_id;

			$toggle_label = sprintf(
				/* translators: %s: parent menu item title */
				__( 'Toggle submenu for %s', 'tectn_theme' ),
				wp_strip_all_tags( $title )
			);

			$item_output .= '<div class="nav-item__row">';
			$item_output .= $link_output;
			$item_output .= '<button type="button" class="nav-submenu-toggle" aria-expanded="false" aria-controls="' . esc_attr( $submenu_id ) . '" aria-label="' . esc_attr( $toggle_label ) . '">';
			$item_output .= '<i class="fas fa-chevron-down" aria-hidden="true"></i>';
			$item_output .= '</button>';
			$item_output .= '</div>';
		} else {
			$item_output .= $link_output;
		}

		$item_output .= isset( $args->after ) ? $args->after : '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}