<?php 
    /**
     * Text + Image template, single row with background option.
     *
     * @param array $block The block settings and attributes.
     */
    // When nested inside Content Section or Content Container, always defer background to the parent.
    $is_inside_container = ! empty( $block['context']['tectn/insideContainer'] );
    $enable_bg         = $is_inside_container ? false : get_field('use_colored_background');
    $content_full_width = (bool) get_field('content_full_width');
    $bg_choice        = get_field('background_color') ?: 'sage';
    $bg_map = [
        'sage'     => 'var(--c-sage)',
        'cream'    => 'var(--c-cream)',
        'charcoal' => 'var(--c-charcoal)',
        'white'    => 'var(--c-white)',
    ];
    $bg_value = $bg_map[$bg_choice] ?? $bg_map['sage'];

    $headline        = get_field('headline');
    $headline_size   = get_field('headline_size');
    $body            = get_field('body_copy');
    $content_position = get_field('content_vertical');
    $image_position  = get_field('image_horizontal');
    $media_type      = get_field('media_type') ?: 'gallery';
    $images          = get_field('images');
    $video_url       = get_field('video_url', false, false); // raw URL for wp_oembed_get()
    $count           = is_array($images) ? count($images) : 0;

    $has_video = ($media_type === 'video' && ! empty($video_url) && is_string($video_url));
    $has_gallery = ($media_type === 'gallery' && $count > 0);
    $has_media = $has_video || $has_gallery;

    if ($has_video) {
        $text_col   = 'col-xs-12 col-md-6';
        $image_col  = 'col-xs-12 col-md-6';
        $grid_class = 'c-image-grid c-image-grid--one';
    } elseif ($count === 1) {
        $text_col   = 'col-xs-12 col-md-6';
        $image_col  = 'col-xs-12 col-md-6';
        $grid_class = 'c-image-grid c-image-grid--one';
    } elseif ($count === 2) {
        $text_col   = 'col-xs-12 col-md-4';
        $image_col  = 'col-xs-12 col-md-8';
        $grid_class = 'c-image-grid c-image-grid--two';
    } elseif ($count === 3) {
        $text_col   = 'col-xs-12 col-md-4';
        $image_col  = 'col-xs-12 col-md-8';
        $grid_class = 'c-image-grid c-image-grid--three';
    } else {
        $text_col   = 'col-xs-12 col-md-12';
        $image_col  = 'd-none';
        $grid_class = 'c-image-grid c-image-grid--one';
    }
    
    $buttons     = get_field('buttons');

    // Build classes for the content group using BEM naming
    $classes_cg = ['c-content-group'];
    if ($content_position === 'middle') {
        $classes_cg[] = 'c-content-group--middle';
    }
    
    if ($content_position === 'bottom') {
        $classes_cg[] = 'c-content-group--bottom';
    }
    
    if ($image_position === 'left') {
        $classes_cg[] = 'c-content-group--reverse';
    }

    $classes_band = [
        'c-waveband',
        $enable_bg ? 'is-bg' : '',
        ($enable_bg && $has_video) ? 'c-waveband--has-video' : '',
    ];

    $align = !empty($block['align']) ? 'align' . $block['align'] : '';
    $classes_band[] = $align;

    $style_attr = '';
    if ($enable_bg) {
        $bg_value   = $bg_map[$bg_choice] ?? $bg_map['sage'];
        $style_attr = ' style="--waveband-bg:' . esc_attr( $bg_value ) . ';"';
    }
?>
<?php
  // Editor-only placeholder so empty blocks are still visible when inserted.
  // NOTE: `$block['data']['is_preview']` is not consistently present across all editor render paths,
  // so we rely on `is_admin()` to detect the editor.
  $is_editor = is_admin();

  // Treat WYSIWYG as empty if it's only whitespace / empty tags.
  $body_plain = is_string($body) ? trim( wp_strip_all_tags( $body ) ) : '';

  // Consider the block "empty" if it has no headline, no meaningful body, and no media (images or video).
  $is_empty = empty($headline) && empty($body_plain) && ! $has_media;

  if ( $is_editor && $is_empty ) :
?>
  <div class="block-placeholder" style="border: 1px dashed #cfd3d7; padding: 1rem; border-radius: .5rem; background: rgba(255,255,255,.8);">
    <strong style="display:block; margin-bottom:.25rem;">Text + Image</strong>
    <p style="margin:0;">Add a headline, body copy, and images or a video.</p>
  </div>
<?php
    return;
  endif;
?>
<?php if ($enable_bg): ?>
<div class="<?php echo esc_attr(implode(' ', array_filter($classes_band))); ?>"<?php echo $style_attr; ?>>

    <div class="c-waveband__bg" style="--waveband-max-h: 800px;">
        <span class="c-waveband__wave c-waveband__wave--top" aria-hidden="true"></span>
        <span class="c-waveband__wave c-waveband__wave--bottom" aria-hidden="true"></span>
    </div>

    <div class="c-waveband__content<?php echo $content_full_width ? ' c-waveband__content--full' : ''; ?>">
<?php endif; ?>

<?php if ( ! $enable_bg && $content_full_width ) : ?><div class="c-text-image__content c-text-image__content--full"><?php endif; ?>
<?php if ( ! $enable_bg && ! $content_full_width ) : ?><div class="c-text-image__content"><?php endif; ?>

        <div class="<?php echo esc_attr(implode(' ', $classes_cg)); ?> row<?php echo $has_video ? ' c-content-group__row--has-video' : ''; ?>">
            <div class="<?= esc_attr($text_col); ?> c-content-group__content">
                <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
                <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
                    <?php $partial_path = get_theme_file_path('/partials/button_pair.php'); ?>
                    <?php include $partial_path; ?>
            </div>
            <div class="<?= esc_attr($image_col); ?><?php echo $has_video ? ' c-content-group__media-col--video' : ''; ?>">
            <?php if ( $has_video ) :
                $video_embed = wp_oembed_get( $video_url );
                if ( $video_embed ) : ?>
                <div class="c-content-group__video">
                    <?php echo wp_kses( $video_embed, [
                        'iframe' => [
                            'src'             => true,
                            'width'           => true,
                            'height'          => true,
                            'frameborder'     => true,
                            'allow'           => true,
                            'allowfullscreen' => true,
                            'loading'         => true,
                            'title'           => true,
                        ],
                    ] ); ?>
                </div>
                <?php endif; ?>
            <?php elseif ( $images ) : ?>
                <ul class="<?= esc_attr($grid_class); ?>">
                    <?php if ($count === 3): ?>

                        <li class="c-image-grid__item c-image-grid__item--1">
                        <?php
                        $img = $images[0];
                        $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                        $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                        ?>
                        <img class="c-image-grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                        </li>

                        <li class="c-image-grid__right">
                        <?php for ($i = 1; $i < 3; $i++):
                            $img = $images[$i];
                            $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                            $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                        ?>
                            <div class="c-image-grid__right-item c-image-grid__right-item--<?= $i+1; ?>">
                            <img class="c-image-grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                            </div>
                        <?php endfor; ?>
                        </li>

                    <?php else: ?>
                        <?php foreach ($images as $i => $img):
                        $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                        $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                        ?>
                        <li class="c-image-grid__item c-image-grid__item--<?= $i+1; ?>">
                            <img class="c-image-grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

<?php if ( ! $enable_bg ) : ?></div><?php endif; ?>
<?php if ($enable_bg): ?>
    </div>
</div>
<?php endif; ?>