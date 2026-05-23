<?php
/**
 * Resources block — hybrid sections from Site Settings + page-only items.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'tectn-resources-' . $block['id'];

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$res_bg_style = function_exists( 'get_field' ) ? get_field( 'background_style' ) : null;
$res_bg_style = ( $res_bg_style !== null && $res_bg_style !== '' ) ? (string) $res_bg_style : 'color';
$res_no_bg    = ( $res_bg_style === 'none' );
$res_bg_raw   = function_exists( 'get_field' ) ? get_field( 'background_color' ) : null;
$res_bg_color = ( $res_bg_raw !== null && $res_bg_raw !== '' ) ? esc_attr( (string) $res_bg_raw ) : '#fcfce0';

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	echo '<div class="c-resources c-resources--medium c-resources--cols-3 c-resources--flow-row" style="--resources-max-w: 800px;">';
	echo '<section class="c-resources__section">';
	echo '<h5 class="c-headline-group__preheader">' . esc_html__( 'Resources', 'tectn_theme' ) . '</h5>';
	echo '<h2>' . esc_html__( 'Sample section', 'tectn_theme' ) . '</h2>';
	echo '<ul class="c-resources__grid" role="list">';
	for ( $i = 1; $i <= 4; $i++ ) {
		echo '<li class="c-resources__item"><a class="c-resources__link" href="#">' . esc_html( sprintf( /* translators: %d: placeholder item number */ __( 'Resource link %d', 'tectn_theme' ), $i ) ) . '</a></li>';
	}
	echo '</ul></section></div>';
	return;
}

$is_editor_context =
	is_admin() ||
	( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST );

/**
 * Build resolved sections from flexible content rows.
 *
 * @return list<array{preheader: string, headline: string, headline_size: string, entries: list<array<string, string>>}>
 */
$resolve_sections = static function () {
	$sections = array();
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'resource_sections' ) ) {
		return $sections;
	}
	while ( have_rows( 'resource_sections' ) ) {
		the_row();
		$source = get_sub_field( 'section_source' );
		$source = is_string( $source ) && $source !== '' ? $source : 'site';

		$preheader      = '';
		$headline       = '';
		$headline_size  = 'h2';
		$raw_item_rows  = array();

		if ( $source === 'site' ) {
			$selected = get_sub_field( 'selected_resource_section_key' );
			$site_row = function_exists( 'tectn_find_resource_section_by_selector' )
				? tectn_find_resource_section_by_selector( $selected )
				: null;
			if ( ! is_array( $site_row ) ) {
				continue;
			}
			$preheader     = isset( $site_row['preheader'] ) ? trim( (string) $site_row['preheader'] ) : '';
			$headline      = isset( $site_row['headline'] ) ? trim( (string) $site_row['headline'] ) : '';
			$headline_size = isset( $site_row['headline_size'] ) && (string) $site_row['headline_size'] !== ''
				? (string) $site_row['headline_size']
				: 'h2';
			if ( ! empty( $site_row['resource_section_items'] ) && is_array( $site_row['resource_section_items'] ) ) {
				$raw_item_rows = array_merge( $raw_item_rows, $site_row['resource_section_items'] );
			}
			$page_only = get_sub_field( 'page_only_items' );
			if ( is_array( $page_only ) && ! empty( $page_only ) ) {
				$raw_item_rows = array_merge( $raw_item_rows, $page_only );
			}
		} else {
			$preheader     = get_sub_field( 'preheader' );
			$preheader     = is_string( $preheader ) ? trim( $preheader ) : '';
			$headline      = get_sub_field( 'headline' );
			$headline      = is_string( $headline ) ? trim( $headline ) : '';
			$headline_size = get_sub_field( 'headline_size' );
			$headline_size = is_string( $headline_size ) && $headline_size !== '' ? $headline_size : 'h2';
			$local_items   = get_sub_field( 'resource_items' );
			if ( is_array( $local_items ) ) {
				$raw_item_rows = $local_items;
			}
		}

		$entries = function_exists( 'tectn_resources_normalize_item_rows' )
			? tectn_resources_normalize_item_rows( $raw_item_rows )
			: array();

		if ( empty( $entries ) ) {
			continue;
		}

		$sections[] = array(
			'preheader'     => $preheader,
			'headline'      => $headline,
			'headline_size' => $headline_size,
			'entries'       => $entries,
		);
	}
	return $sections;
};

$sections = $resolve_sections();

$render_placeholder = static function ( $message ) use ( $res_no_bg, $res_bg_color ) {
	$ph_classes = array( 'c-resources', 'c-resources--medium', 'c-resources--cols-3', 'c-resources--flow-row' );
	if ( $res_no_bg ) {
		$ph_classes[] = 'c-resources--no-bg';
	}
	$ph_style = '--resources-max-w: 800px;';
	if ( ! $res_no_bg ) {
		$ph_style .= ' --resources-bg: ' . $res_bg_color . ';';
	}
	echo '<div class="' . esc_attr( implode( ' ', $ph_classes ) ) . '" style="' . esc_attr( $ph_style ) . '">';
	echo '  <div class="c-resources__placeholder">';
	echo '    <strong>' . esc_html__( 'Resources', 'tectn_theme' ) . '</strong><br>';
	echo '    ' . esc_html( $message );
	echo '  </div>';
	echo '</div>';
};

if ( $is_editor_context && empty( $sections ) && empty( $block_data['inserter_preview'] ) ) {
	$render_placeholder( __( 'Add sections in the block sidebar. Choose Site Settings sections or create page-only sections with links.', 'tectn_theme' ) );
	return;
}

if ( empty( $sections ) ) {
	return;
}

$cols = (int) ( get_field( 'list_columns' ) ?: 3 );
$cols = max( 1, min( 4, $cols ) );

$flow = get_field( 'list_flow' ) ?: 'row';
$flow = in_array( $flow, array( 'row', 'column' ), true ) ? $flow : 'row';

$width     = get_field( 'block_width' ) ?: 'medium';
$max_width = array(
	'small'  => 600,
	'medium' => 800,
	'large'  => 1000,
);
$max_w = isset( $max_width[ $width ] ) ? $max_width[ $width ] : 800;

$classes = array(
	'c-resources',
	'c-resources--' . sanitize_html_class( $width ),
	'c-resources--cols-' . $cols,
	'c-resources--flow-' . $flow,
);
if ( $res_no_bg ) {
	$classes[] = 'c-resources--no-bg';
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
	style="<?php echo $res_no_bg ? '' : '--resources-bg: ' . $res_bg_color . '; '; ?>--resources-max-w: <?php echo (int) $max_w; ?>px;">
	<?php foreach ( $sections as $section ) : ?>
		<?php
		$preheader     = $section['preheader'];
		$headline      = $section['headline'];
		$headline_size = $section['headline_size'];
		$entries       = $section['entries'];

		if ( 'column' === $flow && function_exists( 'tectn_information_lists_reorder_for_column_flow' ) ) {
			$entries = tectn_information_lists_reorder_for_column_flow( $entries, $cols );
		}

		$headline_parsed = function_exists( 'tectn_headline_tag_and_class' )
			? tectn_headline_tag_and_class( $headline_size, '' )
			: array( 'tag' => 'h2', 'class' => '' );
		$has_headline    = $preheader !== '' || $headline !== '';
		?>
		<section class="c-resources__section">
			<?php if ( $has_headline ) : ?>
				<div class="c-resources__headline">
					<?php if ( $preheader !== '' ) : ?>
						<h5 class="c-headline-group__preheader"><?php echo esc_html( $preheader ); ?></h5>
					<?php endif; ?>
					<?php if ( $headline !== '' ) : ?>
						<<?php echo esc_attr( $headline_parsed['tag'] ); ?> class="<?php echo esc_attr( trim( $headline_parsed['class'] ) ); ?>"><?php echo esc_html( $headline ); ?></<?php echo esc_attr( $headline_parsed['tag'] ); ?>>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<ul class="c-resources__grid" role="list">
				<?php foreach ( $entries as $ent ) : ?>
					<?php
					$label  = $ent['label'];
					$url    = $ent['url'];
					$title  = $ent['title'];
					$target = $ent['target'];
					$body   = $ent['body'];
					?>
					<li class="c-resources__item">
						<?php if ( $url !== '' ) : ?>
							<a class="c-resources__link"
								href="<?php echo esc_url( $url ); ?>"
								<?php echo $title !== '' ? ' title="' . esc_attr( $title ) . '"' : ''; ?>
								target="<?php echo esc_attr( $target ); ?>"
								<?php echo ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : ''; ?>>
								<?php echo esc_html( $label ); ?>
							</a>
						<?php else : ?>
							<span class="c-resources__text"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>
						<?php if ( $body !== '' ) : ?>
							<p class="c-resources__body"><?php echo nl2br( esc_html( $body ) ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>
</div>
