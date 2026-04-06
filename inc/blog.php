<?php
/**
 * Theme includes.
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	 * Stories grid: 3 columns at desktop without sidebar; 2 when sidebar replaces a column.
	 * Round posts_per_page to a multiple of that column count to avoid a short last row.
	 */
	$base_ppp = (int) $query->get( 'posts_per_page' );
	if ( $base_ppp < 1 ) {
		$base_ppp = (int) get_option( 'posts_per_page' );
	}
	if ( $base_ppp < 1 ) {
		$base_ppp = 10;
	}
	$sb_cfg    = tectn_get_blog_index_sidebar_config();
	$grid_cols = ! empty( $sb_cfg['active'] ) ? 2 : 3;
	$rounded_ppp = (int) ceil( $base_ppp / $grid_cols ) * $grid_cols;
	$query->set( 'posts_per_page', max( $grid_cols, $rounded_ppp ) );

	$query->set( 'ignore_sticky_posts', true );
}
add_action( 'pre_get_posts', 'tectn_blog_index_pre_get_posts', 5 );

/**
 * Blog index sidebar: Post Settings (Blog Posts tab), with legacy fallbacks.
 *
 * @return array{ active: bool, position: string } position is 'left' or 'right'.
 */
function tectn_get_blog_index_sidebar_config() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$cached = array(
		'active'   => false,
		'position' => 'right',
	);
	if ( ! function_exists( 'get_field' ) ) {
		return $cached;
	}
	$from_ps = (bool) get_field( 'blog_index_show_sidebar', 'post-settings' );
	$legacy  = (bool) get_field( 'include_sidebar_on_blog_page', 'post-settings' );
	if ( ! $legacy ) {
		$legacy = (bool) get_field( 'include_sidebar_on_blog_page', 'option' );
	}
	if ( ! $legacy ) {
		$legacy = (bool) get_field( 'include_sidebar_on_blog_page', 'theme-general-settings' );
	}
	$cached['active'] = $from_ps || $legacy;
	$pos                = (string) get_field( 'blog_index_sidebar_position', 'post-settings' );
	$cached['position'] = ( $pos === 'left' ) ? 'left' : 'right';
	return $cached;
}

/**
 * Whether single blog posts should show the sidebar (Post Settings → Blog Posts).
 */
function tectn_include_sidebar_on_blog_posts() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$cached = false;
	if ( ! function_exists( 'get_field' ) ) {
		return $cached;
	}
	$v = get_field( 'include_sidebar_on_blog_posts', 'post-settings' );
	if ( $v === null || $v === false || $v === '' ) {
		$v = get_field( 'include_sidebar_on_blog_posts', 'option' );
	}
	if ( $v === null || $v === false || $v === '' ) {
		$v = get_field( 'include_sidebar_on_blog_posts', 'theme-general-settings' );
	}
	$cached = (bool) $v;
	return $cached;
}

/**
 * Body class for the posts index template (stories layout + scoped CSS).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tectn_body_class_stories_index( $classes ) {
	if ( is_home() && ! is_front_page() ) {
		$classes[] = 'tectn-stories-index';
		$sb        = tectn_get_blog_index_sidebar_config();
		if ( ! empty( $sb['active'] ) ) {
			$classes[] = 'tectn-blog-index-has-sidebar';
			$classes[] = 'tectn-blog-index-sidebar-' . sanitize_html_class( $sb['position'] );
		}
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
	$img_url   = tectn_get_post_card_image_url( $post_id );

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
