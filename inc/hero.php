<?php
/**
 * Theme includes.
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero configuration for the current template context.
 * Use this to decide whether to show the hero and what data to pass to partials/hero/hero.php.
 *
 * - Pages (incl. front): editor chooses via ACF "Hero Style" (show hero when not "none"); data from ACF + featured image.
 * - Single post: hero shown automatically with post title, meta, featured image.
 * - Blog index: hero off; stories layout in index.php (featured post + grid) replaces the old title band.
 * - Archive: hero shown automatically with archive title and description.
 *
 * @return array{ show: bool, type: string, data: array } 'show', 'type' (landing|medium|initiative|post|blog|archive|single), 'data' (headline, paragraph, image_id, ctas, title, etc.).
 */
function tectn_get_hero_config() {
  static $config = null;
  if ( $config !== null ) {
    return $config;
  }

  $default = array( 'show' => false, 'type' => 'landing', 'data' => array() );

  // Events archive: same hero as Hero Medium; options from Site Settings > Events (no wave, gradient or solid, headline only)
  if ( is_post_type_archive( 'tribe_events' ) && ! is_singular( 'tribe_events' ) ) {
    $use_solid = function_exists( 'get_field' ) ? (bool) tectn_get_events_option( 'events_hero_use_solid_color' ) : false;
    $bg_color  = function_exists( 'get_field' ) ? tectn_get_events_option( 'events_hero_background_color' ) : '';
    $bg_image  = function_exists( 'get_field' ) ? tectn_get_events_option( 'events_hero_background_image' ) : 0;
    if ( is_array( $bg_image ) && ! empty( $bg_image['ID'] ) ) {
      $bg_image = (int) $bg_image['ID'];
    } elseif ( ! is_numeric( $bg_image ) ) {
      $bg_image = 0;
    }
    $headline = function_exists( 'get_field' ) ? tectn_get_events_option( 'events_hero_headline_text' ) : '';
    $show_hero = $use_solid && $bg_color || $bg_image || $headline !== '';
    $config = array(
      'show' => true,
      'type' => 'medium',
      'data' => array(
        'background_type'   => $use_solid ? 'color' : 'image',
        'background_color'  => $bg_color ? $bg_color : '#238c55',
        'background_image' => $use_solid ? 0 : $bg_image,
        'headline_text'     => is_string( $headline ) ? $headline : '',
      ),
    );
    return $config;
  }

  // Singular post/CPT only (not pages): hero type "single" for single.php; pages use landing/initiative below
  if ( is_singular() && ! is_page() ) {
    $post = get_queried_object();
    if ( ! $post || ! isset( $post->ID ) ) {
      $config = $default;
      return $config;
    }
    $post_id   = $post->ID;
    $post_type = $post->post_type;
    $image_id  = get_post_thumbnail_id( $post_id );
    $has_image = (int) $image_id > 0;

    $author_link = '';
    if ( $post_type !== 'tribe_events' && post_type_supports( $post_type, 'author' ) ) {
      $author_id   = (int) $post->post_author;
      $author_name = get_the_author_meta( 'display_name', $author_id );
      $count       = count_user_posts( $author_id, $post_type );
      if ( $count > 1 ) {
        $author_link = '<a class="url fn n" href="' . esc_url( get_author_posts_url( $author_id ) ) . '" rel="author" itemprop="author">' . esc_html( $author_name ) . '</a>';
      } else {
        $author_link = '<span class="author" itemprop="author">' . esc_html( $author_name ) . '</span>';
      }
    }

    $date_display = get_the_date( '', $post );
    $date_iso     = get_the_date( 'c', $post );
    if ( $post_type === 'tribe_events' && function_exists( 'tribe_get_start_date' ) ) {
      $event_date = tribe_get_start_date( $post_id, false, get_option( 'date_format' ) );
      $event_iso  = tribe_get_start_date( $post_id, false, 'c' );
      if ( $event_date !== false && $event_date !== '' ) {
        $date_display = $event_date;
      }
      if ( $event_iso !== false && $event_iso !== '' ) {
        $date_iso = $event_iso;
      }
    }

    $category_terms = array();
    $taxonomies     = get_object_taxonomies( $post_type, 'objects' );
    foreach ( $taxonomies as $tax ) {
      if ( ! $tax->hierarchical ) {
        continue;
      }
      $terms = get_the_terms( $post_id, $tax->name );
      if ( $terms && ! is_wp_error( $terms ) ) {
        $category_terms = $terms;
        break;
      }
    }

    $config = array(
      'show' => true,
      'type' => 'single',
      'data' => array(
        'title'          => get_the_title( $post ),
        'date'           => $date_display,
        'date_iso'       => $date_iso,
        'author_link'    => $author_link,
        'category_terms' => $category_terms,
        'image_id'       => $image_id,
        'has_image'      => $has_image,
      ),
    );
    return $config;
  }

  // Blog index (posts page): hero disabled — index.php uses featured story + “Stories” grid (see partials/blog-stories-featured.php).
  if ( is_home() && ! is_front_page() ) {
    $config = array(
      'show' => false,
      'type' => 'blog',
      'data' => array(),
    );
    return $config;
  }

  // Archive: automatic hero with archive title and description
  if ( is_archive() ) {
    $config = array(
      'show' => true,
      'type' => 'archive',
      'data' => array(
        'title'       => get_the_archive_title( '', false ),
        'description' => get_the_archive_description(),
      ),
    );
    return $config;
  }

  // Pages (including front page): editor choice via ACF Hero Style
  if ( is_front_page() || is_page() ) {
    $hero_style = get_field( 'hero_style' );
    $show       = is_front_page() || ( $hero_style && $hero_style !== 'none' );
    if ( ! $show ) {
      $config = $default;
      return $config;
    }
    $post = get_queried_object();
    if ( ! $post ) {
      $config = $default;
      return $config;
    }
    $image_id = get_post_thumbnail_id( $post->ID );
    if ( ! $image_id && function_exists( 'get_field' ) ) {
      $hero_img = get_field( 'hero_image', $post->ID );
      if ( is_array( $hero_img ) && ! empty( $hero_img['ID'] ) ) {
        $image_id = (int) $hero_img['ID'];
      } elseif ( is_numeric( $hero_img ) ) {
        $image_id = (int) $hero_img;
      }
    }
    // Initiative hero: use initiative_hero_options group; background = page featured image unless "use solid color" is on
    if ( $hero_style === 'initiative' ) {
      $init = get_field( 'initiative_hero_options', $post->ID );
      if ( ! is_array( $init ) ) {
        $init = array();
      }
      $use_solid_color = ! empty( $init['use_solid_color'] );
      $logo_id = 0;
      if ( ! empty( $init['logo'] ) ) {
        $logo_id = is_array( $init['logo'] ) && ! empty( $init['logo']['ID'] ) ? (int) $init['logo']['ID'] : (int) $init['logo'];
      }
      $headline_text = isset( $init['headline_text'] ) ? $init['headline_text'] : '';
      if ( is_array( $headline_text ) ) {
        // Clone with display "group" returns array; WYSIWYG is under cloned field key or name
        $headline_text = isset( $headline_text['field_68225159eee20'] ) ? $headline_text['field_68225159eee20'] : ( isset( $headline_text['hero_headline'] ) ? $headline_text['hero_headline'] : '' );
        if ( is_array( $headline_text ) ) {
          $headline_text = trim( implode( ' ', array_filter( $headline_text, 'is_string' ) ) );
        }
      }
      $headline_text = is_string( $headline_text ) ? $headline_text : '';
      $config = array(
        'show' => true,
        'type' => 'initiative',
        'data' => array(
          'background_type'   => $use_solid_color ? 'color' : 'image',
          'background_image'   => $use_solid_color ? 0 : $image_id,
          'background_color'  => isset( $init['background_color'] ) ? $init['background_color'] : '#238c55',
          'headline_type'      => isset( $init['headline_type'] ) ? $init['headline_type'] : 'text',
          'logo_id'            => $logo_id,
          'headline_text'      => $headline_text,
          'gradient_overlay'   => isset( $init['gradient_overlay'] ) ? (bool) $init['gradient_overlay'] : true,
        ),
      );
      return $config;
    }

    // Medium hero: solid color or featured image bg + headline only.
    if ( $hero_style === 'medium' ) {
      $medium = get_field( 'medium_hero_options', $post->ID );
      if ( ! is_array( $medium ) ) {
        $medium = array();
      }

      $page_title   = get_the_title( $post->ID );
      $headline_wys = function_exists( 'get_field' ) ? get_field( 'hero_headline', $post->ID ) : '';
      $headline_wys = is_string( $headline_wys ) ? trim( $headline_wys ) : '';
      $headline_text = $headline_wys !== '' ? $headline_wys : $page_title;

      $use_solid_color   = ! empty( $medium['use_solid_color'] );
      $background_color  = isset( $medium['background_color'] ) && $medium['background_color'] ? $medium['background_color'] : '#238c55';
      $page_image_id     = (int) get_post_thumbnail_id( $post->ID );

      // If user picked image mode but the page has no featured image, hide the entire hero.
      if ( ! $use_solid_color && $page_image_id <= 0 ) {
        return $default;
      }

      $config = array(
        'show' => true,
        'type' => 'medium',
        'data' => array(
          'background_type'   => $use_solid_color ? 'color' : 'image',
          'background_image'  => $use_solid_color ? 0 : $page_image_id,
          'background_color'  => $background_color,
          'headline_text'     => $headline_text,
        ),
      );
      return $config;
    }

    // Small hero: half height of medium; background can be solid or none; no image option.
    if ( $hero_style === 'small' ) {
      $small = get_field( 'small_hero_options', $post->ID );
      if ( ! is_array( $small ) ) {
        $small = array();
      }

      $page_title   = get_the_title( $post->ID );
      $headline_wys = function_exists( 'get_field' ) ? get_field( 'hero_headline', $post->ID ) : '';
      $headline_wys = is_string( $headline_wys ) ? trim( $headline_wys ) : '';
      $headline_text = $headline_wys !== '' ? $headline_wys : $page_title;

      $use_solid_color  = ! empty( $small['use_solid_color'] );
      $background_color = isset( $small['background_color'] ) && $small['background_color'] ? $small['background_color'] : '#238c55';
      if ( ! $use_solid_color ) {
        $background_color = '';
      }

      $text_color_mode = isset( $small['text_color'] ) ? (string) $small['text_color'] : 'light';
      if ( $text_color_mode !== 'dark' && $text_color_mode !== 'light' ) {
        $text_color_mode = 'light';
      }

      $config = array(
        'show' => true,
        'type' => 'medium',
        'data' => array(
          'background_type'  => 'color',
          'background_image' => 0,
          'background_color' => $background_color,
          'headline_text'    => $headline_text,
          'size'              => 'small',
          'text_color'       => $text_color_mode,
        ),
      );
      return $config;
    }

    $ctas = array();
    if ( function_exists( 'have_rows' ) && have_rows( 'hero_cta', $post->ID ) ) {
      while ( have_rows( 'hero_cta', $post->ID ) ) {
        the_row();
        $link = get_sub_field( 'hero_cta_button' );
        if ( ! empty( $link['url'] ) ) {
          $ctas[] = array(
            'url'    => $link['url'],
            'title'  => isset( $link['title'] ) ? $link['title'] : '',
            'target' => isset( $link['target'] ) ? $link['target'] : '_self',
          );
        }
      }
    }
    $config = array(
      'show' => true,
      'type' => 'landing',
      'data' => array(
        'headline'             => get_field( 'hero_headline', $post->ID ),
        'paragraph'             => get_field( 'hero_paragraph', $post->ID ),
        'image_id'              => $image_id,
        'ctas'                  => $ctas,
        'include_featured_post' => (bool) get_field( 'include_featured_post', $post->ID ),
      ),
    );
    return $config;
  }

  $config = $default;
  return $config;
}
