<?php
/**
 * Theme includes.
 * @package tectn_theme
 * ACF options pages, events field group, footer getters.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF options parent: `site-settings-parent` (container menu) after syncing theme JSON;
 * falls back to `site-settings` before sync (legacy top-level options page).
 */
function tectn_site_settings_parent_slug() {
	$pages = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( ! empty( $pages['site-settings-parent'] ) ) {
		return 'site-settings-parent';
	}
	return 'site-settings';
}

/**
 * First sub-item: Brand (same post_id as site-settings for existing get_field(..., 'site-settings') calls).
 *
 * Must run after ACF's register_ui_options_pages (acf/init priority 6) so site-settings-parent exists.
 */
function tectn_register_site_settings_brand_subpage() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_get_options_pages' ) ) {
		return;
	}
	$pages = acf_get_options_pages();
	if ( empty( $pages['site-settings-parent'] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Brand',
			'menu_title'  => 'Brand',
			'menu_slug'   => 'site-settings',
			'parent_slug' => 'site-settings-parent',
			'capability'  => 'edit_posts',
			'post_id'     => 'site-settings',
		)
	);
}
add_action( 'acf/init', 'tectn_register_site_settings_brand_subpage', 7 );

/**
 * Google Maps API key (moved from Brand / site_settings group).
 */
function tectn_register_site_settings_integrations_subpage() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_get_options_pages' ) ) {
		return;
	}
	$pages = acf_get_options_pages();
	if ( empty( $pages['site-settings-parent'] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Integrations',
			'menu_title'  => 'Integrations',
			'menu_slug'   => 'site-settings-integrations',
			'parent_slug' => 'site-settings-parent',
			'capability'  => 'edit_posts',
			'post_id'     => 'site-settings-integrations',
		)
	);
}
add_action( 'acf/init', 'tectn_register_site_settings_integrations_subpage', 8 );

/**
 * Fields for Integrations options sub-page.
 */
function tectn_register_acf_integrations_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tectn_integrations',
			'title'                 => 'Integrations',
			'active'                => true,
			'fields'                => array(
				array(
					'key'           => 'field_tectn_integrations_gmaps_key',
					'label'         => 'Google Maps API key',
					'name'          => 'google_maps_api_key',
					'type'          => 'text',
					'instructions'  => 'Used for ACF Google Map fields and map features. You can also set GOOGLE_MAPS_API_KEY / tectn_google_maps_api_key. Restrict the key by HTTP referrer in Google Cloud Console.',
					'required'      => 0,
					'default_value' => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'site-settings-integrations',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'tectn_register_acf_integrations_field_group', 15 );

/**
 * Remove the self-referencing duplicate submenu WordPress adds for the Site Settings parent.
 */
function tectn_remove_site_settings_parent_submenu_duplicate() {
	$pages = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages['site-settings-parent'] ) ) {
		return;
	}
	remove_submenu_page( 'site-settings-parent', 'site-settings-parent' );
}
add_action( 'admin_menu', 'tectn_remove_site_settings_parent_submenu_duplicate', 999 );

/**
 * Make the Site Settings container menu open-only (no navigation) on wide viewports.
 */
function tectn_site_settings_parent_menu_non_navigable() {
	$pages = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages['site-settings-parent'] ) ) {
		return;
	}
	?>
<script>
(function () {
	var li = document.getElementById('toplevel_page_site-settings-parent');
	if (!li || !li.classList.contains('wp-has-submenu')) return;
	var a = li.querySelector(':scope > a');
	if (!a) return;
	a.setAttribute('href', '#');
	a.addEventListener('click', function (e) {
		if (window.innerWidth > 960) {
			e.preventDefault();
		}
	});
})();
</script>
	<?php
}
add_action( 'admin_footer', 'tectn_site_settings_parent_menu_non_navigable', 99 );

function tectn_register_post_settings_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = tectn_site_settings_parent_slug();
	// Only add if parent exists (Site Settings from ACF UI).
	$pages = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page( array(
		'page_title'   => 'Post Settings',
		'menu_title'   => 'Post Settings',
		'menu_slug'    => 'post-settings',
		'parent_slug'  => $parent,
		'capability'   => 'edit_posts',
		'post_id'      => 'post-settings', // Store/load fields under this key so get_field( $field, 'post-settings' ) works.
	) );
}
add_action( 'acf/init', 'tectn_register_post_settings_options_page', 10 );

/**
 * Register Events Page Settings under Site Settings (moved from Theme Settings).
 * Keeps menu_slug / post_id theme-events-settings so existing field values and get_field() calls still work.
 */
function tectn_register_events_page_options_under_site_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = tectn_site_settings_parent_slug();
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Events Page Settings',
			'menu_title'  => 'Events',
			'menu_slug'   => 'theme-events-settings',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'theme-events-settings',
		)
	);
}
add_action( 'acf/init', 'tectn_register_events_page_options_under_site_settings', 10 );

/**
 * Register Footer Information as a sub-page of Site Settings (contact, social, CTAs, disclaimer).
 */
function tectn_register_footer_information_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = tectn_site_settings_parent_slug();
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Footer Information',
			'menu_title'  => 'Footer Information',
			'menu_slug'   => 'footer-information',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'footer-information',
		)
	);
}
add_action( 'acf/init', 'tectn_register_footer_information_options_page', 10 );

/**
 * Register Forms (embedded form snippets) under Site Settings.
 */
function tectn_register_forms_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = tectn_site_settings_parent_slug();
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Forms',
			'menu_title'  => 'Forms',
			'menu_slug'   => 'tectn-forms',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'tectn-forms',
		)
	);
}
add_action( 'acf/init', 'tectn_register_forms_options_page', 10 );

/**
 * Register Information tables under Site Settings (reusable four-column tables + block).
 */
function tectn_register_info_tables_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}
	$parent = tectn_site_settings_parent_slug();
	$pages  = function_exists( 'acf_get_options_pages' ) ? acf_get_options_pages() : array();
	if ( empty( $pages ) || ! isset( $pages[ $parent ] ) ) {
		return;
	}
	acf_add_options_sub_page(
		array(
			'page_title'  => 'Information tables',
			'menu_title'  => 'Information tables',
			'menu_slug'   => 'tectn-info-tables',
			'parent_slug' => $parent,
			'capability'  => 'edit_posts',
			'post_id'     => 'tectn-info-tables',
		)
	);
}
add_action( 'acf/init', 'tectn_register_info_tables_options_page', 10 );

function tectn_get_footer_information() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	if ( ! function_exists( 'get_field' ) ) {
		$cache = array();
		return $cache;
	}
	$out = array();
	$fi  = get_field( 'footer_information', 'footer-information' );
	if ( ! is_array( $fi ) ) {
		$fi = array();
	}
	$ss = get_field( 'site_settings', 'site-settings' );
	if ( ! is_array( $ss ) ) {
		$ss = get_field( 'site_settings', 'option' );
	}
	if ( ! is_array( $ss ) ) {
		$ss = array();
	}
	$keys = array(
		'contact_information',
		'address',
		'phone_number',
		'email_address',
		'social_platforms',
		'buttons',
		'button_color',
		'disclaimer_text',
	);
	foreach ( $keys as $key ) {
		$from_fi = array_key_exists( $key, $fi ) ? $fi[ $key ] : null;
		$from_ss = array_key_exists( $key, $ss ) ? $ss[ $key ] : null;
		// Legacy: contact saved on Brand options context before it moved to Footer Information.
		$from_legacy_brand = in_array( $key, array( 'address', 'phone_number', 'email_address' ), true )
			? get_field( $key, 'site-settings' )
			: null;
		// Legacy: contact stored at options-page root before fields moved into footer_information (see migrations.php).
		$from_legacy_root = in_array( $key, array( 'address', 'phone_number', 'email_address' ), true ) && function_exists( 'acf_get_metadata' )
			? acf_get_metadata( 'footer-information', $key, false )
			: null;
		if ( ! tectn_footer_information_value_is_empty( $key, $from_fi ) ) {
			$out[ $key ] = $from_fi;
		} elseif ( ! tectn_footer_information_value_is_empty( $key, $from_legacy_root ) ) {
			$out[ $key ] = $from_legacy_root;
		} elseif ( ! tectn_footer_information_value_is_empty( $key, $from_legacy_brand ) ) {
			$out[ $key ] = $from_legacy_brand;
		} elseif ( ! tectn_footer_information_value_is_empty( $key, $from_ss ) ) {
			$out[ $key ] = $from_ss;
		}
	}
	$cache = $out;
	return $cache;
}

/**
 * @param string $key Field name.
 * @param mixed  $val Value from ACF.
 */
function tectn_footer_information_value_is_empty( $key, $val ) {
	if ( null === $val ) {
		return true;
	}
	if ( 'social_platforms' === $key || 'buttons' === $key ) {
		return ! is_array( $val ) || array() === $val;
	}
	if ( 'contact_information' === $key ) {
		return ! is_array( $val ) || array() === $val;
	}
	if ( 'disclaimer_text' === $key || 'address' === $key || 'phone_number' === $key || 'email_address' === $key ) {
		return is_string( $val ) ? trim( $val ) === '' : empty( $val );
	}
	if ( 'button_color' === $key ) {
		return $val === '' || $val === null;
	}
	return empty( $val );
}
function tectn_register_acf_events_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'                   => 'group_tectn_events_settings',
		'title'                 => 'Events Page Hero & Intro',
		'fields'                => array(
			array(
				'key'   => 'field_tectn_events_hero_tab',
				'label' => 'Hero',
				'name'  => '',
				'type'  => 'tab',
			),
			array(
				'key'           => 'field_tectn_events_use_solid_color',
				'label'         => 'Background',
				'name'          => 'events_hero_use_solid_color',
				'type'          => 'true_false',
				'instructions'  => 'Use a solid color (no overlay). Leave off to use a background image with gradient overlay.',
				'ui'            => 1,
				'default_value' => 0,
			),
			array(
				'key'               => 'field_tectn_events_hero_bg_color',
				'label'             => 'Background color',
				'name'              => 'events_hero_background_color',
				'type'              => 'color_picker',
				'default_value'     => '#238c55',
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_tectn_events_use_solid_color',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
			),
			array(
				'key'               => 'field_tectn_events_hero_bg_image',
				'label'             => 'Background image',
				'name'              => 'events_hero_background_image',
				'type'              => 'image',
				'return_format'     => 'id',
				'preview_size'      => 'medium',
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_tectn_events_use_solid_color',
							'operator' => '!=',
							'value'    => '1',
						),
					),
				),
			),
			array(
				'key'   => 'field_tectn_events_hero_headline',
				'label' => 'Hero headline',
				'name'  => 'events_hero_headline_text',
				'type'  => 'text',
				'instructions' => 'Text shown over the hero (e.g. "Events").',
			),
			array(
				'key'   => 'field_tectn_events_intro_tab',
				'label' => 'Intro (above calendar)',
				'name'  => '',
				'type'  => 'tab',
			),
			array(
				'key'   => 'field_tectn_events_intro_heading',
				'label' => 'Intro heading',
				'name'  => 'events_intro_heading',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_tectn_events_intro_body',
				'label' => 'Intro body',
				'name'  => 'events_intro_body',
				'type'  => 'wysiwyg',
				'tabs'  => 'all',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'theme-events-settings',
				),
			),
		),
	) );
}
add_action( 'acf/init', 'tectn_register_acf_events_settings' );
