<?php
/**
 * Sponsorships (ACF Block)
 * Reuses posts-grid background; headline + repeater tiers with sponsors (logo + optional link).
 */

$block_id   = !empty($block['anchor']) ? $block['anchor'] : 'sponsorships-' . $block['id'];
$svg_id     = 'sp-' . preg_replace('/[^a-z0-9]+/i', '', $block['id']);

$classes = ['c-posts', 'c-sponsorships'];
if (!empty($block['className'])) $classes[] = $block['className'];

$theme_variant = get_field('color_palette') ?: 'green-gold';
$classes[] = 'c-posts--' . sanitize_html_class($theme_variant);

$bg_image      = get_field('background_image');
$bg_max_height = (int) (get_field('max_bg_height') ?: 800);

$preheader     = get_field('preheader');
$headline      = get_field('headline');
$headline_size = get_field('headline_size') ?: 'h2';
$headline_tag  = preg_replace('/\s.*/', '', $headline_size);
$headline_attr = trim(str_replace($headline_tag, '', $headline_size));
$headline_class = ($headline_attr !== '') ? ' class="' . esc_attr($headline_attr) . '"' : '';

$bg_url = '';
if (!empty($bg_image) && is_array($bg_image)) {
  $bg_url = esc_url($bg_image['sizes']['large'] ?? $bg_image['url']);
}

$tiers = get_field('tiers');
if (!is_array($tiers)) $tiers = [];
?>

<section id="<?= esc_attr($block_id); ?>" class="<?= esc_attr(implode(' ', $classes)); ?> alignfull" style="--posts-bg-max-h: <?= esc_attr($bg_max_height); ?>px;">
  <!-- Background art layer (same as posts-grid) -->
  <div class="c-posts__bg" aria-hidden="true">
    <?php if ($bg_url): ?>
      <div class="c-posts__bgImage" style="background-image:url('<?= $bg_url; ?>')"></div>
    <?php endif; ?>

    <svg class="c-posts__overlaySvg c-posts__overlaySvg--front" viewBox="0 0 1440 1200" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <linearGradient id="gradientFront-<?= esc_attr($svg_id); ?>" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="rgba(185,220,105,0.70)" />
          <stop offset="55%" stop-color="rgba(39,140,85,0.55)" />
          <stop offset="100%" stop-color="rgba(20,110,60,0.25)" />
        </linearGradient>
        <filter id="softBlurFront-<?= esc_attr($svg_id); ?>" x="-20%" y="-20%" width="140%" height="140%">
          <feGaussianBlur in="SourceGraphic" stdDeviation="12" />
        </filter>
        <filter id="grainFront-<?= esc_attr($svg_id); ?>" x="-20%" y="-20%" width="140%" height="140%">
          <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" result="noise"/>
          <feColorMatrix in="noise" type="matrix"
            values="1 0 0 0 0
            0 1 0 0 0
            0 0 1 0 0
            0 0 0 0.03 0" result="grainAlpha"/>
          <feBlend in="SourceGraphic" in2="grainAlpha" mode="multiply"/>
        </filter>
      </defs>
      <path d="M 0 300 C 200 100, 1200 800, 1440 300 L 1500 1200 L -50 1200 Z" fill="url(#gradientFront-<?= esc_attr($svg_id); ?>)" opacity="0.55" filter="url(#softBlurFront-<?= esc_attr($svg_id); ?>)" />
      <g filter="url(#grainFront-<?= esc_attr($svg_id); ?>)">
        <path d="M 0 300 C 200 100, 1200 800, 1440 300 L 1500 1200 L -50 1200 Z" fill="url(#gradientFront-<?= esc_attr($svg_id); ?>)" opacity="0.85" />
      </g>
    </svg>

    <svg class="c-posts__overlaySvg c-posts__overlaySvg--back" viewBox="0 0 1440 600" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <radialGradient id="gradientBack-<?= esc_attr($svg_id); ?>" cx="75%" cy="20%" r="85%">
          <stop offset="0%" stop-color="rgba(236,186,39,0.75)" />
          <stop offset="100%" stop-color="rgba(105,143,61,0.70)" />
        </radialGradient>
        <filter id="softBlurBack-<?= esc_attr($svg_id); ?>" x="-20%" y="-20%" width="140%" height="140%">
          <feGaussianBlur in="SourceGraphic" stdDeviation="8" />
        </filter>
        <filter id="grainBack-<?= esc_attr($svg_id); ?>" x="-20%" y="-20%" width="140%" height="140%">
          <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" result="noise"/>
          <feColorMatrix in="noise" type="matrix"
            values="1 0 0 0 0
            0 1 0 0 0
            0 0 1 0 0
            0 0 0 0.03 0" result="grainAlpha"/>
          <feBlend in="SourceGraphic" in2="grainAlpha" mode="multiply"/>
        </filter>
      </defs>
      <path d="M 0 300 C 0 300, 200 100, 700 300 C 700 300, 1180 500, 1500 400 L 1500 0 L -50 0 Z" fill="url(#gradientBack-<?= esc_attr($svg_id); ?>)" opacity="0.55" filter="url(#softBlurBack-<?= esc_attr($svg_id); ?>)" />
      <g filter="url(#grainBack-<?= esc_attr($svg_id); ?>)">
        <path d="M 0 300 C 0 300, 200 100, 700 300 C 700 300, 1180 500, 1500 400 L 1500 0 L -50 0 Z" fill="url(#gradientBack-<?= esc_attr($svg_id); ?>)" opacity="0.85" />
      </g>
    </svg>

    <span class="c-posts__wave c-posts__wave--top" aria-hidden="true"></span>
    <span class="c-posts__wave c-posts__wave--bottom" aria-hidden="true"></span>
  </div>

  <!-- Foreground content -->
  <div class="c-posts__inner c-sponsorships__inner l-container wrap">

    <?php if ($preheader || $headline): ?>
      <header class="c-sponsorships__header">
        <?php if ($preheader): ?>
          <h5 class="c-sponsorships__preheader"><?= esc_html($preheader); ?></h5>
        <?php endif; ?>
        <?php if ($headline): ?>
          <<?= esc_attr($headline_tag); ?><?= $headline_class; ?> class="c-sponsorships__title"><?= esc_html($headline); ?></<?= esc_attr($headline_tag); ?>>
        <?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($tiers)): ?>
      <div class="c-sponsorships__tiers">
        <?php foreach ($tiers as $tier):
          $hide_tier = (bool) ($tier['hide_tier'] ?? false);
          $tier_title = $tier['tier_title'] ?? '';
          $tier_bg_style = $tier['tier_background_style'] ?? 'color';
          $tier_no_bg = ($tier_bg_style === 'none');
          $tier_bg = !$tier_no_bg && !empty($tier['tier_background_color']) ? esc_attr($tier['tier_background_color']) : '#F9FBF3';
          $logos_per_row = (int) ($tier['logos_per_row'] ?? 4);
          $logos_per_row = max(1, min(6, $logos_per_row));
          $sponsors = $tier['sponsors'] ?? [];
          if (!is_array($sponsors)) $sponsors = [];
        ?>
          <div class="c-sponsorships__tier<?= $hide_tier ? ' c-sponsorships__tier--hidden' : ''; ?><?= $tier_no_bg ? ' c-sponsorships__tier--no-bg' : ''; ?> c-sponsorships__tier--cols-<?= $logos_per_row; ?>" style="<?= $tier_no_bg ? '' : '--tier-bg: ' . $tier_bg . ';'; ?> --sponsors-cols: <?= $logos_per_row; ?>;"<?= $hide_tier ? ' hidden' : ''; ?>>
            <?php if ($tier_title): ?>
              <h3 class="c-sponsorships__tier-title"><?= esc_html($tier_title); ?></h3>
            <?php endif; ?>
            <div class="c-sponsorships__grid">
              <?php foreach ($sponsors as $sponsor):
                $logo = $sponsor['logo'] ?? null;
                $url = isset($sponsor['url']) ? trim((string) $sponsor['url']) : '';
                $visit_text = isset($sponsor['visit_site_text']) ? trim((string) $sponsor['visit_site_text']) : 'Visit Site';
                $has_link = $url !== '';
                $show_visit_band = $has_link && $visit_text !== '';
                $logo_url = '';
                if (!empty($logo) && is_array($logo)) {
                  $logo_url = esc_url($logo['sizes']['medium_large'] ?? $logo['url'] ?? '');
                }
              ?>
                <div class="c-sponsorships__sponsor">
                  <?php if ($has_link): ?>
                    <a class="c-sponsorships__sponsor-link" href="<?= esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                  <?php endif; ?>
                    <div class="c-sponsorships__sponsor-block<?= $show_visit_band ? '' : ' c-sponsorships__sponsor-block--no-visit'; ?>">
                      <div class="c-sponsorships__logo-wrap">
                        <?php if ($logo_url): ?>
                          <img class="c-sponsorships__logo" src="<?= $logo_url; ?>" alt="" loading="lazy" />
                        <?php endif; ?>
                      </div>
                      <span class="c-sponsorships__visit<?= $show_visit_band ? '' : ' c-sponsorships__visit--placeholder'; ?>"><?= $show_visit_band ? esc_html($visit_text) : ''; ?></span>
                    </div>
                  <?php if ($has_link): ?>
                    </a>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>
