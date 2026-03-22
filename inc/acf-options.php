<?php
/**
 * Theme includes.
 * @package tectn_theme
 * ACF options pages, events field group, footer getters.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Register Events Page Settings under Site Settings (moved from Theme Settings).
 * Keeps menu_slug / post_id theme-events-settings so existing field values and get_field() calls still work.
 */
function tectn_register_events_page_options_under_site_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = 'site-settings';
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Events Page Settings',
			'menu_title'  => 'Events',
			'menu_slug'   => 'theme-events-settings',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'theme-events-settings',
		)
	);
}
add_action( 'acf/init', 'tectn_register_events_page_options_under_site_settings', 10 );

/**
 * Register Footer Information as a sub-page of Site Settings (contact, social, CTAs, disclaimer).
 */
function tectn_register_footer_information_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = 'site-settings';
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Footer Information',
			'menu_title'  => 'Footer Information',
			'menu_slug'   => 'footer-information',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'footer-information',
		)
	);
}
add_action( 'acf/init', 'tectn_register_footer_information_options_page', 10 );

/**
 * Register Forms (embedded form snippets) under Site Settings.
 */
function tectn_register_forms_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = 'site-settings';
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Forms',
			'menu_title'  => 'Forms',
			'menu_slug'   => 'tectn-forms',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'tectn-forms',
		)
	);
}
add_action( 'acf/init', 'tectn_register_forms_options_page', 10 );
function tectn_get_footer_information() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	if ( ! function_exists( 'get_field' ) ) {
		$cache = array();
		return $cache;
	}
	$out = array();
	$fi  = get_field( 'footer_information', 'footer-information' );
	if ( ! is_array( $fi ) ) {
		$fi = array();
	}
	$ss = get_field( 'site_settings', 'site-settings' );
	if ( ! is_array( $ss ) ) {
		$ss = get_field( 'site_settings', 'option' );
	}
	if ( ! is_array( $ss ) ) {
		$ss = array();
	}
	$keys = array(
		'contact_information',
		'address',
		'phone_number',
		'email_address',
		'social_platforms',
		'buttons',
		'button_color',
		'disclaimer_text',
		'footer_tagline',
	);
	foreach ( $keys as $key ) {
		$from_fi = array_key_exists( $key, $fi ) ? $fi[ $key ] : null;
		$from_ss = array_key_exists( $key, $ss ) ? $ss[ $key ] : null;
		if ( ! tectn_footer_information_value_is_empty( $key, $from_fi ) ) {
			$out[ $key ] = $from_fi;
		} elseif ( ! tectn_footer_information_value_is_empty( $key, $from_ss ) ) {
			$out[ $key ] = $from_ss;
		}
	}
	$cache = $out;
	return $cache;
}

/**
 * @param string $key Field name.
 * @param mixed  $val Value from ACF.
 */
function tectn_footer_information_value_is_empty( $key, $val ) {
	if ( null === $val ) {
		return true;
	}
	if ( 'social_platforms' === $key || 'buttons' === $key ) {
		return ! is_array( $val ) || array() === $val;
	}
	if ( 'contact_information' === $key ) {
		return ! is_array( $val ) || array() === $val;
	}
	if ( 'disclaimer_text' === $key || 'address' === $key || 'phone_number' === $key || 'email_address' === $key ) {
		return is_string( $val ) ? trim( $val ) === '' : empty( $val );
	}
	if ( 'button_color' === $key ) {
		return $val === '' || $val === null;
	}
	if ( 'footer_tagline' === $key ) {
		return $val === null || $val === '' || ( is_string( $val ) && trim( $val ) === '' );
	}
	return empty( $val );
}
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
