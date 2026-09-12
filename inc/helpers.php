<?php
/**
 * Theme includes.
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse headline_size (from ACF field_6992657b77c7f) into tag and class for output.
 * When "Hero" is selected (value contains "hero"), returns h2 with class "hero" plus any block class.
 *
 * @param string $headline_size Value from get_field('headline_size').
 * @param string $block_class   Optional block-specific class to append (e.g. c-slider__headline).
 * @return array{ tag: string, class: string } Safe tag (h1-h6) and combined class string.
 */
function tectn_headline_tag_and_class( $headline_size, $block_class = '' ) {
	$headline_size = (string) $headline_size;
	$tag           = preg_replace( '/\s.*/', '', $headline_size );
	$tag           = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $tag : 'h2';
	$is_hero       = ( strpos( $headline_size, 'hero' ) !== false );
	if ( $is_hero ) {
		$tag   = 'h2';
		$class = 'hero';
	} else {
		$class = '';
		if ( preg_match( '/class\s*=\s*["\']?([^"\']*)["\']?/', $headline_size, $m ) ) {
			$class = trim( $m[1] );
		}
	}
	if ( $block_class !== '' ) {
		$class = trim( $class . ' ' . $block_class );
	}
	return array( 'tag' => $tag, 'class' => $class );
}

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
 * Featured image URL for post cards (size: post-card).
 *
 * After cropping/replacing the attachment in the media library, the main file can be newer than
 * the post-card intermediate still referenced in metadata; using that URL shows stale pixels.
 * If the main file is newer than the post-card file on disk, use the full-size URL instead.
 *
 * @param int $post_id Post ID.
 * @return string URL or empty string.
 */
function tectn_get_post_card_image_url( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return '';
	}
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $thumb_id ) {
		return '';
	}
	$meta = wp_get_attachment_metadata( $thumb_id );
	if ( empty( $meta['sizes']['post-card']['file'] ) ) {
		return (string) get_the_post_thumbnail_url( $post_id, 'post-card' );
	}
	$main_file = get_attached_file( $thumb_id );
	if ( ! $main_file || ! is_readable( $main_file ) ) {
		return (string) get_the_post_thumbnail_url( $post_id, 'post-card' );
	}
	$size_rel  = $meta['sizes']['post-card']['file'];
	$size_path = path_join( dirname( $main_file ), $size_rel );
	if ( ! is_readable( $size_path ) ) {
		$full = wp_get_attachment_image_url( $thumb_id, 'full' );
		return $full ? (string) $full : (string) get_the_post_thumbnail_url( $post_id, 'post-card' );
	}
	if ( filemtime( $main_file ) > filemtime( $size_path ) ) {
		$full = wp_get_attachment_image_url( $thumb_id, 'full' );
		return $full ? (string) $full : (string) get_the_post_thumbnail_url( $post_id, 'post-card' );
	}
	return (string) get_the_post_thumbnail_url( $post_id, 'post-card' );
}

/**
 * Allowed slider/slideshow focal point keys → CSS object-position values.
 *
 * @return array<string, string>
 */
function tectn_slider_focal_point_map() {
	return array(
		'center'       => 'center center',
		'top'          => 'center top',
		'bottom'       => 'center bottom',
		'left'         => 'left center',
		'right'        => 'right center',
		'top_left'     => 'left top',
		'top_right'    => 'right top',
		'bottom_left'  => 'left bottom',
		'bottom_right' => 'right bottom',
	);
}

/**
 * Resolve a focal point field value to a CSS object-position string.
 *
 * @param mixed $focal_point Field value (key) or already a CSS string.
 * @return string
 */
function tectn_slider_focal_css( $focal_point ) {
	$map = tectn_slider_focal_point_map();
	$key = is_string( $focal_point ) ? sanitize_key( $focal_point ) : 'center';
	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}
	if ( is_string( $focal_point ) && preg_match( '/^(left|center|right)(\s+(top|center|bottom))?$/i', trim( $focal_point ) ) ) {
		return strtolower( trim( $focal_point ) );
	}
	return 'center center';
}

/**
 * Whether a value is ACF Blocks auto-inline-editing placeholder (not a real field value).
 *
 * During some editor canvas renders with autoInlineEditing, get_field() returns
 * strings like "acf_auto_inline_editing_field_name_{name}" instead of stored data.
 * Those strings are truthy and break true/false and select checks.
 *
 * @param mixed $value Raw field value.
 * @return bool
 */
function tectn_acf_is_inline_editing_placeholder( $value ) {
	return is_string( $value ) && strpos( $value, 'acf_auto_inline_editing_field_name_' ) === 0;
}

/**
 * Coerce an ACF value to bool, ignoring auto-inline-editing placeholders.
 *
 * @param mixed $value   Raw field value (e.g. from get_sub_field).
 * @param bool  $default Fallback when empty or placeholder.
 * @return bool
 */
function tectn_acf_value_is_true( $value, $default = false ) {
	if ( tectn_acf_is_inline_editing_placeholder( $value ) ) {
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
 * Coerce an ACF value to trimmed text, ignoring auto-inline-editing placeholders.
 *
 * @param mixed  $value   Raw field value.
 * @param string $default Fallback when empty or placeholder.
 * @return string
 */
function tectn_acf_value_text( $value, $default = '' ) {
	if ( ! is_string( $value ) || tectn_acf_is_inline_editing_placeholder( $value ) ) {
		return $default;
	}
	return trim( $value );
}

/**
 * Whether a value looks like unformatted ACF image/gallery storage (IDs only).
 * Block meta stores attachment IDs; get_field() expands them to arrays with url/ID keys.
 *
 * @param mixed $value Raw value from $block['data'].
 * @return bool
 */
function tectn_acf_is_raw_media_value( $value ) {
	if ( is_int( $value ) || ( is_string( $value ) && $value !== '' && is_numeric( $value ) ) ) {
		return true;
	}
	if ( ! is_array( $value ) || $value === array() ) {
		return false;
	}
	foreach ( $value as $item ) {
		if ( ! is_int( $item ) && ! ( is_string( $item ) && $item !== '' && is_numeric( $item ) ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Read an ACF block field: prefer $block['data'], skip inline-editing placeholders.
 * For image/gallery fields, $block['data'] often holds raw attachment IDs — use get_field()
 * when available so return_format (array/url) is applied.
 *
 * @param string                    $name      Field name.
 * @param array<string, mixed>|null $block     Block array from render template.
 * @param string                    $field_key Optional field key (field_…).
 * @return mixed|null Null when unset / only placeholder available.
 */
function tectn_acf_block_field( $name, $block = null, $field_key = '' ) {
	$from_block_data = false;
	$value           = null;

	if ( is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) {
		$data = $block['data'];
		if ( $field_key !== '' && array_key_exists( $field_key, $data ) && ! tectn_acf_is_inline_editing_placeholder( $data[ $field_key ] ) ) {
			$from_block_data = true;
			$value           = $data[ $field_key ];
		} elseif ( array_key_exists( $name, $data ) && ! tectn_acf_is_inline_editing_placeholder( $data[ $name ] ) ) {
			$from_block_data = true;
			$value           = $data[ $name ];
		}
	}

	// Raw attachment IDs in block meta are not usable as image arrays — prefer formatted get_field().
	if ( $value !== null && tectn_acf_is_raw_media_value( $value ) && function_exists( 'get_field' ) ) {
		$formatted = get_field( $name );
		if ( ! tectn_acf_is_inline_editing_placeholder( $formatted ) && $formatted !== null && $formatted !== false && $formatted !== '' ) {
			return $formatted;
		}
	}

	if ( $value === null && ! $from_block_data ) {
		$value = function_exists( 'get_field' ) ? get_field( $name ) : null;
		if ( tectn_acf_is_inline_editing_placeholder( $value ) ) {
			return null;
		}
	}

	return $value;
}

/**
 * Coerce ACF true/false for blocks (handles "0"/"1" and inline-editing placeholders).
 *
 * @param string                    $name      Field name.
 * @param array<string, mixed>|null $block     Block array.
 * @param string                    $field_key Optional field key.
 * @param bool                      $default   When unset.
 * @return bool
 */
function tectn_acf_block_bool( $name, $block = null, $field_key = '', $default = false ) {
	$value = tectn_acf_block_field( $name, $block, $field_key );
	if ( $value === null || $value === '' ) {
		return (bool) $default;
	}
	if ( is_bool( $value ) ) {
		return $value;
	}
	if ( is_int( $value ) || is_float( $value ) ) {
		return (int) $value === 1;
	}
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
 * Read an ACF block array/repeater/gallery field; empty array when missing or placeholder.
 *
 * @param string                    $name      Field name.
 * @param array<string, mixed>|null $block     Block array.
 * @param string                    $field_key Optional field key.
 * @return array<mixed>
 */
function tectn_acf_block_array( $name, $block = null, $field_key = '' ) {
	$value = tectn_acf_block_field( $name, $block, $field_key );
	return is_array( $value ) ? $value : array();
}

