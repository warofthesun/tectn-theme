<?php
/**
 * Theme includes.
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse headline_size (from ACF field_6992657b77c7f) into tag and class for output.
 * When "Hero" is selected (value contains "hero"), returns h2 with class "hero" plus any block class.
 *
 * @param string $headline_size Value from get_field('headline_size').
 * @param string $block_class   Optional block-specific class to append (e.g. c-slider__headline).
 * @return array{ tag: string, class: string } Safe tag (h1-h6) and combined class string.
 */
function tectn_headline_tag_and_class( $headline_size, $block_class = '' ) {
	$headline_size = (string) $headline_size;
	$tag           = preg_replace( '/\s.*/', '', $headline_size );
	$tag           = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $tag : 'h2';
	$is_hero       = ( strpos( $headline_size, 'hero' ) !== false );
	if ( $is_hero ) {
		$tag   = 'h2';
		$class = 'hero';
	} else {
		$class = '';
		if ( preg_match( '/class\s*=\s*["\']?([^"\']*)["\']?/', $headline_size, $m ) ) {
			$class = trim( $m[1] );
		}
	}
	if ( $block_class !== '' ) {
		$class = trim( $class . ' ' . $block_class );
	}
	return array( 'tag' => $tag, 'class' => $class );
}

/**
 * Shared BEM class list for content-group block (text + image layout).
 * Use in blocks/partials that output c-content-group to avoid duplication.
 *
 * @param array $args Keys: content_position (middle|bottom), image_position (left), row_one, row_two.
 * @return string[] Class list for the wrapper.
 */
function tectn_content_group_classes( $args = array() ) {
  $classes = array( 'c-content-group' );
  $args = wp_parse_args( $args, array(
    'content_position' => '',
    'image_position'   => '',
    'row_one'          => false,
    'row_two'          => false,
  ) );
  if ( $args['content_position'] === 'middle' ) {
    $classes[] = 'c-content-group--middle';
  }
  if ( $args['content_position'] === 'bottom' ) {
    $classes[] = 'c-content-group--bottom';
  }
  if ( $args['image_position'] === 'left' ) {
    $classes[] = 'c-content-group--reverse';
  }
  if ( ! empty( $args['row_one'] ) ) {
    $classes[] = 'c-content-group--row-one';
  }
  if ( ! empty( $args['row_two'] ) ) {
    $classes[] = 'c-content-group--row-two';
  }
  return $classes;
}
