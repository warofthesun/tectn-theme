<!--archive-->
<?php get_header(); ?>
<?php if ( tectn_get_hero_config()['show'] ) { include get_template_directory() . '/partials/hero/hero.php'; } ?>

<?php
if ( function_exists( 'tectn_is_events_listing_view' ) && tectn_is_events_listing_view() && function_exists( 'tectn_get_events_option' ) ) {
	$intro_heading = tectn_get_events_option( 'events_intro_heading' );
	$intro_body    = tectn_get_events_option( 'events_intro_body' );
	$intro_heading = is_string( $intro_heading ) ? trim( $intro_heading ) : '';
	$intro_body    = is_string( $intro_body ) ? trim( $intro_body ) : '';
	if ( $intro_heading !== '' || $intro_body !== '' ) {
		echo '<div class="events-intro wrap row"><div class="events-intro__inner col-xs-12">';
		if ( $intro_heading !== '' ) {
			echo '<h2 class="events-intro__heading">' . esc_html( $intro_heading ) . '</h2>';
		}
		if ( $intro_body !== '' ) {
			echo '<div class="events-intro__body">' . wp_kses_post( $intro_body ) . '</div>';
		}
		echo '</div></div>';
	}
}
?>

			<div id="content">

				<div id="inner-content" class="wrap  row">

						<main id="main" class="col-xs-12 col-sm-8 col-lg-9 " role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article">

								<header class="entry-header article__header">

									<h3 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
									<p class="byline entry-meta vcard">
										<?php printf( __( 'Posted', 'tectn_theme' ).' %1$s %2$s',
                  							     /* the time the post was published */
                  							     '<time class="updated entry-time" datetime="' . get_the_time('Y-m-d') . '" itemprop="datePublished">' . get_the_time(get_option('date_format')) . '</time>',
                       								/* the author of the post */
                       								'<span class="by">'.__('by', 'tectn_theme').'</span> <span class="entry-author author" itemprop="author" itemscope itemptype="http://schema.org/Person">' . get_the_author_link( get_the_author_meta( 'ID' ) ) . '</span>'
                    							); ?>
									</p>

								</header>

								<section class="entry-content ">

									<?php the_post_thumbnail( 'tectn-thumb-300' ); ?>

									<?php the_excerpt(); ?>

								</section>

								<footer class="article__footer">

								</footer>

							</article>

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
												<p><?php _e( 'This is the error message in the archive.php template.', 'tectn_theme' ); ?></p>
										</footer>
									</article>

							<?php endif; ?>

						</main>

					<?php get_sidebar(); ?>

				</div>

			</div>

<?php get_footer(); ?>
