<?php
/**
 * Forms block — outputs embed code from Site Settings → Forms (by stable form_key).
 *
 * @package tectn_theme
 */

$block_id   = ! empty( $block['anchor'] ) ? $block['anchor'] : 'tectn-forms-' . $block['id'];
$is_preview = ! empty( $block['data']['is_preview'] );

if ( $is_preview ) {
	?>
	<div id="<?php echo esc_attr( $block_id ); ?>" class="c-formsEmbed c-formsEmbed--preview">
		<p class="c-formsEmbed__preview-note"><?php esc_html_e( 'Forms block — choose a form from Site Settings → Forms in the sidebar.', 'tectn_theme' ); ?></p>
	</div>
	<?php
	return;
}

$selected = function_exists( 'tectn_forms_block_get_selected_raw' )
	? tectn_forms_block_get_selected_raw( $block )
	: ( function_exists( 'get_field' ) ? get_field( 'selected_form_key' ) : null );

if ( $selected === null || $selected === '' || $selected === false ) {
	return;
}

$row = function_exists( 'tectn_find_embedded_form_by_selector' )
	? tectn_find_embedded_form_by_selector( $selected )
	: null;
if ( ! is_array( $row ) ) {
	return;
}

$code = isset( $row['form_embed_code'] ) ? (string) $row['form_embed_code'] : '';
if ( trim( $code ) === '' ) {
	return;
}

if ( function_exists( 'tectn_normalize_form_embed_markup' ) ) {
	$code = tectn_normalize_form_embed_markup( $code );
}

$classes = array( 'c-formsEmbed' );
if ( ! empty( $block['className'] ) ) {
	$extra = preg_split( '/\s+/', trim( $block['className'] ) );
	foreach ( $extra as $c ) {
		if ( $c !== '' ) {
			$classes[] = sanitize_html_class( $c );
		}
	}
}
?>
<div id="<?php echo esc_attr( $block_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional third-party embed (scripts/iframes); editors are trusted.
	echo $code;
	?>
</div>
