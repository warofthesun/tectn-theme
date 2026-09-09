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
	$rows = array_values( $rows );
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$k = isset( $row['form_key'] ) ? trim( (string) $row['form_key'] ) : '';
		if ( $k === '' && function_exists( 'update_sub_field' ) ) {
			update_sub_field(
				array( 'embedded_forms', $i + 1, 'form_key' ),
				wp_generate_uuid4(),
				'tectn-forms'
			);
		}
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
 * Copy legacy Google Maps API key (standalone Theme Settings field) into site_settings group once (legacy storage).
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
 * Copy Maps API key from legacy site_settings / options into Site Settings → Integrations once (for admin UI).
 */
function tectn_migrate_google_maps_key_to_integrations() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( get_option( 'tectn_google_maps_key_migrated_v2', '' ) === '1' ) {
		return;
	}
	$on_integrations = get_field( 'google_maps_api_key', 'site-settings-integrations' );
	if ( is_string( $on_integrations ) && trim( $on_integrations ) !== '' ) {
		update_option( 'tectn_google_maps_key_migrated_v2', '1' );
		return;
	}
	$key = '';
	$ss  = get_field( 'site_settings', 'site-settings' );
	if ( ! is_array( $ss ) ) {
		$ss = get_field( 'site_settings', 'option' );
	}
	if ( is_array( $ss ) && ! empty( $ss['google_maps_api_key'] ) && is_string( $ss['google_maps_api_key'] ) ) {
		$key = trim( $ss['google_maps_api_key'] );
	}
	if ( $key === '' ) {
		$legacy = get_field( 'google_maps_api_key', 'option' );
		if ( is_string( $legacy ) && $legacy !== '' ) {
			$key = trim( $legacy );
		}
	}
	if ( $key === '' ) {
		$opt = get_option( 'tectn_google_maps_api_key', '' );
		if ( is_string( $opt ) && $opt !== '' ) {
			$key = trim( $opt );
		}
	}
	if ( $key !== '' ) {
		update_field( 'google_maps_api_key', $key, 'site-settings-integrations' );
	}
	update_option( 'tectn_google_maps_key_migrated_v2', '1' );
}
add_action( 'acf/init', 'tectn_migrate_google_maps_key_to_integrations', 101 );

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
 * After contact fields moved into the footer_information group, copy values from legacy options-page root keys once.
 */
function tectn_migrate_footer_contact_into_footer_group() {
	if ( get_option( 'tectn_footer_contact_into_group_v1', '' ) === '1' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) || ! function_exists( 'tectn_footer_information_value_is_empty' ) ) {
		return;
	}
	$fi = get_field( 'footer_information', 'footer-information' );
	if ( ! is_array( $fi ) ) {
		$fi = array();
	}
	$changed = false;
	foreach ( array( 'address', 'phone_number', 'email_address' ) as $name ) {
		$current = isset( $fi[ $name ] ) ? $fi[ $name ] : null;
		if ( ! tectn_footer_information_value_is_empty( $name, $current ) ) {
			continue;
		}
		$legacy = function_exists( 'acf_get_metadata' ) ? acf_get_metadata( 'footer-information', $name, false ) : null;
		if ( tectn_footer_information_value_is_empty( $name, $legacy ) ) {
			$legacy = get_field( $name, 'footer-information' );
		}
		if ( ! tectn_footer_information_value_is_empty( $name, $legacy ) ) {
			$fi[ $name ] = $legacy;
			$changed     = true;
		}
	}
	if ( $changed ) {
		update_field( 'footer_information', $fi, 'footer-information' );
	}
	update_option( 'tectn_footer_contact_into_group_v1', '1' );
}
add_action( 'acf/init', 'tectn_migrate_footer_contact_into_footer_group', 103 );

/**
 * Restore Hero Options (and other ACF side boxes) yanked out of the Document sidebar
 * via WordPress "Move down" meta box reorder. Runs once per site.
 *
 * Meta box id for field group group_68260ada5ea86 is acf-group_68260ada5ea86.
 */
function tectn_migrate_restore_hero_options_metabox_order() {
	if ( get_option( 'tectn_hero_options_metabox_order_reset_v1', '' ) === '1' ) {
		return;
	}

	$hero_box_id = 'acf-group_68260ada5ea86';
	$user_ids    = get_users(
		array(
			'fields' => 'ID',
			'number' => -1,
		)
	);

	foreach ( $user_ids as $user_id ) {
		$user_id = (int) $user_id;
		if ( $user_id <= 0 ) {
			continue;
		}

		// Un-hide if Screen Options / preferences hid the box.
		$hidden = get_user_meta( $user_id, 'metaboxhidden_page', true );
		if ( is_array( $hidden ) && in_array( $hero_box_id, $hidden, true ) ) {
			$hidden = array_values( array_diff( $hidden, array( $hero_box_id ) ) );
			update_user_meta( $user_id, 'metaboxhidden_page', $hidden );
		}

		$order = get_user_meta( $user_id, 'meta-box-order_page', true );
		if ( ! is_array( $order ) || $order === array() ) {
			continue;
		}

		$contexts = array( 'side', 'normal', 'advanced' );
		$found_in = '';
		$boxes    = array();

		foreach ( $contexts as $context ) {
			if ( empty( $order[ $context ] ) || ! is_string( $order[ $context ] ) ) {
				$boxes[ $context ] = array();
				continue;
			}
			$ids = array_values(
				array_filter(
					array_map( 'trim', explode( ',', $order[ $context ] ) )
				)
			);
			if ( in_array( $hero_box_id, $ids, true ) ) {
				$found_in = $context;
				$ids      = array_values( array_diff( $ids, array( $hero_box_id ) ) );
			}
			$boxes[ $context ] = $ids;
		}

		// Already on side, or not in a custom order list: leave alone.
		if ( $found_in === '' || $found_in === 'side' ) {
			continue;
		}

		// Stuck in normal/advanced after "Move down": put back on side.
		array_unshift( $boxes['side'], $hero_box_id );
		$boxes['side'] = array_values( array_unique( $boxes['side'] ) );

		$new_order = array();
		foreach ( $contexts as $context ) {
			if ( ! empty( $boxes[ $context ] ) ) {
				$new_order[ $context ] = implode( ',', $boxes[ $context ] );
			} elseif ( isset( $order[ $context ] ) ) {
				$new_order[ $context ] = '';
			}
		}
		// Preserve any unexpected keys from the original option.
		foreach ( $order as $key => $value ) {
			if ( ! isset( $new_order[ $key ] ) ) {
				$new_order[ $key ] = $value;
			}
		}

		update_user_meta( $user_id, 'meta-box-order_page', $new_order );
	}

	update_option( 'tectn_hero_options_metabox_order_reset_v1', '1' );
}
add_action( 'admin_init', 'tectn_migrate_restore_hero_options_metabox_order', 20 );
