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
 * Enforce ACF color picker palettes and sanitize values before iris init (editor + classic admin).
 *
 * Invalid colors (e.g. legacy named Text+Image values like "sage") or field.val()→iris during
 * mount can throw and trip Gutenberg's block error boundary ("cannot be previewed").
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
	  var LEGACY = {
	    sage: DEFAULT,
	    cream: '#F0F4EC',
	    charcoal: '#5C6B80',
	    white: '#FFFFFF'
	  };

	  acf.add_filter('color_picker_args', function(args, $field){
	    args.palettes = PALETTES;
	    return args;
	  });

	  function isEmptyColor(val) {
	    if (val === null || val === undefined) return true;
	    val = String(val).trim().toLowerCase();
	    if (!val || val === 'transparent' || val === 'false') return true;
	    var rgba = val.match(/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(?:,\s*([\d.]+)\s*)?\)$/);
	    if (rgba && rgba[1] !== undefined && parseFloat(rgba[1]) === 0) return true;
	    var hsla = val.match(/^hsla?\([^)]+,\s*([\d.]+)\s*\)$/);
	    if (hsla && parseFloat(hsla[1]) === 0) return true;
	    return false;
	  }

	  function normalizeColor(val) {
	    if (isEmptyColor(val)) return DEFAULT;
	    var key = String(val).trim().toLowerCase();
	    if (LEGACY[key]) return LEGACY[key];
	    return String(val).trim();
	  }

	  function writeSilent(field, color) {
	    if (!field || typeof field.$input !== 'function') return;
	    var $input = field.$input();
	    var $text = typeof field.$inputText === 'function' ? field.$inputText() : null;
	    if (!$input || !$input.length) return;
	    if (typeof acf.val === 'function') {
	      acf.val($input, color, true);
	    } else {
	      $input.val(color);
	    }
	    if ($text && $text.length) {
	      $text.val(color);
	    }
	  }

	  // Before wpColorPicker/iris init — fix legacy/empty values in the DOM.
	  function sanitizeBeforeInit(field) {
	    try {
	      if (!field || typeof field.$input !== 'function') return;
	      var $input = field.$input();
	      var $text = typeof field.$inputText === 'function' ? field.$inputText() : null;
	      if (!$input || !$input.length) return;
	      var current = $input.val();
	      var next = normalizeColor(current);
	      if (next === current) return;
	      writeSilent(field, next);
	      if ($text && $text.length) $text.val(next);
	    } catch (e) {}
	  }

	  function ensureDefault(field) {
	    try {
	      if (!field || typeof field.$input !== 'function') return;
	      var $input = field.$input();
	      if (!$input || !$input.length) return;
	      var current = $input.val();
	      var next = normalizeColor(current);
	      if (next === String(current || '').trim()) return;
	      writeSilent(field, next);
	    } catch (e) {}
	  }

	  acf.addAction('new_field/type=color_picker', sanitizeBeforeInit);
	  acf.addAction('ready_field/type=color_picker', ensureDefault);
	  acf.addAction('append_field/type=color_picker', ensureDefault);
	  acf.addAction('show_field/type=color_picker', ensureDefault);
	})();
	</script>
	<?php
}
add_action( 'acf/input/admin_footer', 'tectn_acf_input_admin_footer_color_picker_palettes', 20 );

/**
 * Ensure ACF image Edit/Remove controls work after upload (side meta boxes / conditional fields).
 */
function tectn_acf_input_admin_footer_image_actions_fix() {
	?>
	<script>
	(function($){
	  if (!window.acf || !acf.addAction) return;

	  function bindImageActions(field) {
	    if (!field || !field.$el || !field.$el.length) return;
	    if (typeof field.removeAttachment !== 'function' || typeof field.editAttachment !== 'function') return;

	    var $actions = field.$el.find('.acf-actions');
	    if (!$actions.length) return;

	    $actions.find('a[data-name="remove"]').off('click.tectnImage').on('click.tectnImage', function(e){
	      e.preventDefault();
	      e.stopPropagation();
	      field.removeAttachment();
	    });

	    $actions.find('a[data-name="edit"]').off('click.tectnImage').on('click.tectnImage', function(e){
	      e.preventDefault();
	      e.stopPropagation();
	      field.editAttachment('edit-button');
	    });
	  }

	  acf.addAction('ready_field/type=image', bindImageActions);
	  acf.addAction('append_field/type=image', bindImageActions);
	  acf.addAction('show_field/type=image', bindImageActions);
	})(jQuery);
	</script>
	<?php
}
add_action( 'acf/input/admin_footer', 'tectn_acf_input_admin_footer_image_actions_fix', 25 );

/**
 * Map legacy named background colors (pre color-picker) to hex.
 *
 * @param mixed $value Color value.
 * @return mixed
 */
function tectn_normalize_legacy_color_value( $value ) {
	if ( ! is_string( $value ) ) {
		return $value;
	}
	$legacy = array(
		'sage'     => TECTN_COLOR_PICKER_DEFAULT,
		'cream'    => '#F0F4EC',
		'charcoal' => '#5C6B80',
		'white'    => '#FFFFFF',
	);
	$key = strtolower( trim( $value ) );
	return isset( $legacy[ $key ] ) ? $legacy[ $key ] : $value;
}

/**
 * Map legacy Text + Image named background colors to hex for the color picker.
 *
 * @param mixed $value   Field value.
 * @param mixed $post_id Post ID.
 * @param array $field   Field array.
 * @return mixed
 */
function tectn_load_text_image_background_color( $value, $post_id, $field ) {
	return tectn_normalize_legacy_color_value( $value );
}
add_filter( 'acf/load_value/key=field_6991eeaf3dec2', 'tectn_load_text_image_background_color', 10, 3 );
add_filter( 'acf/format_value/key=field_6991eeaf3dec2', 'tectn_load_text_image_background_color', 10, 3 );

/**
 * Sanitize any color_picker value that is a legacy named color (editor iris safety).
 *
 * @param mixed $value   Field value.
 * @param mixed $post_id Post ID.
 * @param array $field   Field array.
 * @return mixed
 */
function tectn_load_color_picker_legacy_names( $value, $post_id, $field ) {
	return tectn_normalize_legacy_color_value( $value );
}
add_filter( 'acf/load_value/type=color_picker', 'tectn_load_color_picker_legacy_names', 10, 3 );
