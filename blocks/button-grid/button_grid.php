<?php
/**
 * Button Grid (ACF Block)
 * Repeater of link buttons; optional block background; width and buttons-per-row options.
 */

$block_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'button-grid-' . $block['id'];

$bg_style   = get_field( 'background_style' ) ?: 'color';
$no_bg      = ( $bg_style === 'none' );
$bg_color   = ! $no_bg && get_field( 'background_color' ) ? esc_attr( get_field( 'background_color' ) ) : '#fcfce0';
$per_row    = (int) ( get_field( 'buttons_per_row' ) ?: 3 );
$per_row    = max( 2, min( 4, $per_row ) );
$width      = get_field( 'block_width' ) ?: 'medium';
$max_width  = array( 'small' => 400, 'medium' => 600, 'large' => 800 );
$max_w      = isset( $max_width[ $width ] ) ? $max_width[ $width ] : 600;
$btn_color  = get_field( 'button_color' ) ?: 'primary';
$btn_color  = in_array( $btn_color, array( 'primary', 'secondary', 'accent' ), true ) ? $btn_color : 'primary';

$buttons = get_field( 'buttons' );
if ( ! is_array( $buttons ) ) {
	$buttons = array();
}

$classes = array( 'c-button-grid', 'c-button-grid--' . sanitize_html_class( $width ), 'c-button-grid--cols-' . (int) $per_row );
if ( $no_bg ) {
	$classes[] = 'c-button-grid--no-bg';
}
if ( ! empty( $block['className'] ) ) {
	$classes[] = $block['className'];
}
?>

<div id="<?php echo esc_attr( $block_id ); ?>"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	style="<?php echo $no_bg ? '' : '--button-grid-bg: ' . $bg_color . ';'; ?> --button-grid-max-w: <?php echo (int) $max_w; ?>px;">
	<div class="c-button-pair c-button-pair--<?php echo esc_attr( $btn_color ); ?> c-button-grid__list">
		<?php
		foreach ( $buttons as $row ) {
			$link = isset( $row['link'] ) && is_array( $row['link'] ) ? $row['link'] : array();
			$url   = isset( $link['url'] ) ? $link['url'] : '';
			$title = isset( $link['title'] ) ? $link['title'] : '';
			$target = isset( $link['target'] ) ? $link['target'] : '_self';
			if ( ! $url || ! $title ) {
				continue;
			}
			$style = isset( $row['button_style'] ) ? $row['button_style'] : 'solid';
			$style = in_array( $style, array( 'solid', 'outline', 'text' ), true ) ? $style : 'solid';
			$btn_classes = array( 'c-button-pair__button', 'c-button-grid__button', 'c-button-pair__button--' . $style );
			?>
			<a class="<?php echo esc_attr( implode( ' ', $btn_classes ) ); ?>"
				href="<?php echo esc_url( $url ); ?>"
				target="<?php echo esc_attr( $target ); ?>"
				<?php echo ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : ''; ?>>
				<?php echo esc_html( $title ); ?>
			</a>
		<?php } ?>
	</div>
</div>
