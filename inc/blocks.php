<?php
/**
 * Theme includes.
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register ACF blocks (single source of truth).
 */
function tectn_register_acf_blocks() {
  $block_dirs = glob( get_template_directory() . '/blocks/*', GLOB_ONLYDIR );
  if ( ! $block_dirs ) {
    return;
  }
  foreach ( $block_dirs as $dir ) {
    register_block_type( $dir );
  }
}
add_action( 'init', 'tectn_register_acf_blocks' );
