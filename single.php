<!--single-->
<?php get_header(); ?>
<?php if ( tectn_get_hero_config()['show'] ) { include get_template_directory() . '/partials/hero/hero.php'; } ?>

			<div id="content">

				<div id="inner-content" class="wrap row">

					<main id="main" class="col-xs-12" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							 <article id="post-<?php the_ID(); ?>" <?php post_class('cf row'); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">

							<section class="entry-content cf <?php if(get_field('include_sidebar_on_blog_posts', 'option')) :  ?>col-xs-12 col-sm-7<?php else : ?>col-xs-12 col-md-9<?php endif; ?>" itemprop="articleBody">
								<?php the_content(); ?>
                			</section>
							<?php if(get_field('include_sidebar_on_blog_posts', 'option')) :  ?>			
							<?php get_sidebar(); ?>
							<?php endif; ?>

                <footer class="article__footer">
                  <?php the_tags( '<p class="tags"><span class="tags-title">' . __( 'Tags:', 'tectn_theme' ) . '</span> ', ', ', '</p>' ); ?>

                </footer> <?php // end article footer ?>

              </article> <?php // end article ?>

						<?php
						$current_post_type = get_post_type();
						if ( in_array( $current_post_type, array( 'post', 'tribe_events' ), true ) ) {
							// Heading and background from Site Settings > Post Settings (per post type).
							get_template_part( 'template-parts/related-posts' );
						}
						?>

						<?php endwhile; ?>

						<?php else : ?>

							<article id="post-not-found" class="hentry ">
									<header class="article__header">
										<h1><?php _e( 'Oops, Post Not Found!', 'tectn_theme' ); ?></h1>
									</header>
									<section class="entry-content">
										<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'tectn_theme' ); ?></p>
									</section>
									<footer class="article__footer">
											<p><?php _e( 'This is the error message in the single.php template.', 'tectn_theme' ); ?></p>
									</footer>
							</article>

						<?php endif; ?>

					</main>
					

				</div>

			</div>

<?php get_footer(); ?>
