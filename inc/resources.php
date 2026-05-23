<?php
/**
 * Resources (Site Settings) — reusable sections with stable IDs and link items.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embedded resource sections from Site Settings → Resources.
 *
 * @return list<array<string, mixed>>
 */
function tectn_get_embedded_resource_sections() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$rows = get_field( 'embedded_resource_sections', 'tectn-resources' );
	return is_array( $rows ) ? $rows : array();
}

/**
 * After saving Resources options: ensure every section row has a non-empty resource_section_key.
 *
 * @param int|string $post_id Post ID or options screen id.
 */
function tectn_resources_ensure_section_keys_on_save( $post_id ) {
	static $lock = false;
	if ( $lock || (string) $post_id !== 'tectn-resources' ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$rows = get_field( 'embedded_resource_sections', 'tectn-resources' );
	if ( ! is_array( $rows ) ) {
		return;
	}
	$changed = false;
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$k = isset( $row['resource_section_key'] ) ? trim( (string) $row['resource_section_key'] ) : '';
		if ( $k === '' ) {
			$rows[ $i ]['resource_section_key'] = wp_generate_uuid4();
			$changed                          = true;
		}
	}
	if ( $changed ) {
		$lock = true;
		update_field( 'embedded_resource_sections', $rows, 'tectn-resources' );
		$lock = false;
	}
}
add_action( 'acf/save_post', 'tectn_resources_ensure_section_keys_on_save', 25 );

/**
 * Find a resource section by stable resource_section_key or legacy numeric index.
 *
 * @param mixed $selected UUID or legacy "0", "1", ….
 * @return array<string, mixed>|null
 */
function tectn_find_resource_section_by_selector( $selected ) {
	if ( $selected === null || $selected === false || $selected === '' ) {
		return null;
	}
	$selected = is_string( $selected ) ? trim( $selected ) : (string) $selected;
	if ( $selected === '' ) {
		return null;
	}
	$rows = tectn_get_embedded_resource_sections();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['resource_section_key'] ) ? trim( (string) $row['resource_section_key'] ) : '';
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
 * Normalize repeater rows (item_link + optional item_body) into render entries.
 *
 * @param list<array<string, mixed>> $rows Raw ACF repeater rows.
 * @return list<array{label: string, url: string, title: string, target: string, body: string}>
 */
function tectn_resources_normalize_item_rows( array $rows ) {
	$entries = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$link = isset( $row['item_link'] ) && is_array( $row['item_link'] ) ? $row['item_link'] : array();
		$url    = isset( $link['url'] ) ? trim( (string) $link['url'] ) : '';
		$title  = isset( $link['title'] ) ? trim( (string) $link['title'] ) : '';
		$target = isset( $link['target'] ) && (string) $link['target'] !== '' ? (string) $link['target'] : '_self';
		$label  = $title !== '' ? $title : $url;
		$body   = isset( $row['item_body'] ) ? trim( (string) $row['item_body'] ) : '';
		if ( $label === '' && $url === '' && $body === '' ) {
			continue;
		}
		if ( $label === '' ) {
			$label = $url;
		}
		$entries[] = array(
			'label'  => $label,
			'url'    => $url,
			'title'  => $title,
			'target' => $target,
			'body'   => $body,
		);
	}
	return $entries;
}

/**
 * Populate Resources block section select from Site Settings repeater.
 *
 * @param array<string, mixed> $field ACF field array.
 * @return array<string, mixed>
 */
function tectn_load_field_resource_section_selected_key( $field ) {
	if ( ! is_array( $field ) || ( $field['key'] ?? '' ) !== 'field_tectn_resource_section_selected_key' ) {
		return $field;
	}
	$field['choices'] = array(
		'' => __( '— Select a section —', 'tectn_theme' ),
	);
	$rows = tectn_get_embedded_resource_sections();
	foreach ( $rows as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = isset( $row['resource_section_key'] ) ? trim( (string) $row['resource_section_key'] ) : '';
		if ( $key === '' ) {
			continue;
		}
		$label = isset( $row['resource_section_admin_label'] ) ? trim( (string) $row['resource_section_admin_label'] ) : '';
		if ( $label === '' ) {
			/* translators: %d: 1-based section row number in Site Settings. */
			$label = sprintf( __( 'Resource section %d', 'tectn_theme' ), $i + 1 );
		}
		$field['choices'][ $key ] = $label;
	}
	if ( count( $field['choices'] ) === 1 ) {
		$field['instructions'] = __( 'Add one or more sections under Site Settings → Resources, save that page to generate IDs, then refresh this screen.', 'tectn_theme' );
	}
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_resource_section_selected_key', 'tectn_load_field_resource_section_selected_key' );
