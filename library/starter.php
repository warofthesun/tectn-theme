<?php

function starter_head_cleanup() {
	// category feeds
	// remove_action( 'wp_head', 'feed_links_extra', 3 );
	// post and comment feeds
	// remove_action( 'wp_head', 'feed_links', 2 );
	// EditURI link
	remove_action( 'wp_head', 'rsd_link' );
	// windows live writer
	remove_action( 'wp_head', 'wlwmanifest_link' );
	// previous link
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	// start link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	// links for adjacent posts
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	// WP version
	remove_action( 'wp_head', 'wp_generator' );
	// remove WP version from css
	add_filter( 'style_loader_src', 'starter_remove_wp_ver_css_js', 9999 );
	// remove Wp version from scripts
	add_filter( 'script_loader_src', 'starter_remove_wp_ver_css_js', 9999 );

} /* end starter head cleanup */

// A better title
// http://www.deluxeblogtips.com/2012/03/better-title-meta-tag.html
function rw_title( $title, $sep, $seplocation ) {
  global $page, $paged;

  // Don't affect in feeds.
  if ( is_feed() ) return $title;

  // Add the blog's name
  if ( 'right' == $seplocation ) {
    $title .= get_bloginfo( 'name' );
  } else {
    $title = get_bloginfo( 'name' ) . $title;
  }

  // Add the blog description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );

  if ( $site_description && ( is_home() || is_front_page() ) ) {
    $title .= " {$sep} {$site_description}";
  }

  // Add a page number if necessary:
  if ( $paged >= 2 || $page >= 2 ) {
    $title .= " {$sep} " . sprintf( __( 'Page %s', 'dbt' ), max( $paged, $page ) );
  }

  return $title;

} // end better title

// remove WP version from RSS
function starter_rss_version() { return ''; }

// remove WP version from scripts
function starter_remove_wp_ver_css_js( $src ) {
	if ( strpos( $src, 'ver=' ) )
		$src = remove_query_arg( 'ver', $src );
	return $src;
}

// remove injected CSS for recent comments widget
function starter_remove_wp_widget_recent_comments_style() {
	if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
		remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
	}
}

// remove injected CSS from recent comments widget
function starter_remove_recent_comments_style() {
	global $wp_widget_factory;
	if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
		remove_action( 'wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style') );
	}
}

// remove injected CSS from gallery
function starter_gallery_style($css) {
	return preg_replace( "!<style type='text/css'>(.*?)</style>!s", '', $css );
}


/*********************
SCRIPTS & ENQUEUEING
*********************/

// Cache-busting version: theme version in production; filemtime in dev when file exists.
function tectn_asset_version( $path_relative_to_theme ) {
	$path = get_template_directory() . '/' . $path_relative_to_theme;
	if ( file_exists( $path ) ) {
		return (string) filemtime( $path );
	}
	return wp_get_theme()->get( 'Version' ) ?: '1.0.0';
}

// Load ScrollReveal only on front page / blog where hero or archive cards are used.
function tectn_needs_scroll_reveal() {
	return is_front_page() || is_home() || is_archive();
}

// loading scripts and styles with versioning and conditional third-party assets
function starter_scripts_and_styles() {
	if ( is_admin() ) {
		return;
	}

	$theme_uri = get_stylesheet_directory_uri();
	$version   = tectn_asset_version( 'library/css/style.css' );
	$js_version = tectn_asset_version( 'library/js/scripts.js' );

	// Main stylesheet (always)
	wp_register_style( 'starter-stylesheet', $theme_uri . '/library/css/style.css', array(), $version, 'all' );
	wp_enqueue_style( 'starter-stylesheet' );

	// Comment reply script for threaded comments
	if ( is_singular() && comments_open() && ( get_option( 'thread_comments' ) == 1 ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Font Awesome: enqueue in footer so it doesn't block render
	wp_register_script( 'font-awesome-kit', 'https://kit.fontawesome.com/059e62f330.js', array(), null, true );
	wp_enqueue_script( 'font-awesome-kit' );

	// ScrollReveal: only on front, blog, archive so other pages stay light
	$script_deps = array( 'jquery' );
	if ( tectn_needs_scroll_reveal() ) {
		wp_register_script( 'scrollreveal', 'https://unpkg.com/scrollreveal@4.0.9/dist/scrollreveal.min.js', array(), '4.0.9', true );
		wp_enqueue_script( 'scrollreveal' );
		wp_add_inline_script( 'scrollreveal', "window.sr = ScrollReveal({ duration: 600, reset: true, easing: 'ease-in', scale: .98, distance: '50px' });", 'after' );
		$script_deps[] = 'scrollreveal';
	}

	// Main theme script (footer); depends on ScrollReveal when loaded so sr is defined before scripts.js runs
	wp_register_script( 'starter-js', $theme_uri . '/library/js/scripts.js', $script_deps, $js_version, true );
	wp_enqueue_script( 'starter-js' );

	// Modernizr: only if a script or style depends on it (filter to true to load)
	$use_modernizr = apply_filters( 'tectn_load_modernizr', false );
	if ( $use_modernizr ) {
		wp_register_script( 'starter-modernizr', $theme_uri . '/library/js/libs/modernizr.custom.min.js', array(), '2.5.3', true );
		wp_enqueue_script( 'starter-modernizr' );
	}
}





/*********************
THEME SUPPORT
*********************/

// Adding WP 3+ Functions & Theme Support
function starter_theme_support() {

	// wp thumbnails (sizes handled in functions.php)
	add_theme_support( 'post-thumbnails' );

	// default thumb size
	set_post_thumbnail_size(125, 125, true);

	// wp custom background (thx to @bransonwerner for update)
	add_theme_support( 'custom-background',
	    array(
	    'default-image' => '',    // background image default
	    'default-color' => '',    // background color default (dont add the #)
	    'wp-head-callback' => '_custom_background_cb',
	    'admin-head-callback' => '',
	    'admin-preview-callback' => ''
	    )
	);

	// rss thingy
	add_theme_support('automatic-feed-links');

	// to add header image support go here: http://themble.com/support/adding-header-background-image-support/

	// adding post format support
	add_theme_support( 'post-formats',
		array(
			'aside',             // title less blurb
			'gallery',           // gallery of images
			'link',              // quick link to other site
			'image',             // an image
			'quote',             // a quick quote
			'status',            // a Facebook like status update
			'video',             // video
			'audio',             // audio
			'chat'               // chat transcript
		)
	);

	// wp menus
	add_theme_support( 'menus' );

	// registering wp3+ menus
	register_nav_menus(
		array(
			'main-nav' => __( 'The Main Menu', 'tectn_theme' ),   // main nav in header
			'footer-links' => __( 'Footer Links', 'tectn_theme' ) // secondary nav in footer
		)
	);

	// Enable support for HTML5 markup.
	add_theme_support( 'html5', array(
		'comment-list',
		'search-form',
		'comment-form'
	) );

} /* end starter theme support */


/*********************
RELATED POSTS FUNCTION
*********************/

// Related Posts Function (call using starter_related_posts(); )
function starter_related_posts() {
	echo '<ul id="starter-related-posts">';
	global $post;
	$tags = wp_get_post_tags( $post->ID );
	if($tags) {
		foreach( $tags as $tag ) {
			$tag_arr .= $tag->slug . ',';
		}
		$args = array(
			'tag' => $tag_arr,
			'numberposts' => 5, /* you can change this to show more */
			'post__not_in' => array($post->ID)
		);
		$related_posts = get_posts( $args );
		if($related_posts) {
			foreach ( $related_posts as $post ) : setup_postdata( $post ); ?>
				<li class="related_post"><a class="entry-unrelated" href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></li>
			<?php endforeach; }
		else { ?>
			<?php echo '<li class="no_related_post">' . __( 'No Related Posts Yet!', 'tectn_theme' ) . '</li>'; ?>
		<?php }
	}
	wp_reset_postdata();
	echo '</ul>';
} /* end starter related posts function */

/*********************
PAGE NAVI
*********************/

// Numeric Page Navi (built into the theme by default)
function starter_page_navi() {
  global $wp_query;
  $bignum = 999999999;
  if ( $wp_query->max_num_pages <= 1 )
    return;
  echo '<nav class="pagination">';
  echo paginate_links( array(
    'base'         => str_replace( $bignum, '%#%', esc_url( get_pagenum_link($bignum) ) ),
    'format'       => '',
    'current'      => max( 1, get_query_var('paged') ),
    'total'        => $wp_query->max_num_pages,
    'prev_text'    => '&larr;',
    'next_text'    => '&rarr;',
    'type'         => 'list',
    'end_size'     => 3,
    'mid_size'     => 3
  ) );
  echo '</nav>';
} /* end page navi */

/*********************
RANDOM CLEANUP ITEMS
*********************/

// remove the p from around imgs (http://css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/)
function starter_filter_ptags_on_images($content){
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}

// This removes the annoying […] to a Read More link
function starter_excerpt_more($more) {
	global $post;
	// edit here if you like
	return '...  <a class="excerpt-read-more" href="'. get_permalink( $post->ID ) . '" title="'. __( 'Read ', 'tectn_theme' ) . esc_attr( get_the_title( $post->ID ) ).'">'. __( 'Read more &raquo;', 'tectn_theme' ) .'</a>';
}



?>
