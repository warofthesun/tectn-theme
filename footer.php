<footer class="footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
	<?php
	$has_contact   = false;
	$contact_info  = array();
	if ( function_exists( 'get_field' ) ) {
		$site_settings = get_field( 'site_settings', 'site-settings' );
		if ( ! is_array( $site_settings ) ) {
			$site_settings = get_field( 'site_settings', 'option' );
		}
		if ( is_array( $site_settings ) ) {
			if ( ! empty( $site_settings['contact_information'] ) && is_array( $site_settings['contact_information'] ) ) {
				$contact_info = $site_settings['contact_information'];
			} else {
				// Seamless clone may store fields at site_settings root.
				$contact_info = array(
					'address'       => isset( $site_settings['address'] ) ? $site_settings['address'] : '',
					'phone_number'  => isset( $site_settings['phone_number'] ) ? $site_settings['phone_number'] : '',
					'email_address' => isset( $site_settings['email_address'] ) ? $site_settings['email_address'] : '',
				);
			}
		}
		$addr  = isset( $contact_info['address'] ) ? $contact_info['address'] : '';
		$phone = isset( $contact_info['phone_number'] ) ? $contact_info['phone_number'] : '';
		$email = isset( $contact_info['email_address'] ) ? $contact_info['email_address'] : '';
		$has_contact = ( is_string( $addr ) && trim( $addr ) !== '' ) || ( is_string( $phone ) && trim( $phone ) !== '' ) || ( is_string( $email ) && trim( $email ) !== '' );
	}
	?>
<div id="inner-footer" class="wrap row">
	<div class="col-xs-12 col-md-6 footer__logo-nav">
		<div class="footer__logo">
			<?php
			$footer_logo = null;
			if ( function_exists( 'get_field' ) ) {
				$site_settings = get_field( 'site_settings', 'site-settings' );
				if ( ! is_array( $site_settings ) ) {
					$site_settings = get_field( 'site_settings', 'option' );
				}
				if ( is_array( $site_settings ) ) {
					if ( ! empty( $site_settings['secondary_logo'] ) && is_array( $site_settings['secondary_logo'] ) && ! empty( $site_settings['secondary_logo']['url'] ) ) {
						$footer_logo = $site_settings['secondary_logo'];
					} elseif ( ! empty( $site_settings['primary_logo'] ) && is_array( $site_settings['primary_logo'] ) && ! empty( $site_settings['primary_logo']['url'] ) ) {
						$footer_logo = $site_settings['primary_logo'];
					}
				}
			}
			if ( $footer_logo && ! empty( $footer_logo['url'] ) ) {
				$alt = ! empty( $footer_logo['alt'] ) ? $footer_logo['alt'] : get_bloginfo( 'name' );
				echo '<img src="' . esc_url( $footer_logo['url'] ) . '" alt="' . esc_attr( $alt ) . '">';
			} else {
				echo '<img src="' . esc_url( get_template_directory_uri() . '/library/images/tectn-logo.png' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
			}
			?>
		</div>
		<?php
		$social_platforms = array();
		if ( function_exists( 'get_field' ) ) {
			$footer_site_settings = get_field( 'site_settings', 'site-settings' );
			if ( ! is_array( $footer_site_settings ) ) {
				$footer_site_settings = get_field( 'site_settings', 'option' );
			}
			if ( is_array( $footer_site_settings ) && ! empty( $footer_site_settings['social_platforms'] ) ) {
				$social_platforms = $footer_site_settings['social_platforms'];
			}
		}
		if ( ! empty( $social_platforms ) ) :
			?>
		<nav class="footer__social" aria-label="<?php esc_attr_e( 'Social media', 'tectn_theme' ); ?>">
			<ul class="footer__social-list">
			<?php
			foreach ( $social_platforms as $row ) {
				$link = isset( $row['social_link'] ) && is_array( $row['social_link'] ) ? $row['social_link'] : array();
				$url  = isset( $link['url'] ) ? $link['url'] : '';
				if ( $url === '' ) {
					continue;
				}
				$title  = isset( $link['title'] ) ? $link['title'] : '';
				$target = isset( $link['target'] ) ? $link['target'] : '_blank';
				$icon   = isset( $row['social_icon'] ) ? $row['social_icon'] : '';
				if ( is_array( $icon ) && ! empty( $icon['class'] ) ) {
					$icon_html = '<i class="' . esc_attr( $icon['class'] ) . '" aria-hidden="true"></i>';
				} elseif ( is_string( $icon ) && $icon !== '' ) {
					if ( strpos( $icon, '<' ) !== false ) {
						$icon_html = $icon;
					} else {
						$icon_html = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
					}
				} else {
					$icon_html = '';
				}
				if ( $icon_html !== '' ) {
					echo '<li class="footer__social-item"><a href="' . esc_url( $url ) . '" class="footer__social-link" target="' . esc_attr( $target ) . '" rel="noopener noreferrer"' . ( $title ? ' title="' . esc_attr( $title ) . '"' : '' ) . '>' . $icon_html . '<span class="screen-reader-text">' . esc_html( $title ? $title : __( 'Social link', 'tectn_theme' ) ) . '</span></a></li>';
				}
			}
			?>
			</ul>
		</nav>
		<?php endif; ?>
		<nav role="navigation">
			<?php wp_nav_menu(array(
			'container' => 'div',
			'container_class' => 'footer__links ',
			'menu' => __( 'Footer Links', 'tectn_theme' ),   // nav name
			'menu_class' => 'nav footer-nav ',            // adding custom nav class
			'theme_location' => 'footer-links',             // where it's located in the theme
			'before' => '',                                 // before the menu
			'after' => '',                                  // after the menu
			'link_before' => '',                            // before each link
			'link_after' => '',                             // after each link
			'depth' => 0,                                   // limit the depth of the nav
			'fallback_cb' => 'starter_footer_links_fallback'  // fallback function
			)); ?>
		</nav>
		<?php
		if ( $has_contact ) :
			?>
		<div class="footer__contact footer__contact--left">
			<?php if ( trim( (string) $addr ) !== '' ) : ?><div class="footer__address"><?php echo wp_kses_post( $addr ); ?></div><?php endif; ?>
			<?php if ( trim( (string) $phone ) !== '' ) : ?><div class="footer__phone"><?php echo esc_html( $phone ); ?></div><?php endif; ?>
			<?php if ( trim( (string) $email ) !== '' ) : ?><a href="mailto:<?php echo esc_attr( $email ); ?>" class="footer__email"><?php echo esc_html( $email ); ?></a><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<div class="col-xs-12 col-md-6 footer__right">
		<?php
		$cta_buttons   = array();
		$cta_btn_color = '';
		if ( function_exists( 'get_field' ) ) {
			$cta_site_settings = get_field( 'site_settings', 'site-settings' );
			if ( ! is_array( $cta_site_settings ) ) {
				$cta_site_settings = get_field( 'site_settings', 'option' );
			}
			if ( is_array( $cta_site_settings ) && ! empty( $cta_site_settings['buttons'] ) ) {
				$cta_buttons = $cta_site_settings['buttons'];
			}
			if ( ! empty( $cta_site_settings['button_color'] ) ) {
				$cta_btn_color = $cta_site_settings['button_color'];
			}
		}
		if ( ! empty( $cta_buttons ) ) {
			$buttons_data = $cta_buttons;
			$button_color = $cta_btn_color;
			include get_template_directory() . '/partials/button_pair.php';
		}

		$disclaimer_text = '';
		if ( function_exists( 'get_field' ) ) {
			$disclaimer_settings = get_field( 'site_settings', 'site-settings' );
			if ( ! is_array( $disclaimer_settings ) ) {
				$disclaimer_settings = get_field( 'site_settings', 'option' );
			}
			if ( is_array( $disclaimer_settings ) && isset( $disclaimer_settings['disclaimer_text'] ) && $disclaimer_settings['disclaimer_text'] !== '' ) {
				$disclaimer_text = $disclaimer_settings['disclaimer_text'];
			}
		}
		if ( $disclaimer_text !== '' ) :
			?>
		<div class="footer__disclaimer <?php echo $has_contact ? 'footer__disclaimer--right' : ''; ?>">
			<?php echo nl2br( esc_html( trim( $disclaimer_text ) ) ); ?>
		</div>
		<?php endif; ?>
	</div>

	<div class="footer__copyright">&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>.</div>
</div>


			</footer>

		</div>

		<?php // all js scripts are loaded in library/starter.php ?>
		<?php wp_footer(); ?>

		

	</body>

</html> <!-- end of site. what a ride! -->
