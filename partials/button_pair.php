<?php if (have_rows('buttons')) : ?>
    <div class="button-pair">
        <?php while (have_rows('buttons')) : the_row();
        $link = get_sub_field('button');
        if (!$link) continue;

        $url    = $link['url'] ?? '';
        $title  = $link['title'] ?? '';
        $target = $link['target'] ?? '_self';
        if (!$url || !$title) continue;
        ?>
        <a class="button"
            href="<?php echo esc_url($url); ?>"
            target="<?php echo esc_attr($target); ?>"
            <?php echo ($target === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>
            <?php echo esc_html($title); ?>
        </a>
        <?php endwhile; ?>
    </div>
<?php endif; ?>