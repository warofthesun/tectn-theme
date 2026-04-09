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
 * Allow trusted script/iframes in Form embed textareas when saving Site Settings → Forms only.
 *
 * ACF runs wp_kses_post_deep() on $_POST['acf'] when the user lacks unfiltered_html (common on
 * multisite or for roles below Administrator), which strips Bloomerang-style <script> blocks;
 * repeater saves can then persist empty embeds for one or all rows.
 *
 * @param bool $allow Default from current_user_can( 'unfiltered_html' ).
 * @return bool
 */
function tectn_forms_allow_unfiltered_html_on_options_save( $allow ) {
	if ( $allow ) {
		return true;
	}
	if ( ! is_admin() || ! function_exists( 'acf_get_form_data' ) ) {
		return false;
	}
	// Set in acf_save_post() before the kses check (see acf/includes/acf-form-functions.php).
	if ( (string) acf_get_form_data( 'post_id' ) !== 'tectn-forms' ) {
		return false;
	}
	// Match capability on the Forms options sub-page (acf-options.php registers edit_posts).
	return current_user_can( 'edit_posts' );
}
add_filter( 'acf/allow_unfiltered_html', 'tectn_forms_allow_unfiltered_html_on_options_save', 5 );

/**
 * Map ACF subfield keys to names when a row only has field_* keys (raw load edge cases).
 *
 * @param array<string, mixed> $row Repeater sub-row.
 * @return array<string, mixed>
 */
function tectn_normalize_embedded_form_row( array $row ) {
	$map = array(
		'field_tectn_form_admin_label'   => 'form_admin_label',
		'field_tectn_form_row_key'       => 'form_key',
		'field_tectn_form_embed_code'    => 'form_embed_code',
		'field_tectn_form_isolate_frame' => 'form_isolate_frame',
	);
	foreach ( $map as $field_key => $name ) {
		if ( ! array_key_exists( $name, $row ) && array_key_exists( $field_key, $row ) ) {
			$row[ $name ] = $row[ $field_key ];
		}
	}
	return $row;
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
	if ( ! is_array( $rows ) ) {
		return array();
	}
	$out = array();
	foreach ( $rows as $row ) {
		if ( is_array( $row ) ) {
			$out[] = tectn_normalize_embedded_form_row( $row );
		}
	}
	return $out;
}
/**
 * After saving Forms options: fill empty form_key values only (never rewrite the whole repeater).
 *
 * Replacing the entire repeater via update_field() while rows contain large script embeds can drop or
 * corrupt sibling rows during the same save; updating one subfield at a time avoids that.
 */
function tectn_forms_schedule_missing_form_keys( $post_id ) {
	if ( (string) $post_id !== 'tectn-forms' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_sub_field' ) ) {
		return;
	}
	static $scheduled = false;
	if ( $scheduled ) {
		return;
	}
	$scheduled = true;
	add_action( 'shutdown', 'tectn_forms_fill_missing_form_keys_shutdown', 5 );
}
add_action( 'acf/save_post', 'tectn_forms_schedule_missing_form_keys', 50 );

/**
 * Runs after the request so ACF has persisted the repeater; patches only empty form_key cells.
 */
function tectn_forms_fill_missing_form_keys_shutdown() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_sub_field' ) ) {
		return;
	}
	$rows = get_field( 'embedded_forms', 'tectn-forms' );
	if ( ! is_array( $rows ) || $rows === array() ) {
		return;
	}
	$rows = array_values( $rows );
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$k = isset( $row['form_key'] ) ? trim( (string) $row['form_key'] ) : '';
		if ( $k !== '' ) {
			continue;
		}
		update_sub_field(
			array( 'embedded_forms', $i + 1, 'form_key' ),
			wp_generate_uuid4(),
			'tectn-forms'
		);
	}
}

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
	if ( $data === array() && isset( $block['attrs']['data'] ) && is_array( $block['attrs']['data'] ) ) {
		$data = $block['attrs']['data'];
	}

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
 * Wrap raw embed HTML in an iframe via srcdoc so each form has its own JavaScript global scope.
 * Needed for vendors (e.g. Bloomerang) that limit one “interaction” form per page.document.
 *
 * @param string $html Full embed markup (scripts, etc.).
 * @param string $iframe_title Accessible title (optional).
 * @return string iframe HTML or empty string if $html is empty.
 */
function tectn_forms_embed_iframe_srcdoc( $html, $iframe_title = '' ) {
	if ( ! is_string( $html ) || trim( $html ) === '' ) {
		return '';
	}
	$title = is_string( $iframe_title ) ? trim( $iframe_title ) : '';
	if ( $title === '' ) {
		$title = __( 'Embedded form', 'tectn_theme' );
	}
	$document = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><base target="_top"></head><body>'
		. $html
		. '</body></html>';
	$flags = ENT_QUOTES | ENT_SUBSTITUTE;
	if ( defined( 'ENT_HTML5' ) ) {
		$flags |= ENT_HTML5;
	}
	$srcdoc = htmlspecialchars( $document, $flags, 'UTF-8' );
	$uid    = wp_unique_id( 'tectn-form-iframe-' );
	return sprintf(
		'<iframe id="%1$s" class="c-formsEmbed__iframe" title="%2$s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" srcdoc="%3$s" style="width:100%%;min-height:32rem;border:0;display:block;background:transparent"></iframe>',
		esc_attr( $uid ),
		esc_attr( $title ),
		$srcdoc
	);
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
