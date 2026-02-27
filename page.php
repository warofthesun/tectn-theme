<!--page-->
<?php get_header(); ?>
<?php if ( is_front_page() || get_field( 'hero_style' ) ) : include get_template_directory() . '/partials/hero/hero.php'; endif; ?>

			<div id="content">

				<div id="inner-content" class="wrap  row">
						<main id="main" class="col-xs-12" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

								<section class="entry-content " itemprop="articleBody">
									<?php if( have_rows('custom_page_content') ): include 'partials/custom-page-content/custom-page-content.php'; endif; ?>	
						
									<?php the_content(); ?>

									<?php if( have_rows('faqs') ): include 'partials/faqs.php'; endif; ?>

									<?php if( have_rows('services') ): include 'partials/services.php'; endif; ?>

									<?php if(have_rows('resources')) : include 'partials/resources.php'; endif; ?>

								</section> <?php // end article section ?>

							</article>

							<?php endwhile; endif; ?>

						</main>

				</div>

			</div>
<?php get_footer(); ?>
