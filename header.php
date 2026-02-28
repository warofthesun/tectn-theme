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

			<header class="header" role="banner" itemscope itemtype="http://schema.org/WPHeader">

				<div id="inner-header" class="row">
					<nav role="navigation" class="header-nav" itemscope itemtype="http://schema.org/SiteNavigationElement">
					<a href="<?php echo home_url(); ?>" rel="nofollow">
						<div id="logo" class="h1" itemscope itemtype="http://schema.org/Organization" aria-label="<?php bloginfo('name'); ?>">
							<img src="<?php echo get_template_directory_uri(); ?>/library/images/tectn-logo.png" alt="Tectn Logo">
						</div>
					</a>
					<div class="header-nav__wrapper">
						<?php wp_nav_menu(array(
    					         'container' => false,                           // remove nav container
    					         'container_class' => 'menu ',                 // class of container (should you choose to use it)
    					         'menu' => __( 'The Main Menu', 'tectn_theme' ),  // nav name
    					         'menu_class' => 'nav top-nav ',               // adding custom nav class
    					         'theme_location' => 'main-nav',                 // where it's located in the theme
    					         'before' => '',                                 // before the menu
        			               'after' => '',                                  // after the menu
        			               'link_before' => '',                            // before each link
        			               'link_after' => '',                             // after each link
        			               'depth' => 0,                                   // limit the depth of the nav
    					         'fallback_cb' => ''                             // fallback function (if there is one)
						)); ?>

						<?php wp_nav_menu(array(
    					         'container' => false,                           // remove nav container
    					         'container_class' => 'login_forms ',                 // class of container (should you choose to use it)
    					         'menu' => __( 'Login and Forms', 'tectn_theme' ),  // nav name
    					         'menu_class' => 'nav top-nav ',               // adding custom nav class
    					         'theme_location' => 'login_forms',                 // where it's located in the theme
    					         'before' => '',                                 // before the menu
        			               'after' => '',                                  // after the menu
        			               'link_before' => '',                            // before each link
        			               'link_after' => '',                             // after each link
        			               'depth' => 0,                                   // limit the depth of the nav
    					         'fallback_cb' => ''                             // fallback function (if there is one)
						)); ?>
					</div>
					</nav>
					<div id="mobile-nav">
						Menu <i class="fas fa-chevron-down"></i>
					</div>
				</div>

			</header>
