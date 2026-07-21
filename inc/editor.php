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
 * First swatch in the shared ACF color picker palette (sage).
 */
define( 'TECTN_COLOR_PICKER_DEFAULT', '#EFF5D1' );

/**
 * Whether a color picker value is empty or fully transparent.
 *
 * @param mixed $value Color value from ACF.
 * @return bool
 */
function tectn_is_empty_color( $value ) {
	if ( $value === null || $value === false || $value === '' ) {
		return true;
	}
	if ( ! is_string( $value ) ) {
		return false;
	}
	$val = strtolower( trim( $value ) );
	if ( $val === '' || $val === 'transparent' || $val === 'false' ) {
		return true;
	}
	if ( preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*,\s*([\d.]+)\s*\)$/', $val, $m ) ) {
		return (float) $m[1] === 0.0;
	}
	if ( preg_match( '/^hsla?\([^,]+,[^,]+,[^,]+,\s*([\d.]+)\s*\)$/', $val, $m ) ) {
		return (float) $m[1] === 0.0;
	}
	return false;
}

/**
 * Resolve a color picker value, falling back to the first palette swatch when empty.
 *
 * @param mixed $value Color value from ACF.
 * @return string
 */
function tectn_color_or_default( $value ) {
	return tectn_is_empty_color( $value ) ? TECTN_COLOR_PICKER_DEFAULT : (string) $value;
}

/**
 * Enforce ACF color picker palettes and empty → first-swatch default (editor + classic admin).
 */
function tectn_acf_input_admin_footer_color_picker_palettes() {
	$default = TECTN_COLOR_PICKER_DEFAULT;
	?>
	<script>
	(function(){
	  if (!window.acf || !acf.add_filter) return;
	  var DEFAULT = <?php echo wp_json_encode( $default ); ?>;
	  var PALETTES = [
	    '#EFF5D1',
	    '#F4F5ED',
	    '#A1B152',
	    '#505829',
	    '#ECBA27'
	  ];

	  acf.add_filter('color_picker_args', function(args, $field){
	    args.palettes = PALETTES;
	    return args;
	  });

	  function isEmptyColor(val) {
	    if (val === null || val === undefined) return true;
	    val = String(val).trim().toLowerCase();
	    if (!val || val === 'transparent' || val === 'false') return true;
	    // Fully transparent rgba / hsla (opacity-enabled pickers).
	    var rgba = val.match(/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(?:,\s*([\d.]+)\s*)?\)$/);
	    if (rgba && rgba[1] !== undefined && parseFloat(rgba[1]) === 0) return true;
	    var hsla = val.match(/^hsla?\([^)]+,\s*([\d.]+)\s*\)$/);
	    if (hsla && parseFloat(hsla[1]) === 0) return true;
	    return false;
	  }

	  function ensureDefault(field) {
	    if (!field || typeof field.val !== 'function') return;
	    if (!isEmptyColor(field.val())) return;
	    field.val(DEFAULT);
	  }

	  // Apply when a picker loads, is appended (repeater/clone), or is revealed by conditional logic.
	  acf.addAction('ready_field/type=color_picker', ensureDefault);
	  acf.addAction('append_field/type=color_picker', ensureDefault);
	  acf.addAction('show_field/type=color_picker', ensureDefault);
	})();
	</script>
	<?php
}
add_action( 'acf/input/admin_footer', 'tectn_acf_input_admin_footer_color_picker_palettes', 20 );

/**
 * Map legacy Text + Image named background colors to hex for the color picker.
 *
 * @param mixed $value   Field value.
 * @param mixed $post_id Post ID.
 * @param array $field   Field array.
 * @return mixed
 */
function tectn_load_text_image_background_color( $value, $post_id, $field ) {
	$legacy = array(
		'sage'     => TECTN_COLOR_PICKER_DEFAULT,
		'cream'    => '#F0F4EC',
		'charcoal' => '#5C6B80',
		'white'    => '#FFFFFF',
	);
	if ( is_string( $value ) && isset( $legacy[ $value ] ) ) {
		return $legacy[ $value ];
	}
	return $value;
}
add_filter( 'acf/load_value/key=field_6991eeaf3dec2', 'tectn_load_text_image_background_color', 10, 3 );
