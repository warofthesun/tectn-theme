<?php 
    /**
     * Headline group template, headline, body copy, CTAs
     *
     * @param array $block The block settings and attributes.
     */

    $preheader         = get_field('preheader');
    $headline          = get_field('headline');
    $headline_size     = get_field('headline_size');
    $on_dark           = (bool) get_field('on_dark_background');
    $headline_parsed   = function_exists('tectn_headline_tag_and_class') ? tectn_headline_tag_and_class( $headline_size, '' ) : array( 'tag' => 'h2', 'class' => '' );
    $body              = get_field('body_copy');

    $is_preview = !empty($block['data']['is_preview']);

    if ($is_preview) {
    $preview = get_template_directory_uri() . '/blocks/headline-group/preview.png';
    echo '<img src="' . esc_url($preview) . '" style="width:100%;height:auto;display:block;" alt="">';
    return;
    }
?>

<div class="row">
    <div class="col-xs-12 c-headline-group">
    <?php if ( $preheader ) : ?><h5 class="c-headline-group__preheader<?php echo $on_dark ? ' light' : ''; ?>"><?php echo esc_html( $preheader ); ?></h5><?php endif; ?>
    <?php if ( $headline ) : ?><<?php echo esc_attr( $headline_parsed['tag'] ); ?> class="<?php echo esc_attr( trim( $headline_parsed['class'] . ( $on_dark ? ' light' : '' ) ) ); ?>"><?php echo esc_html( $headline ); ?></<?php echo esc_attr( $headline_parsed['tag'] ); ?>><?php endif; ?>
    <?php if($body) : ?><?php echo wp_kses_post($body); ?><?php endif; ?>
        <?php $partial_path = get_theme_file_path('/partials/button_pair.php'); ?>
        <?php include $partial_path; ?>
    </div>
</div>
