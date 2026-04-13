<?php
/**
 * Information lists block — grid of items from Site Settings → Information lists.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'tectn-information-lists-' . $block['id'];

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$il_bg_style = function_exists( 'get_field' ) ? get_field( 'background_style' ) : null;
$il_bg_style = ( $il_bg_style !== null && $il_bg_style !== '' ) ? (string) $il_bg_style : 'color';
$il_no_bg    = ( $il_bg_style === 'none' );
$il_bg_raw   = function_exists( 'get_field' ) ? get_field( 'background_color' ) : null;
$il_bg_color = ( $il_bg_raw !== null && $il_bg_raw !== '' ) ? esc_attr( (string) $il_bg_raw ) : '#fcfce0';

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	echo '<div class="c-information-lists c-information-lists--medium c-information-lists--cols-3 c-information-lists--flow-row" style="--information-lists-max-w: 600px;">';
	echo '<ul class="c-information-lists__grid" role="list">';
	for ( $i = 1; $i <= 6; $i++ ) {
		echo '<li class="c-information-lists__item"><span class="c-information-lists__text">' . esc_html( sprintf( /* translators: %d: placeholder item number */ __( 'List item %d', 'tectn_theme' ), $i ) ) . '</span></li>';
	}
	echo '</ul></div>';
	return;
}

$is_editor_context =
	is_admin() ||
	( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST );

$selected = function_exists( 'tectn_information_lists_block_get_selected_raw' )
	? tectn_information_lists_block_get_selected_raw( $block )
	: ( function_exists( 'get_field' ) ? get_field( 'selected_information_list_key' ) : null );

$list = null;
if ( $selected !== null && $selected !== '' && $selected !== false ) {
	$list = function_exists( 'tectn_find_information_list_by_selector' )
		? tectn_find_information_list_by_selector( $selected )
		: null;
}

$items = array();
if ( is_array( $list ) && ! empty( $list['information_list_items'] ) && is_array( $list['information_list_items'] ) ) {
	$items = $list['information_list_items'];
}

if ( $is_editor_context && ( ! is_array( $list ) || empty( $items ) ) && empty( $block_data['inserter_preview'] ) ) {
	$ph_classes = array( 'c-information-lists', 'c-information-lists--medium', 'c-information-lists--cols-3', 'c-information-lists--flow-row' );
	if ( $il_no_bg ) {
		$ph_classes[] = 'c-information-lists--no-bg';
	}
	$ph_style = '--information-lists-max-w: 600px;';
	if ( ! $il_no_bg ) {
		$ph_style .= ' --information-lists-bg: ' . $il_bg_color . ';';
	}
	echo '<div class="' . esc_attr( implode( ' ', $ph_classes ) ) . '" style="' . esc_attr( $ph_style ) . '">';
	echo '  <div class="c-information-lists__placeholder">';
	echo '    <strong>' . esc_html__( 'Information lists', 'tectn_theme' ) . '</strong><br>';
	echo '    ' . esc_html__( 'Choose a list from Site Settings → Information lists in the block settings.', 'tectn_theme' );
	echo '  </div>';
	echo '</div>';
	return;
}

if ( ! is_array( $list ) || empty( $items ) ) {
	return;
}

$cols = (int) ( get_field( 'list_columns' ) ?: 3 );
$cols = max( 1, min( 4, $cols ) );

$flow = get_field( 'list_flow' ) ?: 'row';
$flow = in_array( $flow, array( 'row', 'column' ), true ) ? $flow : 'row';

$entries = array();
foreach ( $items as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$name = isset( $row['item_name'] ) ? trim( (string) $row['item_name'] ) : '';
	$link = isset( $row['item_link'] ) && is_array( $row['item_link'] ) ? $row['item_link'] : array();
	$url    = isset( $link['url'] ) ? trim( (string) $link['url'] ) : '';
	$title  = isset( $link['title'] ) ? trim( (string) $link['title'] ) : '';
	$target = isset( $link['target'] ) ? (string) $link['target'] : '_self';
	$label  = $name !== '' ? $name : $title;
	if ( $label === '' && $url === '' ) {
		continue;
	}
	if ( $label === '' ) {
		$label = $url;
	}
	$entries[] = array(
		'label'  => $label,
		'url'    => $url,
		'title'  => $title,
		'target' => $target,
	);
}

if ( empty( $entries ) ) {
	if ( $is_editor_context && empty( $block_data['inserter_preview'] ) ) {
		$ph2_classes = array( 'c-information-lists', 'c-information-lists--medium', 'c-information-lists--cols-3', 'c-information-lists--flow-row' );
		if ( $il_no_bg ) {
			$ph2_classes[] = 'c-information-lists--no-bg';
		}
		$ph2_style = '--information-lists-max-w: 600px;';
		if ( ! $il_no_bg ) {
			$ph2_style .= ' --information-lists-bg: ' . $il_bg_color . ';';
		}
		echo '<div class="' . esc_attr( implode( ' ', $ph2_classes ) ) . '" style="' . esc_attr( $ph2_style ) . '">';
		echo '  <div class="c-information-lists__placeholder">';
		echo '    <strong>' . esc_html__( 'Information lists', 'tectn_theme' ) . '</strong><br>';
		echo '    ' . esc_html__( 'Add at least one list item with a name or link under Site Settings → Information lists.', 'tectn_theme' );
		echo '  </div>';
		echo '</div>';
	}
	return;
}

if ( 'column' === $flow && function_exists( 'tectn_information_lists_reorder_for_column_flow' ) ) {
	$entries = tectn_information_lists_reorder_for_column_flow( $entries, $cols );
}

$width     = get_field( 'block_width' ) ?: 'medium';
$max_width = array(
	'small'  => 400,
	'medium' => 600,
	'large'  => 800,
);
$max_w = isset( $max_width[ $width ] ) ? $max_width[ $width ] : 600;

$classes = array(
	'c-information-lists',
	'c-information-lists--' . sanitize_html_class( $width ),
	'c-information-lists--cols-' . $cols,
	'c-information-lists--flow-' . $flow,
);
if ( $il_no_bg ) {
	$classes[] = 'c-information-lists--no-bg';
}
if ( ! empty( $block['className'] ) ) {
	$extra = preg_split( '/\s+/', trim( $block['className'] ) );
	foreach ( $extra as $c ) {
		if ( $c !== '' ) {
			$classes[] = sanitize_html_class( $c );
		}
	}
}
?>
<div id="<?php echo esc_attr( $block_id ); ?>"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	style="<?php echo $il_no_bg ? '' : '--information-lists-bg: ' . $il_bg_color . '; '; ?>--information-lists-max-w: <?php echo (int) $max_w; ?>px;">
	<ul class="c-information-lists__grid" role="list">
		<?php foreach ( $entries as $ent ) : ?>
			<?php
			$label  = $ent['label'];
			$url    = $ent['url'];
			$title  = $ent['title'];
			$target = $ent['target'];
			?>
			<li class="c-information-lists__item">
				<?php if ( $url !== '' ) : ?>
					<a class="c-information-lists__link"
						href="<?php echo esc_url( $url ); ?>"
						<?php echo $title !== '' ? ' title="' . esc_attr( $title ) . '"' : ''; ?>
						target="<?php echo esc_attr( $target ); ?>"
						<?php echo ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : ''; ?>>
						<?php echo esc_html( $label ); ?>
					</a>
				<?php else : ?>
					<span class="c-information-lists__text"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
