<?php
/**
 * Headline group template, headline, body copy, CTAs
 *
 * @param array $block The block settings and attributes.
 */

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
	$src = get_template_directory_uri() . '/blocks/headline-group/preview.png';
	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

$preheader       = get_field( 'preheader' );
$headline        = get_field( 'headline' );
$headline_size   = get_field( 'headline_size' );
$on_dark         = (bool) get_field( 'on_dark_background' );
$headline_parsed = function_exists( 'tectn_headline_tag_and_class' ) ? tectn_headline_tag_and_class( $headline_size, '' ) : array( 'tag' => 'h2', 'class' => '' );
$body            = get_field( 'body_copy' );

$body_plain    = is_string( $body ) ? trim( wp_strip_all_tags( $body ) ) : '';
$has_buttons   = function_exists( 'have_rows' ) && have_rows( 'buttons' );
$is_head_empty =
	( ! is_string( $preheader ) || trim( $preheader ) === '' ) &&
	( ! is_string( $headline ) || trim( $headline ) === '' ) &&
	$body_plain === '' &&
	! $has_buttons;

if ( $is_editor_context && empty( $block_data['inserter_preview'] ) && $is_head_empty ) {
	echo '<div class="row">';
	echo '  <div class="col-xs-12 c-headline-group__placeholder">';
	echo '    <strong>' . esc_html__( 'Headline group', 'tectn_theme' ) . '</strong><br>';
	echo '    ' . esc_html__( 'Add a headline, body copy, and optional buttons in the sidebar.', 'tectn_theme' );
	echo '  </div>';
	echo '</div>';
	return;
}
?>

<div class="row">
    <div class="col-xs-12 c-headline-group">
    <?php if ( $preheader ) : ?><h5 class="c-headline-group__preheader<?php echo $on_dark ? ' light' : ''; ?>"><?php echo esc_html( $preheader ); ?></h5><?php endif; ?>
    <?php if ( $headline ) : ?><<?php echo esc_attr( $headline_parsed['tag'] ); ?> class="<?php echo esc_attr( trim( $headline_parsed['class'] . ( $on_dark ? ' light' : '' ) ) ); ?>"><?php echo esc_html( $headline ); ?></<?php echo esc_attr( $headline_parsed['tag'] ); ?>><?php endif; ?>
    <?php if ( $body ) : ?><?php echo wp_kses_post( $body ); ?><?php endif; ?>
        <?php $partial_path = get_theme_file_path( '/partials/button_pair.php' ); ?>
        <?php include $partial_path; ?>
    </div>
</div>
