<!--HERO-LARGE-->
<div class="hero__container hero__container--large">
	<div class="hero__container--inner">
		<div class="hero__content hero__content--image col-xs-12"  style="background-image: url('<?php if( get_field('hero_image') ) : the_field('hero_image');  endif; ?>')">
		<div class="hero__content hero__content--text col-xs-12">
			<h1><?php if( get_field('page_title') ) : the_field('page_title');  else : the_title(); endif; ?></h1>
		</div>
		<div class="hero__content hero__content--text-angle col-xs-12 col-md-10">
			<?php echo file_get_contents( get_template_directory() . '/library/images/hero_large_gold_angle.svg' ); ?>
		</div>
		</div>
	</div>
	<div class="content-curve content-curve--up">
		<?php echo file_get_contents( get_template_directory() . '/library/images/content_curve__up.svg' ); ?>
	</div>
</div>