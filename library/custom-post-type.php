<?php

// Flush rewrite rules for custom post types
add_action( 'after_switch_theme', 'starter_flush_rewrite_rules' );

// Flush your rewrite rules
function starter_flush_rewrite_rules() {
	flush_rewrite_rules();
}

// let's create the function for the custom type
function custom_post() {
	// creating (registering) the custom type
	register_post_type( 'testimonials', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Testimonials', 'tectn_theme' ), /* This is the Title of the Group */
			'singular_name' => __( 'Testimonial', 'tectn_theme' ), /* This is the individual type */
			'all_items' => __( 'All Testimonials', 'tectn_theme' ), /* the all items menu item */
			'add_new' => __( 'Add New', 'tectn_theme' ), /* The add new menu item */
			'add_new_item' => __( 'Add New Testimonial', 'tectn_theme' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'tectn_theme' ), /* Edit Dialog */
			'edit_item' => __( 'Edit Testimonial', 'tectn_theme' ), /* Edit Display Title */
			'new_item' => __( 'New Testimonial', 'tectn_theme' ), /* New Display Title */
			'view_item' => __( 'View Testimonial', 'tectn_theme' ), /* View Display Title */
			'search_items' => __( 'Search Testimonials', 'tectn_theme' ), /* Search Custom Type Title */
			'not_found' =>  __( 'Nothing found in the Database.', 'tectn_theme' ), /* This displays if there are no entries yet */
			'not_found_in_trash' => __( 'Nothing found in Trash', 'tectn_theme' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'This is where you will create the testimonials that may be used around the site.', 'tectn_theme' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */
			'menu_icon' => 'dashicons-heart', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'testimonials', 'with_front' => false ), /* you can specify its url slug */
			'has_archive' => 'testimonials', /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor'/*,  'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky' */)
		) /* end of options */
	); /* end of register post type */

	register_post_type( 'pickup_site',
		array(
			'labels' => array(
				'name'               => __( 'Pick-up Sites', 'tectn_theme' ),
				'singular_name'      => __( 'Pick-up Site', 'tectn_theme' ),
				'all_items'          => __( 'All Pick-up Sites', 'tectn_theme' ),
				'add_new'            => __( 'Add New', 'tectn_theme' ),
				'add_new_item'       => __( 'Add New Pick-up Site', 'tectn_theme' ),
				'edit_item'          => __( 'Edit Pick-up Site', 'tectn_theme' ),
				'new_item'           => __( 'New Pick-up Site', 'tectn_theme' ),
				'view_item'          => __( 'View Pick-up Site', 'tectn_theme' ),
				'search_items'       => __( 'Search Pick-up Sites', 'tectn_theme' ),
				'not_found'          => __( 'No pick-up sites found.', 'tectn_theme' ),
				'not_found_in_trash' => __( 'No pick-up sites found in Trash', 'tectn_theme' ),
				'parent_item_colon'  => '',
			),
			'description'         => __( 'Pick-up site locations for the map block.', 'tectn_theme' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'menu_position'       => 9,
			'menu_icon'           => 'dashicons-location-alt',
			'rewrite'             => array( 'slug' => 'pick-up-site', 'with_front' => false ),
			'has_archive'         => 'pick-up-sites',
			'capability_type'     => 'post',
			'hierarchical'       => false,
			'supports'            => array( 'title', 'editor' ),
		)
	);

	/* this adds your post categories to your custom post type */
	/* register_taxonomy_for_object_type( 'category', 'testimonials' );
	/* this adds your post tags to your custom post type */
	/* register_taxonomy_for_object_type( 'post_tag', 'testimonials' ); */

	// People: data-only post type for About/Team pages.
	register_post_type( 'people',
		array(
			'labels' => array(
				'name'               => __( 'People', 'tectn_theme' ),
				'singular_name'      => __( 'Person', 'tectn_theme' ),
				'all_items'          => __( 'All People', 'tectn_theme' ),
				'add_new'            => __( 'Add New Person', 'tectn_theme' ),
				'add_new_item'       => __( 'Add New Person', 'tectn_theme' ),
				'edit_item'          => __( 'Edit Person', 'tectn_theme' ),
				'new_item'           => __( 'New Person', 'tectn_theme' ),
				'view_item'          => __( 'View Person', 'tectn_theme' ),
				'search_items'       => __( 'Search People', 'tectn_theme' ),
				'not_found'          => __( 'No people found.', 'tectn_theme' ),
				'not_found_in_trash' => __( 'No people found in Trash', 'tectn_theme' ),
				'parent_item_colon'  => '',
			),
			'description'         => __( 'People used on About/Team style pages.', 'tectn_theme' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'menu_position'       => 10,
			'menu_icon'           => 'dashicons-groups',
			'rewrite'             => array( 'slug' => 'people', 'with_front' => false ),
			'has_archive'         => false,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			// Attach only the dedicated People taxonomies in the UI.
			'taxonomies'          => array( 'person_category', 'person_tag' ),
		)
	);

	// People taxonomies: dedicated categories and tags.
	register_taxonomy(
		'person_category',
		array( 'people' ),
		array(
			'hierarchical'      => true,
			'labels'            => array(
				'name'              => __( 'People Categories', 'tectn_theme' ),
				'singular_name'     => __( 'People Category', 'tectn_theme' ),
				'search_items'      => __( 'Search People Categories', 'tectn_theme' ),
				'all_items'         => __( 'All People Categories', 'tectn_theme' ),
				'parent_item'       => __( 'Parent People Category', 'tectn_theme' ),
				'parent_item_colon' => __( 'Parent People Category:', 'tectn_theme' ),
				'edit_item'         => __( 'Edit People Category', 'tectn_theme' ),
				'update_item'       => __( 'Update People Category', 'tectn_theme' ),
				'add_new_item'      => __( 'Add New People Category', 'tectn_theme' ),
				'new_item_name'     => __( 'New People Category Name', 'tectn_theme' ),
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'person-category' ),
		)
	);

	register_taxonomy(
		'person_tag',
		array( 'people' ),
		array(
			'hierarchical'      => false,
			'labels'            => array(
				'name'              => __( 'People Tags', 'tectn_theme' ),
				'singular_name'     => __( 'People Tag', 'tectn_theme' ),
				'search_items'      => __( 'Search People Tags', 'tectn_theme' ),
				'all_items'         => __( 'All People Tags', 'tectn_theme' ),
				'edit_item'         => __( 'Edit People Tag', 'tectn_theme' ),
				'update_item'       => __( 'Update People Tag', 'tectn_theme' ),
				'add_new_item'      => __( 'Add New People Tag', 'tectn_theme' ),
				'new_item_name'     => __( 'New People Tag Name', 'tectn_theme' ),
				'separate_items_with_commas' => __( 'Separate people tags with commas', 'tectn_theme' ),
				'add_or_remove_items'        => __( 'Add or remove people tags', 'tectn_theme' ),
				'choose_from_most_used'      => __( 'Choose from the most used people tags', 'tectn_theme' ),
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'person-tag' ),
		)
	);

}

	// adding the function to the Wordpress init
	add_action( 'init', 'custom_post');

	/*
	for more information on taxonomies, go here:
	http://codex.wordpress.org/Function_Reference/register_taxonomy
	*/

	// now let's add custom categories (these act like categories)
	register_taxonomy( 'custom_cat',
		array('testimonials'), /* if you change the name of register_post_type( 'testimonials', then you have to change this */
		array('hierarchical' => true,     /* if this is true, it acts like categories */
			'labels' => array(
				'name' => __( 'Testimonial Categories', 'tectn_theme' ), /* name of the custom taxonomy */
				'singular_name' => __( 'Testimonial Category', 'tectn_theme' ), /* single taxonomy name */
				'search_items' =>  __( 'Search Testimonial Categories', 'tectn_theme' ), /* search title for taxomony */
				'all_items' => __( 'All Testimonial Categories', 'tectn_theme' ), /* all title for taxonomies */
				'parent_item' => __( 'Parent Testimonial Category', 'tectn_theme' ), /* parent title for taxonomy */
				'parent_item_colon' => __( 'Parent Testimonial Category:', 'tectn_theme' ), /* parent taxonomy title */
				'edit_item' => __( 'Edit Testimonial Category', 'tectn_theme' ), /* edit custom taxonomy title */
				'update_item' => __( 'Update Testimonial Category', 'tectn_theme' ), /* update title for taxonomy */
				'add_new_item' => __( 'Add New Testimonial Category', 'tectn_theme' ), /* add new title for taxonomy */
				'new_item_name' => __( 'New Testimonial Category Name', 'tectn_theme' ) /* name title for taxonomy */
			),
			'show_admin_column' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'custom-slug' ),
		)
	);

	// now let's add custom tags (these act like categories)
	register_taxonomy( 'testimonial_tag',
		array('testimonials'), /* if you change the name of register_post_type( 'testimonials', then you have to change this */
		array('hierarchical' => false,    /* if this is false, it acts like tags */
			'labels' => array(
				'name' => __( 'Testimonial Tags', 'tectn_theme' ), /* name of the custom taxonomy */
				'singular_name' => __( 'Testimonial Tag', 'tectn_theme' ), /* single taxonomy name */
				'search_items' =>  __( 'Search Testimonial Tags', 'tectn_theme' ), /* search title for taxomony */
				'all_items' => __( 'All Testimonial Tags', 'tectn_theme' ), /* all title for taxonomies */
				'parent_item' => __( 'Parent Testimonial Tag', 'tectn_theme' ), /* parent title for taxonomy */
				'parent_item_colon' => __( 'Parent Testimonial Tag:', 'tectn_theme' ), /* parent taxonomy title */
				'edit_item' => __( 'Edit Testimonial Tag', 'tectn_theme' ), /* edit Testimonial taxonomy title */
				'update_item' => __( 'Update Testimonial Tag', 'tectn_theme' ), /* update title for taxonomy */
				'add_new_item' => __( 'Add New Testimonial Tag', 'tectn_theme' ), /* add new title for taxonomy */
				'new_item_name' => __( 'New Testimonial Tag Name', 'tectn_theme' ) /* name title for taxonomy */
			),
			'show_admin_column' => true,
			'show_ui' => true,
			'query_var' => true,
		)
	);




?>
