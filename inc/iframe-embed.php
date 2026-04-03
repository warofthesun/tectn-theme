<?php
/**
 * Iframe Embed block helpers.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed HTML for pasted iframe embeds (maps, video players, etc.).
 *
 * @return array<string, array<string, bool>>
 */
function tectn_iframe_embed_allowed_iframe_tags() {
	return array(
		'iframe' => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'style'           => true,
			'class'           => true,
			'id'              => true,
			'title'           => true,
			'name'            => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'referrerpolicy'  => true,
			'loading'         => true,
			'sandbox'         => true,
			'scrolling'       => true,
			'marginwidth'     => true,
			'marginheight'    => true,
			'align'           => true,
		),
	);
}
