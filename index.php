<!--index-->
<?php get_header(); ?>
<?php include get_template_directory() . '/partials/hero/hero.php'; ?>
			<div id="content">

				<div id="inner-content" class="wrap row">

						<main id="main" class="col-xs-12 <?php if ( get_field( 'include_sidebar_on_blog_page', 'option' ) ) : ?>col-sm-8 col-lg-9<?php endif; ?> row" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<div class="c-storiesPage">
							<?php
							$tectn_featured_post_id = function_exists( 'tectn_get_blog_featured_post_id' ) ? (int) tectn_get_blog_featured_post_id() : 0;
							if ( $tectn_featured_post_id ) {
								include get_template_directory() . '/partials/blog-stories-featured.php';
							}
							?>

							<div class="c-storiesIndex">
							<?php if ( have_posts() ) : ?>
								<div id="tectn-stories-header" class="c-storiesDivider">
									<span class="c-storiesDivider__label"><?php esc_html_e( 'Stories', 'tectn_theme' ); ?></span>
									<span class="c-storiesDivider__line" aria-hidden="true"></span>
								</div>
								<div class="c-posts__grid">
									<?php
									while ( have_posts() ) :
										the_post();
										if ( function_exists( 'tectn_render_blog_post_card' ) ) {
											tectn_render_blog_post_card( $GLOBALS['post'] );
										}
									endwhile;
									?>
								</div>
								<?php starter_page_navi(); ?>

							<?php elseif ( $tectn_featured_post_id ) : ?>
								<p class="c-storiesIndex__empty"><?php esc_html_e( 'No additional posts yet.', 'tectn_theme' ); ?></p>

							<?php else : ?>

									<article id="post-not-found" class="hentry ">
											<header class="article__header">
												<h1><?php esc_html_e( 'Oops, Post Not Found!', 'tectn_theme' ); ?></h1>
										</header>
											<section class="entry-content">
												<p><?php esc_html_e( 'Uh Oh. Something is missing. Try double checking things.', 'tectn_theme' ); ?></p>
										</section>
										<footer class="article__footer">
												<p><?php esc_html_e( 'This is the error message in the index.php template.', 'tectn_theme' ); ?></p>
										</footer>
									</article>

							<?php endif; ?>
							</div>
							</div>

						</main>
						<?php if ( get_field( 'include_sidebar_on_blog_page', 'option' ) ) : ?>
							<?php get_sidebar(); ?>
						<?php endif; ?>

				</div>

			</div>

<?php get_footer(); ?>
