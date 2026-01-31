<!--page-->
<?php get_header(); ?>
<?php if (is_front_page() ) :
	include 'partials/hero/hero-landing.php'; else : $hero_style = get_field('hero_style'); ?>
	<?php if ($hero_style == 'landing'): include 'partials/hero/hero-landing.php'; elseif ($hero_style == 'large'): ?>
	<?php include 'partials/hero/hero-large.php'; ?>
	<?php elseif ($hero_style == 'medium'): ?>
		<?php include 'partials/hero/hero-medium.php'; ?>
	<?php elseif ($hero_style == 'small'): ?>
		<?php include 'partials/hero/hero-small.php'; ?>
	<?php else : endif; ?>
<?php endif; ?>

			<div id="content">

				<div id="inner-content" class="wrap  row">
						<main id="main" class="col-xs-12" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
							<?php if( have_rows('custom_page_content')) : ?>
								<?php while( have_rows('custom_page_content')) : the_row(); 
									if( get_row_layout() == 'multi_row_content_image'):
										$row_one = get_sub_field('row_one');

										$headline       = $row_one['headline'] ?? '';
										$body           = $row_one['body_copy'] ?? '';
										$primary_cta    = $row_one['primary_cta'] ?? null;   // ACF Link array
										$secondary_cta  = $row_one['secondary_cta'] ?? null; // ACF Link array
										$feature_image  = $row_one['feature_image'] ?? null; // ACF Image array (recommended)
										$support_1      = $row_one['support_image_1'] ?? null;
										$support_2      = $row_one['support_image_2'] ?? null;
									endif; ?>

									<?php echo esc_html($headline); ?>
									<?php echo wp_kses_post($body); ?>

								<?php endwhile; endif; ?>
							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

								<!--header class="article-header">

									<h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>

									<p class="byline vcard">
										<?php //printf( __( 'Posted', 'trustmfa_theme').' <time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time> '.__( 'by',  'trustmfa_theme').' <span class="author">%3$s</span>', get_the_time('Y-m-j'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?>
									</p>

								</header--> <?php // end article header ?>

								<section class="entry-content " itemprop="articleBody">

									<?php the_content(); ?>

									<?php if( have_rows('faqs') ): include 'partials/faqs.php'; endif; ?>

									<?php if( have_rows('services') ): include 'partials/services.php'; endif; ?>

									<?php if(have_rows('resources')) : include 'partials/resources.php'; endif; ?>

								</section> <?php // end article section ?>

							</article>

							<?php endwhile; endif; ?>

						</main>

				</div>

			</div>
<?php get_footer(); ?>
