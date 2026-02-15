<!--HERO-SMALL-->
<div class="hero__container hero__container--small">
	<div class="hero__container--inner row">
		<div class="hero__content hero__content--text  col-xs-12">
			<h1><?php if( get_field('page_title') ) : the_field('page_title'); else: single_post_title();  endif; ?></h1>
		</div>
	</div>
	<!--div class="header_curve_lower"><?php //echo file_get_contents( get_template_directory() . '/library/images/lower_blue_header_curve.svg' ); ?></div-->
	<div class="content-curve content-curve--outer">
		<?php echo file_get_contents( get_template_directory() . '/library/images/content_curve__outer.svg' ); ?>
	</div>
</div>