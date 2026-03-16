<?php
/**
 * Single event template. Title and featured image are shown in the hero only;
 * we strip them from the body content to avoid duplication.
 */
add_filter( 'the_content', 'tectn_single_event_remove_title_and_thumbnail', 1 );

/**
 * Strip the first title (h1) and first featured image from event content.
 * Hero already shows title and image; this runs only on single event views.
 *
 * @param string $content Post content.
 * @return string
 */
function tectn_single_event_remove_title_and_thumbnail( $content ) {
	if ( ! is_singular( 'tribe_events' ) ) {
		return $content;
	}
	remove_filter( 'the_content', 'tectn_single_event_remove_title_and_thumbnail', 1 );

	// Remove first h1 (entry title in content).
	$content = preg_replace( '/<h1[^>]*>.*?<\/h1>\s*/is', '', $content, 1 );

	// Remove first featured image: TEC class, block editor block, or classic post thumbnail wrapper.
	$content = preg_replace( '/<div[^>]*class="[^"]*tribe-events-event-image[^"]*"[^>]*>.*?<\/div>\s*/is', '', $content, 1 );
	$content = preg_replace( '/<figure[^>]*class="[^"]*wp-block-post-featured-image[^"]*"[^>]*>.*?<\/figure>\s*/is', '', $content, 1 );
	$content = preg_replace( '/<div[^>]*class="[^"]*post-thumbnail[^"]*"[^>]*>.*?<\/div>\s*/is', '', $content, 1 );

	return $content;
}

get_header();
if ( tectn_get_hero_config()['show'] ) {
	include get_template_directory() . '/partials/hero/hero.php';
}
?>
			<div id="content">
				<div id="inner-content" class="wrap row">
					<main id="main" class="col-xs-12" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
						<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
							<article id="post-<?php the_ID(); ?>" <?php post_class( 'cf row' ); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">
								<section class="entry-content cf col-xs-12 col-md-9" itemprop="articleBody">
									<?php the_content(); ?>
								</section>
								<footer class="article__footer">
									<?php the_tags( '<p class="tags"><span class="tags-title">' . __( 'Tags:', 'tectn_theme' ) . '</span> ', ', ', '</p>' ); ?>
								</footer>
							</article>
							<?php get_template_part( 'template-parts/related-posts' ); ?>
						<?php endwhile; ?>
						<?php else : ?>
							<article id="post-not-found" class="hentry">
								<header class="article__header">
									<h1><?php _e( 'Oops, Post Not Found!', 'tectn_theme' ); ?></h1>
								</header>
								<section class="entry-content">
									<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'tectn_theme' ); ?></p>
								</section>
								<footer class="article__footer">
									<p><?php _e( 'This is the error message for the single event template.', 'tectn_theme' ); ?></p>
								</footer>
							</article>
						<?php endif; ?>
					</main>
				</div>
			</div>
<?php get_footer(); ?>
