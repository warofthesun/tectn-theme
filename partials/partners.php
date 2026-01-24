<!--partners-->
<?php if (get_field('include_highlighted_partners_section')) : ?>
<?php if( have_rows('highlighted_partners') ): ?>
<div class="partners__container partners__container_highlighted row"> 
    <?php while ( have_rows('highlighted_partners') ) : the_row(); ?>
    <?php $link = get_sub_field('partner_website'); ?>
    <?php if( $link ): $link_url = $link['url']; $link_target = $link['target'] ? $link['target'] : '_self';?>
    <a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php endif; ?>
    <div class="partners">
        <?php
            $image = get_sub_field('partner_logo');
            $alt_text = get_sub_field('partner_name');

            if( !empty($image) ):
            // vars
                $url = $image['url'];
                $title = $image['title'];
                $caption = $image['caption'];

                // thumbnail
                $size = 'large';
                $thumb = $image['sizes'][ $size ];
                $width = $image['sizes'][ $size . '-width' ];
                $height = $image['sizes'][ $size . '-height' ]; ?>

                
                <img src="<?php echo $thumb; ?>" alt="<?php echo $alt_text; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" />
                

            <?php endif; ?>
    </div>
    <?php if( $link ): ?></a><?php endif; ?>
    <?php endwhile; ?>
</div>

<?php else : endif; ?> 
<div class="partners callout_text">
    <?php if(get_field('highlighted_partners_callout_text')): the_field('highlighted_partners_callout_text'); endif; ?>
</div>
<?php endif; ?>

<?php if( have_rows('partners') ): ?>


<div class="partners__container"> 
    <?php while ( have_rows('partners') ) : the_row(); ?>
    <?php $link = get_sub_field('partner_website'); ?>
    <?php if( $link ): $link_url = $link['url']; $link_target = $link['target'] ? $link['target'] : '_self';?>
    <a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php endif; ?>
    <div class="partners">
        <?php
            $image = get_sub_field('partner_logo');
            $alt_text = get_sub_field('partner_name');
            

            if( !empty($image) ):
            // vars
                $url = $image['url'];
                $title = $image['title'];
                $caption = $image['caption'];

                // thumbnail
                $size = 'large';
                $thumb = $image['sizes'][ $size ];
                $width = $image['sizes'][ $size . '-width' ];
                $height = $image['sizes'][ $size . '-height' ]; ?>

                
                <img src="<?php echo $thumb; ?>" alt="<?php echo $alt_text; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" />
            

            <?php endif; ?>
    </div>
    <?php if( $link ): ?></a><?php endif; ?>
    <?php endwhile; ?>
</div>

<?php else : endif; ?> 