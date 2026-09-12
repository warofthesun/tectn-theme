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
 * Sorted by block.json title so the inserter lists them alphabetically
 * (e.g. Text + Image before Text + Image Combo), not by folder name.
 */
function tectn_register_acf_blocks() {
	$block_dirs = glob( get_template_directory() . '/blocks/*', GLOB_ONLYDIR );
	if ( ! $block_dirs ) {
		return;
	}

	$blocks = array();
	foreach ( $block_dirs as $dir ) {
		$json_path = $dir . '/block.json';
		$title     = basename( $dir );
		if ( is_readable( $json_path ) ) {
			$meta = json_decode( (string) file_get_contents( $json_path ), true );
			if ( is_array( $meta ) && ! empty( $meta['title'] ) && is_string( $meta['title'] ) ) {
				$title = $meta['title'];
			}
		}
		$blocks[] = array(
			'dir'   => $dir,
			'title' => $title,
		);
	}

	usort(
		$blocks,
		static function ( $a, $b ) {
			return strcasecmp( $a['title'], $b['title'] );
		}
	);

	foreach ( $blocks as $block ) {
		register_block_type( $block['dir'] );
	}
}
add_action( 'init', 'tectn_register_acf_blocks' );

/**
 * Sync Combo ACF "Sponsor Scroll" toggle onto the block attribute that provides context.
 *
 * @param array $parsed_block Block being rendered.
 * @return array
 */
function tectn_content_container_sync_sponsor_scroll_context( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || $parsed_block['blockName'] !== 'tectn/content-container' ) {
		return $parsed_block;
	}

	$data = array();
	if ( ! empty( $parsed_block['attrs']['data'] ) && is_array( $parsed_block['attrs']['data'] ) ) {
		$data = $parsed_block['attrs']['data'];
	}

	$raw = $data['show_sponsor_scroll'] ?? 0;
	$on  = ! empty( $raw ) && $raw !== '0' && $raw !== 0 && $raw !== false;

	if ( ! isset( $parsed_block['attrs'] ) || ! is_array( $parsed_block['attrs'] ) ) {
		$parsed_block['attrs'] = array();
	}
	$parsed_block['attrs']['showSponsorScroll'] = (bool) $on;

	return $parsed_block;
}
add_filter( 'render_block_data', 'tectn_content_container_sync_sponsor_scroll_context', 10, 1 );

/**
 * Editor: keep Combo inner Sponsor Scroll in sync with the On/Off switch.
 */
function tectn_content_container_editor_assets() {
	$path = get_template_directory() . '/blocks/content-container/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'tectn-content-container-editor',
		get_template_directory_uri() . '/blocks/content-container/editor.js',
		array( 'wp-blocks', 'wp-data', 'wp-dom-ready', 'wp-element' ),
		(string) filemtime( $path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'tectn_content_container_editor_assets' );

/**
 * Default ACF Blocks to v3 for WordPress 7+ iframe editor compatibility.
 * Restores the toolbar pencil (opens Expanded Editor) and enables modern editing.
 * Individual block.json `acf.blockVersion` values still win when set.
 *
 * @param int   $version Default ACF block version.
 * @param array $block   Block settings.
 * @return int
 */
function tectn_acf_default_block_version( $version, $block ) {
	return 3;
}
add_filter( 'acf/blocks/default_block_version', 'tectn_acf_default_block_version', 10, 2 );

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
