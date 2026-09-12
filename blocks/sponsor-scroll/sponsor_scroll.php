<?php
/**
 * Sponsor Scroll block.
 *
 * @param array $block The block settings and attributes.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_data = ( ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	echo '<div class="c-sponsor-scroll c-sponsor-scroll--preview" aria-hidden="true">';
	echo '<div class="c-sponsor-scroll__label">' . esc_html__( 'Sponsored by', 'tectn' ) . '</div>';
	echo '<div class="c-sponsor-scroll__track"><div class="c-sponsor-scroll__items">';
	echo '<span class="c-sponsor-scroll__item">' . esc_html__( 'Sponsor Name | $200', 'tectn' ) . '</span>';
	echo '</div></div></div>';
	return;
}

$is_preview = ! empty( $block['mode'] ) && $block['mode'] === 'preview';

$label = tectn_acf_block_field( 'label', $block );
if ( ! is_string( $label ) || trim( $label ) === '' ) {
	$label = __( 'Sponsored by', 'tectn' );
} else {
	$label = trim( $label );
}

$sponsors_raw = tectn_acf_block_array( 'sponsors', $block );
$sponsors     = array();

foreach ( $sponsors_raw as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
	if ( $name === '' ) {
		continue;
	}
	$amount = isset( $row['amount'] ) ? trim( (string) $row['amount'] ) : '';
	$sponsors[] = array(
		'name'   => $name,
		'amount' => $amount,
	);
}

if ( empty( $sponsors ) ) {
	if ( $is_preview || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<div class="c-sponsor-scroll__placeholder">';
		echo '<strong>' . esc_html__( 'Sponsor Scroll', 'tectn' ) . '</strong>';
		echo '<p>' . esc_html__( 'Add sponsors in the block settings.', 'tectn' ) . '</p>';
		echo '</div>';
	}
	return;
}

if ( ! is_admin() ) {
	$block_path = get_template_directory() . '/blocks/sponsor-scroll';
	$block_uri  = get_template_directory_uri() . '/blocks/sponsor-scroll';
	wp_enqueue_script(
		'tectn-sponsor-scroll-view',
		$block_uri . '/view.js',
		array(),
		file_exists( $block_path . '/view.js' ) ? (string) filemtime( $block_path . '/view.js' ) : null,
		true
	);
}

$block_id = ! empty( $block['id'] ) ? $block['id'] : 'sponsor-scroll-' . wp_unique_id();
$classes  = array( 'c-sponsor-scroll' );
if ( ! empty( $block['align'] ) ) {
	$classes[] = 'align' . sanitize_html_class( $block['align'] );
}
if ( ! empty( $block['className'] ) ) {
	$classes[] = $block['className'];
}
?>
<div
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	id="<?php echo esc_attr( $block_id ); ?>"
	data-sponsor-scroll
>
	<div class="c-sponsor-scroll__label"><?php echo esc_html( $label ); ?></div>
	<div class="c-sponsor-scroll__track">
		<div class="c-sponsor-scroll__items">
			<?php foreach ( $sponsors as $index => $sponsor ) : ?>
				<?php if ( $index > 0 ) : ?>
					<span class="c-sponsor-scroll__sep" aria-hidden="true">•</span>
				<?php endif; ?>
				<span class="c-sponsor-scroll__item">
					<?php
					echo esc_html( $sponsor['name'] );
					if ( $sponsor['amount'] !== '' ) {
						echo ' | <span class="c-sponsor-scroll__amount">' . esc_html( $sponsor['amount'] ) . '</span>';
					}
					?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
</div>
