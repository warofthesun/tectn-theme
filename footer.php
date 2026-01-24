<?php if(get_field('include_footer_testimonials')): include 'partials/testimonials/testimonials-footer.php'; endif; ?>
<div class="footer__tagline"><?php the_field('footer_tagline', 'option'); ?> </div>

<footer class="footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
	
<div class="footer__curve">
	<div class="lower_content_curve"><?php echo file_get_contents( get_template_directory() . '/library/images/lower_content_curve.svg' ); ?></div>
</div>
<div id="inner-footer" class="wrap row">
	<div class="col-xs-12 col-md-6 logo_nav">
		<div class="footer__logo"><?php echo file_get_contents( get_template_directory() . '/library/images/mfa_logo_dark.svg' ); ?></div>
		<nav role="navigation">
			<?php wp_nav_menu(array(
			'container' => 'div',                           // enter '' to remove nav container (just make sure .footer-links in _base.scss isn't wrapping)
			'container_class' => 'footer-links ',         // class of container (should you choose to use it)
			'menu' => __( 'Footer Links', 'trustmfa_theme' ),   // nav name
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
	</div>
	<div class="col-xs-12 col-md-6 contact_info">
		<?php if(get_field('address', 'option')): ?><div class="address__physical"><?php the_field('address', 'option'); ?></div><?php endif; ?>
		<?php if(get_field('phone_number', 'option')): ?><div class="phone_number"><?php the_field('phone_number', 'option'); ?></div><?php endif; ?>
		<?php if(get_field('email_address', 'option')): ?><a href="mailto:<?php the_field('email_address', 'option');?>" class="address__email">email</a><?php endif; ?>
		
	</div>
	
	
<div class="source-org copyright">&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>.</div>
</div>


			</footer>

		</div>

		<?php // all js scripts are loaded in library/starter.php ?>
		<?php wp_footer(); ?>

		

	</body>

</html> <!-- end of site. what a ride! -->
