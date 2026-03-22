<?php
/**
 * Load bundled ACF when ACF Pro is not active.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $path ACF filesystem path.
 * @return string
 */
function tectn_acf_settings_path( $path ) {
	return get_stylesheet_directory() . '/inc/acf/';
}

/**
 * @param string $dir ACF URL directory.
 * @return string
 */
function tectn_acf_settings_dir( $dir ) {
	return get_stylesheet_directory_uri() . '/inc/acf/';
}

/**
 * Registers path/dir filters and loads theme-bundled ACF. Skips when ACF Pro plugin or another bundle is active.
 */
function tectn_load_bundled_acf_if_needed() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
		return;
	}
	if ( defined( 'MY_ACF_PATH' ) ) {
		return;
	}
	add_filter( 'acf/settings/path', 'tectn_acf_settings_path' );
	add_filter( 'acf/settings/dir', 'tectn_acf_settings_dir' );
	include_once get_stylesheet_directory() . '/inc/acf/acf.php';
}
