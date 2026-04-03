<?php
/**
 * Information table block — outputs a table from Site Settings → Information tables.
 *
 * @package tectn_theme
 */

$block_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'tectn-info-table-' . $block['id'];

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	$src = get_template_directory_uri() . '/blocks/info-table/preview.png';
	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

$selected = function_exists( 'tectn_info_tables_block_get_selected_raw' )
	? tectn_info_tables_block_get_selected_raw( $block )
	: ( function_exists( 'get_field' ) ? get_field( 'selected_info_table_key' ) : null );

if ( $selected === null || $selected === '' || $selected === false ) {
	return;
}

$table = function_exists( 'tectn_find_info_table_by_selector' )
	? tectn_find_info_table_by_selector( $selected )
	: null;
if ( ! is_array( $table ) ) {
	return;
}

$h1 = isset( $table['header_col_1'] ) ? (string) $table['header_col_1'] : '';
$h2 = isset( $table['header_col_2'] ) ? (string) $table['header_col_2'] : '';
$h3 = isset( $table['header_col_3'] ) ? (string) $table['header_col_3'] : '';
$h4 = isset( $table['header_col_4'] ) ? (string) $table['header_col_4'] : '';

$body_rows = isset( $table['table_rows'] ) && is_array( $table['table_rows'] ) ? $table['table_rows'] : array();

$classes = array( 'c-infoTable' );
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
	<div class="c-infoTable__scroll">
		<table class="c-infoTable__table">
			<thead class="c-infoTable__head">
				<tr>
					<th class="c-infoTable__th c-infoTable__th--col1" scope="col"><?php echo esc_html( $h1 ); ?></th>
					<th class="c-infoTable__th c-infoTable__th--col2" scope="col"><?php echo esc_html( $h2 ); ?></th>
					<th class="c-infoTable__th c-infoTable__th--col3" scope="col"><?php echo esc_html( $h3 ); ?></th>
					<th class="c-infoTable__th c-infoTable__th--col4" scope="col"><?php echo esc_html( $h4 ); ?></th>
				</tr>
			</thead>
			<?php if ( ! empty( $body_rows ) ) : ?>
				<tbody class="c-infoTable__body">
					<?php foreach ( $body_rows as $row ) : ?>
						<?php
						if ( ! is_array( $row ) ) {
							continue;
						}
						$item          = isset( $row['col_item'] ) ? (string) $row['col_item'] : '';
						$accepted      = isset( $row['col_accepted'] ) ? (string) $row['col_accepted'] : '';
						$not_accepted  = isset( $row['col_not_accepted'] ) ? (string) $row['col_not_accepted'] : '';
						$recycled      = isset( $row['col_recycled_by'] ) ? (string) $row['col_recycled_by'] : '';
						?>
						<tr class="c-infoTable__row">
							<th class="c-infoTable__item" scope="row"><?php echo esc_html( $item ); ?></th>
							<td class="c-infoTable__cell c-infoTable__cell--rich">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- KSES allows safe HTML from editor.
								echo $accepted !== '' ? wp_kses_post( $accepted ) : '';
								?>
							</td>
							<td class="c-infoTable__cell">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF may return paragraphs from wpautop formatting.
								echo $not_accepted !== '' ? wp_kses_post( $not_accepted ) : '';
								?>
							</td>
							<td class="c-infoTable__cell">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $recycled !== '' ? wp_kses_post( $recycled ) : '';
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
	</div>
</div>
