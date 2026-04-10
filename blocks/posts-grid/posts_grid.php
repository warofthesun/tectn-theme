<?php
/**
 * Posts Grid (ACF Block)
 */

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_inserter_preview =
  ! empty( $block['mode'] ) &&
  $block['mode'] === 'preview' &&
  ! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
  $src = get_template_directory_uri() . '/blocks/posts-grid/preview.png';
  echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
  return;
}

$block_id = !empty($block['anchor']) ? $block['anchor'] : 'posts-grid-' . $block['id'];

$remove_bottom_margin = (bool) get_field('remove_bottom_margin');
$classes = ['c-posts'];
if (!empty($block['className'])) $classes[] = $block['className'];
if ($remove_bottom_margin) $classes[] = 'c-posts--no-mb';

$theme_variant = get_field('color_palette') ?: 'green-gold';
$classes[] = 'c-posts--' . sanitize_html_class($theme_variant);

$post_type     = get_field('post_type') ?: 'post';
$per_page      = (int) (get_field('posts_per_page') ?: 6);
$bg_image      = get_field('background_image');
$bg_max_height = (int) (get_field('max_bg_height') ?: 800);

$heading   = get_field('section_heading');

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

// Special handling for Events: only upcoming or ongoing events.
if ($post_type === 'tribe_events') {
  $now = current_time('Y-m-d H:i:s');
  $q = new WP_Query([
    'post_type'      => 'tribe_events',
    'post_status'    => 'publish',
    'posts_per_page' => $cap,
    // Order by start date, but include events whose end date is in the future
    'meta_key'       => '_EventStartDate',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
      [
        'key'     => '_EventEndDate',
        'value'   => $now,
        'compare' => '>=',
        'type'    => 'DATETIME',
      ],
    ],
    'no_found_rows'  => true,
  ]);

  $posts_to_render = $q->posts;
} elseif ($post_type === 'post' && !empty($sticky_ids)) {
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

// Only show "view more" button if there are more items than we're displaying.
if ( $post_type === 'tribe_events' ) {
  $now_for_count = current_time('Y-m-d H:i:s');
  $count_query = new WP_Query([
    'post_type'      => 'tribe_events',
    'post_status'    => 'publish',
    'posts_per_page' => $cap + 1,
    'fields'         => 'ids',
    'meta_key'       => '_EventStartDate',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
      [
        'key'     => '_EventEndDate',
        'value'   => $now_for_count,
        'compare' => '>=',
        'type'    => 'DATETIME',
      ],
    ],
    'no_found_rows'  => true,
  ]);
} else {
  $count_query = new WP_Query([
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'posts_per_page' => $cap + 1,
    'fields'         => 'ids',
    'no_found_rows'  => true,
  ]);
}
$has_more_posts = count($count_query->posts) > $cap;

$display_count = count($posts_to_render);
$effective_bg_max_h = ($display_count >= 1 && $display_count <= 3) ? 600 : $bg_max_height;

$no_events_message = '';
if ( $post_type === 'tribe_events' && empty( $posts_to_render ) && function_exists( 'get_field' ) ) {
  $no_events_message = get_field( 'no_upcoming_events_message', 'post-settings' );
}
$show_no_events = ( $post_type === 'tribe_events' && empty( $posts_to_render ) && $no_events_message !== '' && $no_events_message !== false );
if ( $show_no_events ) {
  $classes[] = 'c-posts--no-events';
}
?>

<section id="<?= esc_attr($block_id); ?>" class="<?= esc_attr(implode(' ', $classes)); ?>  alignfull" style="--posts-bg-max-h: <?= esc_attr($effective_bg_max_h); ?>px;">
  <?php if ( ! $show_no_events ) : ?>
  <!-- Background art layer (decorative only) -->
  <div class="c-posts__bg" aria-hidden="true">
  <?php if ($bg_url): ?>
    <div class="c-posts__bgImage" style="background-image:url('<?= $bg_url; ?>')"></div>
  <?php endif; ?>

    <svg class="c-posts__overlaySvg c-posts__overlaySvg--front" viewBox="0 0 1440 1200" preserveAspectRatio="none" aria-hidden="true">
    <defs>
      <linearGradient id="gradientFront" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="rgba(185,220,105,0.70)" />
        <stop offset="55%" stop-color="rgba(39,140,85,0.55)" />
        <stop offset="100%" stop-color="rgba(20,110,60,0.25)" />
      </linearGradient>

      <!-- Blur for soft edge -->
      <filter id="softBlurFront" x="-20%" y="-20%" width="140%" height="140%">
        <feGaussianBlur class="js-blurFront" in="SourceGraphic" stdDeviation="12" />
      </filter>

      <filter id="grain" x="-20%" y="-20%" width="140%" height="140%">
        <!-- base noise -->
        <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" result="noise"/>
        <!-- make it subtle: convert to low-alpha gray -->
        <feColorMatrix in="noise" type="matrix"
          values="
            1 0 0 0 0
            0 1 0 0 0
            0 0 1 0 0
            0 0 0 0.06 0" result="grainAlpha"/>
        <!-- blend grain over the source -->
        <feBlend in="SourceGraphic" in2="grainAlpha" mode="multiply"/>
      </filter>
    </defs>

    <!-- Soft halo (edge feather) -->
    <path
      d="M 0 300
        C 200 100, 1200 800, 1440 300
        L 1500 1200
        L -50 1200 Z"
      fill="url(#gradientFront)" opacity="0.55" filter="url(#softBlurFront)"
    />

    <!-- Crisp core -->
    <g filter="url(#grain)">
      <path
        d="M 0 300
          C 200 100, 1200 800, 1440 300
          L 1500 1200
          L -50 1200 Z"
        fill="url(#gradientFront)" opacity="0.85"
      />
    </g>
  </svg>

  <svg class="c-posts__overlaySvg c-posts__overlaySvg--back" viewBox="0 0 1440 600" preserveAspectRatio="none" aria-hidden="true">
    <defs>
      <radialGradient id="gradientBack" cx="75%" cy="20%" r="85%">
        <stop offset="0%" stop-color="rgba(236,186,39,0.75)" />
        <stop offset="100%" stop-color="rgba(105,143,61,0.70)" />
      </radialGradient>
      <!-- Blur for soft edge -->
      <filter id="softBlurBack" x="-20%" y="-20%" width="140%" height="140%">
        <feGaussianBlur class="js-blurBack" in="SourceGraphic" stdDeviation="8" />
      </filter>

      <filter id="grain" x="-20%" y="-20%" width="140%" height="140%">
        <!-- base noise -->
        <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" result="noise"/>
        <!-- make it subtle: convert to low-alpha gray -->
        <feColorMatrix in="noise" type="matrix"
          values="
            1 0 0 0 0
            0 1 0 0 0
            0 0 1 0 0
            0 0 0 0.06 0" result="grainAlpha"/>
        <!-- blend grain over the source -->
        <feBlend in="SourceGraphic" in2="grainAlpha" mode="multiply"/>
      </filter>
    </defs>

    <path
      d="M 0 300
         C 0 300, 200 100, 700 300
         C 700 300, 1180 500, 1500 400
         L 1500 0
         L -50 0 Z"
      fill="url(#gradientBack)"  opacity="0.55" filter="url(#softBlurBack)"
    />
    <g filter="url(#grain)">
      <path
        d="M 0 300
          C 0 300, 200 100, 700 300
          C 700 300, 1180 500, 1500 400
          L 1500 0
          L -50 0 Z"
        fill="url(#gradientBack)"  opacity="0.85"
      />
    </g>
  </svg>

  <span class="c-posts__wave c-posts__wave--top" aria-hidden="true"></span>
  <span class="c-posts__wave c-posts__wave--bottom" aria-hidden="true"></span>
</div>
  <?php endif; ?>

  <!-- Foreground content -->
  <div class="c-posts__inner l-container wrap">

    <?php if (!empty($posts_to_render)): ?>
      <?php $grid_count = count($posts_to_render); $grid_class = ($grid_count <= 2) ? ' c-posts__grid--count-' . $grid_count : ''; ?>
      <div class="c-posts__grid<?= esc_attr($grid_class); ?>">
        <?php foreach ($posts_to_render as $p): ?>
          <?php if ( $p->post_type === 'post' && function_exists( 'tectn_render_blog_post_card' ) ) : ?>
            <?php tectn_render_blog_post_card( $p, array( 'show_cta' => true ) ); ?>
          <?php else : ?>
          <?php
            // Use explicit post IDs so we don't depend on global $post
            $post_id  = $p->ID;
            $date     = ( $p->post_type === 'tribe_events' && function_exists( 'tectn_get_event_post_card_date' ) )
              ? tectn_get_event_post_card_date( $post_id, 'm/d/y' )
              : get_the_date( 'm/d/y', $post_id );
            $title    = get_the_title($post_id);
            // For events, allow external override URL; otherwise use the normal permalink.
            if ( $p->post_type === 'tribe_events' && function_exists( 'tectn_get_event_external_url' ) ) {
              $permalink = tectn_get_event_external_url( $post_id );
              if ( ! $permalink ) {
                $permalink = get_permalink( $post_id );
              }
            } else {
              $permalink = get_permalink( $post_id );
            }

            $img_url = tectn_get_post_card_image_url( $post_id );

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
            <a class="c-postCard__link" href="<?= esc_url($permalink); ?>"<?php if ( $p->post_type === 'tribe_events' ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>>
              <div class="c-postCard__media">
                <?php if ($img_url): ?>
                  <img class="c-postCard__img" src="<?= esc_url($img_url); ?>" alt="" loading="lazy" />
                <?php endif; ?>
              </div>

              <div class="c-postCard__body">
                <h5 class="c-postCard__meta"><?= esc_html( $date ); ?></h5>
                <h3 class="c-postCard__title"><?= esc_html( $title ); ?></h3>
                <?php if ( ! empty( $chips ) ) : ?>
                  <div class="c-postCard__chips">
                    <?php foreach ( $chips as $chip ) : ?>
                      <span class="c-chip"><?= esc_html( $chip ); ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <span class="c-postCard__cta">read more</span>
              </div>
            </a>
          </article>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?php if ( $show_no_events && $no_events_message ) : ?>
        <div class="c-posts__no-events">
          <div class="c-posts__no-events-inner"><h4 class="c-posts__no-events-title"><?php echo wp_kses_post( $no_events_message ); ?></h4></div>
        </div>
      <?php else : ?>
        <p>No posts found.</p>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($has_more_posts): $partial_path = get_theme_file_path('/partials/button_pair.php'); include $partial_path; endif; ?>
  </div>
</section>