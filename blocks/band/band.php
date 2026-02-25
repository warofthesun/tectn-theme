<?php
/**
 * Band wrapper block
 *
 * @param array $block The block settings and attributes.
 */

$is_preview = !empty($block['data']['is_preview']);

if ($is_preview) {
  $preview = get_template_directory_uri() . '/blocks/band/preview.png';
  echo '<img src="' . esc_url($preview) . '" style="width:100%;height:auto;display:block;" alt="">';
  return;
}

// Fields (add these in ACF)
$py = get_field('padding_y') ?: 'xl';

$bg_enable = (bool) get_field('bg_enable');
$bg_image  = get_field('bg_image');
$bg_height = (int) (get_field('bg_height') ?: 800);

// Optional overlay fields if you add them
$bg_opacity = get_field('bg_opacity'); // 0-100
$bg_overlay = get_field('bg_overlay'); // rgba(...) or leave empty

$classes = ['c-band', "c-band--py-{$py}"];

$align = !empty($block['align']) ? 'align' . $block['align'] : '';
if ($align) $classes[] = $align;

$styles = [];
if ($bg_enable && !empty($bg_image['url'])) {
  $styles[] = "--band-bg-image: url('{$bg_image['url']}')";
  $styles[] = "--band-bg-height: {$bg_height}px";

  if ($bg_opacity !== null && $bg_opacity !== '') {
    $styles[] = "--band-bg-opacity: " . ((float)$bg_opacity / 100);
  }
  if (!empty($bg_overlay)) {
    $styles[] = "--band-bg-overlay: {$bg_overlay}";
  }

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
}
?>

<section class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?> alignfull"
         <?php if (!empty($styles)) echo 'style="' . esc_attr(implode('; ', $styles)) . '"'; ?>>

  <?php if ($bg_enable && !empty($bg_image['url'])) : ?>
    <div class="c-band__bg" aria-hidden="true">
      <div class="c-band__bg-media"></div>
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