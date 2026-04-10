<?php
/**
 * Related posts section: same post type, reuse posts-grid card markup and background.
 * Heading and background image come from the Post Settings options page (Site Settings > Post Settings),
 * per post type, via tectn_get_related_posts_settings(). When no background image is set, uses solid
 * $primary-500 with the same gradient overlay.
 *
 * @param array $args Optional. post_id, post_type, limit (default 3), heading, background_image_url.
 */
$args = wp_parse_args( isset( $args ) ? $args : array(), array(
	'post_id'   => get_the_ID(),
	'post_type' => get_post_type(),
	'limit'     => 3,
	'heading'   => '',
	'background_image_url' => '',
) );
$post_id   = (int) $args['post_id'];
$post_type = $args['post_type'] ? $args['post_type'] : get_post_type( $post_id );
$limit     = max( 1, min( 10, (int) $args['limit'] ) );

if ( ! $args['heading'] && function_exists( 'tectn_get_related_posts_settings' ) ) {
	$settings = tectn_get_related_posts_settings( $post_type );
	$heading  = $settings['heading'];
	$bg_url   = $settings['background_image_url'];
} else {
	$heading = $args['heading'] ? $args['heading'] : __( 'Related Posts', 'tectn_theme' );
	$bg_url  = $args['background_image_url'] ? $args['background_image_url'] : '';
}

$related = function_exists( 'tectn_get_related_posts' ) ? tectn_get_related_posts( $post_id, $post_type, $limit ) : array();
if ( empty( $related ) ) {
	return;
}

$section_id = 'related-posts-' . $post_id;
$has_bg_img = ! empty( $bg_url );
$effective_bg_max_h = ( count( $related ) >= 1 && count( $related ) <= 3 ) ? 600 : 800;
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="c-posts c-posts--related alignfull" style="--posts-bg-max-h: <?php echo esc_attr( $effective_bg_max_h ); ?>px;">
	<h2 class="c-posts__heading c-posts__heading--related"><?php echo esc_html( $heading ); ?></h2>
	<!-- Background: image or solid + same gradient overlay as posts-grid (below heading in DOM so title is never obscured) -->
	<div class="c-posts__bg c-posts__bg--related" aria-hidden="true">
		<?php if ( $has_bg_img ) : ?>
			<div class="c-posts__bgImage" style="background-image:url('<?php echo esc_url( $bg_url ); ?>')"></div>
		<?php else : ?>
			<div class="c-posts__bgSolid c-posts__bgSolid--related" aria-hidden="true"></div>
		<?php endif; ?>

		<svg class="c-posts__overlaySvg c-posts__overlaySvg--front" viewBox="0 0 1440 1200" preserveAspectRatio="none" aria-hidden="true">
			<defs>
				<linearGradient id="gradientFrontRelated" x1="0" y1="0" x2="1" y2="1">
					<stop offset="0%" stop-color="rgba(185,220,105,0.70)" />
					<stop offset="55%" stop-color="rgba(39,140,85,0.55)" />
					<stop offset="100%" stop-color="rgba(20,110,60,0.25)" />
				</linearGradient>
				<filter id="softBlurFrontRelated" x="-20%" y="-20%" width="140%" height="140%">
					<feGaussianBlur class="js-blurFront" in="SourceGraphic" stdDeviation="12" />
				</filter>
				<filter id="grainRelated" x="-20%" y="-20%" width="140%" height="140%">
					<feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" result="noise"/>
					<feColorMatrix in="noise" type="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 0.06 0" result="grainAlpha"/>
					<feBlend in="SourceGraphic" in2="grainAlpha" mode="multiply"/>
				</filter>
			</defs>
			<path d="M 0 300 C 200 100, 1200 800, 1440 300 L 1500 1200 L -50 1200 Z" fill="url(#gradientFrontRelated)" opacity="0.55" filter="url(#softBlurFrontRelated)" />
			<g filter="url(#grainRelated)">
				<path d="M 0 300 C 200 100, 1200 800, 1440 300 L 1500 1200 L -50 1200 Z" fill="url(#gradientFrontRelated)" opacity="0.85" />
			</g>
		</svg>

		<svg class="c-posts__overlaySvg c-posts__overlaySvg--back" viewBox="0 0 1440 600" preserveAspectRatio="none" aria-hidden="true">
			<defs>
				<radialGradient id="gradientBackRelated" cx="75%" cy="20%" r="85%">
					<stop offset="0%" stop-color="rgba(236,186,39,0.75)" />
					<stop offset="100%" stop-color="rgba(105,143,61,0.70)" />
				</radialGradient>
				<filter id="softBlurBackRelated" x="-20%" y="-20%" width="140%" height="140%">
					<feGaussianBlur class="js-blurBack" in="SourceGraphic" stdDeviation="8" />
				</filter>
				<filter id="grainBackRelated" x="-20%" y="-20%" width="140%" height="140%">
					<feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" result="noise"/>
					<feColorMatrix in="noise" type="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 0.06 0" result="grainAlpha"/>
					<feBlend in="SourceGraphic" in2="grainAlpha" mode="multiply"/>
				</filter>
			</defs>
			<path d="M 0 300 C 0 300, 200 100, 700 300 C 700 300, 1180 500, 1500 400 L 1500 0 L -50 0 Z" fill="url(#gradientBackRelated)" opacity="0.55" filter="url(#softBlurBackRelated)" />
			<g filter="url(#grainBackRelated)">
				<path d="M 0 300 C 0 300, 200 100, 700 300 C 700 300, 1180 500, 1500 400 L 1500 0 L -50 0 Z" fill="url(#gradientBackRelated)" opacity="0.85" />
			</g>
		</svg>

		<span class="c-posts__wave c-posts__wave--top" aria-hidden="true"></span>
	</div>

	<div class="c-posts__inner l-container wrap">
		<?php
		$grid_count = count( $related );
		$grid_class = ( $grid_count <= 2 ) ? ' c-posts__grid--count-' . $grid_count : '';
		?>
		<div class="c-posts__grid<?php echo esc_attr( $grid_class ); ?>">
			<?php foreach ( $related as $p ) : ?>
				<?php
				$p_id      = $p->ID;
				$date      = ( $p->post_type === 'tribe_events' && function_exists( 'tectn_get_event_post_card_date' ) )
					? tectn_get_event_post_card_date( $p_id, 'm/d/y' )
					: get_the_date( 'm/d/y', $p_id );
				$title     = get_the_title( $p_id );
				$permalink = get_permalink( $p_id );
				$img_url   = tectn_get_post_card_image_url( $p_id );

				$categories = get_the_category( $p_id );
				$tags       = get_the_tags( $p_id );
				$chips      = array();
				if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
					foreach ( $categories as $cat ) {
						$chips[] = $cat->name;
					}
				}
				if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
					foreach ( $tags as $tag ) {
						$chips[] = $tag->name;
					}
				}
				$chips = array_values( array_unique( array_filter( $chips ) ) );
				$chips = array_slice( $chips, 0, 3 );
				?>
				<article class="c-postCard">
					<a class="c-postCard__link" href="<?php echo esc_url( $permalink ); ?>">
						<div class="c-postCard__media">
							<?php if ( $img_url ) : ?>
								<img class="c-postCard__img" src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
							<?php endif; ?>
						</div>
						<div class="c-postCard__body">
							<h5 class="c-postCard__meta"><?php echo esc_html( $date ); ?></h5>
							<h3 class="c-postCard__title"><?php echo esc_html( $title ); ?></h3>
							<?php if ( ! empty( $chips ) ) : ?>
								<div class="c-postCard__chips">
									<?php foreach ( $chips as $chip ) : ?>
										<span class="c-chip"><?php echo esc_html( $chip ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<span class="c-postCard__cta"><?php esc_html_e( 'READ NOW', 'tectn_theme' ); ?></span>
						</div>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
