<?php
/**
 * Reusable section background partial
 * Usage: include get_theme_file_path('blocks/partials/section_bg.php');
 *
 * Expected $args keys:
 * - bg_url (string)
 * - max_h (int)  e.g. 800
 * - page_bg_var (string) css var name, e.g. '--c-page-bg'
 * - overlays (array) ['front' => '<svg...>', 'back' => '<svg...>'] OR render flags
 */

$bg_url      = $args['bg_url'] ?? '';
$max_h       = (int) ($args['max_h'] ?? 800);
$page_bg_var = $args['page_bg_var'] ?? '--c-page-bg';
$overlays    = $args['overlays'] ?? [];
?>

<div class="c-sectionBg" aria-hidden="true" style="--section-bg-max-h: <?= esc_attr($max_h); ?>px; --section-page-bg: var(<?= esc_attr($page_bg_var); ?>);">
  <?php if ($bg_url): ?>
    <div class="c-sectionBg__image" style="background-image:url('<?= esc_url($bg_url); ?>')"></div>
  <?php endif; ?>

  <?php if (!empty($overlays['back'])): ?>
    <div class="c-sectionBg__overlay c-sectionBg__overlay--back"><?= $overlays['back']; ?></div>
  <?php endif; ?>

  <?php if (!empty($overlays['front'])): ?>
    <div class="c-sectionBg__overlay c-sectionBg__overlay--front"><?= $overlays['front']; ?></div>
  <?php endif; ?>

  <span class="c-sectionBg__wave c-sectionBg__wave--top" aria-hidden="true"></span>
  <span class="c-sectionBg__wave c-sectionBg__wave--bottom" aria-hidden="true"></span>
</div>