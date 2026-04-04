<?php
/**
 * Image Slider block
 * Supports multiple slider styles; v1 = table of contents (list left, image right). List items from gallery image titles; one image per item.
 *
 * @param array $block The block settings and attributes.
 */

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_editor_context =
	is_admin() ||
	( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST );

// Block inserter thumbnails use the REST block-renderer; ACF often omits $is_preview and strips
// non-field keys from data. Use a registered top-level attribute (block.json) so the flag survives.
$is_inserter_preview =
	! empty( $block['inserterPreview'] ) || ! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	$variant = isset( $block_data['slider_type'] ) ? sanitize_key( (string) $block_data['slider_type'] ) : '';
	$map     = array(
		'table_of_contents' => 'preview-table-of-contents.png',
		'horizontal'        => 'preview-horizontal.png',
		'slideshow'         => 'preview-slideshow.png',
	);
	$file = isset( $map[ $variant ] ) ? $map[ $variant ] : 'preview.png';
	$src  = get_template_directory_uri() . '/blocks/slider/' . $file;
	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

$slider_type    = get_field( 'slider_type' ) ?: 'table_of_contents';
$headline       = get_field( 'headline' );
$headline_size  = get_field( 'headline_size' ) ?: 'h2';
$preheader      = get_field( 'preheader' ); // Same preheader pattern as headline-group: output as <h5 class="c-headline-group__preheader">
$on_dark        = (bool) get_field( 'on_dark_background' );
$body           = get_field( 'body' );
$autoplay      = (bool) get_field( 'autoplay' );
if ( $slider_type === 'slideshow' ) {
	$autoplay = true;
}
$show_captions = (bool) get_field( 'show_captions' );
$list_item_icon = get_field( 'list_item_icon' );
$gallery        = get_field( 'gallery' );

$list_icon_html = '';
if ( is_array( $list_item_icon ) && ! empty( $list_item_icon['class'] ) ) {
	$list_icon_html = '<i class="' . esc_attr( $list_item_icon['class'] ) . '" aria-hidden="true"></i>';
} elseif ( is_string( $list_item_icon ) && $list_item_icon !== '' ) {
	if ( strpos( $list_item_icon, '<' ) !== false ) {
		$list_icon_html = $list_item_icon;
	} else {
		$list_icon_html = '<i class="' . esc_attr( $list_item_icon ) . '" aria-hidden="true"></i>';
	}
}

$items = array();
if ( is_array( $gallery ) && ! empty( $gallery ) ) {
	foreach ( $gallery as $img ) {
		$url     = isset( $img['url'] ) ? $img['url'] : '';
		$title   = isset( $img['title'] ) ? $img['title'] : '';
		$id      = isset( $img['ID'] ) ? (int) $img['ID'] : ( isset( $img['id'] ) ? (int) $img['id'] : 0 );
		$caption = '';
		$author  = '';
		if ( $id ) {
			$caption = isset( $img['caption'] ) && (string) $img['caption'] !== '' ? $img['caption'] : wp_get_attachment_caption( $id );
			$author  = get_field( 'caption_author', $id ) ?: '';
			$size    = ( $slider_type === 'slideshow' ) ? 'tectn_slider_square' : 'large';
			$src     = wp_get_attachment_image_url( $id, $size );
			if ( $src ) {
				$url = $src;
			}
		}
		if ( $url !== '' ) {
			$items[] = array(
				'url'     => $url,
				'title'   => $title !== '' ? $title : __( 'Untitled', 'tectn_theme' ),
				'caption' => is_string( $caption ) ? $caption : '',
				'author'  => is_string( $author ) ? $author : '',
			);
		}
	}
}

$block_id = isset( $block['id'] ) ? $block['id'] : 'slider-' . wp_rand( 1000, 9999 );
$align    = ! empty( $block['align'] ) ? ' align' . $block['align'] : '';

$slider_empty_editor = array(
	'table_of_contents' => array(
		'title' => __( 'Table of contents slider', 'tectn_theme' ),
		'body'  => __( 'Add a headline and gallery images. Each image title becomes a list item on the left; visitors click a title to change the image on the right.', 'tectn_theme' ),
	),
	'horizontal'        => array(
		'title' => __( 'Horizontal slider', 'tectn_theme' ),
		'body'  => __( 'Add gallery images. Visitors use arrows or dots to change slides. Turn on Show captions in the block settings if images have captions or authors.', 'tectn_theme' ),
	),
	'slideshow'         => array(
		'title' => __( 'Slideshow', 'tectn_theme' ),
		'body'  => __( 'Add gallery images for a centered autoplay slideshow (square crop). Enable captions in the block settings when needed.', 'tectn_theme' ),
	),
);

$slider_empty_front = array(
	'table_of_contents' => __( 'Add images to the gallery. Each image title will become a clickable list item.', 'tectn_theme' ),
	'horizontal'        => __( 'Add images to the gallery to use this horizontal slider.', 'tectn_theme' ),
	'slideshow'         => __( 'Add images to the gallery to use this slideshow.', 'tectn_theme' ),
);

if ( empty( $items ) ) {
	if ( $is_editor_context && empty( $block_data['inserter_preview'] ) && empty( $block['inserterPreview'] ) ) {
		$empty_key = isset( $slider_empty_editor[ $slider_type ] ) ? $slider_type : 'table_of_contents';
		$empty_ed  = $slider_empty_editor[ $empty_key ];
		echo '<div class="c-slider c-slider--empty' . esc_attr( $align ) . '"><div class="c-slider__placeholder c-slider__placeholder--' . esc_attr( $empty_key ) . '"><strong>' . esc_html( $empty_ed['title'] ) . '</strong><br>' . esc_html( $empty_ed['body'] ) . '</div></div>';
		return;
	}
	$front_key = isset( $slider_empty_front[ $slider_type ] ) ? $slider_type : 'table_of_contents';
	echo '<div class="c-slider c-slider--empty' . esc_attr( $align ) . '"><p class="c-slider__empty">' . esc_html( $slider_empty_front[ $front_key ] ) . '</p></div>';
	return;
}

if ( $slider_type !== 'table_of_contents' && $slider_type !== 'horizontal' && $slider_type !== 'slideshow' ) {
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
$has_header = ( (string) $preheader !== '' || (string) $headline !== '' || (string) $body !== '' );
?>
<div class="c-slider c-slider--<?php echo esc_attr( $slider_type ); ?><?php echo esc_attr( $align ); ?>"
	 id="<?php echo esc_attr( $block_id ); ?>"
	 data-slider-type="<?php echo esc_attr( $slider_type ); ?>"
	 data-items="<?php echo esc_attr( wp_json_encode( $items ) ); ?>"
	 data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
	 data-show-captions="<?php echo $show_captions ? '1' : '0'; ?>"
	 role="region"
	 aria-label="<?php esc_attr_e( 'Image slider', 'tectn_theme' ); ?>">

	<?php if ( $slider_type === 'table_of_contents' ) : ?>
	<div class="c-slider__content">
		<div class="c-slider__col-left">
			<?php
			if ( $preheader ) {
				echo '<h5 class="c-headline-group__preheader' . ( $on_dark ? ' light' : '' ) . '">' . esc_html( $preheader ) . '</h5>';
			}
			if ( (string) $headline !== '' ) {
				$headline_parsed = function_exists( 'tectn_headline_tag_and_class' ) ? tectn_headline_tag_and_class( $headline_size, 'c-slider__headline' ) : array( 'tag' => 'h2', 'class' => 'c-slider__headline' );
				$headline_class   = trim( $headline_parsed['class'] . ( $on_dark ? ' light' : '' ) );
				echo '<' . esc_attr( $headline_parsed['tag'] ) . ' class="' . esc_attr( $headline_class ) . '">' . esc_html( $headline ) . '</' . esc_attr( $headline_parsed['tag'] ) . '>';
			}
			?>
			<?php if ( $body !== '' ) : ?>
				<div class="c-slider__body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>

			<nav class="c-slider__list" aria-label="<?php esc_attr_e( 'Choose item', 'tectn_theme' ); ?>">
				<ul class="c-slider__list-inner">
					<?php foreach ( $items as $i => $item ) : ?>
						<li>
							<button type="button"
									class="c-slider__list-btn<?php echo $i === 0 ? ' c-slider__list-btn--active' : ''; ?><?php echo $list_icon_html !== '' ? ' c-slider__list-btn--has-icon' : ''; ?>"
									data-index="<?php echo (int) $i; ?>"
									aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"
									aria-label="<?php echo esc_attr( $item['title'] ); ?>">
								<?php if ( $list_icon_html !== '' ) : ?>
									<span class="c-slider__list-icon" aria-hidden="true">
										<?php echo wp_kses_post( $list_icon_html ); ?>
									</span>
								<?php endif; ?>
								<span class="c-slider__list-label"><?php echo esc_html( $item['title'] ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>

		<div class="c-slider__panel">
			<div class="c-slider__image-wrap">
				<?php $first = $items[0]; ?>
				<div class="c-slider__slide c-slider__slide--current" data-slider-slide>
					<img src="<?php echo esc_url( $first['url'] ); ?>"
						 alt="<?php echo esc_attr( $first['title'] ); ?>"
						 class="c-slider__image"
						 data-slider-image>
				</div>
				<div class="c-slider__slide c-slider__slide--next" data-slider-slide>
					<img src="<?php echo esc_url( $first['url'] ); ?>"
						 alt=""
						 class="c-slider__image"
						 data-slider-image>
				</div>
			</div>
		</div>
	</div>
	<?php elseif ( $slider_type === 'horizontal' ) : ?>
	<?php if ( $has_header ) : ?>
	<div class="c-slider__content c-slider__content--centered">
		<?php
if ( $preheader ) {
		echo '<h5 class="c-headline-group__preheader' . ( $on_dark ? ' light' : '' ) . '">' . esc_html( $preheader ) . '</h5>';
	}
		if ( (string) $headline !== '' ) {
			$headline_parsed = function_exists( 'tectn_headline_tag_and_class' ) ? tectn_headline_tag_and_class( $headline_size, 'c-slider__headline' ) : array( 'tag' => 'h2', 'class' => 'c-slider__headline' );
			$headline_class   = trim( $headline_parsed['class'] . ( $on_dark ? ' light' : '' ) );
			echo '<' . esc_attr( $headline_parsed['tag'] ) . ' class="' . esc_attr( $headline_class ) . '">' . esc_html( $headline ) . '</' . esc_attr( $headline_parsed['tag'] ) . '>';
		}
		?>
		<?php if ( $body !== '' ) : ?>
			<div class="c-slider__body"><?php echo wp_kses_post( $body ); ?></div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php $first = $items[0]; $first_has_caption = $show_captions && ( (string) $first['caption'] !== '' || (string) $first['author'] !== '' ); ?>
	<div class="c-slider__panel">
		<div class="c-slider__image-wrap c-slider__image-wrap--fixed">
			<div class="c-slider__slide c-slider__slide--current" data-slider-slide>
				<img src="<?php echo esc_url( $first['url'] ); ?>"
					 alt="<?php echo esc_attr( $first['title'] ); ?>"
					 class="c-slider__image c-slider__image--cover"
					 data-slider-image>
			</div>
			<div class="c-slider__slide c-slider__slide--next" data-slider-slide>
				<img src="<?php echo esc_url( $first['url'] ); ?>"
					 alt=""
					 class="c-slider__image c-slider__image--cover"
					 data-slider-image>
			</div>
			<?php if ( $show_captions ) : ?>
			<div class="c-slider__caption<?php echo $first_has_caption ? ' c-slider__caption--visible' : ''; ?>" data-slider-caption aria-live="polite">
				<?php if ( $first_has_caption ) : ?>
					<?php if ( (string) $first['caption'] !== '' ) : ?>
						<p class="c-slider__caption-text"><?php echo esc_html( $first['caption'] ); ?></p>
					<?php endif; ?>
					<?php if ( (string) $first['author'] !== '' ) : ?>
						<p class="c-slider__caption-author"><?php echo esc_html( $first['author'] ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<button type="button" class="c-slider__arrow c-slider__arrow--prev" data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'tectn_theme' ); ?>"></button>
		<button type="button" class="c-slider__arrow c-slider__arrow--next" data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'tectn_theme' ); ?>"></button>
		<nav class="c-slider__dots" aria-label="<?php esc_attr_e( 'Slide navigation', 'tectn_theme' ); ?>">
			<?php foreach ( $items as $i => $item ) : ?>
				<button type="button" class="c-slider__dot<?php echo $i === 0 ? ' c-slider__dot--active' : ''; ?>" data-index="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'tectn_theme' ), $i + 1 ) ); ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
			<?php endforeach; ?>
		</nav>
	</div>

	<?php elseif ( $slider_type === 'slideshow' ) : ?>
	<?php if ( $has_header ) : ?>
	<div class="c-slider__content c-slider__content--centered">
		<?php
if ( $preheader ) {
		echo '<h5 class="c-headline-group__preheader' . ( $on_dark ? ' light' : '' ) . '">' . esc_html( $preheader ) . '</h5>';
	}
		if ( (string) $headline !== '' ) {
			$headline_parsed = function_exists( 'tectn_headline_tag_and_class' ) ? tectn_headline_tag_and_class( $headline_size, 'c-slider__headline' ) : array( 'tag' => 'h2', 'class' => 'c-slider__headline' );
			$headline_class   = trim( $headline_parsed['class'] . ( $on_dark ? ' light' : '' ) );
			echo '<' . esc_attr( $headline_parsed['tag'] ) . ' class="' . esc_attr( $headline_class ) . '">' . esc_html( $headline ) . '</' . esc_attr( $headline_parsed['tag'] ) . '>';
		}
		?>
		<?php if ( $body !== '' ) : ?>
			<div class="c-slider__body"><?php echo wp_kses_post( $body ); ?></div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php
	$first = $items[0];
	$first_has_caption = $show_captions && ( (string) $first['caption'] !== '' || (string) $first['author'] !== '' );
	?>
	<div class="c-slider__panel">
		<div class="c-slider__image-wrap c-slider__image-wrap--square">
			<div class="c-slider__slide c-slider__slide--current" data-slider-slide>
				<img src="<?php echo esc_url( $first['url'] ); ?>"
					 alt="<?php echo esc_attr( $first['title'] ); ?>"
					 class="c-slider__image c-slider__image--cover"
					 data-slider-image>
			</div>
			<div class="c-slider__slide c-slider__slide--next" data-slider-slide>
				<img src="<?php echo esc_url( $first['url'] ); ?>"
					 alt=""
					 class="c-slider__image c-slider__image--cover"
					 data-slider-image>
			</div>
			<?php if ( $show_captions ) : ?>
			<div class="c-slider__caption<?php echo $first_has_caption ? ' c-slider__caption--visible' : ''; ?>" data-slider-caption aria-live="polite">
				<?php if ( $first_has_caption ) : ?>
					<?php if ( (string) $first['caption'] !== '' ) : ?>
						<p class="c-slider__caption-text"><?php echo esc_html( $first['caption'] ); ?></p>
					<?php endif; ?>
					<?php if ( (string) $first['author'] !== '' ) : ?>
						<p class="c-slider__caption-author"><?php echo esc_html( $first['author'] ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<button type="button" class="c-slider__arrow c-slider__arrow--prev" data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'tectn_theme' ); ?>"></button>
			<button type="button" class="c-slider__arrow c-slider__arrow--next" data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'tectn_theme' ); ?>"></button>
			<nav class="c-slider__dots" aria-label="<?php esc_attr_e( 'Slide navigation', 'tectn_theme' ); ?>">
				<?php foreach ( $items as $i => $item ) : ?>
					<button type="button" class="c-slider__dot<?php echo $i === 0 ? ' c-slider__dot--active' : ''; ?>" data-index="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'tectn_theme' ), $i + 1 ) ); ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
				<?php endforeach; ?>
			</nav>
		</div>
	</div>
	<?php endif; ?>
</div>
