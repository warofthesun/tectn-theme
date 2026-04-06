<?php
/**
 * Theme includes.
 * @package tectn_theme
 * Events Calendar integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the external URL for an event, if set, normalized with https://.
 *
 * @param int $event_id Event post ID.
 * @return string External URL or empty string.
 */
function tectn_get_event_external_url( $event_id ) {
  $event_id = (int) $event_id;
  if ( $event_id <= 0 || get_post_type( $event_id ) !== 'tribe_events' ) {
    return '';
  }
  if ( ! function_exists( 'get_field' ) ) {
    return '';
  }
  $raw = get_field( 'external_event_url', $event_id );
  if ( ! is_string( $raw ) ) {
    return '';
  }
  $raw = trim( $raw );
  if ( $raw === '' ) {
    return '';
  }
  // Auto-add https:// when the scheme is missing.
  if ( ! preg_match( '#^https?://#i', $raw ) ) {
    $raw = 'https://' . ltrim( $raw, '/' );
  }
  $url = esc_url_raw( $raw );
  return $url ? $url : '';
}

/**
 * Whether events should require an external URL (from Post Settings > Events).
 *
 * @return bool
 */
function tectn_events_require_external_url() {
  if ( ! function_exists( 'get_field' ) ) {
    return false;
  }
  $required = get_field( 'events_require_external_url', 'post-settings' );
  return (bool) $required;
}

/**
 * Override tribe_events event links globally with the external URL when present.
 */
function tectn_filter_tribe_event_links( $url, $post, $leavename, $sample ) {
  if ( ! $post || $post->post_type !== 'tribe_events' ) {
    return $url;
  }
  $external = tectn_get_event_external_url( $post->ID );
  return $external ? $external : $url;
}
add_filter( 'post_type_link', 'tectn_filter_tribe_event_links', 10, 4 );

if ( function_exists( 'tribe_get_event_link' ) ) {
  /**
   * Mirror override for TEC helper links, when used.
   *
   * @param string $link
   * @param int    $event_id
   * @return string
   */
  function tectn_filter_tribe_get_event_link( $link, $event_id ) {
    $external = tectn_get_event_external_url( $event_id );
    return $external ? $external : $link;
  }
  add_filter( 'tribe_get_event_link', 'tectn_filter_tribe_get_event_link', 10, 2 );
}

/**
 * Enqueue Events Calendar overrides after plugin CSS (Skeleton or Full)
 * so theme fonts and colors apply. Loads after the active stylesheet to avoid duplicate enqueue.
 */
function tectn_enqueue_tribe_events_overrides() {
	if ( ! tectn_is_events_listing_view() && ! is_singular( 'tribe_events' ) ) {
		return;
	}
	$path = get_template_directory() . '/tribe-events/tribe-events.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	// Depend on the active style so override loads after it; avoid loading the same file twice.
	wp_dequeue_style( 'tribe-events-views-v2-override-style' );
	$dep = ( function_exists( 'tribe_get_option' ) && 'skeleton' === tribe_get_option( 'stylesheetOption', 'tribe' ) )
		? 'tribe-events-views-v2-skeleton'
		: 'tribe-events-views-v2-full';
	wp_enqueue_style(
		'tectn-tribe-events-overrides',
		get_template_directory_uri() . '/tribe-events/tribe-events.css',
		array( $dep ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'tectn_enqueue_tribe_events_overrides', 100 );

/**
 * Whether we are on a public Events Calendar list view (list/month/etc.), not a single event.
 * TEC “Default template” loads page.php with tribe_is_event_query; is_post_type_archive( tribe_events ) is false there.
 */
function tectn_is_events_listing_view() {
	if ( is_admin() ) {
		return false;
	}
	if ( ! post_type_exists( 'tribe_events' ) ) {
		return false;
	}
	if ( is_singular( 'tribe_events' ) ) {
		return false;
	}
	if ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) {
		return true;
	}
	return is_post_type_archive( 'tribe_events' );
}

/**
 * Get an Events page option (Site Settings > Events). Values are stored on post_id theme-events-settings.
 */
function tectn_get_events_option( $field_name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	if ( function_exists( 'get_field_object' ) ) {
		$field_object = get_field_object( $field_name, 'theme-events-settings' );
		if ( $field_object ) {
			return get_field( $field_name, 'theme-events-settings' );
		}
	}
	return get_field( $field_name, 'option' );
}

/**
 * Prepend Hero Medium + intro before TEC view HTML when a non-default Events template is selected.
 * Default TEC template uses page.php (intro) + header hero; see tectn_is_events_listing_view().
 */
function tectn_events_hero_and_intro_before_html( $before ) {
	if ( ! tectn_is_events_listing_view() ) {
		return $before;
	}
	// Hero + intro for “default” TEC template are output via header (hero) and page.php (intro); this filter only for other template modes.
	if ( function_exists( 'tribe_get_option' ) && tribe_get_option( 'tribeEventsTemplate', 'default' ) === 'default' ) {
		return $before;
	}
	$hero_config = tectn_get_hero_config();
	$out        = '';
	if ( ! empty( $hero_config['show'] ) && $hero_config['type'] === 'medium' ) {
		ob_start();
		$hero_config = $hero_config; // pass to partial
		include get_template_directory() . '/partials/hero/hero.php';
		$out .= ob_get_clean();
	}
	$intro_heading = tectn_get_events_option( 'events_intro_heading' );
	$intro_body    = tectn_get_events_option( 'events_intro_body' );
	if ( ( $intro_heading !== null && $intro_heading !== '' ) || ( $intro_body !== null && $intro_body !== '' ) ) {
		$out .= '<div class="events-intro wrap row">';
		$out .= '<div class="events-intro__inner col-xs-12">';
		if ( $intro_heading !== null && $intro_heading !== '' ) {
			$out .= '<h2 class="events-intro__heading">' . esc_html( $intro_heading ) . '</h2>';
		}
		if ( $intro_body !== null && $intro_body !== '' ) {
			$out .= '<div class="events-intro__body">' . wp_kses_post( $intro_body ) . '</div>';
		}
		$out .= '</div></div>';
	}
	return $out . $before;
}
add_filter( 'tribe_events_before_html', 'tectn_events_hero_and_intro_before_html', 10, 1 );
/**
 * Inject event categories (tags) below the body content; structure matches posts-grid chips.
 */
function tectn_tribe_event_list_categories_after_description() {
	global $post;
	if ( ! $post || ! isset( $post->ID ) ) {
		return;
	}
	$taxonomy = Tribe__Events__Main::instance()->get_event_taxonomy();
	$terms    = get_the_terms( $post->ID, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	echo '<div class="tribe-event-categories c-postCard__chips">';
	foreach ( $terms as $term ) {
		$url = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $url ) ) {
			$url = '#';
		}
		echo '<a href="' . esc_url( $url ) . '" class="c-chip">' . esc_html( $term->name ) . '</a>';
	}
	echo '</div>';
}
add_action( 'tribe_template_after_include:events/v2/list/event/description', 'tectn_tribe_event_list_categories_after_description' );

