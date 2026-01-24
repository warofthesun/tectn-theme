<!--testimonials-footer-->
<?php 
$footer_testimonials = get_field('choose_testimonials');
if( $footer_testimonials ) : ?>

<div class="testimonials testimonials__background_<?php if(get_field('testimonial_background')) : the_field('testimonial_background'); else : ?>gold<?php endif; ?> testimonial__count_<?php echo $count; ?>">
	<div class="testimonials__content row wrap">
        <?php foreach ( $footer_testimonials as $post) : setup_postdata($post); ?>
                <div class="testimonial">
                    <img class="quote_mark" src="<?php echo get_template_directory_uri() . '/library/images/marker/quote_no_border.png'; ?>">
                        <h3><?php the_content(); ?></h3>
                        <div class="attribution"><?php the_title(); ?></div>
                </div>
                
            <?php endforeach; ?>
    </div>
    <?php wp_reset_postdata(); ?>
</div>
<?php endif; ?>