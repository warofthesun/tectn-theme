<?php
/**
 * TecTN theme bootstrap — loads modular includes from `inc/`.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tectn_theme_dir = get_template_directory();

// LOAD starter CORE (if you remove this, the theme will break).
require_once $tectn_theme_dir . '/library/starter.php';

// CUSTOMIZE THE WORDPRESS ADMIN (off by default).
// require_once $tectn_theme_dir . '/library/admin.php';

require_once $tectn_theme_dir . '/inc/blocks.php';
require_once $tectn_theme_dir . '/inc/helpers.php';
require_once $tectn_theme_dir . '/inc/blog.php';
require_once $tectn_theme_dir . '/inc/hero.php';
require_once $tectn_theme_dir . '/inc/related-posts.php';
require_once $tectn_theme_dir . '/inc/events.php';

/**
 * Register the required plugins for this theme.
 */
require_once $tectn_theme_dir . '/inc/required-plugs.php';

/**
 * Load bundled ACF only when ACF Pro is not active (no early return from this file).
 */
require_once $tectn_theme_dir . '/inc/acf-bundled.php';
tectn_load_bundled_acf_if_needed();

require_once $tectn_theme_dir . '/inc/theme-setup.php';
require_once $tectn_theme_dir . '/inc/editor.php';
require_once $tectn_theme_dir . '/inc/acf-options.php';
require_once $tectn_theme_dir . '/inc/forms.php';
require_once $tectn_theme_dir . '/inc/migrations.php';
require_once $tectn_theme_dir . '/inc/shortcodes-misc.php';
