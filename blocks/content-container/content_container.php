<?php
/**
 * Content Container block (formerly Band)
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
	$src  = get_template_directory_uri() . '/blocks/content-container/' . $file;

	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

// Fields (add these in ACF)
$py = get_field('padding_y') ?: 'xl';

$bg_enable = (bool) get_field('bg_enable');
$bg_type   = get_field('bg_type') ?: 'image'; // image | color

// Image background fields
$bg_image  = get_field('bg_image');
$bg_height = (int) (get_field('bg_height') ?: 800);

// Solid color background fields
$bg_color  = get_field('bg_color'); // hex from ACF color picker

$remove_bottom_margin = (bool) get_field('remove_bottom_margin');
$classes = ['c-band', "c-band--py-{$py}"];
if ($remove_bottom_margin) $classes[] = 'c-band--no-mb';
if ( $bg_enable && $bg_type !== 'color' ) {
  $classes[] = 'c-band--grad-strong';
}
if ( $bg_type === 'color' && (bool) get_field( 'bg_color_contains' ) ) {
  $classes[] = 'c-band--bg-contains';
}

$align = !empty($block['align']) ? 'align' . $block['align'] : '';
if ($align) $classes[] = $align;

$styles = [];
$bg_media_class = '';

if ( $bg_enable ) {
  // Shared positioning for the bg slab
  $bg_align_y = get_field('bg_align_y') ?: 'center';

  $anchor_map = [
    'top'    => '0%',
    'center' => '50%',
    'bottom' => '100%',
  ];

  $translate_map = [
    'top'    => '0%',
    'center' => '-50%',
    'bottom' => '-100%',
  ];

  $styles[] = '--band-bg-anchor-y: ' . ($anchor_map[$bg_align_y] ?? '50%');
  $styles[] = '--band-bg-translate-y: ' . ($translate_map[$bg_align_y] ?? '-50%');
  $styles[] = "--band-bg-height: {$bg_height}px";

  // Type-specific
  if ( $bg_type === 'color' ) {
    $bg_media_class = 'c-band__bg-media--color';
    if ( !empty($bg_color) ) {
      $styles[] = "--band-bg-color: {$bg_color}";
    }
  } else {
    // default: image
    $bg_media_class = 'c-band__bg-media--image';
    if ( !empty($bg_image['url']) ) {
      $styles[] = "--band-bg-image: url('{$bg_image['url']}')";
    }
  }
}

$should_render_bg = false;
if ( $bg_enable ) {
  if ( $bg_type === 'color' ) {
    $should_render_bg = !empty($bg_color);
  } else {
    $should_render_bg = !empty($bg_image['url']);
  }
}

// Add curve to background: checkbox "Top" / "Bottom" (default both when not set)
$wave_curves = get_field( 'wave_curves' );
if ( $wave_curves === null || $wave_curves === false ) {
  $wave_top    = get_field( 'wave_top' );
  $wave_bottom = get_field( 'wave_bottom' );
  if ( $wave_top === null && $wave_bottom === null ) {
    $wave_top = true;
    $wave_bottom = true;
  } else {
    $wave_top    = (bool) $wave_top;
    $wave_bottom = (bool) $wave_bottom;
  }
} else {
  $wave_curves = (array) $wave_curves;
  $wave_top    = in_array( 'top', $wave_curves, true );
  $wave_bottom = in_array( 'bottom', $wave_curves, true );
}
$has_waves = $wave_top || $wave_bottom;
if ( ! $bg_enable || $bg_type === 'image' ) {
  $has_waves = false;
}

$wrap_style = '';
if ( $has_waves && $should_render_bg && $bg_type === 'color' && !empty($bg_color) ) {
  $wrap_style = ' style="--waveband-bg:' . esc_attr( $bg_color ) . ';"';
}
?>
<?php if ( $has_waves ) : ?>
<div class="c-band__wrap"<?php echo $wrap_style; ?>>
  <?php if ( $wave_top ) : ?><span class="c-waveband__wave c-waveband__wave--top" aria-hidden="true"></span><?php endif; ?>
  <section class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?> alignfull"
           <?php if (!empty($styles)) echo 'style="' . esc_attr(implode('; ', $styles)) . '"'; ?>>

    <?php if ( $should_render_bg ) : ?>
      <div class="c-band__bg" aria-hidden="true">
        <div class="c-band__bg-media <?php echo esc_attr($bg_media_class); ?>">
          <?php if ( $bg_type !== 'color' ) : ?>
            <div class="c-band__bg-gradient" aria-hidden="true"></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="c-band__container wrap">
      <div class="c-band__inner">
        <?php
          $band_inner_template = [
            ['tectn/text-image', []],
            ['tectn/text-image', []],
          ];
        ?>
        <InnerBlocks
          template="<?php echo esc_attr( wp_json_encode( $band_inner_template ) ); ?>"
          templateLock="insert"
        />
      </div>
    </div>

  </section>
  <?php if ( $wave_bottom ) : ?><span class="c-waveband__wave c-waveband__wave--bottom" aria-hidden="true"></span><?php endif; ?>
</div>
<?php else : ?>
  <section class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?> alignfull"
           <?php if (!empty($styles)) echo 'style="' . esc_attr(implode('; ', $styles)) . '"'; ?>>

    <?php if ( $should_render_bg ) : ?>
      <div class="c-band__bg" aria-hidden="true">
        <div class="c-band__bg-media <?php echo esc_attr($bg_media_class); ?>">
          <?php if ( $bg_type !== 'color' ) : ?>
            <div class="c-band__bg-gradient" aria-hidden="true"></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="c-band__container wrap">
      <div class="c-band__inner">
        <?php
          $band_inner_template = [
            ['tectn/text-image', []],
            ['tectn/text-image', []],
          ];
        ?>
        <InnerBlocks
          template="<?php echo esc_attr( wp_json_encode( $band_inner_template ) ); ?>"
          templateLock="insert"
        />
      </div>
    </div>

  </section>
<?php endif; ?>
