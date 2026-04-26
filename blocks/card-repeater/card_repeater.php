<?php
/**
 * Card Repeater (Dashicons)
 *
 * Repeater: content_cards
 * Sub fields:
 * - Title (text)      : title (optional; if empty the title bar is not output)
 * - Icon (dashicons)  : icon   (returns a string)
 * - Body (WYSIWYG)    : body   (text-only)
 */

// Optional block wrapper alignment support (if used as an ACF Block template)
$align = !empty($block['align']) ? 'align' . $block['align'] : '';

// Get total rows so we can set grid behavior (1–4 = one row; 5+ = 3 per row)
$rows  = get_field('content_cards');
$count = is_array($rows) ? count($rows) : 0;

$count_class = '';
if ($count > 0 && $count <= 4) {
  $count_class = "c-cardrepeater--count-{$count}";
} elseif ($count >= 5) {
  $count_class = "c-cardrepeater--count-many";
}

$classes = array_filter([
  'c-cardrepeater',
  $align,
  $count_class,
]);

// Block rendering in the editor often happens via REST, where is_admin() can be false.
$is_editor_context =
  is_admin() ||
  (function_exists('wp_doing_ajax') && wp_doing_ajax()) ||
  (defined('REST_REQUEST') && REST_REQUEST);

$block_data = (!empty($block) && is_array($block) && !empty($block['data']) && is_array($block['data'])) ? $block['data'] : [];

// Helper: normalize dashicon string into a class
// Accepts: "admin-site" OR "dashicons-admin-site"
$dashicon_class = function ($icon_string) {
  $icon_string = trim((string) $icon_string);
  if ($icon_string === '') return '';

  // If they stored the full class, keep it
  if (str_starts_with($icon_string, 'dashicons-')) {
    return 'dashicons ' . $icon_string;
  }

  // Otherwise assume it's the slug
  return 'dashicons dashicons-' . $icon_string;
};

// Inserter-only preview image
// Show the preview image ONLY when the inserter sets `inserter_preview` via block.json -> example.
$is_inserter_preview =
  !empty($block['mode']) &&
  $block['mode'] === 'preview' &&
  !empty($block_data['inserter_preview']);

if ($is_inserter_preview) {
  // If you later re-enable variations, you can pass preview_variant to switch files.
  $variant = !empty($block_data['preview_variant'])
    ? sanitize_key($block_data['preview_variant'])
    : 'default';

  $map = [
    'default' => 'preview.png',
    //'three'   => 'preview--three.png',
    //'four'    => 'preview--four.png',
    //'many'    => 'preview--many.png',
  ];

  $file = $map[$variant] ?? $map['default'];
  $src  = get_template_directory_uri() . '/blocks/card-repeater/' . $file;

  echo '<img src="' . esc_url($src) . '" style="width:100%;height:auto;display:block;" alt="">';
  return;
}

// Editor empty state (friendly message when inserted but no rows yet)
// IMPORTANT: do NOT gate this by $block['mode'] — the block is inserted in preview mode.
if ($is_editor_context && empty($rows) && empty($block_data['inserter_preview'])) {
  echo '<div class="' . esc_attr(implode(' ', $classes)) . ' row">';
  echo '  <div class="c-cardrepeater__placeholder">';
  echo '    <strong>' . esc_html__('Card Repeater', 'tectn') . '</strong><br>';
  echo '    ' . esc_html__('Add one or more cards in the block settings or click the pencil icon ^ to edit in place.', 'tectn');
  echo '  </div>';
  echo '</div>';
  return;
}

?>

<?php if (have_rows('content_cards')) : ?>
  <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
    <ul class="c-cardrepeater__grid" role="list">
      <?php while (have_rows('content_cards')) : the_row(); ?>
        <?php
          $title = get_sub_field('title');
          $icon  = get_sub_field('icon'); // dashicons string
          $body  = get_sub_field('body');
          $icon_class = $dashicon_class($icon);
          $title_text = is_string($title) ? trim($title) : '';
          $show_title_section = ( $title_text !== '' );
        ?>

        <li class="c-stepcard">
          <?php if ( $show_title_section ) : ?>
            <div class="c-stepcard__head">
              <span class="c-stepcard__kicker"><?php echo esc_html( $title_text ); ?></span>

              <?php if (!empty($icon_class)) : ?>
                <span class="c-stepcard__icon" aria-hidden="true">
                  <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
                </span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($body)) : ?>
            <div class="c-stepcard__body">
              <?php echo wp_kses_post($body); ?>
            </div>
          <?php endif; ?>
        </li>

      <?php endwhile; ?>
    </ul>
  </div>
<?php endif; ?>