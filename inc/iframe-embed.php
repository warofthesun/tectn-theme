<?php
/**
 * Iframe Embed block helpers.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed HTML for pasted iframe embeds (maps, video players, etc.).
 *
 * @return array<string, array<string, bool>>
 */
function tectn_iframe_embed_allowed_iframe_tags() {
	return array(
		'iframe' => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'style'           => true,
			'class'           => true,
			'id'              => true,
			'title'           => true,
			'name'            => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'referrerpolicy'  => true,
			'loading'         => true,
			'sandbox'         => true,
			'scrolling'       => true,
			'marginwidth'     => true,
			'marginheight'    => true,
			'align'           => true,
		),
	);
}

/**
 * Read a block field: prefer $block['data'] (editor preview), then get_field().
 * Skips ACF auto-inline-editing placeholder strings.
 *
 * @param string                    $name      Field name.
 * @param string                    $field_key Field key (field_…).
 * @param array<string, mixed>|null $block     Block array (optional).
 * @return mixed|null
 */
function tectn_iframe_embed_field_value( $name, $field_key, $block = null ) {
	if ( function_exists( 'tectn_acf_block_field' ) ) {
		return tectn_acf_block_field( $name, $block, $field_key );
	}

	if ( is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) {
		$data = $block['data'];
		if ( array_key_exists( $field_key, $data ) ) {
			return $data[ $field_key ];
		}
		if ( array_key_exists( $name, $data ) ) {
			return $data[ $name ];
		}
	}

	if ( function_exists( 'get_field' ) ) {
		return get_field( $name );
	}

	return null;
}

/**
 * Coerce ACF true/false (including string "0" / "1") with a default when unset.
 *
 * @param mixed $value   Raw field value.
 * @param bool  $default Default when null/''.
 * @return bool
 */
function tectn_iframe_embed_coerce_bool( $value, $default = true ) {
	if ( function_exists( 'tectn_acf_is_inline_editing_placeholder' ) && tectn_acf_is_inline_editing_placeholder( $value ) ) {
		return (bool) $default;
	}
	if ( $value === null || $value === '' ) {
		return (bool) $default;
	}
	if ( is_bool( $value ) ) {
		return $value;
	}
	if ( is_int( $value ) || is_float( $value ) ) {
		return (int) $value === 1;
	}
	// ACF often stores "0" / "1"; (bool) "0" is true in PHP — treat explicitly.
	$str = strtolower( trim( (string) $value ) );
	if ( in_array( $str, array( '0', 'false', 'off', 'no' ), true ) ) {
		return false;
	}
	if ( in_array( $str, array( '1', 'true', 'on', 'yes' ), true ) ) {
		return true;
	}
	return (bool) $default;
}

/**
 * BEM modifiers for background, min-height, and aspect ratio (independent).
 *
 * @param array<string, mixed>|null $block Block array for editor data fallback.
 * @return list<string>
 */
function tectn_iframe_embed_modifier_classes( $block = null ) {
	$classes = array();

	$show_background = tectn_iframe_embed_coerce_bool(
		tectn_iframe_embed_field_value( 'show_background', 'field_ieb_show_background', $block ),
		true
	);
	$use_min_height  = tectn_iframe_embed_coerce_bool(
		tectn_iframe_embed_field_value( 'use_min_height', 'field_ieb_use_min_height', $block ),
		true
	);
	$aspect          = tectn_iframe_embed_field_value( 'aspect_ratio', 'field_ieb_aspect_ratio', $block );

	if ( ! $show_background ) {
		$classes[] = 'c-iframe-embed--no-bg';
	}
	if ( ! $use_min_height ) {
		$classes[] = 'c-iframe-embed--no-min-height';
	}
	$classes[] = ( $aspect === '4_3' ) ? 'c-iframe-embed--ratio-4-3' : 'c-iframe-embed--ratio-16-9';

	return $classes;
}
