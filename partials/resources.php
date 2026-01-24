<!--resources-->
<?php if( have_rows('resources') ): ?>
    <div class="resources">
        <?php while( have_rows('resources') ) : the_row(); ?>
        <div class="resourse__item">
            <?php if(get_row_layout() == 'resource_link'): ?>
                <?php 
                    $link = get_sub_field('link');
                    if( $link ): 
                        $link_url = $link['url'];
                        $link_title = $link['title'];
                        $link_target = $link['target'] ? $link['target'] : '_self';
                        ?>
                        <a class="resource__link" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"> <h4 class="h4"><?php echo esc_html( $link_title ); ?></h4></a>
                        <?php if(get_sub_field('description')): ?>
                            <div class="resource__description"><?php the_sub_field('description'); ?></div>
                            <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if(get_row_layout() == 'section_divider'): the_sub_field('divider'); endif; ?>
                        </div>
        <?php endwhile; ?>
                        </div>
<?php endif; ?>