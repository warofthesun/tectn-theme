<!-- custom-page-content-->
<?php if( have_rows('custom_page_content')) : ?>
    <?php while( have_rows('custom_page_content')) : the_row(); 
        if( get_row_layout() == 'multi_row_content_image'): include 'multi-row-content-image.php'; endif; ?>

    <?php endwhile; ?>
<?php endif; ?>