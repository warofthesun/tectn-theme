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
    // Repeater (array of rows)
    $buttons     = get_field('buttons');

    $classes = ['content_group'];
    if ( $content_position === 'middle')  $classes[] = 'content_group--middle';
    if ( $content_position === 'bottom')  $classes[] = 'content_group--bottom';
    if ( $image_position === 'left')  $classes[] = 'content_group--reverse';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)); ?> row">
    <div class="col-xs-12 col-md-6 content">
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
         <?php echo ($target === '_blank') ? 'rel="noopener noreferrer"' : ''; ?>>button
        <?php echo esc_html($title); ?>
      </a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
    </div>
    <div class="col-xs-12 col-md-6 featured_image">
    <?php if( $images ): ?>
            <ul>
                <?php foreach( $images as $image ): ?>
                    <li>
                        <img src="<?php echo esc_url($image['sizes']['large']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>