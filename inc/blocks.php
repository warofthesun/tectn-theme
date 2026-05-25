<?php
/**
 * ACF block registration and shared block behavior.
 *
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

/**
 * Whether the block is a TecTN theme block.
 *
 * @param array $block ACF block array.
 * @return bool
 */
function tectn_block_is_tectn_block( $block ) {
	return ! empty( $block['name'] ) && strpos( (string) $block['name'], 'tectn/' ) === 0;
}

/**
 * Inserter-only preview image (not the live block preview in the canvas).
 *
 * @param array $block ACF block array.
 * @return bool
 */
function tectn_block_is_inserter_preview( $block ) {
	$data = ( ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();
	return ! empty( $block['mode'] )
		&& $block['mode'] === 'preview'
		&& ! empty( $data['inserter_preview'] );
}

/**
 * Whether "Hide on Front End" is enabled for the current block render.
 *
 * @param array|null $block Optional ACF block array (falls back to block data).
 * @return bool
 */
function tectn_block_hide_on_front_end_enabled( $block = null ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( 'hide_on_front_end' );
		if ( null !== $value && '' !== $value ) {
			return (bool) $value;
		}
	}

	if ( $block && ! empty( $block['data']['hide_on_front_end'] ) ) {
		return (bool) $block['data']['hide_on_front_end'];
	}

	return false;
}

/**
 * Before block template: discard output on the front end, or open editor opacity wrapper.
 *
 * @param array    $block      Block settings.
 * @param string   $content    Inner blocks content.
 * @param bool     $is_preview Editor preview render.
 * @param int      $post_id    Post ID.
 * @param WP_Block $wp_block   Block instance.
 * @param array    $context    Block context.
 */
function tectn_block_hide_on_front_pre_render( $block, $content, $is_preview, $post_id, $wp_block, $context ) {
	if ( ! tectn_block_is_tectn_block( $block ) || tectn_block_is_inserter_preview( $block ) ) {
		return;
	}

	if ( ! tectn_block_hide_on_front_end_enabled( $block ) ) {
		return;
	}

	if ( $is_preview ) {
		echo '<div class="tectn-block-hidden-editor">';
		echo '<p class="tectn-block-hidden-editor__notice">';
		echo esc_html__( 'This block is hidden.', 'tectn' );
		echo '</p>';
		echo '<div class="tectn-block-hidden-editor__preview">';
		return;
	}

	ob_start();
	if ( function_exists( 'acf_set_data' ) ) {
		acf_set_data( 'tectn_block_discard_render', true );
	}
}
add_action( 'acf/blocks/pre_block_template_render', 'tectn_block_hide_on_front_pre_render', 10, 6 );

/**
 * After block template: discard buffered output or close editor opacity wrapper.
 *
 * @param array    $block      Block settings.
 * @param string   $content    Inner blocks content.
 * @param bool     $is_preview Editor preview render.
 * @param int      $post_id    Post ID.
 * @param WP_Block $wp_block   Block instance.
 * @param array    $context    Block context.
 */
function tectn_block_hide_on_front_post_render( $block, $content, $is_preview, $post_id, $wp_block, $context ) {
	if ( ! tectn_block_is_tectn_block( $block ) || tectn_block_is_inserter_preview( $block ) ) {
		return;
	}

	if ( function_exists( 'acf_get_data' ) && acf_get_data( 'tectn_block_discard_render' ) ) {
		ob_end_clean();
		acf_set_data( 'tectn_block_discard_render', false );
		return;
	}

	if ( $is_preview && tectn_block_hide_on_front_end_enabled( $block ) ) {
		echo '</div></div>';
	}
}
add_action( 'acf/blocks/post_block_template_render', 'tectn_block_hide_on_front_post_render', 10, 6 );
