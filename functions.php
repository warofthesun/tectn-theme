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
 * Hero configuration for the current template context.
 * Use this to decide whether to show the hero and what data to pass to partials/hero/hero.php.
 *
 * - Pages (incl. front): editor chooses via ACF "Hero Style" (show hero when not "none"); data from ACF + featured image.
 * - Single post: hero shown automatically with post title, meta, featured image.
 * - Blog index: hero shown automatically; title from options or "News", optional image from options.
 * - Archive: hero shown automatically with archive title and description.
 *
 * @return array{ show: bool, type: string, data: array } 'show', 'type' (landing|post|blog|archive), 'data' (headline, paragraph, image_id, ctas, title, etc.).
 */
function tectn_get_hero_config() {
  static $config = null;
  if ( $config !== null ) {
    return $config;
  }

  $default = array( 'show' => false, 'type' => 'landing', 'data' => array() );

  // Single post: automatic hero with title, meta, featured image
  if ( is_singular( 'post' ) ) {
    $post = get_queried_object();
    if ( ! $post ) {
      $config = $default;
      return $config;
    }
    $config = array(
      'show' => true,
      'type' => 'post',
      'data' => array(
        'title'        => get_the_title( $post ),
        'permalink'    => get_permalink( $post ),
        'date'         => get_the_date( '', $post ),
        'date_iso'     => get_the_date( 'c', $post ),
        'author_link'  => get_the_author_posts_link( $post->post_author ),
        'categories'   => get_the_category_list( ', ', '', $post->ID ),
        'image_id'     => get_post_thumbnail_id( $post->ID ),
      ),
    );
    return $config;
  }

  // Blog index (posts page): automatic hero; title/image from options or defaults
  if ( is_home() && ! is_front_page() ) {
    $page_for_posts = (int) get_option( 'page_for_posts' );
    $title          = $page_for_posts ? get_the_title( $page_for_posts ) : __( 'News', 'tectn_theme' );
    $image_id       = $page_for_posts ? get_post_thumbnail_id( $page_for_posts ) : 0;
    $config = array(
      'show' => true,
      'type' => 'blog',
      'data' => array( 'title' => $title, 'image_id' => $image_id ),
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
      $config = array(
        'show' => true,
        'type' => 'initiative',
        'data' => array(
          'background_type'  => $use_solid_color ? 'color' : 'image',
          'background_image' => $use_solid_color ? 0 : $image_id,
          'background_color' => isset( $init['background_color'] ) ? $init['background_color'] : '#238c55',
          'headline_type'    => isset( $init['headline_type'] ) ? $init['headline_type'] : 'text',
          'logo_id'          => $logo_id,
          'headline_text'    => isset( $init['headline_text'] ) ? $init['headline_text'] : '',
          'gradient_style'   => isset( $init['gradient_style'] ) ? $init['gradient_style'] : 'full',
          'lower_content'    => isset( $init['lower_content'] ) ? $init['lower_content'] : '',
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
    ) );
}

/*
The function above adds the ability to use the dropdown menu to select
the new images sizes you have just created from within the media manager
when you add media to your content blocks. If you add more image sizes,
duplicate one of the lines in the array and name it according to your
new image size.
*/

// TGM Plugin Activation Class
require_once locate_template('library/tgm-plugin-activation/class-tgm-plugin-activation.php');


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
 * Enqueue Events Calendar list-view overrides after plugin CSS
 * so theme fonts and colors (from :root) apply.
 */
function tectn_enqueue_tribe_events_overrides() {
	if ( ! is_post_type_archive( 'tribe_events' ) && ! is_singular( 'tribe_events' ) ) {
		return;
	}
	$path = get_template_directory() . '/tribe-events/tribe-events.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'tectn-tribe-events-overrides',
		get_template_directory_uri() . '/tribe-events/tribe-events.css',
		array( 'tribe-events-views-v2-full' ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'tectn_enqueue_tribe_events_overrides', 100 );

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

}

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
