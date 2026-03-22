<?php
/**
 * Theme includes.
 * @package tectn_theme
 * One-time ACF/data migrations.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tectn_migrate_forms_repeater_row_keys() {
	if ( get_option( 'tectn_forms_row_keys_initialized', '' ) === '1' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$rows = get_field( 'embedded_forms', 'tectn-forms' );
	if ( ! is_array( $rows ) ) {
		update_option( 'tectn_forms_row_keys_initialized', '1' );
		return;
	}
	$changed = false;
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$k = isset( $row['form_key'] ) ? trim( (string) $row['form_key'] ) : '';
		if ( $k === '' ) {
			$rows[ $i ]['form_key'] = wp_generate_uuid4();
			$changed                = true;
		}
	}
	if ( $changed ) {
		update_field( 'embedded_forms', $rows, 'tectn-forms' );
	}
	update_option( 'tectn_forms_row_keys_initialized', '1' );
}
add_action( 'acf/init', 'tectn_migrate_forms_repeater_row_keys', 100 );
function tectn_migrate_footer_information_from_site_settings() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( get_option( 'tectn_footer_information_migrated_v1', '' ) === '1' ) {
		return;
	}
	$ss = get_field( 'site_settings', 'site-settings' );
	if ( ! is_array( $ss ) ) {
		$ss = get_field( 'site_settings', 'option' );
	}
	if ( ! is_array( $ss ) ) {
		update_option( 'tectn_footer_information_migrated_v1', '1' );
		return;
	}
	$payload = array();
	$keys    = array(
		'contact_information',
		'address',
		'phone_number',
		'email_address',
		'social_platforms',
		'buttons',
		'button_color',
		'disclaimer_text',
	);
	foreach ( $keys as $key ) {
		if ( array_key_exists( $key, $ss ) && ! tectn_footer_information_value_is_empty( $key, $ss[ $key ] ) ) {
			$payload[ $key ] = $ss[ $key ];
		}
	}
	if ( array() !== $payload ) {
		update_field( 'footer_information', $payload, 'footer-information' );
	}
	update_option( 'tectn_footer_information_migrated_v1', '1' );
}
add_action( 'acf/init', 'tectn_migrate_footer_information_from_site_settings', 99 );

/**
 * Copy legacy Google Maps API key (standalone Theme Settings field) into Site Settings → Google API tab once.
 */
function tectn_migrate_google_maps_key_into_site_settings() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( get_option( 'tectn_google_maps_key_migrated_v1', '' ) === '1' ) {
		return;
	}
	$ss = get_field( 'site_settings', 'site-settings' );
	if ( ! is_array( $ss ) ) {
		$ss = get_field( 'site_settings', 'option' );
	}
	if ( ! is_array( $ss ) ) {
		$ss = array();
	}
	$current = isset( $ss['google_maps_api_key'] ) ? trim( (string) $ss['google_maps_api_key'] ) : '';
	if ( $current !== '' ) {
		update_option( 'tectn_google_maps_key_migrated_v1', '1' );
		return;
	}
	$legacy = get_field( 'google_maps_api_key', 'option' );
	if ( is_string( $legacy ) && $legacy !== '' ) {
		$ss['google_maps_api_key'] = $legacy;
		update_field( 'site_settings', $ss, 'site-settings' );
	}
	update_option( 'tectn_google_maps_key_migrated_v1', '1' );
}
add_action( 'acf/init', 'tectn_migrate_google_maps_key_into_site_settings', 99 );

/**
 * Copy legacy Theme Settings sidebar toggles into Post Settings once.
 */
function tectn_migrate_theme_sidebar_fields_to_post_settings() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( get_option( 'tectn_theme_sidebar_fields_migrated_v1', '' ) === '1' ) {
		return;
	}
	$names = array( 'include_sidebar_on_blog_posts', 'include_sidebar_on_blog_page' );
	foreach ( $names as $name ) {
		$old = get_field( $name, 'option' );
		if ( $old === null ) {
			$old = get_field( $name, 'theme-general-settings' );
		}
		if ( $old === null ) {
			continue;
		}
		update_field( $name, $old, 'post-settings' );
	}
	update_option( 'tectn_theme_sidebar_fields_migrated_v1', '1' );
}
add_action( 'acf/init', 'tectn_migrate_theme_sidebar_fields_to_post_settings', 99 );

/**
 * Copy Footer Tagline from old Theme Settings → Footer sub-page into Footer Information once.
 */
function tectn_migrate_footer_tagline_to_footer_information() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( get_option( 'tectn_footer_tagline_migrated_v1', '' ) === '1' ) {
		return;
	}
	$fi = get_field( 'footer_information', 'footer-information' );
	if ( ! is_array( $fi ) ) {
		$fi = array();
	}
	$existing = isset( $fi['footer_tagline'] ) ? trim( (string) $fi['footer_tagline'] ) : '';
	if ( $existing !== '' ) {
		update_option( 'tectn_footer_tagline_migrated_v1', '1' );
		return;
	}
	$old = get_field( 'footer_tagline', 'acf-options-footer' );
	if ( $old === null || $old === false || $old === '' ) {
		$old = get_field( 'footer_tagline', 'option' );
	}
	if ( $old !== null && $old !== false && $old !== '' ) {
		$fi['footer_tagline'] = $old;
		update_field( 'footer_information', $fi, 'footer-information' );
	}
	update_option( 'tectn_footer_tagline_migrated_v1', '1' );
}
add_action( 'acf/init', 'tectn_migrate_footer_tagline_to_footer_information', 99 );
