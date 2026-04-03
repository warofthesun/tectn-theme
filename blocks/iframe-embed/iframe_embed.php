<?php
/**
 * Iframe Embed block — optional headline + sanitized iframe markup.
 *
 * @param array<string, mixed> $block Block settings and attributes.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	$src = get_template_directory_uri() . '/blocks/iframe-embed/preview.png';
	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

$section_headline = get_field( 'section_headline' );
$raw_embed        = get_field( 'iframe_embed' );
$raw_embed        = is_string( $raw_embed ) ? trim( $raw_embed ) : '';

if ( $is_editor_context && empty( $block_data['inserter_preview'] ) && $raw_embed === '' ) {
	$block_id = isset( $block['id'] ) ? $block['id'] : 'iframe-embed-' . wp_rand( 1000, 9999 );
	$align    = ! empty( $block['align'] ) ? ' align' . $block['align'] : '';
	$classes  = array( 'c-iframe-embed' );
	if ( ! empty( $block['className'] ) ) {
		foreach ( preg_split( '/\s+/', trim( $block['className'] ) ) as $c ) {
			if ( $c !== '' ) {
				$classes[] = sanitize_html_class( $c );
			}
		}
	}
	?>
<div id="<?php echo esc_attr( (string) $block_id ); ?>"
	class="<?php echo esc_attr( implode( ' ', $classes ) . $align ); ?>"
	aria-label="<?php esc_attr_e( 'Embedded content', 'tectn_theme' ); ?>">
	<div class="c-iframe-embed__body">
		<div class="c-iframe-embed__placeholder">
			<strong><?php esc_html_e( 'Iframe Embed', 'tectn_theme' ); ?></strong><br>
			<?php esc_html_e( 'Paste iframe markup in the block settings.', 'tectn_theme' ); ?>
		</div>
	</div>
</div>
	<?php
	return;
}

$block_id = isset( $block['id'] ) ? $block['id'] : 'iframe-embed-' . wp_rand( 1000, 9999 );
$align    = ! empty( $block['align'] ) ? ' align' . $block['align'] : '';

$classes = array( 'c-iframe-embed' );
if ( ! empty( $block['className'] ) ) {
	foreach ( preg_split( '/\s+/', trim( $block['className'] ) ) as $c ) {
		if ( $c !== '' ) {
			$classes[] = sanitize_html_class( $c );
		}
	}
}
?>
<div id="<?php echo esc_attr( (string) $block_id ); ?>"
	class="<?php echo esc_attr( implode( ' ', $classes ) . $align ); ?>"
	aria-label="<?php esc_attr_e( 'Embedded content', 'tectn_theme' ); ?>">
	<?php if ( $section_headline !== '' && $section_headline !== null ) : ?>
		<h2 class="c-iframe-embed__headline"><?php echo esc_html( (string) $section_headline ); ?></h2>
	<?php endif; ?>
	<div class="c-iframe-embed__body">
		<?php if ( $raw_embed !== '' ) : ?>
			<div class="c-iframe-embed__frame">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Explicit wp_kses() with iframe allowlist.
				echo wp_kses( $raw_embed, tectn_iframe_embed_allowed_iframe_tags() );
				?>
			</div>
		<?php else : ?>
			<p class="c-iframe-embed__empty"><?php esc_html_e( 'No embed code yet. Paste an iframe in the block settings.', 'tectn_theme' ); ?></p>
		<?php endif; ?>
	</div>
</div>
