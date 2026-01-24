<!--single-form-->
<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap  row">

						<main id="main" class="col-xs-12 col-sm-8" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class(''); ?> role="article">

								<header class="article-header">

									<h1 class="single-title custom-post-type-title"><?php the_title(); ?></h1>
									<?php if(get_field('form_description')) : ?>
										<div class="form__description"><?php the_field('form_description'); ?></div>
									<?php endif; ?>
									<?php if(get_field('link_to_downloadable_pdf')) : ?>
										<a href="<?php the_field('link_to_downloadable_pdf'); ?>" target="_new">Download PDF</a>
									
									<?php if (get_field('form_code')) : ?> or fill out the form below <?php endif; ?>
										<?php endif; ?>
									

								</header>

								<section class="entry-content ">

									<?php $form = get_field('form_code'); echo do_shortcode($form); ?>

								</section> <!-- end article section -->


							</article>

							<?php endwhile; ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry ">
										<header class="article-header">
											<h1><?php _e( 'Oops, Post Not Found!', 'trustmfa_theme' ); ?></h1>
										</header>
										<section class="entry-content">
											<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'trustmfa_theme' ); ?></p>
										</section>
										<footer class="article-footer">
											<p><?php _e( 'Error Message', 'trustmfa_theme' ); ?></p>
										</footer>
									</article>

							<?php endif; ?>

						</main>

						<?php include 'partials/forms-sidebar.php' ?>

				</div>

			</div>

<?php get_footer(); ?>
