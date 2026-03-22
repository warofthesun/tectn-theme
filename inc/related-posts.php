<?php
/**
 * Theme includes.
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get related posts: up to $limit posts by category first, then tag, then recency.
 * Excludes $post_id. Only returns published posts of the same $post_type.
 *
 * @param int    $post_id   Current post ID.
 * @param string $post_type Post type (e.g. 'post', 'tribe_events').
 * @param int    $limit     Max number of posts (default 3).
 * @return WP_Post[] Array of post objects.
 */
function tectn_get_related_posts( $post_id, $post_type, $limit = 3 ) {
  $post_id   = (int) $post_id;
  $limit     = max( 1, min( 10, (int) $limit ) );
  $post_type = $post_type ? $post_type : 'post';
  $found    = array();

  $tax_query = array();
  $cat_ids   = array();
  $tag_ids   = array();

  if ( $post_type === 'post' ) {
    $categories = get_the_category( $post_id );
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
      $cat_ids = array_map( 'intval', wp_list_pluck( $categories, 'term_id' ) );
    }
    $tags = get_the_tags( $post_id );
    if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
      $tag_ids = array_map( 'intval', wp_list_pluck( $tags, 'term_id' ) );
    }
  }

  // 1) By category (same post type only)
  if ( ! empty( $cat_ids ) ) {
    $q = new WP_Query( array(
      'post_type'      => $post_type,
      'post_status'    => 'publish',
      'post__not_in'   => array( $post_id ),
      'posts_per_page' => $limit,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'fields'         => 'ids',
      'no_found_rows'  => true,
      'tax_query'      => array(
        array(
          'taxonomy' => 'category',
          'field'    => 'term_id',
          'terms'    => $cat_ids,
        ),
      ),
    ) );
    $found = array_merge( $found, $q->posts );
  }

  // 2) By tag if we need more (exclude already found)
  if ( count( $found ) < $limit && ! empty( $tag_ids ) ) {
    $exclude = array_merge( array( $post_id ), $found );
    $q = new WP_Query( array(
      'post_type'      => $post_type,
      'post_status'    => 'publish',
      'post__not_in'   => $exclude,
      'posts_per_page' => $limit - count( $found ),
      'orderby'        => 'date',
      'order'          => 'DESC',
      'fields'         => 'ids',
      'no_found_rows'  => true,
      'tax_query'      => array(
        array(
          'taxonomy' => 'post_tag',
          'field'    => 'term_id',
          'terms'    => $tag_ids,
        ),
      ),
    ) );
    $found = array_merge( $found, $q->posts );
  }

  // 3) By recency to fill remaining
  if ( count( $found ) < $limit ) {
    $exclude = array_merge( array( $post_id ), $found );
    $q = new WP_Query( array(
      'post_type'      => $post_type,
      'post_status'    => 'publish',
      'post__not_in'   => $exclude,
      'posts_per_page' => $limit - count( $found ),
      'orderby'        => 'date',
      'order'          => 'DESC',
      'no_found_rows'  => true,
    ) );
    $found = array_merge( $found, $q->posts );
  }

  $found = array_slice( array_unique( array_filter( array_map( 'intval', $found ) ) ), 0, $limit );
  if ( empty( $found ) ) {
    return array();
  }

  $posts = get_posts( array(
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'post__in'       => $found,
    'orderby'        => 'post__in',
    'posts_per_page' => $limit,
  ) );

  return is_array( $posts ) ? $posts : array();
}

/**
 * Get Related Posts section settings from Post Settings (Site Settings > Post Settings).
 * Returns heading and background image for the given post type. Reads from the Post Settings options page.
 *
 * @param string $post_type Post type (e.g. 'post', 'tribe_events', 'pickup_site').
 * @return array{ heading: string, background_image_id: int, background_image_url: string }
 */
function tectn_get_related_posts_settings( $post_type = 'post' ) {
  $post_type = $post_type ? $post_type : 'post';
  $key       = sanitize_key( $post_type );
  $defaults   = array(
    'post'          => __( 'Related Posts', 'tectn_theme' ),
    'tribe_events'  => __( 'Related Events', 'tectn_theme' ),
    'pickup_site'   => __( 'Related Pick Up Locations', 'tectn_theme' ),
  );
  $heading = isset( $defaults[ $key ] ) ? $defaults[ $key ] : __( 'Related Posts', 'tectn_theme' );
  $bg_id   = 0;
  $bg_url  = '';

  if ( ! function_exists( 'get_field' ) ) {
    return array(
      'heading'              => $heading,
      'background_image_id'  => 0,
      'background_image_url' => '',
    );
  }

  $options_page = 'post-settings';
  $heading_field = 'related_posts_heading_' . $key;
  $bg_field      = 'background_image_' . $key;

  $saved_heading = get_field( $heading_field, $options_page );
  if ( is_string( $saved_heading ) && trim( $saved_heading ) !== '' ) {
    $heading = trim( $saved_heading );
  }

  $img = get_field( $bg_field, $options_page );
  if ( is_array( $img ) && ! empty( $img['ID'] ) ) {
    $bg_id = (int) $img['ID'];
  } elseif ( is_numeric( $img ) ) {
    $bg_id = (int) $img;
  }

  if ( $bg_id > 0 ) {
    $src   = wp_get_attachment_image_src( $bg_id, 'large' );
    $bg_url = $src ? $src[0] : '';
  }

  return array(
    'heading'              => $heading,
    'background_image_id'  => $bg_id,
    'background_image_url' => $bg_url,
  );
}
