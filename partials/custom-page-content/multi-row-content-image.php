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
    if( get_row_layout() == 'multi_row_content_image'): 
    $row_two = get_sub_field('row_two');
    $headline_r2       = $row_two['headline'] ?? '';
    $headline_size_r2  = $row_two['headline_size'] ?? '';
    $body_r2           = $row_two['body_copy'] ?? '';
    $primary_cta_r2    = $row_two['primary_cta'] ?? null;   
    $secondary_cta_r2  = $row_two['secondary_cta'] ?? null; 
    $supporting_images_r2  = $row_two['supporting_images'] ?? null;
endif; ?>

<?php 
    $classes = ['content_group'];
    if ( $row_one )  $classes[] = 'content_group--row_one';
    if ( $content_position === 'middle')  $classes[] = 'content_group--middle';
    if ( $content_position === 'bottom')  $classes[] = 'content_group--bottom';
    if ( $image_position === 'left')  $classes[] = 'content_group--reverse';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <div class="col-xs-12 col-md-6 content">
        <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
        <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
        <?php if (!empty($buttons)) : ?>
        <div class="button_pair">
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
    <div class="col-xs-12 col-md-6 featured_image">
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
    $classes = ['content_group'];
    if ( $row_two )  $classes[] = 'content_group--row_two';
?>
<div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <div class="col-xs-12 col-md-6">
        <?php if($headline_r2) : ?><<?php echo esc_html($headline_size_r2); ?>><?php echo esc_html($headline_r2); ?></<?php echo esc_html($headline_size_r2); ?>><?php endif; ?>
        <?php if($body_r2) : ?><?php echo wp_kses_post($body_r2); ?><?php endif; ?>
    </div>
    <div class="col-xs-12 col-md-5">
        featured image
    </div>
</div>