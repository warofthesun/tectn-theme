<?php
/**
 * Pick-up Sites Map block
 * Renders a map with one pin per Pick-up Site (CPT). Tooltip: location name, address, "Click to learn more" link.
 *
 * @param array $block The block settings and attributes.
 */

$is_preview = ! empty( $block['data']['is_preview'] );

if ( $is_preview ) {
	?>
	<div class="c-pickup-sites-map c-pickup-sites-map--preview" style="min-height:320px;display:flex;align-items:center;justify-content:center;background:#e8e8e8;border:1px solid #ccc;">
		<p style="margin:0;color:#666;">Pick-up Sites Map — Add Pick-up Site posts and set their location to see pins.</p>
	</div>
	<?php
	return;
}

$query = new WP_Query( array(
	'post_type'      => 'pickup_site',
	'post_status'    => 'publish',
	'posts_per_page' => 100,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'no_found_rows'  => true,
) );

$pins = array();
while ( $query->have_posts() ) {
	$query->the_post();
	$post_id = get_the_ID();
	$map     = get_field( 'location_map', $post_id );
	if ( ! is_array( $map ) || ! isset( $map['lat'], $map['lng'] ) || (string) $map['lat'] === '' || (string) $map['lng'] === '' ) {
		continue;
	}
	$name = get_field( 'location_name', $post_id );
	$pins[] = array(
		'lat'     => (float) $map['lat'],
		'lng'     => (float) $map['lng'],
		'address' => isset( $map['address'] ) ? $map['address'] : '',
		'name'    => $name ? $name : get_the_title(),
		'url'     => get_permalink( $post_id ),
	);
}
wp_reset_postdata();

$api_key = function_exists( 'acf_get_setting' ) ? acf_get_setting( 'google_api_key' ) : '';
$api_key = is_string( $api_key ) ? $api_key : '';

// Enqueue block view script on front so the map initializes (viewScript is not always enqueued for ACF-rendered blocks).
if ( ! is_admin() && ! $is_preview ) {
	$block_path = get_template_directory() . '/blocks/pickup-sites-map';
	$block_uri  = get_template_directory_uri() . '/blocks/pickup-sites-map';
	wp_enqueue_script(
		'tectn-pickup-sites-map-view',
		$block_uri . '/view.js',
		array(),
		file_exists( $block_path . '/view.js' ) ? filemtime( $block_path . '/view.js' ) : null,
		true
	);
}

$section_headline   = get_field( 'section_headline' );
$show_search        = (bool) get_field( 'show_search' );
$enable_clustering  = get_field( 'enable_clustering' );
if ( $enable_clustering === null || $enable_clustering === '' ) {
	$enable_clustering = true;
} else {
	$enable_clustering = (bool) $enable_clustering;
}

$block_id = isset( $block['id'] ) ? $block['id'] : 'pickup-sites-map-' . wp_rand( 1000, 9999 );
$align   = ! empty( $block['align'] ) ? ' align' . $block['align'] : '';
?>
<div class="c-pickup-sites-map<?php echo esc_attr( $align ); ?>"
	 id="<?php echo esc_attr( $block_id ); ?>"
	 data-pins="<?php echo esc_attr( wp_json_encode( $pins ) ); ?>"
	 data-api-key="<?php echo esc_attr( $api_key ); ?>"
	 data-show-search="<?php echo $show_search ? '1' : '0'; ?>"
	 data-enable-clustering="<?php echo $enable_clustering ? '1' : '0'; ?>"
	 aria-label="<?php esc_attr_e( 'Map of pick-up sites', 'tectn_theme' ); ?>">
	<?php if ( $section_headline !== '' ) : ?>
		<h2 class="c-pickup-sites-map__headline"><?php echo esc_html( $section_headline ); ?></h2>
	<?php endif; ?>
	<div class="c-pickup-sites-map__body">
		<div class="c-pickup-sites-map__canvas" style="width:100%;height:400px;background:#e8e8e8;"></div>
		<?php if ( empty( $pins ) ) : ?>
			<p class="c-pickup-sites-map__empty"><?php esc_html_e( 'No pick-up sites with locations yet. Add Pick-up Site posts and set their address on the map.', 'tectn_theme' ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $pins ) && $show_search ) : ?>
		<div class="c-pickup-sites-map__search-bar">
			<label for="<?php echo esc_attr( $block_id ); ?>-search" class="c-pickup-sites-map__search-label"><?php esc_html_e( 'Search locations', 'tectn_theme' ); ?></label>
			<input type="search"
				   id="<?php echo esc_attr( $block_id ); ?>-search"
				   class="c-pickup-sites-map__search"
				   placeholder="<?php esc_attr_e( 'Search by name or address…', 'tectn_theme' ); ?>"
				   autocomplete="off"
				   aria-label="<?php esc_attr_e( 'Search pick-up sites by name or address', 'tectn_theme' ); ?>">
		</div>
	<?php endif; ?>
</div>
