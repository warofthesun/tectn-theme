<?php
/**
 * Blog index: large featured story (split layout).
 *
 * Expects $tectn_featured_post_id (int) in scope from index.php.
 *
 * @package tectn_theme
 */

if ( empty( $tectn_featured_post_id ) ) {
	return;
}

$post = get_post( (int) $tectn_featured_post_id );
if ( ! $post || 'publish' !== $post->post_status || 'post' !== $post->post_type ) {
	return;
}

$post_id   = $post->ID;
$permalink = get_permalink( $post_id );
setup_postdata( $post );

$title        = get_the_title( $post_id );
$date_display = get_the_date( '', $post );
$date_iso     = get_the_date( 'c', $post );

$author_id   = post_type_supports( 'post', 'author' ) ? (int) $post->post_author : 0;
$author_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
$author_url  = $author_id ? get_author_posts_url( $author_id ) : '';

$chip_terms = function_exists( 'tectn_get_post_chip_terms_for_links' )
	? tectn_get_post_chip_terms_for_links( $post_id, 4 )
	: array();

if ( has_excerpt( $post_id ) ) {
	$excerpt_raw = get_post_field( 'post_excerpt', $post_id );
	$excerpt     = apply_filters( 'the_excerpt', $excerpt_raw );
} else {
	$excerpt = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 55, '…' );
}

$image_id = get_post_thumbnail_id( $post_id );
$img_alt  = $image_id ? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';
if ( $img_alt === '' ) {
	$img_alt = $title;
}

$featured_mod = $image_id ? '' : ' c-storiesFeatured--no-image';
?>
<section class="c-storiesFeatured<?php echo esc_attr( $featured_mod ); ?>" aria-labelledby="c-storiesFeatured-title">
	<div class="c-storiesFeatured__inner wrap row">
		<div class="c-storiesFeatured__content col-xs-12 col-md-6">
			<h2 id="c-storiesFeatured-title" class="c-storiesFeatured__title"><a class="c-storiesFeatured__titleLink" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h2>
			<?php if ( $author_name || $date_display ) : ?>
				<p class="c-storiesFeatured__meta">
					<?php if ( $author_name && $author_url ) : ?>
						<span class="c-storiesFeatured__meta-item"><i class="fa-regular fa-circle-user c-storiesFeatured__meta-icon" aria-hidden="true"></i><a class="c-storiesFeatured__authorLink" href="<?php echo esc_url( $author_url ); ?>" rel="author"><?php echo esc_html( $author_name ); ?></a></span>
					<?php endif; ?>
					<?php if ( $author_name && $date_display ) : ?>
						<span class="c-storiesFeatured__meta-gap" aria-hidden="true"></span>
					<?php endif; ?>
					<?php if ( $date_display ) : ?>
						<span class="c-storiesFeatured__meta-item"><i class="fa-regular fa-circle-calendar c-storiesFeatured__meta-icon" aria-hidden="true"></i><time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date_display ); ?></time></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<?php if ( ! empty( $chip_terms ) ) : ?>
				<div class="c-storiesFeatured__chips">
					<?php foreach ( $chip_terms as $term ) : ?>
						<?php
						$tlink = get_term_link( $term );
						if ( is_wp_error( $tlink ) ) {
							continue;
						}
						?>
						<a class="c-chip" href="<?php echo esc_url( $tlink ); ?>"><?php echo esc_html( $term->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $excerpt !== '' ) : ?>
				<p class="c-storiesFeatured__excerpt"><?php echo wp_kses_post( $excerpt ); ?></p>
			<?php endif; ?>
			<a class="c-storiesFeatured__cta" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'Read now', 'tectn_theme' ); ?></a>
		</div>
		<?php if ( $image_id ) : ?>
			<div class="c-storiesFeatured__figure col-xs-12 col-md-6">
				<div class="c-storiesFeatured__media">
					<a class="c-storiesFeatured__mediaLink" href="<?php echo esc_url( $permalink ); ?>">
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'large',
						false,
						array(
							'class'    => 'c-storiesFeatured__img',
							'loading'  => 'eager',
							'decoding' => 'async',
							'alt'      => $img_alt,
						)
					);
					?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
