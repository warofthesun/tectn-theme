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

// Hard cap logic (max 6 total cards)
$requested = (int) get_field('posts_per_page');
if ($requested <= 0) $requested = 6;
$cap = min($requested, 6);

// Sticky handling: only applies to the default "post" post type
$sticky_ids = ($post_type === 'post') ? get_option('sticky_posts') : [];
$sticky_ids = is_array($sticky_ids) ? array_values(array_filter(array_map('intval', $sticky_ids))) : [];

$posts_to_render = [];

if ($post_type === 'post' && !empty($sticky_ids)) {
  // 1) Pull sticky posts first (counting toward the cap)
  $sticky_query = new WP_Query([
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $cap,
    'post__in'            => $sticky_ids,
    'orderby'             => 'post__in',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
  ]);

  $sticky_posts = $sticky_query->posts;
  $sticky_count = count($sticky_posts);
  $remaining    = max(0, $cap - $sticky_count);

  // 2) Fill the remaining slots with newest non-sticky posts
  $normal_posts = [];
  if ($remaining > 0) {
    $normal_query = new WP_Query([
      'post_type'           => 'post',
      'post_status'         => 'publish',
      'posts_per_page'      => $remaining,
      'post__not_in'        => $sticky_ids,
      'orderby'             => 'date',
      'order'               => 'DESC',
      'ignore_sticky_posts' => true,
      'no_found_rows'       => true,
    ]);

    $normal_posts = $normal_query->posts;
  }

  $posts_to_render = array_merge($sticky_posts, $normal_posts);
} else {
  // Non-default post types (or no sticky posts): simple capped query
  $q = new WP_Query([
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'posts_per_page' => $cap,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'no_found_rows'  => true,
  ]);

  $posts_to_render = $q->posts;
}
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

    <?php if (!empty($posts_to_render)): ?>
      <div class="c-posts__grid">
        <?php foreach ($posts_to_render as $p): ?>
          <?php
            // Use explicit post IDs so we don't depend on global $post
            $post_id  = $p->ID;
            $date     = get_the_date('m/d/y', $post_id);
            $title    = get_the_title($post_id);
            $permalink = get_permalink($post_id);

            $img_url = get_the_post_thumbnail_url($post_id, 'large');

            // Default WP taxonomies (will be empty for CPTs that don't use them)
            $categories = get_the_category($post_id);
            $tags       = get_the_tags($post_id);

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
        <?php endforeach; ?>
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