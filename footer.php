<?php if(get_field('include_footer_testimonials')): include 'partials/testimonials/testimonials-footer.php'; endif; ?>
<div class="footer__tagline"><?php the_field('footer_tagline', 'option'); ?> </div>

<footer class="footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
	

<div id="inner-footer" class="wrap row">
	<div class="col-xs-12 col-md-6 footer__logo-nav">
		<div class="footer__logo"><?php echo file_get_contents( get_template_directory() . '/library/images/mfa_logo_dark.svg' ); ?></div>
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
	</div>
	<div class="col-xs-12 col-md-6 footer__contact">
		<?php if(get_field('address', 'option')): ?><div class="footer__address"><?php the_field('address', 'option'); ?></div><?php endif; ?>
		<?php if(get_field('phone_number', 'option')): ?><div class="footer__phone"><?php the_field('phone_number', 'option'); ?></div><?php endif; ?>
		<?php if(get_field('email_address', 'option')): ?><a href="mailto:<?php the_field('email_address', 'option');?>" class="footer__email">email</a><?php endif; ?>
		
	</div>
	
	
<div class="footer__copyright">&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>.</div>
</div>


			</footer>

		</div>

		<?php // all js scripts are loaded in library/starter.php ?>
		<?php wp_footer(); ?>

		

	</body>

</html> <!-- end of site. what a ride! -->
