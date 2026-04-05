<?php

// Flush rewrite rules for custom post types
add_action( 'after_switch_theme', 'starter_flush_rewrite_rules' );

// Flush your rewrite rules
function starter_flush_rewrite_rules() {
	flush_rewrite_rules();
}

// let's create the function for the custom type
function custom_post() {
	register_post_type(
		'pickup_site',
		array(
			'labels'              => array(
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
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor' ),
		)
	);

	// People: data-only post type for About/Team pages.
	register_post_type(
		'people',
		array(
			'labels'              => array(
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
			'hierarchical'               => false,
			'labels'                     => array(
				'name'                       => __( 'People Tags', 'tectn_theme' ),
				'singular_name'              => __( 'People Tag', 'tectn_theme' ),
				'search_items'               => __( 'Search People Tags', 'tectn_theme' ),
				'all_items'                  => __( 'All People Tags', 'tectn_theme' ),
				'edit_item'                  => __( 'Edit People Tag', 'tectn_theme' ),
				'update_item'                => __( 'Update People Tag', 'tectn_theme' ),
				'add_new_item'               => __( 'Add New People Tag', 'tectn_theme' ),
				'new_item_name'              => __( 'New People Tag Name', 'tectn_theme' ),
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

add_action( 'init', 'custom_post' );
