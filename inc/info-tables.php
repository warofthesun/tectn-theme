<?php
/**
 * Theme includes.
 *
 * @package tectn_theme
 * Information tables (Site Settings) + Info table block helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embedded info tables from Site Settings → Information tables.
 *
 * @return list<array<string, mixed>>
 */
function tectn_get_embedded_info_tables() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$rows = get_field( 'embedded_info_tables', 'tectn-info-tables' );
	return is_array( $rows ) ? $rows : array();
}

/**
 * After saving Information tables options: ensure every top-level repeater row has a non-empty info_table_key.
 *
 * @param int|string $post_id Post ID or options screen id.
 */
function tectn_info_tables_ensure_row_keys_on_save( $post_id ) {
	static $lock = false;
	if ( $lock || (string) $post_id !== 'tectn-info-tables' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$rows = get_field( 'embedded_info_tables', 'tectn-info-tables' );
	if ( ! is_array( $rows ) ) {
		return;
	}
	$changed = false;
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$k = isset( $row['info_table_key'] ) ? trim( (string) $row['info_table_key'] ) : '';
		if ( $k === '' ) {
			$rows[ $i ]['info_table_key'] = wp_generate_uuid4();
			$changed                      = true;
		}
	}
	if ( $changed ) {
		$lock = true;
		update_field( 'embedded_info_tables', $rows, 'tectn-info-tables' );
		$lock = false;
	}
}
add_action( 'acf/save_post', 'tectn_info_tables_ensure_row_keys_on_save', 25 );

/**
 * Resolve Info table block selection from block JSON + ACF meta.
 *
 * @param array<string, mixed> $block ACF block props passed to the render template.
 * @return mixed|null
 */
function tectn_info_tables_block_get_selected_raw( $block ) {
	$data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();

	$data_keys = array(
		'field_tectn_block_info_table_selected',
		'selected_info_table_key',
		'selected_info_table_index',
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
		$v = get_field( 'selected_info_table_key' );
		if ( $v !== null && $v !== '' && $v !== false ) {
			return $v;
		}
	}

	return null;
}

/**
 * Find an info table by stable info_table_key or legacy numeric index.
 *
 * @param mixed $selected Stored block value (UUID or legacy "0", "1", …).
 * @return array<string, mixed>|null
 */
function tectn_find_info_table_by_selector( $selected ) {
	if ( $selected === null || $selected === false || $selected === '' ) {
		return null;
	}
	$selected = is_string( $selected ) ? trim( $selected ) : (string) $selected;
	if ( $selected === '' ) {
		return null;
	}
	$rows = tectn_get_embedded_info_tables();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['info_table_key'] ) ? trim( (string) $row['info_table_key'] ) : '';
		if ( $key !== '' && $key === $selected ) {
			return $row;
		}
	}
	if ( preg_match( '/^\d+$/', $selected ) ) {
		$idx = (int) $selected;
		if ( isset( $rows[ $idx ] ) && is_array( $rows[ $idx ] ) ) {
			return $rows[ $idx ];
		}
	}
	return null;
}

/**
 * Populate Info table block select from Site Settings repeater (by stable info_table_key).
 *
 * @param array<string, mixed> $field ACF field array.
 * @return array<string, mixed>
 */
function tectn_load_field_info_table_block_selected( $field ) {
	if ( ! is_array( $field ) || ( $field['key'] ?? '' ) !== 'field_tectn_block_info_table_selected' ) {
		return $field;
	}
	$field['choices'] = array(
		'' => __( '— Select a table —', 'tectn_theme' ),
	);
	$rows = tectn_get_embedded_info_tables();
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['info_table_key'] ) ? trim( (string) $row['info_table_key'] ) : '';
		if ( $key === '' ) {
			continue;
		}
		$label = isset( $row['info_table_admin_label'] ) ? trim( (string) $row['info_table_admin_label'] ) : '';
		if ( $label === '' ) {
			/* translators: %d: 1-based table row number in Site Settings. */
			$label = sprintf( __( 'Information table %d', 'tectn_theme' ), $i + 1 );
		}
		$field['choices'][ $key ] = $label;
	}
	if ( count( $field['choices'] ) === 1 ) {
		$field['instructions'] = __( 'Add one or more tables under Site Settings → Information tables, save that page to generate IDs, then refresh this screen to see them listed here.', 'tectn_theme' );
	}
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_block_info_table_selected', 'tectn_load_field_info_table_block_selected' );
