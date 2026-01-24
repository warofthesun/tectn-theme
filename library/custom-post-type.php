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
			'name' => __( 'Testimonials', 'trustmfa_theme' ), /* This is the Title of the Group */
			'singular_name' => __( 'Testimonial', 'trustmfa_theme' ), /* This is the individual type */
			'all_items' => __( 'All Testimonials', 'trustmfa_theme' ), /* the all items menu item */
			'add_new' => __( 'Add New', 'trustmfa_theme' ), /* The add new menu item */
			'add_new_item' => __( 'Add New Testimonial', 'trustmfa_theme' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'trustmfa_theme' ), /* Edit Dialog */
			'edit_item' => __( 'Edit Testimonial', 'trustmfa_theme' ), /* Edit Display Title */
			'new_item' => __( 'New Testimonial', 'trustmfa_theme' ), /* New Display Title */
			'view_item' => __( 'View Testimonial', 'trustmfa_theme' ), /* View Display Title */
			'search_items' => __( 'Search Testimonials', 'trustmfa_theme' ), /* Search Custom Type Title */
			'not_found' =>  __( 'Nothing found in the Database.', 'trustmfa_theme' ), /* This displays if there are no entries yet */
			'not_found_in_trash' => __( 'Nothing found in Trash', 'trustmfa_theme' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'This is where you will create the testimonials that may be used around the site.', 'trustmfa_theme' ), /* Custom Type Description */
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

	register_post_type( 'form', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Forms', 'trustmfa_theme' ), /* This is the Title of the Group */
			'singular_name' => __( 'Form', 'trustmfa_theme' ), /* This is the individual type */
			'all_items' => __( 'All Forms', 'trustmfa_theme' ), /* the all items menu item */
			'add_new' => __( 'Add New', 'trustmfa_theme' ), /* The add new menu item */
			'add_new_item' => __( 'Add New Form', 'trustmfa_theme' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'trustmfa_theme' ), /* Edit Dialog */
			'edit_item' => __( 'Edit Form', 'trustmfa_theme' ), /* Edit Display Title */
			'new_item' => __( 'New Form', 'trustmfa_theme' ), /* New Display Title */
			'view_item' => __( 'View Form', 'trustmfa_theme' ), /* View Display Title */
			'search_items' => __( 'Search Forms', 'trustmfa_theme' ), /* Search Custom Type Title */
			'not_found' =>  __( 'Nothing found in the Database.', 'trustmfa_theme' ), /* This displays if there are no entries yet */
			'not_found_in_trash' => __( 'Nothing found in Trash', 'trustmfa_theme' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'This is where you will create the forms that may be used around the site.', 'trustmfa_theme' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */
			'menu_icon' => 'dashicons-media-document', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'forms', 'with_front' => false ), /* you can specify its url slug */
			'has_archive' => 'form', /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor'/*,  'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky' */)
		) /* end of options */
	); /* end of register post type */

	/* this adds your post categories to your custom post type */
	/* register_taxonomy_for_object_type( 'category', 'testimonials' );
	/* this adds your post tags to your custom post type */
	/* register_taxonomy_for_object_type( 'post_tag', 'testimonials' ); */

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
				'name' => __( 'Testimonial Categories', 'trustmfa_theme' ), /* name of the custom taxonomy */
				'singular_name' => __( 'Testimonial Category', 'trustmfa_theme' ), /* single taxonomy name */
				'search_items' =>  __( 'Search Testimonial Categories', 'trustmfa_theme' ), /* search title for taxomony */
				'all_items' => __( 'All Testimonial Categories', 'trustmfa_theme' ), /* all title for taxonomies */
				'parent_item' => __( 'Parent Testimonial Category', 'trustmfa_theme' ), /* parent title for taxonomy */
				'parent_item_colon' => __( 'Parent Testimonial Category:', 'trustmfa_theme' ), /* parent taxonomy title */
				'edit_item' => __( 'Edit Testimonial Category', 'trustmfa_theme' ), /* edit custom taxonomy title */
				'update_item' => __( 'Update Testimonial Category', 'trustmfa_theme' ), /* update title for taxonomy */
				'add_new_item' => __( 'Add New Testimonial Category', 'trustmfa_theme' ), /* add new title for taxonomy */
				'new_item_name' => __( 'New Testimonial Category Name', 'trustmfa_theme' ) /* name title for taxonomy */
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
				'name' => __( 'Testimonial Tags', 'trustmfa_theme' ), /* name of the custom taxonomy */
				'singular_name' => __( 'Testimonial Tag', 'trustmfa_theme' ), /* single taxonomy name */
				'search_items' =>  __( 'Search Testimonial Tags', 'trustmfa_theme' ), /* search title for taxomony */
				'all_items' => __( 'All Testimonial Tags', 'trustmfa_theme' ), /* all title for taxonomies */
				'parent_item' => __( 'Parent Testimonial Tag', 'trustmfa_theme' ), /* parent title for taxonomy */
				'parent_item_colon' => __( 'Parent Testimonial Tag:', 'trustmfa_theme' ), /* parent taxonomy title */
				'edit_item' => __( 'Edit Testimonial Tag', 'trustmfa_theme' ), /* edit Testimonial taxonomy title */
				'update_item' => __( 'Update Testimonial Tag', 'trustmfa_theme' ), /* update title for taxonomy */
				'add_new_item' => __( 'Add New Testimonial Tag', 'trustmfa_theme' ), /* add new title for taxonomy */
				'new_item_name' => __( 'New Testimonial Tag Name', 'trustmfa_theme' ) /* name title for taxonomy */
			),
			'show_admin_column' => true,
			'show_ui' => true,
			'query_var' => true,
		)
	);




?>
