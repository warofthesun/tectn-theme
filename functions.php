<?php

// LOAD starter CORE (if you remove this, the theme will break)
require_once( 'library/starter.php' );

// CUSTOMIZE THE WORDPRESS ADMIN (off by default)
// require_once( 'library/admin.php' );

/**
 * Register ACF blocks (single source of truth).
 */
function tectn_register_acf_blocks() {
  $block_dirs = glob( __DIR__ . '/blocks/*', GLOB_ONLYDIR );
  if ( ! $block_dirs ) {
    return;
  }
  foreach ( $block_dirs as $dir ) {
    register_block_type( $dir );
  }
}
add_action( 'init', 'tectn_register_acf_blocks' );

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

/**
 * Resolve the blog index “featured” post: newest sticky post, else newest published post.
 * Cached per request. Does not check is_home() — safe for pre_get_posts.
 *
 * @return int Post ID or 0.
 */
function tectn_resolve_blog_featured_post_id() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$cached = 0;
	$sticky = get_option( 'sticky_posts' );
	if ( is_array( $sticky ) && ! empty( $sticky ) ) {
		$sticky = array_values( array_filter( array_map( 'intval', $sticky ) ) );
		$q      = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'post__in'            => $sticky,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'posts_per_page'      => 1,
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		if ( ! empty( $q->posts ) ) {
			$cached = (int) $q->posts[0];
			wp_reset_postdata();
			return $cached;
		}
		wp_reset_postdata();
	}
	$recent = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( ! empty( $recent ) ) {
		$cached = (int) $recent[0];
	}
	return $cached;
}

/**
 * Featured post ID for the posts index template only.
 *
 * @return int Post ID or 0.
 */
function tectn_get_blog_featured_post_id() {
	if ( ! is_home() || is_front_page() ) {
		return 0;
	}
	return tectn_resolve_blog_featured_post_id();
}

/**
 * Exclude the featured post from the main blog loop; ignore sticky ordering in the grid.
 *
 * @param WP_Query $query Main query.
 */
function tectn_blog_index_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() || $query->is_front_page() ) {
		return;
	}
	$fid = tectn_resolve_blog_featured_post_id();
	if ( $fid > 0 ) {
		$not_in = (array) $query->get( 'post__not_in' );
		$not_in[] = $fid;
		$query->set( 'post__not_in', array_values( array_unique( array_map( 'intval', $not_in ) ) ) );
	}

	/*
	 * Stories grid is 3 columns at desktop; “posts per page” defaults (e.g. 10) or +1 fixes leave a
	 * hole in the last row (10→3+3+3+1, 11→3+3+3+2). Round up to a multiple of 3.
	 */
	$base_ppp = (int) $query->get( 'posts_per_page' );
	if ( $base_ppp < 1 ) {
		$base_ppp = (int) get_option( 'posts_per_page' );
	}
	if ( $base_ppp < 1 ) {
		$base_ppp = 10;
	}
	$grid_cols   = 3;
	$rounded_ppp = (int) ceil( $base_ppp / $grid_cols ) * $grid_cols;
	$query->set( 'posts_per_page', max( $grid_cols, $rounded_ppp ) );

	$query->set( 'ignore_sticky_posts', true );
}
add_action( 'pre_get_posts', 'tectn_blog_index_pre_get_posts', 5 );

/**
 * Body class for the posts index template (stories layout + scoped CSS).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tectn_body_class_stories_index( $classes ) {
	if ( is_home() && ! is_front_page() ) {
		$classes[] = 'tectn-stories-index';
	}
	return $classes;
}
add_filter( 'body_class', 'tectn_body_class_stories_index' );

/**
 * Blog index: scroll #tectn-stories-header to 50px from top after paginated loads.
 */
function tectn_enqueue_blog_stories_scroll_script() {
	if ( ! is_home() || is_front_page() ) {
		return;
	}
	$theme_uri = get_stylesheet_directory_uri();
	$path      = get_template_directory() . '/library/js/tectn-blog-stories-scroll.js';
	$ver       = file_exists( $path ) ? (string) filemtime( $path ) : null;
	wp_enqueue_script(
		'tectn-blog-stories-scroll',
		$theme_uri . '/library/js/tectn-blog-stories-scroll.js',
		array(),
		$ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'tectn_enqueue_blog_stories_scroll_script', 20 );

/**
 * Category + tag terms for chip links (categories first), deduped by taxonomy:term_id.
 *
 * @param int $post_id Post ID.
 * @param int $limit   Max terms.
 * @return WP_Term[]
 */
function tectn_get_post_chip_terms_for_links( $post_id, $limit = 4 ) {
	$post_id = (int) $post_id;
	$limit   = max( 1, (int) $limit );
	$terms   = array();
	$seen    = array();

	$cats = get_the_category( $post_id );
	if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
		foreach ( $cats as $t ) {
			$k = $t->taxonomy . ':' . $t->term_id;
			if ( ! isset( $seen[ $k ] ) ) {
				$seen[ $k ] = true;
				$terms[]    = $t;
			}
		}
	}

	$tags = get_the_tags( $post_id );
	if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		foreach ( $tags as $t ) {
			$k = $t->taxonomy . ':' . $t->term_id;
			if ( ! isset( $seen[ $k ] ) ) {
				$seen[ $k ] = true;
				$terms[]    = $t;
			}
		}
	}

	return array_slice( $terms, 0, $limit );
}

/**
 * Output a blog post card (split links — no nested anchors).
 * Post: .c-postCard__mediaLink, .c-postCard__titleLink, optional .c-postCard__cta.
 * Archives: .c-postCard__authorLink, term .c-chip links.
 *
 * @param WP_Post $p Post object.
 * @param array   $args { @type bool $show_cta Whether to output “read more” link to the post. }
 */
function tectn_render_blog_post_card( $p, $args = array() ) {
	if ( ! $p instanceof WP_Post ) {
		return;
	}
	$args = wp_parse_args(
		$args,
		array(
			'show_cta' => false,
		)
	);
	$post_id   = $p->ID;
	$date      = get_the_date( 'm/d/y', $post_id );
	$date_iso  = get_the_date( 'c', $post_id );
	$title     = get_the_title( $post_id );
	$permalink = get_permalink( $post_id );
	$img_url   = get_the_post_thumbnail_url( $post_id, 'post-card' );

	$author_id   = post_type_supports( 'post', 'author' ) ? (int) $p->post_author : 0;
	$author_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
	$author_url  = $author_id ? get_author_posts_url( $author_id ) : '';

	$chip_terms = tectn_get_post_chip_terms_for_links( $post_id, 3 );
	?>
	<article class="c-postCard c-postCard--split">
		<a class="c-postCard__mediaLink" href="<?php echo esc_url( $permalink ); ?>">
			<div class="c-postCard__media">
				<?php if ( $img_url ) : ?>
					<img class="c-postCard__img" src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
				<?php endif; ?>
			</div>
		</a>
		<div class="c-postCard__body">
			<h3 class="c-postCard__title"><a class="c-postCard__titleLink" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
			<?php if ( $author_name || $date ) : ?>
			<h6 class="c-postCard__meta">
				<?php if ( $author_name && $author_url ) : ?>
					<span class="c-postCard__meta-item"><i class="fa-regular fa-circle-user c-postCard__meta-icon" aria-hidden="true"></i><a class="c-postCard__authorLink" href="<?php echo esc_url( $author_url ); ?>" rel="author"><?php echo esc_html( $author_name ); ?></a></span>
				<?php endif; ?>
				<?php if ( $date ) : ?>
					<span class="c-postCard__meta-item"><i class="fa-regular fa-circle-calendar c-postCard__meta-icon" aria-hidden="true"></i><time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date ); ?></time></span>
				<?php endif; ?>
			</h6>
			<?php endif; ?>
			<?php if ( ! empty( $chip_terms ) ) : ?>
				<div class="c-postCard__chips">
					<?php foreach ( $chip_terms as $term ) : ?>
						<?php
						$tlink = get_term_link( $term );
						if ( is_wp_error( $tlink ) ) {
							continue;
						}
						?>
						<a class="c-chip" href="<?php echo esc_url( $tlink ); ?>"><?php echo esc_html( $term->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $args['show_cta'] ) ) : ?>
				<a class="c-postCard__cta" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'read more', 'tectn_theme' ); ?></a>
			<?php endif; ?>
		</div>
	</article>
	<?php
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

  // Events archive: same hero as Hero Medium; options from Theme Settings > Events (no wave, gradient or solid, headline only)
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

/**
 * Get the external URL for an event, if set, normalized with https://.
 *
 * @param int $event_id Event post ID.
 * @return string External URL or empty string.
 */
function tectn_get_event_external_url( $event_id ) {
  $event_id = (int) $event_id;
  if ( $event_id <= 0 || get_post_type( $event_id ) !== 'tribe_events' ) {
    return '';
  }
  if ( ! function_exists( 'get_field' ) ) {
    return '';
  }
  $raw = get_field( 'external_event_url', $event_id );
  if ( ! is_string( $raw ) ) {
    return '';
  }
  $raw = trim( $raw );
  if ( $raw === '' ) {
    return '';
  }
  // Auto-add https:// when the scheme is missing.
  if ( ! preg_match( '#^https?://#i', $raw ) ) {
    $raw = 'https://' . ltrim( $raw, '/' );
  }
  $url = esc_url_raw( $raw );
  return $url ? $url : '';
}

/**
 * Whether events should require an external URL (from Post Settings > Events).
 *
 * @return bool
 */
function tectn_events_require_external_url() {
  if ( ! function_exists( 'get_field' ) ) {
    return false;
  }
  $required = get_field( 'events_require_external_url', 'post-settings' );
  return (bool) $required;
}

/**
 * Override tribe_events event links globally with the external URL when present.
 */
function tectn_filter_tribe_event_links( $url, $post, $leavename, $sample ) {
  if ( ! $post || $post->post_type !== 'tribe_events' ) {
    return $url;
  }
  $external = tectn_get_event_external_url( $post->ID );
  return $external ? $external : $url;
}
add_filter( 'post_type_link', 'tectn_filter_tribe_event_links', 10, 4 );

if ( function_exists( 'tribe_get_event_link' ) ) {
  /**
   * Mirror override for TEC helper links, when used.
   *
   * @param string $link
   * @param int    $event_id
   * @return string
   */
  function tectn_filter_tribe_get_event_link( $link, $event_id ) {
    $external = tectn_get_event_external_url( $event_id );
    return $external ? $external : $link;
  }
  add_filter( 'tribe_get_event_link', 'tectn_filter_tribe_get_event_link', 10, 2 );
}

/*********************
LAUNCH starter
Let's get everything up and running.
*********************/

function tectn_setup() {

  // Add theme support for editor styles
  add_theme_support( 'editor-styles' );

  //Allow editor style.
  add_editor_style( '/library/css/style.css' );

  add_theme_support( 'align-wide' );

  // let's get language support going, if you need it
  load_theme_textdomain( 'tectn_theme', get_template_directory() . '/library/translation' );

  // USE THIS TEMPLATE TO CREATE CUSTOM POST TYPES EASILY
  require_once( 'library/custom-post-type.php' );

  require_once get_template_directory() . '/inc/acf_inc.php';

  // launching operation cleanup
  add_action( 'init', 'starter_head_cleanup' );
  // A better title
  add_filter( 'wp_title', 'rw_title', 10, 3 );
  // remove WP version from RSS
  add_filter( 'the_generator', 'starter_rss_version' );
  // remove pesky injected css for recent comments widget
  add_filter( 'wp_head', 'starter_remove_wp_widget_recent_comments_style', 1 );
  // clean up comment styles in the head
  add_action( 'wp_head', 'starter_remove_recent_comments_style', 1 );
  // clean up gallery output in wp
  add_filter( 'gallery_style', 'starter_gallery_style' );

  // enqueue base scripts and styles
  add_action( 'wp_enqueue_scripts', 'starter_scripts_and_styles', 999 );
  // launching this stuff after theme setup
  starter_theme_support();

  // adding sidebars to Wordpress (these are created in functions.php)
  add_action( 'widgets_init', 'starter_register_sidebars' );

  // cleaning up random code around images
  add_filter( 'the_content', 'starter_filter_ptags_on_images' );
  // cleaning up excerpt
  add_filter( 'excerpt_more', 'starter_excerpt_more' );

  //add additional menu locations
  register_nav_menu( 'login_forms', 'Login' );

} /* end tectn_setup */

// let's get this party started
add_action( 'after_setup_theme', 'tectn_setup' );


/************* OEMBED SIZE OPTIONS *************/

if ( ! isset( $content_width ) ) {
	$content_width = 680;
}

/************* THUMBNAIL SIZE OPTIONS *************/

// Thumbnail sizes
add_image_size( 'tectn-thumb-600', 600, 150, true );
add_image_size( 'tectn-thumb-300', 300, 100, true );
add_image_size( 'gallery-image', 680, 450, true );
add_image_size( 'hero-bg', 1920, 1080, true ); // Hero background; use instead of full for better mobile performance
add_image_size( 'post-card', 800, 600, true ); // Post cards loop: 4:3 aspect-ratio to match .c-postCard__media
add_image_size( 'card-block', 400, 400, true ); // Card block: square for .c-card-block__card-media
add_image_size( 'tectn_slider_square', 800, 800, true ); // Slider slideshow: square crop

/*
to add more sizes, simply copy a line from above
and change the dimensions & name. As long as you
upload a "featured image" as large as the biggest
set width or height, all the other sizes will be
auto-cropped.

To call a different size, simply change the text
inside the thumbnail function.

For example, to call the 300 x 100 sized image,
we would use the function:
<?php the_post_thumbnail( 'tectn-thumb-300' ); ?>
for the 600 x 150 image:
<?php the_post_thumbnail( 'tectn-thumb-600' ); ?>

You can change the names and dimensions to whatever
you like. Enjoy!
*/

add_filter( 'image_size_names_choose', 'starter_custom_image_sizes' );

function starter_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'gallery-image' => __('Gallery Image'),
        'hero-bg' => __('Hero background (1920×1080)'),
        'tectn-thumb-600' => __('600px by 150px'),
        'tectn-thumb-300' => __('300px by 100px'),
        'post-card' => __('Post card (800×600)'),
    ) );
}

/*
The function above adds the ability to use the dropdown menu to select
the new images sizes you have just created from within the media manager
when you add media to your content blocks. If you add more image sizes,
duplicate one of the lines in the array and name it according to your
new image size.
*/

/* Google Maps API key: env var > ACF Theme Settings > wp option. Restrict key by HTTP referrer in Google Cloud Console. */
function tectn_google_maps_api_key() {
	if ( defined( 'GOOGLE_MAPS_API_KEY' ) && GOOGLE_MAPS_API_KEY !== '' ) {
		return GOOGLE_MAPS_API_KEY;
	}
	$env = getenv( 'GOOGLE_MAPS_API_KEY' );
	if ( is_string( $env ) && $env !== '' ) {
		return $env;
	}
	if ( function_exists( 'get_field' ) ) {
		$acf = get_field( 'google_maps_api_key', 'option' );
		if ( is_string( $acf ) && $acf !== '' ) {
			return $acf;
		}
	}
	return (string) get_option( 'tectn_google_maps_api_key', '' );
}

function my_acf_init() {
	acf_update_setting( 'google_api_key', tectn_google_maps_api_key() );
}
add_action( 'acf/init', 'my_acf_init' );

add_filter( 'should_load_remote_block_patterns', '__return_false' );


/************* ACTIVE SIDEBARS ********************/

// Sidebars & Widgetizes Areas
function starter_register_sidebars() {
	register_sidebar(array(
		'id' => 'sidebar1',
		'name' => __( 'Sidebar 1', 'tectn_theme' ),
		'description' => __( 'The first (primary) sidebar.', 'tectn_theme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	register_sidebar(array(
		'id' => 'header-widget-area',
		'name' => __( 'Header', 'tectn_theme' ),
		'description' => __( 'Widget area in the header (right of the navigation).', 'tectn_theme' ),
		'before_widget' => '<div id="%1$s" class="widget header-widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	/*
	to add more sidebars or widgetized areas, just copy
	and edit the above sidebar code. In order to call
	your new sidebar just use the following code:

	Just change the name to whatever your new
	sidebar's id is, for example:

	register_sidebar(array(
		'id' => 'sidebar2',
		'name' => __( 'Sidebar 2', 'tectn_theme' ),
		'description' => __( 'The second (secondary) sidebar.', 'tectn_theme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	To call the sidebar in your template, you can just copy
	the sidebar.php file and rename it to your sidebar's name.
	So using the above example, it would be:
	sidebar-sidebar2.php

	*/
} // don't remove this bracket!

/*
 * Remove Original Tag Meta Box - courtesy of https://rudrastyh.com/wordpress/tag-metabox-like-categories.html
 */
function rudr_post_tags_meta_box_remove() {
	$id = 'tagsdiv-post_tag'; // you can find it in a page source code (Ctrl+U)
	$post_type = 'post'; // remove only from post edit screen
	$position = 'side';
	remove_meta_box( $id, $post_type, $position );
}
add_action( 'admin_menu', 'rudr_post_tags_meta_box_remove');

/*
 * Add Category style Tag box
 */
function rudr_add_new_tags_metabox(){
	$id = 'rudrtagsdiv-post_tag'; // it should be unique
	$heading = 'Tags'; // meta box heading
	$callback = 'rudr_metabox_content'; // the name of the callback function
	$post_type = 'post';
	$position = 'side';
	$pri = 'default'; // priority, 'default' is good for us
	add_meta_box( $id, $heading, $callback, $post_type, $position, $pri );
}
add_action( 'admin_menu', 'rudr_add_new_tags_metabox');

/*
 * Fill
 */
 function rudr_metabox_content($post) {
 		// get all blog post tags as an array of objects
 		$all_tags = get_terms( array('taxonomy' => 'post_tag', 'hide_empty' => 0) );
 		// get all tags assigned to a post
 		$all_tags_of_post = get_the_terms( $post->ID, 'post_tag' );

 		// create an array of post tags ids
 		$ids = array();
 		if ( $all_tags_of_post ) {
 			foreach ($all_tags_of_post as $tag ) {
 				$ids[] = $tag->term_id;
 			}
 		}

 		// HTML
 		echo '<div id="taxonomy-post_tag" class="categorydiv">';
 		echo '<div id="tag-all" class="tabs-panel" style="display:block">';
 		echo '<input type="hidden" name="tax_input[post_tag][]" value="0" />';
 		echo '<ul>';
 		foreach( $all_tags as $tag ){
 			// unchecked by default
 			$checked = "";
 			// if an ID of a tag in the loop is in the array of assigned post tags - then check the checkbox
 			if ( in_array( $tag->term_id, $ids ) ) {
 				$checked = " checked='checked'";
 			}
 			$id = 'post_tag-' . $tag->term_id;
 			echo "<li id='{$id}'>";
 			echo "<label><input type='checkbox' name='tax_input[post_tag][]' id='in-$id'". $checked ." value='$tag->slug' /> $tag->name</label><br />";
 			echo "</li>";
 		}
 		echo '</ul></div></div>'; // end HTML
 	}


/************* COMMENT LAYOUT *********************/

// Comment Layout
function starter_comments( $comment, $args, $depth ) {
   $GLOBALS['comment'] = $comment; ?>
  <div id="comment-<?php comment_ID(); ?>" <?php comment_class('cf'); ?>>
    <article  class="cf">
      <header class="comment-author vcard">
        <?php
        /*
          this is the new responsive optimized comment image. It used the new HTML5 data-attribute to display comment gravatars on larger screens only. What this means is that on larger posts, mobile sites don't have a ton of requests for comment images. This makes load time incredibly fast! If you'd like to change it back, just replace it with the regular wordpress gravatar call:
          echo get_avatar($comment,$size='32',$default='<path_to_url>' );
        */
        ?>
        <?php // custom gravatar call ?>
        <?php
          // create variable
          $bgauthemail = get_comment_author_email();
        ?>
        <img data-gravatar="http://www.gravatar.com/avatar/<?php echo md5( $bgauthemail ); ?>?s=40" class="load-gravatar avatar avatar-48 photo" height="40" width="40" src="<?php echo get_template_directory_uri(); ?>/library/images/nothing.gif" />
        <?php // end custom gravatar call ?>
        <?php printf(__( '<cite class="fn">%1$s</cite> %2$s', 'tectn_theme' ), get_comment_author_link(), edit_comment_link(__( '(Edit)', 'tectn_theme' ),'  ','') ) ?>
        <time datetime="<?php echo comment_time('Y-m-j'); ?>"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php comment_time(__( 'F jS, Y', 'tectn_theme' )); ?> </a></time>

      </header>
      <?php if ($comment->comment_approved == '0') : ?>
        <div class="alert alert--info">
          <p><?php _e( 'Your comment is awaiting moderation.', 'tectn_theme' ) ?></p>
        </div>
      <?php endif; ?>
      <section class="comment-content cf">
        <?php comment_text() ?>
      </section>
      <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
    </article>
  <?php // </li> is added by WordPress automatically ?>
<?php
} // don't remove this bracket!


/*
This is a modification of a function found in the
twentythirteen theme where we can declare some
external fonts. If you're using Google Fonts, you
can replace these fonts, change it in your scss files
and be up and running in seconds.
*/
function custom_fonts() {
  // Enqueue Bebas Neue from Google Fonts. Previous fonts (Francois One and Lato) have been removed.
  wp_enqueue_style('tectn-google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap', array(), null);
}

add_action('wp_enqueue_scripts', 'custom_fonts');
add_action('admin_enqueue_scripts', 'custom_fonts');
add_action('enqueue_block_editor_assets', 'custom_fonts');

/**
 * Enqueue Events Calendar overrides after plugin CSS (Skeleton or Full)
 * so theme fonts and colors apply. Loads after the active stylesheet to avoid duplicate enqueue.
 */
function tectn_enqueue_tribe_events_overrides() {
	if ( ! is_post_type_archive( 'tribe_events' ) && ! is_singular( 'tribe_events' ) ) {
		return;
	}
	$path = get_template_directory() . '/tribe-events/tribe-events.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	// Depend on the active style so override loads after it; avoid loading the same file twice.
	wp_dequeue_style( 'tribe-events-views-v2-override-style' );
	$dep = ( function_exists( 'tribe_get_option' ) && 'skeleton' === tribe_get_option( 'stylesheetOption', 'tribe' ) )
		? 'tribe-events-views-v2-skeleton'
		: 'tribe-events-views-v2-full';
	wp_enqueue_style(
		'tectn-tribe-events-overrides',
		get_template_directory_uri() . '/tribe-events/tribe-events.css',
		array( $dep ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'tectn_enqueue_tribe_events_overrides', 100 );

/**
 * Get an Events page option (Theme Settings > Events). Tries 'option' then sub-page slug.
 */
function tectn_get_events_option( $field_name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$val = get_field( $field_name, 'option' );
	if ( $val !== null && $val !== false && $val !== '' ) {
		return $val;
	}
	$val = get_field( $field_name, 'theme-events-settings' );
	return $val;
}

/**
 * Prepend Hero Medium (Events archive) and intro HTML before the calendar view (Theme Settings > Events).
 * When "Default Page Template" is used, hero and intro are already output in archive.php – do not duplicate.
 */
function tectn_events_hero_and_intro_before_html( $before ) {
	if ( ! is_post_type_archive( 'tribe_events' ) || is_singular( 'tribe_events' ) ) {
		return $before;
	}
	// Default Page Template = theme template (archive.php) already shows hero + intro; skip to avoid duplicate.
	if ( function_exists( 'tribe_get_option' ) && tribe_get_option( 'tribeEventsTemplate', 'default' ) === 'default' ) {
		return $before;
	}
	$hero_config = tectn_get_hero_config();
	$out        = '';
	if ( ! empty( $hero_config['show'] ) && $hero_config['type'] === 'medium' ) {
		ob_start();
		$hero_config = $hero_config; // pass to partial
		include get_template_directory() . '/partials/hero/hero.php';
		$out .= ob_get_clean();
	}
	$intro_heading = tectn_get_events_option( 'events_intro_heading' );
	$intro_body    = tectn_get_events_option( 'events_intro_body' );
	if ( ( $intro_heading !== null && $intro_heading !== '' ) || ( $intro_body !== null && $intro_body !== '' ) ) {
		$out .= '<div class="events-intro wrap row">';
		$out .= '<div class="events-intro__inner col-xs-12">';
		if ( $intro_heading !== null && $intro_heading !== '' ) {
			$out .= '<h2 class="events-intro__heading">' . esc_html( $intro_heading ) . '</h2>';
		}
		if ( $intro_body !== null && $intro_body !== '' ) {
			$out .= '<div class="events-intro__body">' . wp_kses_post( $intro_body ) . '</div>';
		}
		$out .= '</div></div>';
	}
	return $out . $before;
}
add_filter( 'tribe_events_before_html', 'tectn_events_hero_and_intro_before_html', 10, 1 );

// Inject event categories (tags) below the body content; structure matches posts-grid chips.
add_action( 'tribe_template_after_include:events/v2/list/event/description', function() {
  global $post;
  if ( ! $post || ! isset( $post->ID ) ) {
    return;
  }
  $taxonomy = Tribe__Events__Main::instance()->get_event_taxonomy();
  $terms    = get_the_terms( $post->ID, $taxonomy );
  if ( ! $terms || is_wp_error( $terms ) ) {
    return;
  }
  echo '<div class="tribe-event-categories c-postCard__chips">';
  foreach ( $terms as $term ) {
    $url = get_term_link( $term, $taxonomy );
    if ( is_wp_error( $url ) ) {
      $url = '#';
    }
    echo '<a href="' . esc_url( $url ) . '" class="c-chip">' . esc_html( $term->name ) . '</a>';
  }
  echo '</div>';
} );

function tectn_fonts_preconnect( $urls, $relation_type ) {
  if ( 'preconnect' === $relation_type ) {
    $urls[] = array( 'href' => 'https://fonts.googleapis.com' );
    $urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => '' );
  }
  return $urls;
}


add_filter( 'wp_resource_hints', 'tectn_fonts_preconnect', 10, 2 );

// Final fallback: enforce palettes via ACF's JS hook (covers editor + classic admin screens)
add_action( 'acf/input/admin_footer', function () {
  ?>
  <script>
    (function(){
      if (!window.acf || !acf.add_filter) return;
      acf.add_filter('color_picker_args', function(args, $field){
        args.palettes = [
          '#EFF5D1', // sage
          '#F0F4EC', // cream
          '#5C6B80', // charcoal
          '#FFFFFF', // white
          '#698F3D'  // green
        ];
        return args;
      });
    })();
  </script>
  <?php
}, 20 );



/* Load ScrollMagic Scripts 

function scrollmagic_scripts() {

		wp_register_script( 'greensock', get_stylesheet_directory_uri() . '/library/js/libs/greensock/TweenMax.min.js', array(), '', true );

    wp_register_script( 'scrollmagic', get_stylesheet_directory_uri() . '/library/scrollmagic/uncompressed/ScrollMagic.js', array(), '', true );

    wp_register_script( 'animation', get_stylesheet_directory_uri() . '/library/scrollmagic/uncompressed/plugins/animation.gsap.js', array(), '', true );

    wp_register_script( 'indicators', get_stylesheet_directory_uri() . '/library/scrollmagic/uncompressed/plugins/debug.addIndicators.js', array(), '', true );



		// enqueue styles and scripts
		wp_enqueue_script( 'greensock' );
		wp_enqueue_script( 'scrollmagic' );
		wp_enqueue_script( 'animation' );
    wp_enqueue_script( 'indicators' );
}

add_action( 'wp_enqueue_scripts', 'scrollmagic_scripts' );
*/


/**
 * Register the required plugins for this theme.
 *
 */

include 'inc/required-plugs.php';

//Ensure ACF Plugin does not exist
if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check if ACF PRO is active
if ( is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
    // Abort all bundling, ACF PRO plugin takes priority
    return;
}

// Check if another plugin or theme has bundled ACF
if ( defined( 'MY_ACF_PATH' ) ) {
    return;
}

// 1. customize ACF path
add_filter('acf/settings/path', 'my_acf_settings_path');
function my_acf_settings_path( $path ) {
    // update path
    $path = get_stylesheet_directory() . '/inc/acf/';
    // return
    return $path;
}
// 2. customize ACF dir
add_filter('acf/settings/dir', 'my_acf_settings_dir');
function my_acf_settings_dir( $dir ) {
    // update path
    $dir = get_stylesheet_directory_uri() . '/inc/acf/';
    // return
    return $dir;
}
// 3. Hide ACF field group menu item
//add_filter('acf/settings/show_admin', '__return_false');

// 4. Include ACF
include_once( get_stylesheet_directory() . '/inc/acf/acf.php' );

// Turn on ACF Options Page
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Header Settings',
		'menu_title'	=> 'Header',
		'parent_slug'	=> 'theme-general-settings',
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Footer Settings',
		'menu_title'	=> 'Footer',
		'parent_slug'	=> 'theme-general-settings',
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Events Page Settings',
		'menu_title'	=> 'Events',
		'menu_slug'     => 'theme-events-settings',
		'parent_slug'	=> 'theme-general-settings',
	));

}

/**
 * Register Post Settings as a sub-page of Site Settings.
 * Must run after ACF registers UI options pages (acf/init priority 6) so parent "site-settings" exists.
 */
function tectn_register_post_settings_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = 'site-settings';
	// Only add if parent exists (Site Settings from ACF UI).
	$pages = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page( array(
		'page_title'   => 'Post Settings',
		'menu_title'   => 'Post Settings',
		'menu_slug'    => 'post-settings',
		'parent_slug'  => $parent,
		'capability'   => 'edit_posts',
		'post_id'      => 'post-settings', // Store/load fields under this key so get_field( $field, 'post-settings' ) works.
	) );
}
add_action( 'acf/init', 'tectn_register_post_settings_options_page', 10 );

/**
 * Register ACF field group for Events page (hero + intro) in Theme Settings > Events.
 */
function tectn_register_acf_events_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'                   => 'group_tectn_events_settings',
		'title'                 => 'Events Page Hero & Intro',
		'fields'                => array(
			array(
				'key'   => 'field_tectn_events_hero_tab',
				'label' => 'Hero',
				'name'  => '',
				'type'  => 'tab',
			),
			array(
				'key'           => 'field_tectn_events_use_solid_color',
				'label'         => 'Background',
				'name'          => 'events_hero_use_solid_color',
				'type'          => 'true_false',
				'instructions'  => 'Use a solid color (no overlay). Leave off to use a background image with gradient overlay.',
				'ui'            => 1,
				'default_value' => 0,
			),
			array(
				'key'               => 'field_tectn_events_hero_bg_color',
				'label'             => 'Background color',
				'name'              => 'events_hero_background_color',
				'type'              => 'color_picker',
				'default_value'     => '#238c55',
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_tectn_events_use_solid_color',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
			),
			array(
				'key'               => 'field_tectn_events_hero_bg_image',
				'label'             => 'Background image',
				'name'              => 'events_hero_background_image',
				'type'              => 'image',
				'return_format'     => 'id',
				'preview_size'      => 'medium',
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_tectn_events_use_solid_color',
							'operator' => '!=',
							'value'    => '1',
						),
					),
				),
			),
			array(
				'key'   => 'field_tectn_events_hero_headline',
				'label' => 'Hero headline',
				'name'  => 'events_hero_headline_text',
				'type'  => 'text',
				'instructions' => 'Text shown over the hero (e.g. "Events").',
			),
			array(
				'key'   => 'field_tectn_events_intro_tab',
				'label' => 'Intro (above calendar)',
				'name'  => '',
				'type'  => 'tab',
			),
			array(
				'key'   => 'field_tectn_events_intro_heading',
				'label' => 'Intro heading',
				'name'  => 'events_intro_heading',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_tectn_events_intro_body',
				'label' => 'Intro body',
				'name'  => 'events_intro_body',
				'type'  => 'wysiwyg',
				'tabs'  => 'all',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'theme-events-settings',
				),
			),
		),
	) );
}
add_action( 'acf/init', 'tectn_register_acf_events_settings' );

  /* Add a custom block category for TecTN ACF Blocks. */
  add_filter('block_categories_all', function ($categories, $editor_context) {

    $tectn_category = [
      'slug'  => 'tectn-blocks',
      'title' => __('TecTN Blocks', 'tectn'),
      'icon'  => null,
    ];

    // Put it at the top
    array_unshift($categories, $tectn_category);

    return $categories;
  }, 10, 2);


/*CUSTOM SHORTCODE TO PULL FORM POSTS */

add_shortcode( 'formposts', 'display_custom_post_type' ); 

function display_custom_post_type(){
    $args = array( 'post_type' => 'form', 'post_status' => 'publish' );
    $string = '';
    $query = new WP_Query( $args );
    // Loop through the posts and build your output (e.g., list titles with links)
    if ( $query->have_posts() ) {
        $string .= '<div class="forms forms__page">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $string .= '<div class="form__item"><a href="' . get_permalink() . '" class="form__link"><h4>' . get_the_title() . '</h4></a></div>';
        }
        $string .= '</div>';
        wp_reset_postdata(); // Important: Reset the query after using it
    } else {
        $string = 'No posts found';
    }
    return $string;
}

/* EDIT PASSWORD PROTECTED LANGUAGE */
add_filter( 'the_password_form', 'tectn_password_form' );
function tectn_password_form( $output ) {
	$output = str_replace(
		'This content is password protected. To view it please enter your password below:',
		'To simplify your login process, you now only need to enter your password. Your password remains the same as the one you were previously provided.',
		$output
	);

	return $output;
}


/* DON'T DELETE THIS CLOSING TAG */ ?>
