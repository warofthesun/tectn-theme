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
$bg_type   = get_field('bg_type') ?: 'image'; // image | color

// Image background fields
$bg_image  = get_field('bg_image');
$bg_height = (int) (get_field('bg_height') ?: 800);

// Solid color background fields
$bg_color  = get_field('bg_color'); // hex from ACF color picker

$classes = ['c-band', "c-band--py-{$py}"];
if ( $bg_enable && $bg_type !== 'color' ) {
  $classes[] = 'c-band--grad-strong';
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
?>

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