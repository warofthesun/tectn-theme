<!--HERO-LANDING-->
<div class="hero__container hero__container_landing">
	<div class="hero__container_inner">
		<div class="hero__content hero__content_image col-xs-12"  style="background-image: url('<?php if( get_field('hero_image') ) : the_field('hero_image');  endif; ?>')">
		<div class="hero__content hero__content_text col-xs-12 col-md-7">
		
		<svg class="hero__overlay_full_width" viewBox="0 0 1000 600" preserveAspectRatio="none" aria-hidden="true">
			<defs>
				<linearGradient id="heroGrad" x1="1.5" y1="0" x2="0" y2="1">
				<stop offset="0" stop-color="#238c55" stop-opacity="0.70"/>
				<stop offset="0.55" stop-color="#3caa6e" stop-opacity="0.70"/>
				<stop offset="1" stop-color="#f5b049" stop-opacity="0.90"/>
				</linearGradient>
				<linearGradient id="heroGradTwo" x1="1.5" y1="0" x2="0" y2="1">
				<stop offset="0" stop-color="#238c55" stop-opacity="0.95"/>
				<stop offset="0.55" stop-color="#3caa6e" stop-opacity="0.90"/>
				<stop offset="1" stop-color="#f5b049" stop-opacity="0.95"/>
				</linearGradient>

				<!-- Two-layer shadow: contact + ambient -->
				<filter id="layerShadow" x="-35%" y="-35%" width="170%" height="170%">
				<!-- soft ambient shadow -->
				<feDropShadow dx="0" dy="-6" stdDeviation="14" flood-color="#000" flood-opacity="0.14"/>
				<!-- tighter contact shadow -->
				<feDropShadow dx="0" dy="-2" stdDeviation="5"  flood-color="#000" flood-opacity="0.18"/>
				</filter>

				<!-- Gentle highlight (white glow) -->
				<filter id="edgeHighlight" x="-35%" y="-35%" width="170%" height="170%">
				<feDropShadow dx="0" dy="-1" stdDeviation="2" flood-color="#fff" flood-opacity="0.35"/>
				</filter>
			</defs>

			<!-- Front hero curve -->
			<path class="curve curve--back" id="curveBack"
				d="M0,0 H220 C500,140 540,560 760,600 H0 Z"
				fill="url(#heroGrad)"
			/>

			<!-- Middle curve (if you keep it gradient, that's fine; solid reads cleaner) -->
			<path class="curve curve--middle" id="curveMid"
				d="M0,550
				C300,450 720,660 1000,430
				L1000,600 L0,600 Z"
				fill="url(#heroGradTwo)"
			/>

			<!-- Bottom cream curve: apply layered shadow -->
			<path
				d="M0,565
				C350,460 700,690 1000,510
				L1000,610 L0,610 Z"
				fill="#FCF7EE"
				filter="url(#layerShadow)"
			/>

			<!-- Highlight pass: same curve, no fill, just a top edge highlight -->
			<path
				d="M0,565
				C350,460 700,690 1000,510"
				fill="none"
				stroke="rgba(255,255,255,0.55)"
				stroke-width="10"
				stroke-linecap="round"
				filter="url(#edgeHighlight)"
				opacity="0.55"
			/>
			</svg>
			<?php if( get_field('hero_headline') ) : ?>
				<div style="margin-top:auto; margin-bottom:20vh;">
					<h1 class="curve curve--back"><?php the_field('hero_headline');  ?></h1>
				
					<?php if( get_field('hero_paragraph') ) : ?><p><?php the_field('hero_paragraph');  ?></p><?php endif; ?>
				
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
			<?php endif; ?>

			
				
		</div>
		</div>
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