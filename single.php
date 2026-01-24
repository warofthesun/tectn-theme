<!--single-->
<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap row">

					<main id="main" class="col-xs-12" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							 <article id="post-<?php the_ID(); ?>" <?php post_class('cf row'); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">
								
							 <div class="hero__post row wrap">
								
								<div class="hero__post_text col-xs-12 col-md-6">
									<?php printf( '<p class="post-category">' . get_the_category_list(', ') ); ?>
								<h1 class="entry-title single-title" itemprop="headline" rel="bookmark"><?php the_title(); ?></h1><p class="byline entry-meta vcard">

									<?php printf( __( 'Posted', 'trustmfa_theme' ).' %1$s %2$s',
									/* the time the post was published */
									'<time class="updated entry-time" datetime="' . get_the_time('Y-m-d') . '" itemprop="datePublished">' . get_the_time(get_option('date_format')) . '</time>',
									/* the author of the post */
									'<span class="by">'.__( 'by', 'trustmfa_theme' ).'</span> <span class="entry-author author" itemprop="author" itemscope itemptype="http://schema.org/Person">' . get_the_author_link( get_the_author_meta( 'ID' ) ) . '</span>'
									); ?>

								</p></div>
								<div class="hero__post_image col-xs-12 col-md-6"> <div class="hero--image"><?php the_post_thumbnail('gallery-image'); ?></div></div>
							</div>

							<section class="entry-content cf <?php if(get_field('include_sidebar_on_blog_posts', 'option')) :  ?>col-xs-12 col-sm-7<?php else : ?>col-xs-12 col-md-9<?php endif; ?>" itemprop="articleBody">
								<?php the_content(); ?>
                			</section>
							<?php if(get_field('include_sidebar_on_blog_posts', 'option')) :  ?>			
							<?php get_sidebar(); ?>
							<?php endif; ?>

                <footer class="article-footer">
                  <?php the_tags( '<p class="tags"><span class="tags-title">' . __( 'Tags:', 'trustmfa_theme' ) . '</span> ', ', ', '</p>' ); ?>

                </footer> <?php // end article footer ?>

              </article> <?php // end article ?>

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
											<p><?php _e( 'This is the error message in the single.php template.', 'trustmfa_theme' ); ?></p>
									</footer>
							</article>

						<?php endif; ?>

					</main>
					

				</div>

			</div>

<?php get_footer(); ?>
