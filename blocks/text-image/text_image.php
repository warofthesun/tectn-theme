<?php
    /**
     * Text + Image template, single row with background option.
     *
     * @param array $block The block settings and attributes.
     */

    $block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

    $is_editor_context =
        is_admin() ||
        ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
        ( defined( 'REST_REQUEST' ) && REST_REQUEST );

    $is_inserter_preview =
        ! empty( $block['mode'] ) &&
        $block['mode'] === 'preview' &&
        ! empty( $block_data['inserter_preview'] );

    if ( $is_inserter_preview ) {
        $src = get_template_directory_uri() . '/blocks/text-image/preview.png';
        echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
        return;
    }

    // When nested inside Content Section or Text + Image Combo, always defer background to the parent.
    $is_inside_container = ! empty( $block['context']['tectn/insideContainer'] );
    $enable_bg           = $is_inside_container
        ? false
        : tectn_acf_block_bool( 'use_colored_background', $block, 'field_6991ee833dec1', false );
    $content_full_width  = tectn_acf_block_bool( 'content_full_width', $block, 'field_6991ee_content_full', false );

    // Resolve background: hex from color picker, or legacy named values (pre-picker).
    $bg_raw = tectn_acf_block_field( 'background_color', $block );
    $bg_legacy = array(
        'sage'     => '#EFF5D1',
        'cream'    => '#F0F4EC',
        'charcoal' => '#5C6B80',
        'white'    => '#FFFFFF',
    );
    if ( empty( $bg_raw ) ) {
        $bg_value = defined( 'TECTN_COLOR_PICKER_DEFAULT' ) ? TECTN_COLOR_PICKER_DEFAULT : '#EFF5D1';
    } elseif ( isset( $bg_legacy[ $bg_raw ] ) ) {
        $bg_value = $bg_legacy[ $bg_raw ];
    } else {
        $bg_value = $bg_raw;
    }

    $preheader        = tectn_acf_block_field( 'preheader', $block );
    $headline         = tectn_acf_block_field( 'headline', $block );
    $headline_size    = tectn_acf_block_field( 'headline_size', $block );
    $on_dark          = tectn_acf_block_bool( 'on_dark_background', $block, '', false );
    $headline_parsed  = function_exists( 'tectn_headline_tag_and_class' ) ? tectn_headline_tag_and_class( $headline_size, '' ) : array( 'tag' => 'h2', 'class' => '' );
    $body             = tectn_acf_block_field( 'body_copy', $block );
    $content_position = tectn_acf_block_field( 'content_vertical', $block );
    $image_position  = tectn_acf_block_field( 'image_horizontal', $block );
    $media_type_raw   = tectn_acf_block_field( 'media_type', $block );
    $media_type       = ( is_string( $media_type_raw ) && $media_type_raw !== '' ) ? $media_type_raw : 'gallery';
    $images           = tectn_acf_block_array( 'images', $block );
    $slideshow_gallery = tectn_acf_block_array( 'slideshow_gallery', $block );
    $slideshow_aspect  = tectn_acf_block_field( 'slideshow_aspect', $block );
    $slideshow_aspect  = ( is_string( $slideshow_aspect ) && $slideshow_aspect === 'portrait' ) ? 'portrait' : 'square';
    $focal_css         = function_exists( 'tectn_slider_focal_css' ) ? tectn_slider_focal_css( tectn_acf_block_field( 'focal_point', $block ) ) : 'center center';
    $focal_style       = 'object-position: ' . $focal_css;
    $autoplay_raw     = tectn_acf_block_field( 'autoplay', $block );
    // Legacy slideshows had no Autoplay field and always played; default on when unset.
    $autoplay         = ( $media_type === 'slideshow' && $autoplay_raw === null ) ? true : tectn_acf_block_bool( 'autoplay', $block, '', false );
    $show_captions    = tectn_acf_block_bool( 'show_captions', $block, '', false );
    $video_url        = tectn_acf_block_field( 'video_url', $block ); // raw URL for wp_oembed_get()
    if ( ! is_string( $video_url ) ) {
        $video_url = '';
    }
    $count            = count( $images );
    $slideshow_count  = count( $slideshow_gallery );

    $has_video = ($media_type === 'video' && $video_url !== '');
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
    
    $buttons      = get_field( 'buttons' );
    $has_buttons  = function_exists( 'have_rows' ) && have_rows( 'buttons' );

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
                $src     = wp_get_attachment_image_url( $id, 'large' );
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
    if ( $enable_bg ) {
        $style_attr = ' style="--waveband-bg:' . esc_attr( $bg_value ) . ';"';
    }
?>
<?php
  // Treat WYSIWYG as empty if it's only whitespace / empty tags.
  $body_plain = is_string($body) ? trim( wp_strip_all_tags( $body ) ) : '';

  // Consider the block "empty" if it has no preheader, no headline, no meaningful body, no media, and no buttons.
  $is_empty = empty($preheader) && empty($headline) && empty($body_plain) && ! $has_media && ! $has_buttons;

  if ( $is_editor_context && empty( $block_data['inserter_preview'] ) && $is_empty ) :
?>
  <div class="c-text-image__placeholder">
    <strong><?php esc_html_e( 'Text + Image', 'tectn_theme' ); ?></strong><br>
    <?php esc_html_e( 'Add a headline, body copy, and images or a video.', 'tectn_theme' ); ?>
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
            <div class="<?= esc_attr($text_col); ?> c-content-group__content<?php echo $on_dark ? ' c-content-group__content--on-dark' : ''; ?>">
                <?php if ( $preheader ) : ?><h5 class="c-headline-group__preheader<?php echo $on_dark ? ' light' : ''; ?>"><?php echo esc_html( $preheader ); ?></h5><?php endif; ?>
                <?php if ( $headline ) : ?><<?php echo esc_attr( $headline_parsed['tag'] ); ?> class="<?php echo esc_attr( trim( $headline_parsed['class'] . ( $on_dark ? ' light' : '' ) ) ); ?>"><?php echo esc_html( $headline ); ?></<?php echo esc_attr( $headline_parsed['tag'] ); ?>><?php endif; ?>
                <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
                    <?php
                    $button_pair_use_darkbg = $on_dark;
                    $partial_path = get_theme_file_path('/partials/button_pair.php');
                    include $partial_path;
                    unset( $button_pair_use_darkbg );
                    ?>
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
                $outer_aspect_mod  = ( $slideshow_aspect === 'portrait' ) ? 'c-content-group__slideshow--portrait' : 'c-content-group__slideshow--square';
                $wrap_mod          = ( $slideshow_aspect === 'portrait' ) ? 'c-slider__image-wrap--portrait' : 'c-slider__image-wrap--square';
                $img_mod           = ( $slideshow_aspect === 'portrait' ) ? 'c-slider__image--contain' : 'c-slider__image--cover';
                $slider_aspect_mod = 'c-slider--aspect-' . $slideshow_aspect;
                $portrait_wrap_style = '';
                if ( $slideshow_aspect === 'portrait' && is_array( $slideshow_gallery ) && ! empty( $slideshow_gallery[0] ) ) {
                    $first_id = isset( $slideshow_gallery[0]['ID'] ) ? (int) $slideshow_gallery[0]['ID'] : ( isset( $slideshow_gallery[0]['id'] ) ? (int) $slideshow_gallery[0]['id'] : 0 );
                    if ( $first_id ) {
                        $meta = wp_get_attachment_metadata( $first_id );
                        if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                            $portrait_wrap_style = '--slider-aspect-ratio: ' . ( (float) $meta['width'] / (float) $meta['height'] );
                        }
                    }
                }
                ?>
                <div class="c-content-group__slideshow <?php echo esc_attr( $outer_aspect_mod ); ?><?php echo $slideshow_overlap ? ' c-content-group__slideshow--overlap-wave' : ''; ?>">
                <div class="c-slider c-slider--slideshow <?php echo esc_attr( $slider_aspect_mod ); ?>"
                    id="<?php echo esc_attr( $slideshow_id ); ?>"
                    data-slider-type="slideshow"
                    data-items="<?php echo esc_attr( wp_json_encode( $slideshow_items ) ); ?>"
                    data-focal="<?php echo esc_attr( $focal_css ); ?>"
                    data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
                    data-show-captions="<?php echo $show_captions ? '1' : '0'; ?>"
                    style="<?php echo esc_attr( '--slider-focal: ' . $focal_css ); ?>"
                    role="region"
                    aria-label="<?php esc_attr_e( 'Image slideshow', 'tectn_theme' ); ?>">
                    <div class="c-slider__panel">
                        <div class="c-slider__image-wrap <?php echo esc_attr( $wrap_mod ); ?>"<?php echo $portrait_wrap_style !== '' ? ' style="' . esc_attr( $portrait_wrap_style ) . '"' : ''; ?>>
                            <div class="c-slider__slide c-slider__slide--current" data-slider-slide>
                                <img src="<?php echo esc_url( $first['url'] ); ?>"
                                    alt="<?php echo esc_attr( $first['title'] ); ?>"
                                    class="c-slider__image <?php echo esc_attr( $img_mod ); ?>"
                                    style="<?php echo esc_attr( $focal_style ); ?>"
                                    data-slider-image>
                            </div>
                            <div class="c-slider__slide c-slider__slide--next" data-slider-slide>
                                <img src="<?php echo esc_url( $first['url'] ); ?>"
                                    alt=""
                                    class="c-slider__image <?php echo esc_attr( $img_mod ); ?>"
                                    style="<?php echo esc_attr( $focal_style ); ?>"
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
                <div class="c-content-group__slideshow <?php echo esc_attr( ( $slideshow_aspect === 'portrait' ) ? 'c-content-group__slideshow--portrait' : 'c-content-group__slideshow--square' ); ?>">
                    <div class="c-text-image__placeholder">
                        <strong><?php esc_html_e( 'Slideshow', 'tectn_theme' ); ?></strong><br>
                        <?php esc_html_e( 'Add images to the slideshow gallery in the block settings.', 'tectn_theme' ); ?>
                    </div>
                </div>
            <?php elseif ( $has_gallery ) : ?>
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