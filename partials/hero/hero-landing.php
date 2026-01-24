<!--HERO-LANDING-->
<div class="hero__container hero__container_landing">
	<div class="hero__container_inner">
		<div class="hero__content hero__content_image col-xs-12"  style="background-image: url('<?php if( get_field('hero_image') ) : the_field('hero_image');  endif; ?>')">
		<div class="hero__content hero__content_text col-xs-12 col-md-7">
			<div><?php if( get_field('hero_headline') ) : the_field('hero_headline');  endif; ?></div>
			<p><?php if( get_field('hero_paragraph') ) : the_field('hero_paragraph');  endif; ?></p>
			<?php
				// Check rows exists.
					if( have_rows('hero_cta') ): ?>
					<div class="button_pair">
						<?php
						// Loop through rows.
						while( have_rows('hero_cta') ) : the_row();

						$link = get_sub_field('hero_cta_button');
						if( $link ): 
							$link_url = $link['url'];
							$link_title = $link['title'];
							$link_target = $link['target'] ? $link['target'] : '_self';
							?>
							<a class="button" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
						<?php endif; 

						// End loop.
						endwhile; ?>
					</div>
					<?php // No value.
					else :
						// Do something...
					endif; ?>
		</div>
		<div class="hero__content hero__content_text-angle col-xs-12 col-md-4">
			<?php echo file_get_contents( get_template_directory() . '/library/images/hero_landing_gold_angle.svg' ); ?>
		</div>
		</div>
	</div>
	<div class="content_curve__up">
		<?php echo file_get_contents( get_template_directory() . '/library/images/content_curve__up.svg' ); ?>
	</div>

	<?php if(get_field('include_featured_post')) :?>
		<?php
			// Set up arguments for WP_Query
			$args = array(
				'tag' => 'featured',
				'posts_per_page' => 1,
			);

			// Create a new WP_Query object
			$the_query = new WP_Query( $args );

			// Check if there are posts and start the loop
			if ( $the_query->have_posts() ) : ?>
	<div class="hero__featured_post">

   			 <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

       
								<article id="post-<?php the_ID(); ?>" <?php post_class( ' single-post' ); ?> role="article" >
									
									<header class="article-header">
										<div class="article-meta">
										<p class="post-category">Update</p>
										
										</div>
										<div class="entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></div>
										

									</header>
									<div class="hero--image">
										<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
											<?php the_post_thumbnail('gallery-image'); ?>
										</a>
									</div>

								</article>

		<?php endwhile; else : ?>
		</div>
		<?php endif; ?>

		<?php
		// Restore original post data
		wp_reset_postdata();
		?>


	<?php endif; ?>
</div>
</div>