<?php
/**
 * Content Section block
 * No top/bottom curves. Background: image (with 3 overlay choices) or solid color.
 * User can add any content in the middle and set a minimum height.
 *
 * @param array $block The block settings and attributes.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	$variant = ! empty( $block_data['preview_variant'] )
		? sanitize_key( $block_data['preview_variant'] )
		: 'default';

	$map = array(
		'default' => 'preview.png',
	);

	$file = $map[ $variant ] ?? $map['default'];
	$src  = get_template_directory_uri() . '/blocks/content-section/' . $file;

	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

/**
 * @var WP_Block|null $wp_block
 * @var string         $content Serialized inner blocks HTML from ACF render callback (in scope via include).
 */
$is_editor_context =
	is_admin() ||
	( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST );

$has_inner_blocks = false;

if ( isset( $wp_block ) && $wp_block instanceof WP_Block && is_array( $wp_block->inner_blocks ) && count( $wp_block->inner_blocks ) > 0 ) {
	$has_inner_blocks = true;
}

if ( ! $has_inner_blocks && ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
	$has_inner_blocks = count( $block['innerBlocks'] ) > 0;
}

// Editor often exposes inner blocks on parsed_block before WP_Block::inner_blocks is populated (H4).
if ( ! $has_inner_blocks && isset( $wp_block ) && $wp_block instanceof WP_Block && ! empty( $wp_block->parsed_block['innerBlocks'] ) && is_array( $wp_block->parsed_block['innerBlocks'] ) ) {
	$has_inner_blocks = count( $wp_block->parsed_block['innerBlocks'] ) > 0;
}

if ( ! $has_inner_blocks ) {
	$serialized_inner = isset( $content ) ? trim( (string) $content ) : '';
	if ( $serialized_inner !== '' ) {
		$has_inner_blocks = true;
	}
}

// Must keep <InnerBlocks /> in the output or the editor (+) appender disappears. Show hint above appender when empty.
$show_content_section_placeholder =
	$is_editor_context && ! $has_inner_blocks && empty( $block_data['inserter_preview'] );

$py            = get_field('padding_y') ?: 'xl';
$content_align  = get_field('content_align') ?: 'middle';
$min_height    = (int) (get_field('min_height') ?: 400);
$bg_enable     = (bool) get_field('bg_enable');
$bg_type    = get_field('bg_type') ?: 'image';
$bg_image   = get_field('bg_image');
$bg_overlay = get_field('bg_overlay') ?: 'warm'; // none | warm | medium | dark
$bg_color   = get_field('bg_color');
$bg_height  = (int) (get_field('bg_height') ?: 800);
$bg_align_y = get_field('bg_align_y') ?: 'center';

$remove_bottom_margin = (bool) get_field('remove_bottom_margin');
$classes = ['c-content-section', "c-content-section--py-{$py}", "c-content-section--content-{$content_align}"];
$classes[] = 'c-content-section--overlay-' . $bg_overlay;
if ($remove_bottom_margin) $classes[] = 'c-content-section--no-mb';
if ($bg_type === 'color' && (bool) get_field('bg_color_contains')) {
  $classes[] = 'c-content-section--bg-contains';
}

$align = !empty($block['align']) ? 'align' . $block['align'] : '';
if ($align) $classes[] = $align;

// Waves: only when background on and type is color (same as Content Container)
$wave_curves = get_field('wave_curves');
if ($wave_curves === null || $wave_curves === false) {
  $wave_top = false;
  $wave_bottom = false;
} else {
  $wave_curves = (array) $wave_curves;
  $wave_top    = in_array('top', $wave_curves, true);
  $wave_bottom = in_array('bottom', $wave_curves, true);
}
$has_waves = $wave_top || $wave_bottom;
if (!$bg_enable || $bg_type === 'image') {
  $has_waves = false;
}

$should_render_bg = false;
if ($bg_enable) {
  if ($bg_type === 'color') {
    $should_render_bg = !empty($bg_color);
  } else {
    $should_render_bg = !empty($bg_image['url']);
  }
}

$wrap_style = '';
if ($has_waves && $should_render_bg && $bg_type === 'color' && !empty($bg_color)) {
  $wrap_style = ' style="--waveband-bg:' . esc_attr($bg_color) . ';"';
}

$styles = [];
$styles[] = '--content-section-min-height: ' . $min_height . 'px';

$bg_media_class = '';

if ($bg_enable) {
  $anchor_map    = [ 'top' => '0%', 'center' => '50%', 'bottom' => '100%' ];
  $translate_map = [ 'top' => '0%', 'center' => '-50%', 'bottom' => '-100%' ];
  $styles[] = '--content-section-bg-anchor-y: ' . ($anchor_map[$bg_align_y] ?? '50%');
  $styles[] = '--content-section-bg-translate-y: ' . ($translate_map[$bg_align_y] ?? '-50%');
  $styles[] = '--content-section-bg-height: ' . $bg_height . 'px';

  if ($bg_type === 'color') {
    $bg_media_class = 'c-content-section__bg-media--color';
    if (!empty($bg_color)) {
      $styles[] = '--content-section-bg-color: ' . $bg_color;
    }
  } else {
    $bg_media_class = 'c-content-section__bg-media--image';
    if (!empty($bg_image['url'])) {
      $styles[] = "--content-section-bg-image: url('" . esc_url($bg_image['url']) . "')";
    }
  }
}
?>
<?php if ($has_waves) : ?>
<div class="c-content-section__wrap"<?php echo $wrap_style; ?>>
  <?php if ($wave_top) : ?><span class="c-waveband__wave c-waveband__wave--top" aria-hidden="true"></span><?php endif; ?>
  <section class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?> alignfull"
           <?php if (!empty($styles)) echo 'style="' . esc_attr(implode('; ', $styles)) . '"'; ?>>

    <?php if ($should_render_bg) : ?>
      <div class="c-content-section__bg" aria-hidden="true">
        <div class="c-content-section__bg-media <?php echo esc_attr($bg_media_class); ?>">
          <?php if ($bg_type !== 'color') : ?>
            <div class="c-content-section__overlay" aria-hidden="true"></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="c-content-section__container wrap">
      <div class="c-content-section__inner">
        <?php if ( $show_content_section_placeholder ) : ?>
          <div class="c-content-section__placeholder">
            <strong><?php echo esc_html__( 'Content Section', 'tectn_theme' ); ?></strong><br>
            <?php echo esc_html__( 'Use the block inserter (+) below or List view to add blocks inside this section (for example Text + Image). Adjust background and height in the sidebar.', 'tectn_theme' ); ?>
          </div>
        <?php endif; ?>
        <InnerBlocks />
      </div>
    </div>

  </section>
  <?php if ($wave_bottom) : ?><span class="c-waveband__wave c-waveband__wave--bottom" aria-hidden="true"></span><?php endif; ?>
</div>
<?php else : ?>
  <section class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?> alignfull"
         <?php if (!empty($styles)) echo 'style="' . esc_attr(implode('; ', $styles)) . '"'; ?>>

  <?php if ($should_render_bg) : ?>
    <div class="c-content-section__bg" aria-hidden="true">
      <div class="c-content-section__bg-media <?php echo esc_attr($bg_media_class); ?>">
        <?php if ($bg_type !== 'color') : ?>
          <div class="c-content-section__overlay" aria-hidden="true"></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="c-content-section__container wrap">
    <div class="c-content-section__inner">
      <?php if ( $show_content_section_placeholder ) : ?>
        <div class="c-content-section__placeholder">
          <strong><?php echo esc_html__( 'Content Section', 'tectn_theme' ); ?></strong><br>
          <?php echo esc_html__( 'Use the block inserter (+) below or List view to add blocks inside this section (for example Text + Image). Adjust background and height in the sidebar.', 'tectn_theme' ); ?>
        </div>
      <?php endif; ?>
      <InnerBlocks />
    </div>
  </div>

</section>
<?php endif; ?>
