<!--index-->
<?php get_header(); ?>
<?php include 'partials/hero/hero-small.php'; ?>
			<div id="content">

				<div id="inner-content" class="wrap row">

						<main id="main" class="col-xs-12 <?php if(get_field('include_sidebar_on_blog_page', 'option')) :  ?>col-sm-8 col-lg-9<?php endif; ?> row" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							<div class="gallery-item col-xs-12 <?php if(get_field('include_sidebar_on_blog_page', 'option')) :  ?>col-md-12 col-lg-6<?php else :?>col-sm-6 col-lg-4<?php endif; ?>">
								<article id="post-<?php the_ID(); ?>" <?php post_class( ' single-post' ); ?> role="article" >
									<div class="hero--image">
										<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
											<?php the_post_thumbnail('gallery-image'); ?>
										</a>
									</div>
									<header class="article__header">
										<div class="article__meta">
										<?php printf( '<p class="post-category">' . get_the_category_list(', ') ); ?>
										<p class="byline entry-meta vcard">
											<?php printf( __( '', 'tectn_theme' ).' %1$s', '<time class="updated entry-time" datetime="' . get_the_time('Y-m-d') . '" itemprop="datePublished">' . get_the_time(get_option('date_format')) . '</time>',); ?>
										</p>
										</div>
										<h1 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
										

									</header>

								</article>
								<a href="<?php the_permalink() ?>" class="c-button-pair__button c-button-pair__button--secondary c-button-pair__button--text">Read more <i class="fa-solid fa-right"></i></a>
									<!--end gallery item-->
								</div>
							<?php endwhile; ?>

									<?php starter_page_navi(); ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry ">
											<header class="article__header">
												<h1><?php _e( 'Oops, Post Not Found!', 'tectn_theme' ); ?></h1>
										</header>
											<section class="entry-content">
												<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'tectn_theme' ); ?></p>
										</section>
										<footer class="article__footer">
												<p><?php _e( 'This is the error message in the index.php template.', 'tectn_theme' ); ?></p>
										</footer>
									</article>

							<?php endif; ?>


						</main>
						<?php if(get_field('include_sidebar_on_blog_page', 'option')) :  ?>
							<?php get_sidebar(); ?>
						<?php endif; ?>

				</div>

			</div>

<?php get_footer(); ?>
