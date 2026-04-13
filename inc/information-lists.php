<?php
/**
 * Theme includes.
 *
 * @package tectn_theme
 * Information lists (Site Settings) — repeater of lists with stable IDs and link items.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embedded information lists from Site Settings → Information lists.
 *
 * @return list<array<string, mixed>>
 */
function tectn_get_embedded_information_lists() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$rows = get_field( 'embedded_information_lists', 'tectn-information-lists' );
	return is_array( $rows ) ? $rows : array();
}

/**
 * After saving Information lists options: ensure every top-level repeater row has a non-empty information_list_key.
 *
 * @param int|string $post_id Post ID or options screen id.
 */
function tectn_information_lists_ensure_row_keys_on_save( $post_id ) {
	static $lock = false;
	if ( $lock || (string) $post_id !== 'tectn-information-lists' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$rows = get_field( 'embedded_information_lists', 'tectn-information-lists' );
	if ( ! is_array( $rows ) ) {
		return;
	}
	$changed = false;
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$k = isset( $row['information_list_key'] ) ? trim( (string) $row['information_list_key'] ) : '';
		if ( $k === '' ) {
			$rows[ $i ]['information_list_key'] = wp_generate_uuid4();
			$changed                            = true;
		}
	}
	if ( $changed ) {
		$lock = true;
		update_field( 'embedded_information_lists', $rows, 'tectn-information-lists' );
		$lock = false;
	}
}
add_action( 'acf/save_post', 'tectn_information_lists_ensure_row_keys_on_save', 25 );

/**
 * Find an information list by stable information_list_key or legacy numeric index.
 *
 * @param mixed $selected UUID or legacy "0", "1", ….
 * @return array<string, mixed>|null
 */
function tectn_find_information_list_by_selector( $selected ) {
	if ( $selected === null || $selected === false || $selected === '' ) {
		return null;
	}
	$selected = is_string( $selected ) ? trim( $selected ) : (string) $selected;
	if ( $selected === '' ) {
		return null;
	}
	$rows = tectn_get_embedded_information_lists();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['information_list_key'] ) ? trim( (string) $row['information_list_key'] ) : '';
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
 * Reorder list entries for column-major layout using a row-major CSS grid.
 *
 * grid-auto-flow: column does not produce this effect (it adds implicit columns in one row).
 * We output items in row-major order so each row reads across columns after filling down each column first.
 *
 * @param list<array<string, mixed>> $entries Sequential list items (0..n-1).
 * @param int                      $cols  Column count (>=2).
 * @return list<array<string, mixed>>
 */
function tectn_information_lists_reorder_for_column_flow( array $entries, $cols ) {
	$cols = (int) $cols;
	$n    = count( $entries );
	if ( $cols < 2 || $n < 2 ) {
		return $entries;
	}
	$entries = array_values( $entries );
	$rows    = (int) ceil( $n / $cols );
	$out     = array();
	for ( $g = 0; $g < $n; $g++ ) {
		$row = intdiv( $g, $cols );
		$col = $g % $cols;
		$src = $col * $rows + $row;
		if ( $src < $n ) {
			$out[] = $entries[ $src ];
		}
	}
	return $out;
}

/**
 * Resolve Information lists block selection from block JSON + ACF meta.
 *
 * @param array<string, mixed> $block ACF block props passed to the render template.
 * @return mixed|null
 */
function tectn_information_lists_block_get_selected_raw( $block ) {
	$data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();

	$data_keys = array(
		'field_tectn_block_information_list_selected',
		'selected_information_list_key',
		'selected_information_list_index',
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
		$v = get_field( 'selected_information_list_key' );
		if ( $v !== null && $v !== '' && $v !== false ) {
			return $v;
		}
	}

	return null;
}

/**
 * Populate Information lists block select from Site Settings repeater (by stable information_list_key).
 *
 * @param array<string, mixed> $field ACF field array.
 * @return array<string, mixed>
 */
function tectn_load_field_information_list_block_selected( $field ) {
	if ( ! is_array( $field ) || ( $field['key'] ?? '' ) !== 'field_tectn_block_information_list_selected' ) {
		return $field;
	}
	$field['choices'] = array(
		'' => __( '— Select a list —', 'tectn_theme' ),
	);
	$rows = tectn_get_embedded_information_lists();
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['information_list_key'] ) ? trim( (string) $row['information_list_key'] ) : '';
		if ( $key === '' ) {
			continue;
		}
		$label = isset( $row['information_list_admin_label'] ) ? trim( (string) $row['information_list_admin_label'] ) : '';
		if ( $label === '' ) {
			/* translators: %d: 1-based list row number in Site Settings. */
			$label = sprintf( __( 'Information list %d', 'tectn_theme' ), $i + 1 );
		}
		$field['choices'][ $key ] = $label;
	}
	if ( count( $field['choices'] ) === 1 ) {
		$field['instructions'] = __( 'Add one or more lists under Site Settings → Information lists, save that page to generate IDs, then refresh this screen to see them listed here.', 'tectn_theme' );
	}
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_block_information_list_selected', 'tectn_load_field_information_list_block_selected' );
