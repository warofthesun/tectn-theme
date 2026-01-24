<!--HERO-MEDIUM-->
<div class="hero__container hero__container_medium">
	<div class="hero__container_inner">
		<div class="hero__content hero__content_image col-xs-12"  style="background-image: url('<?php if( get_field('hero_image') ) : the_field('hero_image');  endif; ?>')">
		<div class="hero__content hero__content_text col-xs-12 col-md-2">
			<h1><?php if( get_field('page_title') ) : the_field('page_title');  else : the_title(); endif; ?></h1>
		</div>
		<div class="hero__content hero__content_text-angle col-xs-12 col-md-10">
			<?php echo file_get_contents( get_template_directory() . '/library/images/hero_medium_gold_angle.svg' ); ?>
		</div>
		</div>
	</div>
	<div class="content_curve__outer">
		<?php echo file_get_contents( get_template_directory() . '/library/images/content_curve__outer.svg' ); ?>
	</div>
</div>