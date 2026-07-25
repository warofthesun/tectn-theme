<?php
/**
 * ACF helpers and filters.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Populate ACF post_type field choices from UI-visible post types.
 *
 * @param array<string, mixed> $field Field array.
 * @return array<string, mixed>
 */
function tectn_acf_load_field_post_type_choices( $field ) {
	$field['choices'] = array();

	$exclude = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_global_styles',
		'wp_navigation',
		'wp_font_family',
		'wp_font_face',
		'acf-field',
		'acf-field-group',
		'acf-post-type',
		'acf-taxonomy',
		'acf-ui-options-page',
	);

	$pts = get_post_types( array( 'show_ui' => true ), 'objects' );

	foreach ( $pts as $pt ) {
		if ( in_array( $pt->name, $exclude, true ) ) {
			continue;
		}
		if ( strpos( $pt->name, 'acf-' ) === 0 ) {
			continue;
		}
		$field['choices'][ $pt->name ] = $pt->labels->singular_name;
	}

	return $field;
}
add_filter( 'acf/load_field/name=post_type', 'tectn_acf_load_field_post_type_choices' );

/**
 * Resolve the category or tag taxonomy for a post type.
 *
 * Prefers known maps (post, people, tribe_events), then the first matching
 * hierarchical (category) or non-hierarchical (tag) object taxonomy.
 *
 * @param string $post_type Post type slug.
 * @param string $kind      'category' or 'tag'.
 * @return string Taxonomy name or empty string.
 */
function tectn_posts_grid_resolve_filter_taxonomy( $post_type, $kind = 'category' ) {
	$post_type = is_string( $post_type ) && $post_type !== '' ? $post_type : 'post';
	$kind      = ( $kind === 'tag' ) ? 'tag' : 'category';

	$map = array(
		'post'         => array(
			'category' => 'category',
			'tag'      => 'post_tag',
		),
		'people'       => array(
			'category' => 'person_category',
			'tag'      => 'person_tag',
		),
		'tribe_events' => array(
			'category' => 'tribe_events_cat',
			'tag'      => 'post_tag',
		),
	);

	if ( ! empty( $map[ $post_type ][ $kind ] ) && taxonomy_exists( $map[ $post_type ][ $kind ] ) ) {
		return $map[ $post_type ][ $kind ];
	}

	$taxes = get_object_taxonomies( $post_type, 'objects' );
	if ( empty( $taxes ) || ! is_array( $taxes ) ) {
		return '';
	}

	$skip              = array( 'post_format', 'nav_menu', 'link_category', 'wp_pattern_category' );
	$want_hierarchical = ( $kind === 'category' );

	foreach ( $taxes as $tax ) {
		if ( ! $tax instanceof WP_Taxonomy ) {
			continue;
		}
		if ( in_array( $tax->name, $skip, true ) ) {
			continue;
		}
		if ( (bool) $tax->hierarchical === $want_hierarchical ) {
			return $tax->name;
		}
	}

	return '';
}

/**
 * Read the Posts Grid block's selected post_type from current ACF context.
 *
 * @return string
 */
function tectn_acf_get_posts_grid_selected_post_type() {
	if ( ! empty( $_POST['tectn_post_type'] ) && is_string( $_POST['tectn_post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return sanitize_key( wp_unslash( $_POST['tectn_post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( function_exists( 'get_field' ) ) {
		$v = get_field( 'post_type' );
		if ( is_string( $v ) && $v !== '' ) {
			return sanitize_key( $v );
		}
	}

	if ( ! empty( $_POST['post_type'] ) && is_string( $_POST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return sanitize_key( wp_unslash( $_POST['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( ! empty( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$acf = wp_unslash( $_POST['acf'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! empty( $acf['field_699665aeec2b9'] ) && is_string( $acf['field_699665aeec2b9'] ) ) {
			return sanitize_key( $acf['field_699665aeec2b9'] );
		}
		if ( ! empty( $acf['post_type'] ) && is_string( $acf['post_type'] ) ) {
			return sanitize_key( $acf['post_type'] );
		}
	}

	return 'post';
}

/**
 * Build term choices for a Posts Grid filter field.
 *
 * @param string $post_type Post type slug.
 * @param string $kind      'category' or 'tag'.
 * @return array{taxonomy:string,label:string,choices:array<int|string,string>}
 */
function tectn_posts_grid_filter_term_choices( $post_type, $kind ) {
	$taxonomy = tectn_posts_grid_resolve_filter_taxonomy( $post_type, $kind );
	$label    = ( $kind === 'tag' ) ? __( 'Tags', 'tectn_theme' ) : __( 'Categories', 'tectn_theme' );
	$choices  = array();

	if ( $taxonomy !== '' ) {
		$tax_obj = get_taxonomy( $taxonomy );
		if ( $tax_obj ) {
			$label = $tax_obj->labels->name;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$choices[ (string) $term->term_id ] = $term->name;
			}
		}
	}

	return array(
		'taxonomy' => $taxonomy,
		'label'    => $label,
		'choices'  => $choices,
	);
}

/**
 * Force Posts Grid filter fields to plain multi-selects (not taxonomy AJAX).
 *
 * @param array<string, mixed> $field Field array.
 * @return array<string, mixed>
 */
function tectn_acf_load_posts_grid_filter_as_select( $field ) {
	$field['type']          = 'select';
	$field['ui']            = 1;
	$field['ajax']          = 0;
	$field['multiple']      = 1;
	$field['allow_null']    = 1;
	$field['return_format'] = 'value';
	return $field;
}
add_filter( 'acf/load_field/key=field_tectn_posts_grid_filter_categories', 'tectn_acf_load_posts_grid_filter_as_select' );
add_filter( 'acf/load_field/key=field_tectn_posts_grid_filter_tags', 'tectn_acf_load_posts_grid_filter_as_select' );

/**
 * Convert Posts Grid category/tag filters to selects populated for the selected post type.
 *
 * Taxonomy multi_select AJAX always queries the field definition's taxonomy (usually
 * `category`), so Event Categories never appear. Plain selects avoid that.
 *
 * @param array<string, mixed> $field Field array.
 * @param string               $kind  'category' or 'tag'.
 * @return array<string, mixed>
 */
function tectn_acf_prepare_posts_grid_filter_taxonomy_field( $field, $kind ) {
	$post_type = tectn_acf_get_posts_grid_selected_post_type();
	$data      = tectn_posts_grid_filter_term_choices( $post_type, $kind );

	$field['type']          = 'select';
	$field['ui']            = 1;
	$field['ajax']          = 0;
	$field['multiple']      = 1;
	$field['allow_null']    = 1;
	$field['return_format'] = 'value';
	$field['choices']       = $data['choices'];
	$field['label']         = $data['label'];

	if ( $data['taxonomy'] === '' ) {
		$field['instructions'] = ( $kind === 'tag' )
			? __( 'This post type has no tags taxonomy.', 'tectn_theme' )
			: __( 'This post type has no categories taxonomy.', 'tectn_theme' );
	} else {
		$field['instructions'] = sprintf(
			/* translators: %s: taxonomy name (e.g. Event Categories) */
			__( 'Show only items in one or more of these %s.', 'tectn_theme' ),
			$data['label']
		);
	}

	return $field;
}

/**
 * @param array<string, mixed> $field Field array.
 * @return array<string, mixed>
 */
function tectn_acf_prepare_posts_grid_filter_categories( $field ) {
	return tectn_acf_prepare_posts_grid_filter_taxonomy_field( $field, 'category' );
}
add_filter( 'acf/prepare_field/key=field_tectn_posts_grid_filter_categories', 'tectn_acf_prepare_posts_grid_filter_categories' );

/**
 * @param array<string, mixed> $field Field array.
 * @return array<string, mixed>
 */
function tectn_acf_prepare_posts_grid_filter_tags( $field ) {
	return tectn_acf_prepare_posts_grid_filter_taxonomy_field( $field, 'tag' );
}
add_filter( 'acf/prepare_field/key=field_tectn_posts_grid_filter_tags', 'tectn_acf_prepare_posts_grid_filter_tags' );

/**
 * AJAX: terms for Posts Grid category/tag filters for a given post type.
 */
function tectn_ajax_posts_grid_filter_terms() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	check_ajax_referer( 'tectn_posts_grid_filters', 'nonce' );

	$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : 'post';
	$kind      = isset( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : 'category';
	if ( $kind !== 'tag' ) {
		$kind = 'category';
	}

	$data  = tectn_posts_grid_filter_term_choices( $post_type, $kind );
	$terms = array();
	foreach ( $data['choices'] as $id => $name ) {
		$terms[] = array(
			'id'   => (int) $id,
			'name' => $name,
		);
	}

	wp_send_json_success(
		array(
			'taxonomy' => $data['taxonomy'],
			'label'    => $data['label'],
			'terms'    => $terms,
		)
	);
}
add_action( 'wp_ajax_tectn_posts_grid_filter_terms', 'tectn_ajax_posts_grid_filter_terms' );

/**
 * Register a Bold/Italic-only WYSIWYG toolbar for Hero Headline (and any field that selects it).
 *
 * @param array<string, array<int, array<int, string>>> $toolbars Existing ACF toolbars.
 * @return array<string, array<int, array<int, string>>>
 */
function tectn_acf_wysiwyg_toolbars_bold_italic( $toolbars ) {
	$toolbars['Bold Italic'] = array(
		1 => array( 'bold', 'italic' ),
	);
	return $toolbars;
}
add_filter( 'acf/fields/wysiwyg/toolbars', 'tectn_acf_wysiwyg_toolbars_bold_italic' );

/**
 * Force Hero Headline / Paragraph to the Bold/Italic toolbar (and Visual mode)
 * even if the DB field group still has older settings before ACF JSON sync.
 *
 * @param array<string, mixed> $field Field array.
 * @return array<string, mixed>
 */
function tectn_acf_force_hero_bold_italic_toolbar( $field ) {
	$is_paragraph = ( ! empty( $field['name'] ) && $field['name'] === 'hero_paragraph' )
		|| ( ! empty( $field['key'] ) && $field['key'] === 'field_68225191eee21' );

	// Paragraph was a textarea; promote to wysiwyg before ACF JSON sync.
	if ( $is_paragraph ) {
		$field['type'] = 'wysiwyg';
	}

	if ( empty( $field['type'] ) || $field['type'] !== 'wysiwyg' ) {
		return $field;
	}
	$field['toolbar']      = 'bold_italic';
	$field['tabs']         = 'visual';
	$field['media_upload'] = 0;
	return $field;
}
add_filter( 'acf/load_field/key=field_68225159eee20', 'tectn_acf_force_hero_bold_italic_toolbar' );
add_filter( 'acf/load_field/name=hero_headline', 'tectn_acf_force_hero_bold_italic_toolbar' );
add_filter( 'acf/load_field/key=field_68225191eee21', 'tectn_acf_force_hero_bold_italic_toolbar' );
add_filter( 'acf/load_field/name=hero_paragraph', 'tectn_acf_force_hero_bold_italic_toolbar' );
