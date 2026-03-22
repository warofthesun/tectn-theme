<?php
/**
 * Theme includes.
 * @package tectn_theme
 * Theme setup, sidebars, comments, assets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tectn_setup() {

  // Add theme support for editor styles
  add_theme_support( 'editor-styles' );

  //Allow editor style.
  add_editor_style( '/library/css/style.css' );

  add_theme_support( 'align-wide' );

  // let's get language support going, if you need it
  load_theme_textdomain( 'tectn_theme', get_template_directory() . '/library/translation' );

  // USE THIS TEMPLATE TO CREATE CUSTOM POST TYPES EASILY
  require_once get_template_directory() . '/library/custom-post-type.php';

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

/* Google Maps API key: env var > Site Settings (site_settings group) > legacy ACF option > wp option. */
function tectn_google_maps_api_key() {
	if ( defined( 'GOOGLE_MAPS_API_KEY' ) && GOOGLE_MAPS_API_KEY !== '' ) {
		return GOOGLE_MAPS_API_KEY;
	}
	$env = getenv( 'GOOGLE_MAPS_API_KEY' );
	if ( is_string( $env ) && $env !== '' ) {
		return $env;
	}
	if ( function_exists( 'get_field' ) ) {
		$ss = get_field( 'site_settings', 'site-settings' );
		if ( ! is_array( $ss ) ) {
			$ss = get_field( 'site_settings', 'option' );
		}
		if ( is_array( $ss ) && ! empty( $ss['google_maps_api_key'] ) && is_string( $ss['google_maps_api_key'] ) ) {
			return $ss['google_maps_api_key'];
		}
		// Legacy: standalone field saved under default options before Google API tab moved to Site Settings.
		$acf = get_field( 'google_maps_api_key', 'option' );
		if ( is_string( $acf ) && $acf !== '' ) {
			return $acf;
		}
	}
	return (string) get_option( 'tectn_google_maps_api_key', '' );
}

function tectn_acf_init() {
	acf_update_setting( 'google_api_key', tectn_google_maps_api_key() );
}
add_action( 'acf/init', 'tectn_acf_init' );

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
function tectn_fonts_preconnect( $urls, $relation_type ) {
  if ( 'preconnect' === $relation_type ) {
    $urls[] = array( 'href' => 'https://fonts.googleapis.com' );
    $urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => '' );
  }
  return $urls;
}


add_filter( 'wp_resource_hints', 'tectn_fonts_preconnect', 10, 2 );
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
