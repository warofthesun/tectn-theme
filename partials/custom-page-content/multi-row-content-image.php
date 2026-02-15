<?php if( get_row_layout() == 'multi_row_content_image'): 
    $row_one = get_sub_field('row_one');
    $headline       = $row_one['headline'] ?? '';
    $headline_size  = $row_one['headline_size'] ?? '';
    $body           = $row_one['body_copy'] ?? '';
    $content_position = $row_one['content_vertical'] ?? null;
    $featured_image = $row_one['featured_image'] ?? null;
    $image_position = $row_one['image_horizontal'] ?? null;
    // Repeater (array of rows)
    $buttons_rows     = $row_one['buttons'] ?? [];
    if (!is_array($buttons_rows)) $buttons_rows = [];

    // Build a clean buttons array you can loop over in markup
    $buttons = [];

    foreach ($buttons_rows as $button_row) {
        $link = $button_row['button'] ?? null;
    
        if (is_array($link) && !empty($link['url'])) {
          $buttons[] = [
            'url'    => $link['url'],
            'title'  => $link['title'] ?? 'Learn more',
            'target' => $link['target'] ?? '_self',
          ];
        }
      }

endif; 
    $classes = ['content-group'];
    if ( $row_one )  $classes[] = 'content-group--row-one';
    if ( $content_position === 'middle')  $classes[] = 'content-group--middle';
    if ( $content_position === 'bottom')  $classes[] = 'content-group--bottom';
    if ( $image_position === 'left')  $classes[] = 'content-group--reverse';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <div class="col-xs-12 col-md-6 content-group__content">
        <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
        <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
        <?php if (!empty($buttons)) : ?>
        <div class="button-pair">
            <?php foreach ($buttons as $btn) : ?>
            <a class="button"
                href="<?php echo esc_url($btn['url']); ?>"
                target="<?php echo esc_attr($btn['target']); ?>"
                <?php echo ($btn['target'] === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html($btn['title']); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-xs-12 col-md-6 content-group__image">
        <?php if( $featured_image ) :
        // Image variables.
        $url = $featured_image['url'];
        $title = $featured_image['title'];
        $alt = $featured_image['alt'];
        $caption = $featured_image['caption'];

        // Thumbnail size attributes.
        $size = 'large';
        $thumb = $featured_image['sizes'][ $size ];
        $width = $featured_image['sizes'][ $size . '-width' ];
        $height = $featured_image['sizes'][ $size . '-height' ]; ?>
        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($alt); ?>" />
        <?php endif; ?>
    </div>
</div>

 <?php
 if( get_row_layout() == 'multi_row_content_image'): 
 $row_two = get_sub_field('row_two');
 $headline       = $row_two['headline'] ?? '';
 $headline_size  = $row_two['headline_size'] ?? '';
 $body           = $row_two['body_copy'] ?? '';
 $content_position = $row_two['content_vertical'] ?? null;
 $image_position = $row_two['image_horizontal'] ?? null;
 $supporting_images = $row_two['supporting_images'] ?? [];
 if (!is_array($supporting_images)) $supporting_images = [];

 $buttons_rows     = $row_two['buttons'] ?? [];
 if (!is_array($buttons_rows)) $buttons_rows = [];

 // Build a clean buttons array you can loop over in markup
 $buttons = [];

 foreach ($buttons_rows as $button_row) {
     $link = $button_row['button'] ?? null;
 
     if (is_array($link) && !empty($link['url'])) {
       $buttons[] = [
         'url'    => $link['url'],
         'title'  => $link['title'] ?? 'Learn more',
         'target' => $link['target'] ?? '_self',
       ];
     }
   }
endif; 
    $classes = ['content-group'];
    if ( $row_two )  $classes[] = 'content-group--row-two';
    if ( $content_position === 'middle')  $classes[] = 'content-group--middle';
    if ( $content_position === 'bottom')  $classes[] = 'content-group--bottom';
    if ( $image_position === 'left')  $classes[] = 'content-group--reverse';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <div class="col-xs-12 col-md-4 content-group__content">
        <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
        <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
        <?php if (!empty($buttons)) : ?>
        <div class="button-pair">
            <?php foreach ($buttons as $btn) : ?>
            <a class="button"
                href="<?php echo esc_url($btn['url']); ?>"
                target="<?php echo esc_attr($btn['target']); ?>"
                <?php echo ($btn['target'] === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html($btn['title']); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($supporting_images)) : ?>
    <ul class="col-xs-12 col-md-8 supporting_images">
            <?php foreach ($supporting_images as $image) : ?>
                <li>
            <figure class="gallery__item">
                <?php
                echo wp_get_attachment_image(
                    $image['ID'],
                    'large',            // change size as needed: thumbnail, medium, large, full, or custom
                    false,
                    [
                    'class'   => 'gallery__img',
                    'loading' => 'lazy',
                    'alt'     => $image['alt'] ?: '', // wp_get_attachment_image usually handles alt, but this is fine too
                    ]
                );
                ?>
            </figure>
            </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
</div>