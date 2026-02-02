<!--page-->
<?php get_header(); ?>
<?php if (is_front_page() ) : include 'partials/hero/hero-headline.php'; else : $hero_style = get_field('hero_style'); ?>
	<?php if ($hero_style == 'landing'): include 'partials/hero/hero-landing.php'; ?>
	<?php elseif ($hero_style == 'large'): include 'partials/hero/hero-large.php'; ?>
	<?php elseif ($hero_style == 'medium'): include 'partials/hero/hero-medium.php'; ?>
	<?php elseif ($hero_style == 'small'): include 'partials/hero/hero-small.php'; ?>
	<?php else : endif; ?>
<?php endif; ?>

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
