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

/**
 * Term choices for Resources Site pages section filters.
 *
 * @param string $kind 'category' or 'tag'.
 * @return array<string, string> term_id => name
 */
function tectn_resources_page_term_choices( $kind = 'category' ) {
	$taxonomy = ( $kind === 'tag' ) ? 'page_tag' : 'page_category';
	$choices  = array();
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return $choices;
	}
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $choices;
	}
	foreach ( $terms as $term ) {
		$choices[ (string) $term->term_id ] = $term->name;
	}
	return $choices;
}

/**
 * Populate Page categories multi-select for Resources Site pages section.
 *
 * @param array<string, mixed> $field ACF field.
 * @return array<string, mixed>
 */
function tectn_load_field_resource_pages_filter_categories( $field ) {
	if ( ! is_array( $field ) ) {
		return $field;
	}
	$field['choices'] = tectn_resources_page_term_choices( 'category' );
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_resource_pages_filter_categories', 'tectn_load_field_resource_pages_filter_categories' );

/**
 * Populate Page tags multi-select for Resources Site pages section.
 *
 * @param array<string, mixed> $field ACF field.
 * @return array<string, mixed>
 */
function tectn_load_field_resource_pages_filter_tags( $field ) {
	if ( ! is_array( $field ) ) {
		return $field;
	}
	$field['choices'] = tectn_resources_page_term_choices( 'tag' );
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_resource_pages_filter_tags', 'tectn_load_field_resource_pages_filter_tags' );

/**
 * Normalize a list of page IDs or WP_Post objects into Resources link entries.
 *
 * @param list<int|WP_Post> $pages Pages or IDs.
 * @return list<array{label: string, url: string, title: string, target: string, body: string}>
 */
function tectn_resources_normalize_page_entries( $pages ) {
	$entries = array();
	if ( ! is_array( $pages ) ) {
		return $entries;
	}
	foreach ( $pages as $page ) {
		$post = null;
		if ( $page instanceof WP_Post ) {
			$post = $page;
		} elseif ( is_numeric( $page ) ) {
			$post = get_post( (int) $page );
		}
		if ( ! $post instanceof WP_Post || $post->post_type !== 'page' || $post->post_status !== 'publish' ) {
			continue;
		}
		$title = get_the_title( $post );
		$url   = get_permalink( $post );
		if ( ! is_string( $url ) || $url === '' ) {
			continue;
		}
		$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
		$excerpt = is_string( $excerpt ) ? trim( wp_strip_all_tags( $excerpt ) ) : '';
		$entries[] = array(
			'label'  => $title !== '' ? $title : $url,
			'url'    => $url,
			'title'  => $title,
			'target' => '_self',
			'body'   => $excerpt,
		);
	}
	return $entries;
}

/**
 * Resolve pages for a Resources Site pages section row.
 *
 * @param string               $pick_mode   'manual' or 'taxonomy'.
 * @param list<int>|mixed      $selected    Relationship IDs when manual.
 * @param string               $filter_by   'category' or 'tag'.
 * @param list<string|int>|mixed $term_ids  Selected term IDs.
 * @return list<array{label: string, url: string, title: string, target: string, body: string}>
 */
function tectn_resources_resolve_site_page_entries( $pick_mode, $selected, $filter_by, $term_ids ) {
	$pick_mode = ( $pick_mode === 'taxonomy' ) ? 'taxonomy' : 'manual';

	if ( $pick_mode === 'manual' ) {
		$ids = array();
		if ( is_array( $selected ) ) {
			foreach ( $selected as $item ) {
				if ( $item instanceof WP_Post ) {
					$ids[] = (int) $item->ID;
				} elseif ( is_numeric( $item ) ) {
					$ids[] = (int) $item;
				}
			}
		}
		$ids = array_values( array_unique( array_filter( $ids ) ) );
		if ( empty( $ids ) ) {
			return array();
		}
		$posts = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'post__in'               => $ids,
				'orderby'                => 'post__in',
				'posts_per_page'         => count( $ids ),
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		return tectn_resources_normalize_page_entries( is_array( $posts ) ? $posts : array() );
	}

	$filter_by = ( $filter_by === 'tag' ) ? 'tag' : 'category';
	$taxonomy  = ( $filter_by === 'tag' ) ? 'page_tag' : 'page_category';
	$ids       = array();
	if ( is_array( $term_ids ) ) {
		foreach ( $term_ids as $tid ) {
			if ( is_numeric( $tid ) ) {
				$ids[] = (int) $tid;
			}
		}
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );
	if ( empty( $ids ) || ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 100,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $ids,
					'operator' => 'IN',
				),
			),
		)
	);
	return tectn_resources_normalize_page_entries( is_array( $posts ) ? $posts : array() );
}
