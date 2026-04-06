<!--page-->
<?php get_header(); ?>
<?php if ( tectn_get_hero_config()['show'] ) { include get_template_directory() . '/partials/hero/hero.php'; } ?>

<?php
// Events Calendar (TEC default template uses page.php + tribe_is_event_query): intro from Site Settings > Events.
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
						<main id="main" class="col-xs-12" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

								<section class="entry-content " itemprop="articleBody">
									<?php if( have_rows('custom_page_content') ): include 'partials/custom-page-content/custom-page-content.php'; endif; ?>	
						
									<?php the_content(); ?>

								</section> <?php // end article section ?>

							</article>

							<?php endwhile; endif; ?>

						</main>

				</div>

			</div>
<?php get_footer(); ?>
