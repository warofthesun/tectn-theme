<?php 
    /**
     * Headline group template, headline, body copy, CTAs
     *
     * @param array $block The block settings and attributes.
     */


    $headline       = get_field('headline');
    $headline_size  = get_field('headline_size');
    $body           = get_field('body_copy');

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
        <?php $partial_path = get_theme_file_path('/partials/button_pair.php'); ?>
        <?php include $partial_path; ?>
    </div>
</div>
