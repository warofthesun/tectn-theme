<?php
/**
 * Posts Grid (ACF Block)
 */

$block_id = !empty($block['anchor']) ? $block['anchor'] : 'posts-grid-' . $block['id'];

$classes = ['c-posts'];
if (!empty($block['className'])) $classes[] = $block['className'];

$theme_variant = get_field('color_pallete') ?: 'green-gold';
$classes[] = 'c-posts--' . sanitize_html_class($theme_variant);

$post_type     = get_field('post_type') ?: 'post';
$per_page      = (int) (get_field('posts_per_page') ?: 6);
$bg_image      = get_field('background_image');
$bg_max_height = (int) (get_field('max_bg_height') ?: 800);

$heading   = get_field('section_heading');
$cta_label = get_field('cta_label') ?: 'View all posts';
$cta_link  = get_field('cta_link');

$bg_url = '';
if (!empty($bg_image) && is_array($bg_image)) {
  $bg_url = esc_url($bg_image['sizes']['large'] ?? $bg_image['url']);
}

$args = [
  'post_type'      => $post_type,
  'post_status'    => 'publish',
  'posts_per_page' => $per_page,
  'orderby'        => 'date',
  'order'          => 'DESC',
];

$q = new WP_Query($args);
?>

<section id="<?= esc_attr($block_id); ?>" class="<?= esc_attr(implode(' ', $classes)); ?>  alignfull" style="--bg-max-h: <?= esc_attr($bg_max_height); ?>px;">
  <!-- Background art layer (decorative only) -->
  <div class="c-posts__bg" aria-hidden="true">
    <?php if ($bg_url): ?>
      <div class="c-posts__bgImage" style="background-image:url('<?= $bg_url; ?>')"></div>
    <?php endif; ?>

    <div class="c-posts__blob c-posts__blob--gold"></div>
    <div class="c-posts__blob c-posts__blob--green"></div>
    <div class="c-posts__blob c-posts__blob--deep"></div>

    <svg class="c-posts__wave" viewBox="0 0 1440 160" preserveAspectRatio="none" focusable="false">
      <path d="M0,96 C240,160 480,160 720,112 C960,64 1200,32 1440,64 L1440,160 L0,160 Z" />
    </svg>
  </div>

  <!-- Foreground content -->
  <div class="c-posts__inner l-container wrap">

    <?php if ($q->have_posts()): ?>
      <div class="c-posts__grid">
        <?php while ($q->have_posts()): $q->the_post(); ?>
          <?php
            $date = get_the_date('m/d/y');
            $title = get_the_title();
            $permalink = get_permalink();

            $img_url = get_the_post_thumbnail_url(get_the_ID(), 'large');

            // Default WP taxonomies (will be empty for CPTs that don't use them)
            $categories = get_the_category(get_the_ID());
            $tags = get_the_tags(get_the_ID());

            // Build chip list: Categories first, then Tags
            $chips = [];

            if (!empty($categories) && !is_wp_error($categories)) {
              foreach ($categories as $cat) {
                $chips[] = $cat->name;
              }
            }

            if (!empty($tags) && !is_wp_error($tags)) {
              foreach ($tags as $tag) {
                $chips[] = $tag->name;
              }
            }

            // De-dupe + limit
            $chips = array_values(array_unique(array_filter($chips)));
            $chips = array_slice($chips, 0, 3);
          ?>

          <article class="c-postCard">
            <a class="c-postCard__link" href="<?= esc_url($permalink); ?>">
              <div class="c-postCard__media">
                <?php if ($img_url): ?>
                  <img class="c-postCard__img" src="<?= esc_url($img_url); ?>" alt="" loading="lazy" />
                <?php endif; ?>
              </div>

              <div class="c-postCard__body">
                <div class="c-postCard__meta"><?= esc_html($date); ?></div>
                <h3 class="c-postCard__title"><?= esc_html($title); ?></h3>

                <?php if (!empty($chips)): ?>
                  <div class="c-postCard__chips">
                    <?php foreach ($chips as $chip): ?>
                      <span class="c-chip"><?= esc_html($chip); ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <span class="c-postCard__cta">Learn more</span>
              </div>
            </a>
          </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    <?php else: ?>
      <p>No posts found.</p>
    <?php endif; ?>

    <?php if (!empty($cta_link['url'])): ?>
      <div class="c-posts__footer">
        <a class="c-button c-button--gold" href="<?= esc_url($cta_link['url']); ?>" <?= !empty($cta_link['target']) ? 'target="_blank" rel="noopener"' : ''; ?>>
          <?= esc_html($cta_label); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>