<?php
/*
 Template Name: Partners Page
*/
?>
<?php get_header(); ?>
<?php include 'partials/hero/hero-medium.php'; ?>

			<div id="content">

				<div id="inner-content" class="wrap  row">

						<main id="main" class="col-xs-12 col-sm-6 col-md-5" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

								<section class="entry-content " itemprop="articleBody">

									<?php the_content(); ?>

								</section> <?php // end article section ?>

							</article>

							<?php endwhile; endif; ?>

						</main>
						
						<div class="col-xs-12 col-sm-6 col-md-7">
                            <?php if( have_rows('partners') ): ?>
									<?php include 'partials/partners.php'; ?>
									<?php endif; ?>
                        </div>

				</div>

			</div>
<?php get_footer(); ?>
