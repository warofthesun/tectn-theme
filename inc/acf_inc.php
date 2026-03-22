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
