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
    $media_type       = get_field('media_type') ?: 'gallery';
    $images           = get_field('images');
    $slideshow_gallery = get_field('slideshow_gallery');
    $show_captions    = (bool) get_field('show_captions');
    $video_url        = get_field('video_url', false, false); // raw URL for wp_oembed_get()
    $count            = is_array($images) ? count($images) : 0;
    $slideshow_count  = is_array($slideshow_gallery) ? count($slideshow_gallery) : 0;

    $has_video = ($media_type === 'video' && ! empty($video_url) && is_string($video_url));
    $has_gallery = ($media_type === 'gallery' && $count > 0);
    $has_slideshow = ($media_type === 'slideshow' && $slideshow_count > 0);
    $has_media = $has_video || $has_gallery || $has_slideshow;

    if ($has_video || $has_slideshow || $media_type === 'slideshow') {
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
        ($enable_bg && ($has_slideshow || $media_type === 'slideshow')) ? 'c-waveband--has-slideshow' : '',
    ];

    $align = !empty($block['align']) ? 'align' . $block['align'] : '';
    $classes_band[] = $align;

    // Build slideshow items (same structure as Slider block) when media type is slideshow
    $slideshow_items = array();
    if ( $has_slideshow && is_array( $slideshow_gallery ) ) {
        foreach ( $slideshow_gallery as $img ) {
            $url     = isset( $img['url'] ) ? $img['url'] : '';
            $title   = isset( $img['title'] ) ? $img['title'] : '';
            $id      = isset( $img['ID'] ) ? (int) $img['ID'] : ( isset( $img['id'] ) ? (int) $img['id'] : 0 );
            $caption = '';
            $author  = '';
            if ( $id ) {
                $caption = isset( $img['caption'] ) && (string) $img['caption'] !== '' ? $img['caption'] : wp_get_attachment_caption( $id );
                $author  = function_exists( 'get_field' ) ? ( get_field( 'caption_author', $id ) ?: '' ) : '';
                $src     = wp_get_attachment_image_url( $id, 'tectn_slider_square' );
                if ( $src ) {
                    $url = $src;
                }
            }
            if ( $url !== '' ) {
                $slideshow_items[] = array(
                    'url'     => $url,
                    'title'   => $title !== '' ? $title : __( 'Untitled', 'tectn_theme' ),
                    'caption' => is_string( $caption ) ? $caption : '',
                    'author'  => is_string( $author ) ? $author : '',
                );
            }
        }
    }

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

        <div class="<?php echo esc_attr(implode(' ', $classes_cg)); ?> row<?php echo $has_video ? ' c-content-group__row--has-video' : ''; ?><?php echo ($has_slideshow || $media_type === 'slideshow') ? ' c-content-group__row--has-slideshow' : ''; ?>">
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
            <?php elseif ( $has_slideshow && ! empty( $slideshow_items ) ) :
                $slideshow_id = isset( $block['id'] ) ? 'text-image-slider-' . $block['id'] : 'text-image-slider-' . wp_rand( 1000, 9999 );
                if ( ! is_admin() ) {
                    $block_path = get_template_directory() . '/blocks/slider';
                    $block_uri  = get_template_directory_uri() . '/blocks/slider';
                    wp_enqueue_script(
                        'tectn-slider-view',
                        $block_uri . '/view.js',
                        array(),
                        file_exists( $block_path . '/view.js' ) ? filemtime( $block_path . '/view.js' ) : null,
                        true
                    );
                }
                $first = $slideshow_items[0];
                $first_has_caption = $show_captions && ( (string) $first['caption'] !== '' || (string) $first['author'] !== '' );
                $slideshow_overlap = $enable_bg;
                ?>
                <div class="c-content-group__slideshow c-content-group__slideshow--square<?php echo $slideshow_overlap ? ' c-content-group__slideshow--overlap-wave' : ''; ?>">
                <div class="c-slider c-slider--slideshow"
                    id="<?php echo esc_attr( $slideshow_id ); ?>"
                    data-slider-type="slideshow"
                    data-items="<?php echo esc_attr( wp_json_encode( $slideshow_items ) ); ?>"
                    data-autoplay="1"
                    data-show-captions="<?php echo $show_captions ? '1' : '0'; ?>"
                    role="region"
                    aria-label="<?php esc_attr_e( 'Image slideshow', 'tectn_theme' ); ?>">
                    <div class="c-slider__panel">
                        <div class="c-slider__image-wrap c-slider__image-wrap--square">
                            <div class="c-slider__slide c-slider__slide--current" data-slider-slide>
                                <img src="<?php echo esc_url( $first['url'] ); ?>"
                                    alt="<?php echo esc_attr( $first['title'] ); ?>"
                                    class="c-slider__image c-slider__image--cover"
                                    data-slider-image>
                            </div>
                            <div class="c-slider__slide c-slider__slide--next" data-slider-slide>
                                <img src="<?php echo esc_url( $first['url'] ); ?>"
                                    alt=""
                                    class="c-slider__image c-slider__image--cover"
                                    data-slider-image>
                            </div>
                            <?php if ( $show_captions ) : ?>
                            <div class="c-slider__caption<?php echo $first_has_caption ? ' c-slider__caption--visible' : ''; ?>" data-slider-caption aria-live="polite">
                                <?php if ( $first_has_caption ) : ?>
                                    <?php if ( (string) $first['caption'] !== '' ) : ?>
                                        <p class="c-slider__caption-text"><?php echo esc_html( $first['caption'] ); ?></p>
                                    <?php endif; ?>
                                    <?php if ( (string) $first['author'] !== '' ) : ?>
                                        <p class="c-slider__caption-author"><?php echo esc_html( $first['author'] ); ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <button type="button" class="c-slider__arrow c-slider__arrow--prev" data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'tectn_theme' ); ?>"></button>
                            <button type="button" class="c-slider__arrow c-slider__arrow--next" data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'tectn_theme' ); ?>"></button>
                            <nav class="c-slider__dots" aria-label="<?php esc_attr_e( 'Slide navigation', 'tectn_theme' ); ?>">
                                <?php foreach ( $slideshow_items as $i => $item ) : ?>
                                    <button type="button" class="c-slider__dot<?php echo $i === 0 ? ' c-slider__dot--active' : ''; ?>" data-index="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'tectn_theme' ), $i + 1 ) ); ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
                                <?php endforeach; ?>
                            </nav>
                        </div>
                    </div>
                </div>
                </div>
            <?php elseif ( $media_type === 'slideshow' ) : ?>
                <div class="c-content-group__slideshow c-content-group__slideshow--square">
                    <div class="block-placeholder" style="border: 1px dashed #cfd3d7; padding: 2rem; border-radius: .5rem; background: rgba(255,255,255,.8); min-height: 200px; display: flex; align-items: center; justify-content: center;">
                        <p style="margin: 0; color: #666;">Add images to the slideshow gallery above.</p>
                    </div>
                </div>
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