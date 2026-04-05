<footer class="footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
	<?php
	$footer_opts  = function_exists( 'tectn_get_footer_information' ) ? tectn_get_footer_information() : array();
	$has_contact  = false;
	$contact_info = array();
	if ( ! empty( $footer_opts['contact_information'] ) && is_array( $footer_opts['contact_information'] ) ) {
		$contact_info = $footer_opts['contact_information'];
	} else {
		// Seamless clone may store fields at group root.
		$contact_info = array(
			'address'       => isset( $footer_opts['address'] ) ? $footer_opts['address'] : '',
			'phone_number'  => isset( $footer_opts['phone_number'] ) ? $footer_opts['phone_number'] : '',
			'email_address' => isset( $footer_opts['email_address'] ) ? $footer_opts['email_address'] : '',
		);
	}
	$addr  = isset( $contact_info['address'] ) ? $contact_info['address'] : '';
	$phone = isset( $contact_info['phone_number'] ) ? $contact_info['phone_number'] : '';
	$email = isset( $contact_info['email_address'] ) ? $contact_info['email_address'] : '';
	$has_contact = ( is_string( $addr ) && trim( $addr ) !== '' ) || ( is_string( $phone ) && trim( $phone ) !== '' ) || ( is_string( $email ) && trim( $email ) !== '' );
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
		$social_platforms = ( ! empty( $footer_opts['social_platforms'] ) && is_array( $footer_opts['social_platforms'] ) )
			? $footer_opts['social_platforms']
			: array();
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
			<?php if ( ($phone || $email) !== '' ) : ?>
				<div class="footer__contact-info">
					<?php if ( trim( (string) $phone ) !== '' ) : ?><div class="footer__phone contact-info__item"><?php echo esc_html( $phone ); ?></div><?php endif; ?>
					<?php if ( trim( (string) $email ) !== '' ) : ?><a href="mailto:<?php echo esc_attr( $email ); ?>" class="footer__email contact-info__item"><?php echo esc_html( $email ); ?></a><?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<div class="col-xs-12 col-md-6 footer__right">
		<?php
		$cta_buttons   = ( ! empty( $footer_opts['buttons'] ) && is_array( $footer_opts['buttons'] ) )
			? $footer_opts['buttons']
			: array();
		$cta_btn_color = ! empty( $footer_opts['button_color'] ) ? $footer_opts['button_color'] : '';
		if ( ! empty( $cta_buttons ) ) {
			$buttons_data = $cta_buttons;
			$button_color = $cta_btn_color;
			include get_template_directory() . '/partials/button_pair.php';
		}

		$disclaimer_text = ( isset( $footer_opts['disclaimer_text'] ) && $footer_opts['disclaimer_text'] !== '' )
			? (string) $footer_opts['disclaimer_text']
			: '';
		// With contact info the right column uses text-align:right (see _64em.scss); keep disclaimer there + --right.
		// Without contact, render disclaimer full-width so it centers across the footer instead of the right half-column.
		if ( $has_contact && $disclaimer_text !== '' ) :
			?>
		<div class="footer__disclaimer footer__disclaimer--right">
			<?php
			$disclaimer_trimmed = trim( $disclaimer_text );
			if ( preg_match( '/<\s*br\s*\/?>/i', $disclaimer_trimmed ) ) {
				echo wp_kses_post( $disclaimer_trimmed );
			} else {
				echo nl2br( esc_html( $disclaimer_trimmed ), false );
			}
			?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( ! $has_contact && $disclaimer_text !== '' ) : ?>
	<div class="col-xs-12 footer__disclaimer-wrap">
		<div class="footer__disclaimer">
			<?php
			$disclaimer_trimmed = trim( $disclaimer_text );
			if ( preg_match( '/<\s*br\s*\/?>/i', $disclaimer_trimmed ) ) {
				echo wp_kses_post( $disclaimer_trimmed );
			} else {
				echo nl2br( esc_html( $disclaimer_trimmed ), false );
			}
			?>
		</div>
	</div>
	<?php endif; ?>

	<div class="footer__copyright">&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>.</div>
</div>


			</footer>

		</div>

		<?php // all js scripts are loaded in library/starter.php ?>
		<?php wp_footer(); ?>

		

	</body>

</html> <!-- end of site. what a ride! -->
