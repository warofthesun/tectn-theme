<?php
// Button Pair: repeater buttons (per-button style class) + optional parent color class

if (have_rows('buttons')) :

    // Field OUTSIDE the repeater (on the block / group / post) used to theme the whole pair.
    // Example stored values: "button-pair--green", "button-pair--dark", etc.
    $pair_color = get_field('button_color');

    $pair_classes = ['button-pair'];
    if (!empty($pair_color)) {
        // Allow either a single class or a space-separated list
        foreach (preg_split('/\s+/', trim((string) $pair_color)) as $cls) {
            if ($cls !== '') $pair_classes[] = sanitize_html_class($cls);
        }
    }
?>
    <div class="<?php echo esc_attr(implode(' ', array_filter($pair_classes))); ?>">
        <?php while (have_rows('buttons')) : the_row();

            // Field INSIDE the repeater (per-row) used to style the individual button.
            // Example stored values: "button--solid", "button--outline", "button--ghost", etc.
            $style = get_sub_field('button_style');

            $link = get_sub_field('button');
            if (!$link) continue;

            $url    = $link['url'] ?? '';
            $title  = $link['title'] ?? '';
            $target = $link['target'] ?? '_self';
            if (!$url || !$title) continue;

            $btn_classes = ['button'];
            if (!empty($style)) {
                // Allow either a single class or a space-separated list
                foreach (preg_split('/\s+/', trim((string) $style)) as $cls) {
                    if ($cls !== '') $btn_classes[] = sanitize_html_class($cls);
                }
            }
        ?>
            <a class="<?php echo esc_attr(implode(' ', array_filter($btn_classes))); ?>"
                href="<?php echo esc_url($url); ?>"
                target="<?php echo esc_attr($target); ?>"
                <?php echo ($target === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html($title); ?>
            </a>
        <?php endwhile; ?>
    </div>
<?php endif; ?>