<?php
// Button Pair: repeater buttons (per-button style class) + optional parent color class

if (have_rows('buttons')) :

    // Field OUTSIDE the repeater (on the block / group / post) used to theme the whole pair.
    // BEM: block c-button-pair, modifiers e.g. c-button-pair--primary
    $pair_color = get_field('button_color');

    $pair_classes = ['c-button-pair'];
    if (!empty($pair_color)) {
        foreach (preg_split('/\s+/', trim((string) $pair_color)) as $cls) {
            if ($cls !== '') $pair_classes[] = 'c-button-pair--' . sanitize_html_class($cls);
        }
    }
?>
    <div class="<?php echo esc_attr(implode(' ', array_filter($pair_classes))); ?>">
        <?php while (have_rows('buttons')) : the_row();

            // Field INSIDE the repeater: BEM element c-button-pair__button, modifiers e.g. c-button-pair__button--solid
            $style = get_sub_field('button_style');

            $link = get_sub_field('button');
            if (!$link) continue;

            $url    = $link['url'] ?? '';
            $title  = $link['title'] ?? '';
            $target = $link['target'] ?? '_self';
            if (!$url || !$title) continue;

            $btn_classes = ['c-button-pair__button'];
            if (!empty($style)) {
                foreach (preg_split('/\s+/', trim((string) $style)) as $cls) {
                    if ($cls !== '') $btn_classes[] = 'c-button-pair__button--' . sanitize_html_class($cls);
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