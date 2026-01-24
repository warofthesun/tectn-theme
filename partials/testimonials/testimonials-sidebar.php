<!--testimonials-sidebar-->
<?php 
$sidebar_testimonials = get_field('choose_sidebar_testimonials');
if( $sidebar_testimonials ) : ?>
<div  class="sidebar testimonials testimonials__sidebar col-xs-12 col-sm-4" role="complementary">
    <ul>
	<?php foreach ( $sidebar_testimonials as $post) : setup_postdata($post); ?>
    <li class="testimonial">
        <img class="quote_mark" src="<?php echo get_template_directory_uri() . '/library/images/marker/quote.png'; ?>">
        <div class="testimonial_content">
            <h3><?php the_content(); ?></h3>
            <div class="attribution"><?php the_title(); ?></div>
        </div>
    </li>
    <?php endforeach; ?>
    </ul>
    <?php  wp_reset_postdata(); ?>
</div>
<?php endif; ?>
                