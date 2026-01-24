<!--forms-sidebar-->

<?php 
$current_post_id = get_the_ID();
$query = new WP_Query( [
    'post_type'      => 'form',
    'nopaging'       => true,
    'post__not_in'   => array( $current_post_id ),
] ); ?>

<?php if ( $query->have_posts() ) : ?>
    
<div class="sidebar forms forms__sidebar col-xs-12 col-sm-4" role="complementary">
    
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>

                    <div class="form__item">
                        <a href="<?php the_permalink(); ?>" class="form__link"><h4><?php the_title(); ?></h4></a>
                    </div>

                <?php endwhile; ?>

</div>
<?php endif; ?>

<?php wp_reset_postdata(); ?>