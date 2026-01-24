<!--page-->
<?php get_header(); ?>
<?php if (is_front_page() ) :
	include 'partials/hero/hero-landing.php'; else : $hero_style = get_field('hero_style'); ?>
	<?php if ($hero_style == 'landing'): include 'partials/hero/hero-landing.php'; elseif ($hero_style == 'large'): ?>
	<?php include 'partials/hero/hero-large.php'; ?>
	<?php elseif ($hero_style == 'medium'): ?>
		<?php include 'partials/hero/hero-medium.php'; ?>
	<?php elseif ($hero_style == 'small'): ?>
		<?php include 'partials/hero/hero-small.php'; ?>
	<?php else : endif; ?>
<?php endif; ?>

			<div id="content">

				<div id="inner-content" class="wrap  row">
						<?php 
						$testimonial = get_field('include_sidebar_testimonials');
						$sidebar = get_field('include_sidebar_on_site_pages');
						?>
						<main id="main" class="col-xs-12 <?php if($testimonial || $sidebar) :  ?>col-sm-8<?php endif; ?>" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

								<!--header class="article-header">

									<h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>

									<p class="byline vcard">
										<?php //printf( __( 'Posted', 'trustmfa_theme').' <time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time> '.__( 'by',  'trustmfa_theme').' <span class="author">%3$s</span>', get_the_time('Y-m-j'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?>
									</p>

								</header--> <?php // end article header ?>

								<section class="entry-content " itemprop="articleBody">

									<?php the_content(); ?>

									<?php if( have_rows('faqs') ): include 'partials/faqs.php'; endif; ?>

									<?php if( have_rows('services') ): include 'partials/services.php'; endif; ?>

									<?php if(have_rows('resources')) : include 'partials/resources.php'; endif; ?>

								</section> <?php // end article section ?>

							</article>

							<?php endwhile; endif; ?>

						</main>

						<?php if($testimonial) : ?>
							<?php include 'partials/testimonials/testimonials-sidebar.php' ?>	
						<?php elseif($sidebar) :  ?>
							<?php get_sidebar(); ?>
						<?php endif; ?>

				</div>

			</div>
<?php get_footer(); ?>
