<?php
/**
 * Theme includes.
 * @package tectn_theme
 * Embedded forms and Forms block helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embedded forms from Site Settings → Forms (repeater rows with form_embed_code, form_key).
 *
 * @return list<array{form_admin_label?: string, form_key?: string, form_embed_code?: string}>
 */
function tectn_get_embedded_forms() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$rows = get_field( 'embedded_forms', 'tectn-forms' );
	return is_array( $rows ) ? $rows : array();
}
/**
 * After saving Forms options: ensure every repeater row has a non-empty form_key.
 *
 * @param int|string $post_id Post ID or options screen id (e.g. tectn-forms).
 */
function tectn_forms_ensure_row_keys_on_save( $post_id ) {
	static $lock = false;
	if ( $lock || (string) $post_id !== 'tectn-forms' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$rows = get_field( 'embedded_forms', 'tectn-forms' );
	if ( ! is_array( $rows ) ) {
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
		$lock = true;
		update_field( 'embedded_forms', $rows, 'tectn-forms' );
		$lock = false;
	}
}
add_action( 'acf/save_post', 'tectn_forms_ensure_row_keys_on_save', 25 );

/**
 * Resolve Forms block selection from block JSON + ACF meta (handles field rename / key-based storage).
 *
 * Prefer $block['data'] first: it is the canonical store for ACF block attributes. get_field( 'selected_form_key' )
 * without a post ID can follow the wrong context during some Loops/cached renders; block JSON stays correct.
 * Fall back to get_field() when block markup has no data yet (e.g. edge REST/preview cases).
 *
 * @param array<string, mixed> $block ACF block props passed to the render template.
 * @return mixed|null
 */
function tectn_forms_block_get_selected_raw( $block ) {
	$data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();

	$data_keys = array(
		'field_tectn_block_forms_selected',
		'selected_form_key',
		'selected_form_index',
	);
	foreach ( $data_keys as $k ) {
		if ( ! array_key_exists( $k, $data ) ) {
			continue;
		}
		$v = $data[ $k ];
		if ( $v !== null && $v !== '' && $v !== false ) {
			return $v;
		}
	}

	if ( function_exists( 'get_field' ) ) {
		$v = get_field( 'selected_form_key' );
		if ( $v !== null && $v !== '' && $v !== false ) {
			return $v;
		}
	}

	return null;
}

/**
 * Decode entity-encoded embed HTML (e.g. &lt;script) so browsers execute scripts / render iframes.
 *
 * @param string $code Raw embed string from options.
 * @return string
 */
function tectn_normalize_form_embed_markup( $code ) {
	if ( ! is_string( $code ) || $code === '' ) {
		return $code;
	}
	if ( strpos( $code, '&lt;' ) === false ) {
		return $code;
	}
	if ( ! preg_match( '/&lt;(script|iframe|div|form)\b/i', $code ) ) {
		return $code;
	}
	$flags   = ENT_QUOTES | ENT_SUBSTITUTE;
	$flags  |= defined( 'ENT_HTML5' ) ? ENT_HTML5 : 0;
	$decoded = html_entity_decode( $code, $flags, 'UTF-8' );
	if ( $decoded !== $code && ( strpos( $decoded, '<script' ) !== false || strpos( $decoded, '<iframe' ) !== false ) ) {
		return $decoded;
	}
	return $code;
}

/**
 * Find a repeater row by stable form_key, or by legacy numeric index string.
 *
 * @param mixed $selected Stored block value (UUID or legacy "0", "1", …).
 * @return array<string, mixed>|null Row with form_embed_code etc., or null.
 */
function tectn_find_embedded_form_by_selector( $selected ) {
	if ( $selected === null || $selected === false || $selected === '' ) {
		return null;
	}
	$selected = is_string( $selected ) ? trim( $selected ) : (string) $selected;
	if ( $selected === '' ) {
		return null;
	}
	$rows = tectn_get_embedded_forms();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['form_key'] ) ? trim( (string) $row['form_key'] ) : '';
		if ( $key !== '' && $key === $selected ) {
			return $row;
		}
	}
	// Legacy: block stored repeater row index before form_key existed.
	if ( preg_match( '/^\d+$/', $selected ) ) {
		$idx = (int) $selected;
		if ( isset( $rows[ $idx ] ) && is_array( $rows[ $idx ] ) ) {
			return $rows[ $idx ];
		}
	}
	return null;
}

/**
 * Populate Forms block select from Site Settings → Forms repeater (by stable form_key).
 *
 * @param array<string, mixed> $field ACF field array.
 * @return array<string, mixed>
 */
function tectn_load_field_forms_block_selected_form( $field ) {
	if ( ! is_array( $field ) || ( $field['key'] ?? '' ) !== 'field_tectn_block_forms_selected' ) {
		return $field;
	}
	$field['choices'] = array(
		'' => __( '— Select a form —', 'tectn_theme' ),
	);
	$rows = tectn_get_embedded_forms();
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['form_key'] ) ? trim( (string) $row['form_key'] ) : '';
		if ( $key === '' ) {
			continue;
		}
		$label = isset( $row['form_admin_label'] ) ? trim( (string) $row['form_admin_label'] ) : '';
		if ( $label === '' ) {
			/* translators: %d: 1-based form row number in Site Settings. */
			$label = sprintf( __( 'Form %d', 'tectn_theme' ), $i + 1 );
		}
		$field['choices'][ $key ] = $label;
	}
	if ( count( $field['choices'] ) === 1 ) {
		$field['instructions'] = __( 'Add one or more forms under Site Settings → Forms, save that page to generate Form IDs, then refresh this screen to see them listed here.', 'tectn_theme' );
	}
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_block_forms_selected', 'tectn_load_field_forms_block_selected_form' );
