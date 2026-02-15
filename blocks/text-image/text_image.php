<?php 
/**
 * Text + Image template.
 *
 * @param array $block The block settings and attributes.
 */

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
        $grid_class = 'image_grid image_grid--one';
      } elseif ($count === 2) {
        $text_col  = 'col-xs-12 col-md-4';
        $image_col = 'col-xs-12 col-md-8';
        $grid_class = 'image_grid image_grid--two';
      } elseif ($count === 3) {
        $text_col  = 'col-xs-12 col-md-4';
        $image_col = 'col-xs-12 col-md-8';
        $grid_class = 'image_grid image_grid--three';
      } else {
        // Optional: fallback if no images (choose whatever makes sense)
        $text_col  = 'col-xs-12 col-md-12';
        $image_col = 'd-none'; // or 'col-xs-12 col-md-12' and show a placeholder
      }
    
    $buttons     = get_field('buttons');

    $classes = ['content_group'];
    if ( $content_position === 'middle')  $classes[] = 'content_group--middle';
    if ( $content_position === 'bottom')  $classes[] = 'content_group--bottom';
    if ( $image_position === 'left')  $classes[] = 'content_group--reverse';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <div class="<?= esc_attr($text_col); ?> content">
        <?php if($headline) : ?><<?php echo esc_html($headline_size); ?>><?php echo esc_html($headline); ?></<?php echo esc_html($headline_size); ?>><?php endif; ?>
        <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
            <?php if (have_rows('buttons')) : ?>
  <div class="button_pair">
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
    <div class="<?= esc_attr($image_col); ?>">
    <?php if( $images ) : ?>
        <ul class="image_grid <?= esc_attr($grid_class); ?>">
            <?php if ($count === 3): ?>

                <li class="image_grid__item image_grid__item--1">
                <?php
                $img = $images[0];
                $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                ?>
                <img class="image_grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                </li>

                <li class="image_grid__right">
                <?php for ($i = 1; $i < 3; $i++):
                    $img = $images[$i];
                    $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                    $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                ?>
                    <div class="image_grid__right-item image_grid__right-item--<?= $i+1; ?>">
                    <img class="image_grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                    </div>
                <?php endfor; ?>
                </li>

            <?php else: ?>
                <?php foreach ($images as $i => $img):
                $url = is_array($img) ? $img['url'] : wp_get_attachment_url($img);
                $alt = is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true);
                ?>
                <li class="image_grid__item image_grid__item--<?= $i+1; ?>">
                    <img class="image_grid__img" src="<?= esc_url($url); ?>" alt="<?= esc_attr($alt); ?>">
                </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>