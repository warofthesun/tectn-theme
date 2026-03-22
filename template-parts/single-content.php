<?php
/**
 * Main content area for single post/singular template.
 * Reusable template part; expects global $post or passed post.
 */
if ( ! have_posts() ) {
	?>
	<article id="post-not-found" class="hentry">
		<header class="article__header">
			<h1><?php _e( 'Oops, Post Not Found!', 'tectn_theme' ); ?></h1>
		</header>
		<section class="entry-content">
			<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'tectn_theme' ); ?></p>
		</section>
		<footer class="article__footer">
			<p><?php _e( 'This is the error message in the single.php template.', 'tectn_theme' ); ?></p>
		</footer>
	</article>
	<?php
	return;
}
while ( have_posts() ) {
	the_post();
	$include_sidebar = function_exists( 'tectn_include_sidebar_on_blog_posts' ) && tectn_include_sidebar_on_blog_posts();
	$content_col     = $include_sidebar ? 'col-xs-12 col-sm-7' : 'col-xs-12 col-md-9';
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cf row' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
		<section class="entry-content cf <?php echo esc_attr( $content_col ); ?>" itemprop="articleBody">
			<?php the_content(); ?>
		</section>
		<?php if ( $include_sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
		<footer class="article__footer">
			<?php the_tags( '<p class="tags"><span class="tags-title">' . __( 'Tags:', 'tectn_theme' ) . '</span> ', ', ', '</p>' ); ?>
		</footer>
	</article>
	<?php
}
