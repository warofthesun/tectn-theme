<?php
/**
 * Card Repeater (Dashicons)
 *
 * Repeater: content_cards
 * Sub fields:
 * - Title (text)      : title
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
?>

<?php if (have_rows('content_cards')) : ?>
  <div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <ul class="c-cardrepeater__grid" role="list">
      <?php while (have_rows('content_cards')) : the_row(); ?>
        <?php
          $title = get_sub_field('title');
          $icon  = get_sub_field('icon'); // dashicons string
          $body  = get_sub_field('body');
          $icon_class = $dashicon_class($icon);
        ?>

        <li class="c-stepcard">
          <div class="c-stepcard__head">
            <?php if (!empty($title)) : ?>
              <span class="c-stepcard__kicker"><?php echo esc_html($title); ?></span>
            <?php endif; ?>

            <?php if (!empty($icon_class)) : ?>
              <span class="c-stepcard__icon" aria-hidden="true">
                <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
              </span>
            <?php endif; ?>
          </div>

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