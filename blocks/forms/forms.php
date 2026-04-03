<?php
/**
 * Forms block — outputs embed code from Site Settings → Forms (by stable form_key).
 *
 * @package tectn_theme
 */

$block_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'tectn-forms-' . $block['id'];

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_editor_context =
	is_admin() ||
	( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST );

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	$src = get_template_directory_uri() . '/blocks/forms/preview.png';
	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

$selected = function_exists( 'tectn_forms_block_get_selected_raw' )
	? tectn_forms_block_get_selected_raw( $block )
	: ( function_exists( 'get_field' ) ? get_field( 'selected_form_key' ) : null );

$row = null;
if ( $selected !== null && $selected !== '' && $selected !== false && function_exists( 'tectn_find_embedded_form_by_selector' ) ) {
	$row = tectn_find_embedded_form_by_selector( $selected );
}

$code = is_array( $row ) && isset( $row['form_embed_code'] ) ? (string) $row['form_embed_code'] : '';

$forms_incomplete =
	$selected === null || $selected === '' || $selected === false ||
	! is_array( $row ) ||
	trim( $code ) === '';

if ( $is_editor_context && empty( $block_data['inserter_preview'] ) && $forms_incomplete ) {
	?>
	<div id="<?php echo esc_attr( $block_id ); ?>" class="c-formsEmbed">
		<div class="c-formsEmbed__placeholder">
			<strong><?php esc_html_e( 'Forms', 'tectn_theme' ); ?></strong><br>
			<?php esc_html_e( 'Choose a form under Site Settings → Forms, or pick one in this block’s sidebar.', 'tectn_theme' ); ?>
		</div>
	</div>
	<?php
	return;
}

if ( $selected === null || $selected === '' || $selected === false ) {
	return;
}

if ( ! is_array( $row ) ) {
	return;
}

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
