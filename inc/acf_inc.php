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
 * Populate ACF post_type field choices from public post types.
 *
 * @param array<string, mixed> $field Field array.
 * @return array<string, mixed>
 */
function tectn_acf_load_field_post_type_choices( $field ) {
	$field['choices'] = array();

	$pts = get_post_types( array( 'public' => true ), 'objects' );

	foreach ( $pts as $pt ) {
		if ( in_array( $pt->name, array( 'attachment' ), true ) ) {
			continue;
		}
		$field['choices'][ $pt->name ] = $pt->labels->singular_name;
	}

	return $field;
}
add_filter( 'acf/load_field/name=post_type', 'tectn_acf_load_field_post_type_choices' );

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
