<?php
/**
 * Tracking codes (Site Settings → Sitewide → Tracking Codes).
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow script tags in tracking code textareas when saving Site Settings → Tracking Codes.
 *
 * @param bool $allow Default from current_user_can( 'unfiltered_html' ).
 * @return bool
 */
function tectn_tracking_codes_allow_unfiltered_html_on_options_save( $allow ) {
	if ( $allow ) {
		return true;
	}
	if ( ! is_admin() || ! function_exists( 'acf_get_form_data' ) ) {
		return false;
	}
	if ( (string) acf_get_form_data( 'post_id' ) !== 'tectn-tracking-codes' ) {
		return false;
	}
	return current_user_can( 'edit_posts' );
}
add_filter( 'acf/allow_unfiltered_html', 'tectn_tracking_codes_allow_unfiltered_html_on_options_save', 5 );

/**
 * Tracking code rows from Site Settings → Tracking Codes.
 *
 * @return list<array<string, mixed>>
 */
function tectn_get_tracking_codes() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$rows = get_field( 'tracking_codes', 'tectn-tracking-codes' );
	return is_array( $rows ) ? $rows : array();
}

/**
 * Echo tracking snippets for head or footer.
 *
 * @param string $placement head|footer
 */
function tectn_output_tracking_codes( $placement ) {
	if ( is_admin() ) {
		return;
	}
	$placement = (string) $placement;
	if ( ! in_array( $placement, array( 'head', 'footer' ), true ) ) {
		return;
	}

	foreach ( tectn_get_tracking_codes() as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$row_placement = isset( $row['tracking_code_placement'] ) ? (string) $row['tracking_code_placement'] : 'head';
		if ( $row_placement === '' ) {
			$row_placement = 'head';
		}
		if ( $row_placement !== $placement ) {
			continue;
		}
		$code = isset( $row['tracking_code_markup'] ) ? trim( (string) $row['tracking_code_markup'] ) : '';
		if ( $code === '' ) {
			continue;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional third-party tracking snippets from trusted admins.
		echo $code . "\n";
	}
}

/**
 * @return void
 */
function tectn_output_tracking_codes_in_head() {
	tectn_output_tracking_codes( 'head' );
}
add_action( 'wp_head', 'tectn_output_tracking_codes_in_head', 1 );

/**
 * @return void
 */
function tectn_output_tracking_codes_in_footer() {
	tectn_output_tracking_codes( 'footer' );
}
add_action( 'wp_footer', 'tectn_output_tracking_codes_in_footer', 1 );
