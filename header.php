<!doctype html>
<html <?php language_attributes(); ?> class="no-js">

<head>
		<meta charset="utf-8">

		<title><?php wp_title(''); ?></title>

	<?php // mobile meta (hooray!) ?>
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

		<?php // icons & favicons (for more: http://www.jonathantneal.com/blog/understand-the-favicon/) ?>
		<link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/library/images/apple-touch-icon.png">
		<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
		<meta name="msapplication-TileColor" content="#f01d4f">
		<meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/library/images/win8-tile-icon.png">
            <meta name="theme-color" content="#121212">

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

		<?php // wordpress head functions — Font Awesome & ScrollReveal enqueued in starter.php ?>
		<?php wp_head(); ?>
		<?php // end of wordpress head ?>
		<?php // drop Google Analytics Here ?>
		<?php // end analytics ?>

	</head>

	<body <?php body_class(); ?> itemscope itemtype="http://schema.org/WebPage">

		<div id="container">

			<?php
			$hero_cfg = function_exists( 'tectn_get_hero_config' ) ? tectn_get_hero_config() : array();
			$hero_single = isset( $hero_cfg['type'] ) && $hero_cfg['type'] === 'single';
			$header_classes = 'header';
			if ( $hero_single ) {
				$header_classes .= ' header--hero-transparent';
			}
			$logo = null;
			if ( function_exists( 'get_field' ) ) {
				$site_settings = get_field( 'site_settings', 'site-settings' );
				if ( ! is_array( $site_settings ) || ! isset( $site_settings['primary_logo'] ) ) {
					$site_settings = get_field( 'site_settings', 'option' );
				}
				if ( is_array( $site_settings ) ) {
					if ( $hero_single && ! empty( $site_settings['secondary_logo'] ) && is_array( $site_settings['secondary_logo'] ) && ! empty( $site_settings['secondary_logo']['url'] ) ) {
						$logo = $site_settings['secondary_logo'];
					} elseif ( isset( $site_settings['primary_logo'] ) ) {
						$logo = $site_settings['primary_logo'];
					}
				}
			}
		?>
		<header class="<?php echo esc_attr( $header_classes ); ?>" role="banner" itemscope itemtype="http://schema.org/WPHeader">

				<div id="inner-header" class="row">
					<a href="<?php echo home_url(); ?>" class="header-logo" rel="nofollow">
						<div id="logo" class="h1" itemscope itemtype="http://schema.org/Organization" aria-label="<?php bloginfo('name'); ?>">
							<?php
							if ( $logo && is_array( $logo ) && ! empty( $logo['url'] ) ) {
								$alt = ! empty( $logo['alt'] ) ? $logo['alt'] : get_bloginfo( 'name' );
								echo '<img src="' . esc_url( $logo['url'] ) . '" alt="' . esc_attr( $alt ) . '">';
							} else {
								echo '<img src="' . esc_url( get_template_directory_uri() . '/library/images/tectn-logo.png' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
							}
							?>
						</div>
					</a>
					<nav role="navigation" class="header-nav" itemscope itemtype="http://schema.org/SiteNavigationElement">
						<div class="header-nav__wrapper">
							<?php wp_nav_menu(array(
								'container' => false,
								'container_class' => 'menu ',
								'menu' => __( 'The Main Menu', 'tectn_theme' ),
								'menu_class' => 'nav top-nav ',
								'theme_location' => 'main-nav',
								'before' => '',
								'after' => '',
								'link_before' => '',
								'link_after' => '',
								'depth' => 0,
								'fallback_cb' => ''
							)); ?>

							<?php wp_nav_menu(array(
								'container' => false,
								'container_class' => 'login_forms ',
								'menu' => __( 'Login and Forms', 'tectn_theme' ),
								'menu_class' => 'nav top-nav ',
								'theme_location' => 'login_forms',
								'before' => '',
								'after' => '',
								'link_before' => '',
								'link_after' => '',
								'depth' => 0,
								'fallback_cb' => ''
							)); ?>
						</div>
					</nav>
					<?php if ( is_active_sidebar( 'header-widget-area' ) ) : ?>
						<div class="header-widget-area" role="complementary">
							<?php dynamic_sidebar( 'header-widget-area' ); ?>
						</div>
					<?php endif; ?>
					<div id="mobile-nav">
						Menu <i class="fas fa-chevron-down"></i>
					</div>
				</div>

			</header>
