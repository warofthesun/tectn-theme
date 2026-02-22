<?php 
    /**
     * Text + Image template, single row with background option.
     *
     * @param array $block The block settings and attributes.
     */
    $enable_bg      = get_field('use_colored_background');
    $bg_choice      = get_field('background_color') ?: 'sage';
    $bg_map = [
        'sage'     => 'var(--c-sage)',
        'cream'    => 'var(--c-cream)',
        'charcoal' => 'var(--c-charcoal)',
        'white'    => 'var(--c-white)',
    ];
    $bg_value = $bg_map[$bg_choice] ?? $bg_map['sage'];

    $headline       = get_field('headline');
    $headline_size  = get_field('headline_size');
    $body           = get_field('body_copy');
    $content_position = get_field('content_vertical');
    $image_position = get_field('image_horizontal');
    $images = get_field('images');
    $count = is_array($images) ? count($images) : 0;
    if ($count === 1) {
        $text_col  = 'col-xs-12 col-md-6';
        $image_col = 'col-xs-12 col-md-6';
        $grid_class = 'image-grid image-grid--one';
      } elseif ($count === 2) {
        $text_col  = 'col-xs-12 col-md-4';
        $image_col = 'col-xs-12 col-md-8';
        $grid_class = 'image-grid image-grid--two';
      } elseif ($count === 3) {
        $text_col  = 'col-xs-12 col-md-4';
        $image_col = 'col-xs-12 col-md-8';
        $grid_class = 'image-grid image-grid--three';
      } else {
        // Optional: fallback if no images (choose whatever makes sense)
        $text_col  = 'col-xs-12 col-md-12';
        $image_col = 'd-none'; // or 'col-xs-12 col-md-12' and show a placeholder
      }
    
    $buttons     = get_field('buttons');

    $classes_cg = ['content-group'];
    if ($content_position === 'middle') {
        $classes_cg[] = 'content-group--middle';
    }
    
    if ($content_position === 'bottom') {
        $classes_cg[] = 'content-group--bottom';
    }
    
    if ($image_position === 'left') {
        $classes_cg[] = 'content-group--reverse';
    }

    $classes_band = [
        'c-waveband',
        $enable_bg ? 'is-bg' : '',
    ];

    $align = !empty($block['align']) ? 'align' . $block['align'] : '';
    $classes_band[] = $align;

    $style = '';
    if ($enable_bg) {
        $bg_value = $bg_map[$bg_choice] ?? $bg_map['sage'];
        $style = '--waveband-bg:' . $bg_value . ';';
    }
?>
<?php
    $is_preview = !empty($block['data']['is_preview']);

    if ($is_preview) {
    $preview = get_template_directory_uri() . '/blocks/text-image/preview.png';
    echo '<img src="' . esc_url($preview) . '" style="width:100%;height:auto;display:block;" alt="">';
    return;
    }
?>
<?php if ($enable_bg): ?>
<div class="<?php echo esc_attr(implode(' ', array_filter($classes_band))); ?>">

    <div class="c-waveband__bg"
      <?php if ($style) echo 'style="' . esc_attr($style) . ' --waveband-max-h: 800px;"'; ?>
    >
        <span class="c-waveband__wave c-waveband__wave--top" aria-hidden="true"></span>
        <span class="c-waveband__wave c-waveband__wave--bottom" aria-hidden="true"></span>
    </div>

    <div class="c-waveband__content">
<?php endif; ?>

  
        <div class="<?php echo esc_attr(implode(' ', $classes_cg)); ?> row">
            <div class="<?= esc_attr($text_col); ?> content-group__content">
                <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
                <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
                    <?php $partial_path = get_theme_file_path('/partials/button_pair.php'); ?>
                    <?php include $partial_path; ?>
            </div>
            <div class="<?= esc_attr($image_col); ?>">
            <?php if( $images ) : ?>
                <ul class="image-grid <?= esc_attr($grid_class); ?>">
                    <?php if ($count === 3): ?>

                        <li class="image-grid__item image-grid__item--1">
                        <?php
                        $img = $images[0];
                        $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                        $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                        ?>
                        <img class="image-grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                        </li>

                        <li class="image-grid__right">
                        <?php for ($i = 1; $i < 3; $i++):
                            $img = $images[$i];
                            $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                            $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                        ?>
                            <div class="image-grid__right-item image-grid__right-item--<?= $i+1; ?>">
                            <img class="image-grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                            </div>
                        <?php endfor; ?>
                        </li>

                    <?php else: ?>
                        <?php foreach ($images as $i => $img):
                        $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                        $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                        ?>
                        <li class="image-grid__item image-grid__item--<?= $i+1; ?>">
                            <img class="image-grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
<?php if ($enable_bg): ?>
    </div>
</div>
<?php endif; ?>