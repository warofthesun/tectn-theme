<?php
/**
 * Image Slider block
 * Supports multiple slider styles; v1 = table of contents (list left, image right). List items from gallery image titles; one image per item.
 *
 * @param array $block The block settings and attributes.
 */

$is_preview = ! empty( $block['data']['is_preview'] );

if ( $is_preview ) {
	?>
	<div class="c-slider c-slider--preview" style="padding:2em;background:#f5f5f5;border:1px solid #ddd;">
		<p style="margin:0;color:#666;">Image Slider — Add a headline, body copy, and gallery. Each image title becomes a list item.</p>
	</div>
	<?php
	return;
}

$slider_type = get_field( 'slider_type' ) ?: 'table_of_contents';
$headline    = get_field( 'headline' );
$body        = get_field( 'body' );
$gallery     = get_field( 'gallery' );

$items = array();
if ( is_array( $gallery ) && ! empty( $gallery ) ) {
	foreach ( $gallery as $img ) {
		$url   = isset( $img['url'] ) ? $img['url'] : '';
		$title = isset( $img['title'] ) ? $img['title'] : '';
		if ( $url !== '' ) {
			$items[] = array(
				'url'   => $url,
				'title' => $title !== '' ? $title : __( 'Untitled', 'tectn_theme' ),
			);
		}
	}
}

$block_id = isset( $block['id'] ) ? $block['id'] : 'slider-' . wp_rand( 1000, 9999 );
$align    = ! empty( $block['align'] ) ? ' align' . $block['align'] : '';

if ( empty( $items ) ) {
	echo '<div class="c-slider c-slider--empty' . esc_attr( $align ) . '"><p class="c-slider__empty">' . esc_html__( 'Add images to the gallery. Each image title will become a clickable list item.', 'tectn_theme' ) . '</p></div>';
	return;
}

if ( $slider_type !== 'table_of_contents' ) {
	// Future slider types (navigation, slideshow) will be added here.
	echo '<div class="c-slider c-slider--empty' . esc_attr( $align ) . '"><p class="c-slider__empty">' . esc_html__( 'This slider style is not yet available.', 'tectn_theme' ) . '</p></div>';
	return;
}

// Enqueue view script on front (ACF-rendered blocks don't always get viewScript enqueued)
if ( ! is_admin() ) {
	$block_path = get_template_directory() . '/blocks/slider';
	$block_uri  = get_template_directory_uri() . '/blocks/slider';
	wp_enqueue_script(
		'tectn-slider-view',
		$block_uri . '/view.js',
		array(),
		file_exists( $block_path . '/view.js' ) ? filemtime( $block_path . '/view.js' ) : null,
		true
	);
}
?>
<div class="c-slider c-slider--toc<?php echo esc_attr( $align ); ?>"
	 id="<?php echo esc_attr( $block_id ); ?>"
	 data-slider-type="<?php echo esc_attr( $slider_type ); ?>"
	 data-items="<?php echo esc_attr( wp_json_encode( $items ) ); ?>"
	 role="region"
	 aria-label="<?php esc_attr_e( 'Image slider', 'tectn_theme' ); ?>">

	<?php if ( $headline !== '' ) : ?>
		<h2 class="c-slider__headline"><?php echo esc_html( $headline ); ?></h2>
	<?php endif; ?>

	<?php if ( $body !== '' ) : ?>
		<div class="c-slider__body"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
	<?php endif; ?>

	<div class="c-slider__content">
		<nav class="c-slider__list" aria-label="<?php esc_attr_e( 'Choose item', 'tectn_theme' ); ?>">
			<ul class="c-slider__list-inner">
				<?php foreach ( $items as $i => $item ) : ?>
					<li>
						<button type="button"
								class="c-slider__list-btn<?php echo $i === 0 ? ' c-slider__list-btn--active' : ''; ?>"
								data-index="<?php echo (int) $i; ?>"
								aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"
								aria-label="<?php echo esc_attr( $item['title'] ); ?>">
							<?php echo esc_html( $item['title'] ); ?>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="c-slider__panel">
			<div class="c-slider__image-wrap">
				<?php $first = $items[0]; ?>
				<img src="<?php echo esc_url( $first['url'] ); ?>"
					 alt="<?php echo esc_attr( $first['title'] ); ?>"
					 class="c-slider__image"
					 data-slider-image>
			</div>
		</div>
	</div>
</div>
