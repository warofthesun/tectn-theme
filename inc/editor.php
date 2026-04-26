<?php
/**
 * Theme includes.
 * @package tectn_theme
 * Block editor and ACF admin UI tweaks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin & block editor: theme overrides (link modal, etc.).
 */
function tectn_enqueue_admin_styles() {
	if ( ! function_exists( 'get_template_directory_uri' ) || ! function_exists( 'tectn_asset_version' ) ) {
		return;
	}
	wp_enqueue_style(
		'tectn-admin',
		get_template_directory_uri() . '/library/css/admin.css',
		array(),
		tectn_asset_version( 'library/css/admin.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'tectn_enqueue_admin_styles', 20 );

/**
 * Custom block category for TecTN ACF blocks.
 *
 * @param array[]             $categories       Categories.
 * @param WP_Block_Editor_Context $editor_context Editor context.
 * @return array[]
 */
function tectn_block_categories_all( $categories, $editor_context ) {
	$tectn_category = array(
		'slug'  => 'tectn-blocks',
		'title' => __( 'TecTN Blocks', 'tectn' ),
		'icon'  => null,
	);
	array_unshift( $categories, $tectn_category );
	return $categories;
}
add_filter( 'block_categories_all', 'tectn_block_categories_all', 10, 2 );

/**
 * Enforce ACF color picker palettes (editor + classic admin).
 */
function tectn_acf_input_admin_footer_color_picker_palettes() {
	?>
	<script>
	(function(){
	  if (!window.acf || !acf.add_filter) return;
	  acf.add_filter('color_picker_args', function(args, $field){
	    args.palettes = [
	      '#EFF5D1', // sage
	      '#F0F4EC', // cream
	      '#5C6B80', // charcoal
	      '#FFFFFF', // white
	      '#698F3D'  // green
	    ];
	    return args;
	  });
	})();
	</script>
	<?php
}
add_action( 'acf/input/admin_footer', 'tectn_acf_input_admin_footer_color_picker_palettes', 20 );
