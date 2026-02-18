<?php 
    /**
     * Headline group template, headline, body copy, CTAs
     *
     * @param array $block The block settings and attributes.
     */


    $headline       = get_field('headline');
    $headline_size  = get_field('headline_size');
    $body           = get_field('body_copy');
    $buttons     = get_field('buttons');

    $is_preview = !empty($block['data']['is_preview']);

    if ($is_preview) {
    $preview = get_template_directory_uri() . '/blocks/headline-group/preview.png';
    echo '<img src="' . esc_url($preview) . '" style="width:100%;height:auto;display:block;" alt="">';
    return;
    }
?>

<div class="row">
    <div class="col-xs-12 headline-group">
    <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
    <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
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
    </div>
</div>
