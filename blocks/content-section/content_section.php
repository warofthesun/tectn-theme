<?php
/**
 * Content Section block
 * No top/bottom curves. Background: image (with 3 overlay choices) or solid color.
 * User can add any content in the middle and set a minimum height.
 *
 * @param array $block The block settings and attributes.
 */

$is_preview = !empty($block['data']['is_preview']);

if ($is_preview) {
  // Working preview: same structure and classes as the block for consistent styling
  $preview_min_h = 280;
  $preview_bg = 'linear-gradient(135deg, #d4a574 0%, #8b7355 50%, #6b5344 100%)';
  ?>
  <section class="c-content-section c-content-section--py-xl c-content-section--overlay-warm c-content-section--content-middle alignfull" style="--content-section-min-height:<?php echo (int) $preview_min_h; ?>px; min-height:<?php echo (int) $preview_min_h; ?>px; position:relative; overflow:hidden;">
    <div class="c-content-section__bg" aria-hidden="true" style="position:absolute;inset:0;pointer-events:none;z-index:0;">
      <div class="c-content-section__bg-media c-content-section__bg-media--image" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;height:100%;background:<?php echo esc_attr($preview_bg); ?>;background-size:cover;background-position:center;"></div>
      <div class="c-content-section__overlay" aria-hidden="true" style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(236,186,39,0.45) 0%, rgba(105,143,61,0.6) 68%);pointer-events:none;z-index:1;"></div>
    </div>
    <div class="c-content-section__container wrap" style="position:relative;z-index:2;">
      <div class="c-content-section__inner" style="display:flex;align-items:center;justify-content:center;min-height:<?php echo (int) ($preview_min_h - 80); ?>px;padding:2em;">
        <p style="margin:0;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,0.3);font-size:1rem;">Content Section — Add blocks here</p>
      </div>
    </div>
  </section>
  <?php
  return;
}

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
      <InnerBlocks />
    </div>
  </div>

</section>
<?php endif; ?>
