<!--services-->
<?php if( have_rows('services') ): ?>
    <div class="services">
        <?php while( have_rows('services') ) : the_row(); ?>
        <div class="service">
            <img src="<?php the_sub_field('icon'); ?>">
            <div class="h5 service__text"><?php the_sub_field('service'); ?></div>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>