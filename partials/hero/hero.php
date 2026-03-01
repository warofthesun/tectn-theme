<?php
/**
 * Reusable hero partial. Uses tectn_get_hero_config() for context (page, post, blog, archive).
 * Editor choice on pages via ACF "Hero Style"; automatic type on single post, blog index, archive.
 */
$hero = isset( $hero_config ) ? $hero_config : tectn_get_hero_config();
if ( empty( $hero['show'] ) ) {
  return;
}
$hero_type = isset( $hero['type'] ) ? $hero['type'] : 'landing';
$d = isset( $hero['data'] ) ? $hero['data'] : array();
?>
<!-- Hero (<?php echo esc_attr( $hero_type ); ?>) -->

<?php if ( $hero_type === 'post' ) : ?>
  <div class="hero__container hero__container--post">
    <div class="hero__post row wrap">
      <div class="hero__post-text col-xs-12 col-md-6">
        <?php if ( ! empty( $d['categories'] ) ) : ?>
          <p class="post-category"><?php echo $d['categories']; ?></p>
        <?php endif; ?>
        <h1 class="entry-title single-title" itemprop="headline" rel="bookmark"><?php echo esc_html( $d['title'] ); ?></h1>
        <p class="byline entry-meta vcard">
          <?php
          $datetime = isset( $d['date_iso'] ) ? $d['date_iso'] : '';
          printf(
            __( 'Posted', 'tectn_theme' ) . ' %1$s %2$s',
            '<time class="updated entry-time" datetime="' . esc_attr( $datetime ) . '" itemprop="datePublished">' . esc_html( $d['date'] ) . '</time>',
            '<span class="by">' . __( 'by', 'tectn_theme' ) . '</span> <span class="entry-author author" itemprop="author">' . $d['author_link'] . '</span>'
          );
          ?>
        </p>
      </div>
      <div class="hero__post-image col-xs-12 col-md-6">
        <div class="hero--image">
          <?php
          if ( ! empty( $d['image_id'] ) ) {
            echo wp_get_attachment_image( (int) $d['image_id'], 'gallery-image' );
          }
          ?>
        </div>
      </div>
    </div>
  </div>

<?php elseif ( $hero_type === 'blog' || $hero_type === 'archive' ) : ?>
  <?php
  $bg_url = '';
  if ( ! empty( $d['image_id'] ) ) {
    $img = wp_get_attachment_image_src( (int) $d['image_id'], 'hero-bg' );
    $bg_url = $img ? $img[0] : '';
  }
  ?>
  <div class="hero__container hero__container--<?php echo esc_attr( $hero_type ); ?>">
    <div class="hero__container--inner">
      <?php if ( $bg_url ) : ?>
        <div class="hero__content hero__content--image col-xs-12" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');"></div>
      <?php endif; ?>
      <div class="hero__content hero__content--text col-xs-12">
        <h1 class="hero__title page-title"><?php echo $d['title']; ?></h1>
        <?php if ( $hero_type === 'archive' && ! empty( $d['description'] ) ) : ?>
          <div class="hero__description taxonomy-description"><?php echo $d['description']; ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php elseif ( $hero_type === 'events' ) : ?>
  <?php
  // Events hero: same gradient overlay as initiative when image; solid color when no image; no wave, no logo.
  $events_bg_style = '';
  if ( isset( $d['background_type'] ) && $d['background_type'] === 'color' && ! empty( $d['background_color'] ) ) {
    $events_bg_style = 'background-color: ' . esc_attr( $d['background_color'] ) . ';';
  } elseif ( ! empty( $d['background_image'] ) ) {
    $img = wp_get_attachment_image_src( (int) $d['background_image'], 'hero-bg' );
    if ( $img ) {
      $events_bg_style = 'background-image: url(' . esc_url( $img[0] ) . ');';
    }
  }
  $has_image = ! empty( $events_bg_style ) && strpos( $events_bg_style, 'background-image' ) !== false;
  ?>
  <div class="hero__container hero__container--events hero__container--initiative hero__container--gradient-full">
    <div class="hero__container--inner">
      <?php if ( $events_bg_style ) : ?>
        <div class="hero__content hero__content--bg col-xs-12" style="<?php echo $events_bg_style; ?>"></div>
      <?php endif; ?>

      <?php if ( $has_image ) : ?>
        <div class="hero__overlay hero__overlay--initiative hero__overlay--full-width" aria-hidden="true"></div>
      <?php endif; ?>

      <div class="hero__content hero__content--text hero__content--initiative hero__content--events col-xs-12">
        <div class="hero__headline hero__headline--initiative hero__headline--events">
          <?php if ( ! empty( $d['headline_text'] ) ) : ?>
            <h1 class="hero__title hero__title--initiative"><?php echo esc_html( $d['headline_text'] ); ?></h1>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

<?php elseif ( $hero_type === 'initiative' ) : ?>
  <?php
  $init_bg_style = '';
  if ( isset( $d['background_type'] ) && $d['background_type'] === 'color' && ! empty( $d['background_color'] ) ) {
    $init_bg_style = 'background-color: ' . esc_attr( $d['background_color'] ) . ';';
  } elseif ( ! empty( $d['background_image'] ) ) {
    $img = wp_get_attachment_image_src( (int) $d['background_image'], 'hero-bg' );
    if ( $img ) {
      $init_bg_style = 'background-image: url(' . esc_url( $img[0] ) . ');';
    }
  }
  $gradient_class = ( isset( $d['gradient_style'] ) && $d['gradient_style'] === 'contained' ) ? 'hero__container--gradient-contained' : 'hero__container--gradient-full';
  ?>
  <div class="hero__container hero__container--initiative <?php echo esc_attr( $gradient_class ); ?>">
    <div class="hero__container--inner">
      <?php if ( $init_bg_style ) : ?>
        <div class="hero__content hero__content--bg col-xs-12" style="<?php echo $init_bg_style; ?>"></div>
      <?php endif; ?>

      <?php if ( $gradient_class === 'hero__container--gradient-full' ) : ?>
        <div class="hero__overlay hero__overlay--initiative hero__overlay--full-width" aria-hidden="true"></div>
      <?php endif; ?>

      <?php if ( $gradient_class === 'hero__container--gradient-full' ) : ?>
        <div class="hero__wave hero__wave--initiative" aria-hidden="true">
          <svg class="hero__wave-svg" viewBox="0 455 1000 160" preserveAspectRatio="none">
            <defs>
              <filter id="layerShadowWave" x="-35%" y="-35%" width="170%" height="170%">
                <feDropShadow dx="0" dy="-6" stdDeviation="14" flood-color="#000" flood-opacity="0.14"/>
                <feDropShadow dx="0" dy="-2" stdDeviation="5" flood-color="#000" flood-opacity="0.18"/>
              </filter>
              <filter id="edgeHighlightWave" x="-35%" y="-35%" width="170%" height="170%">
                <feDropShadow dx="0" dy="-1" stdDeviation="2" flood-color="#fff" flood-opacity="0.35"/>
              </filter>
            </defs>
            <path d="M0,565 C350,460 700,690 1000,510 L1000,615 L0,615 Z" fill="#FCF7EE" filter="url(#layerShadowWave)"/>
            <path d="M0,565 C350,460 700,690 1000,510" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="10" stroke-linecap="round" filter="url(#edgeHighlightWave)" opacity="0.55"/>
          </svg>
        </div>
      <?php endif; ?>

      <div class="hero__content hero__content--text hero__content--initiative col-xs-12">
        <div class="hero__headline hero__headline--initiative">
          <?php if ( ! empty( $d['headline_type'] ) && $d['headline_type'] === 'logo' && ! empty( $d['logo_id'] ) ) : ?>
            <?php echo wp_get_attachment_image( (int) $d['logo_id'], 'medium', false, array( 'class' => 'hero__logo' ) ); ?>
          <?php elseif ( ! empty( $d['headline_text'] ) ) : ?>
            <h1 class="hero__title hero__title--initiative"><?php echo esc_html( $d['headline_text'] ); ?></h1>
          <?php endif; ?>
        </div>
      </div>

      <?php if ( ! empty( $d['lower_content'] ) || $gradient_class === 'hero__container--gradient-contained' ) : ?>
        <div class="hero__lower hero__lower--initiative <?php echo $gradient_class === 'hero__container--gradient-contained' ? 'hero__lower--gradient-contained' : ''; ?>">
          <?php if ( $gradient_class === 'hero__container--gradient-contained' ) : ?>
            <div class="hero__lower-gradient hero__overlay hero__overlay--initiative hero__overlay--full-width" aria-hidden="true"></div>
            <div class="hero__wave hero__wave--initiative hero__wave--lower" aria-hidden="true">
              <svg class="hero__wave-svg" viewBox="0 455 1000 160" preserveAspectRatio="none">
                <defs>
                  <filter id="layerShadowWaveLower" x="-35%" y="-35%" width="170%" height="170%">
                    <feDropShadow dx="0" dy="-6" stdDeviation="14" flood-color="#000" flood-opacity="0.14"/>
                    <feDropShadow dx="0" dy="-2" stdDeviation="5" flood-color="#000" flood-opacity="0.18"/>
                  </filter>
                  <filter id="edgeHighlightWaveLower" x="-35%" y="-35%" width="170%" height="170%">
                    <feDropShadow dx="0" dy="-1" stdDeviation="2" flood-color="#fff" flood-opacity="0.35"/>
                  </filter>
                </defs>
                <path class="hero__wave-fill" d="M0,565 C350,460 700,690 1000,510 L1000,615 L0,615 Z" filter="url(#layerShadowWaveLower)"/>
                <path d="M0,565 C350,460 700,690 1000,510" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="10" stroke-linecap="round" filter="url(#edgeHighlightWaveLower)" opacity="0.55"/>
              </svg>
            </div>
          <?php endif; ?>
          <?php if ( ! empty( $d['lower_content'] ) ) : ?>
            <div class="hero__lower-content">
              <?php echo $d['lower_content']; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php else : ?>
  <?php
  // Landing (full hero with overlay, optional headline/paragraph/CTAs, optional featured post)
  $bg_url = '';
  if ( ! empty( $d['image_id'] ) ) {
    $img = wp_get_attachment_image_src( (int) $d['image_id'], 'hero-bg' );
    $bg_url = $img ? $img[0] : '';
  }
  $landing_internal = ! is_front_page();
  ?>
  <div class="hero__container hero__container--landing<?php echo $landing_internal ? ' hero__container--landing-internal' : ''; ?>">
    <div class="hero__container--inner">
      <div class="hero__content hero__content--image col-xs-12" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');"></div>

      <div class="hero__overlay hero__overlay--full-width" aria-hidden="true">
        <svg class="hero__overlay-svg" viewBox="0 0 1000 600" preserveAspectRatio="none">
          <defs>
            <linearGradient id="heroGrad" x1="1.5" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="#238c55" stop-opacity="0.70"/>
              <stop offset="0.55" stop-color="#3caa6e" stop-opacity="0.70"/>
              <stop offset="1" stop-color="#f5b049" stop-opacity="0.90"/>
            </linearGradient>
            <linearGradient id="heroGradTwo" x1="1.5" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="#238c55" stop-opacity="0.95"/>
              <stop offset="0.55" stop-color="#3caa6e" stop-opacity="0.90"/>
              <stop offset="1" stop-color="#f5b049" stop-opacity="0.95"/>
            </linearGradient>
            <filter id="layerShadow" x="-35%" y="-35%" width="170%" height="170%">
              <feDropShadow dx="0" dy="-6" stdDeviation="14" flood-color="#000" flood-opacity="0.14"/>
              <feDropShadow dx="0" dy="-2" stdDeviation="5" flood-color="#000" flood-opacity="0.18"/>
            </filter>
            <filter id="edgeHighlight" x="-35%" y="-35%" width="170%" height="170%">
              <feDropShadow dx="0" dy="-1" stdDeviation="2" flood-color="#fff" flood-opacity="0.35"/>
            </filter>
          </defs>
          <rect class="rectangle" width="100%" height="100%" x="0" y="0" style="fill:url(#heroGrad)"></rect>
          <path class="curve curve--back" id="curveBack" d="M0,0 H220 C500,140 540,560 760,600 H0 Z" fill="url(#heroGrad)"/>
          <path class="curve curve--middle" id="curveMid" d="M0,550 C300,450 720,660 1000,430 L1000,600 L0,600 Z" fill="url(#heroGradTwo)"/>
          <path class="hero__wave-fill" d="M0,565 C350,460 700,690 1000,510 L1000,615 L0,615 Z" filter="url(#layerShadow)"/>
          <path d="M0,565 C350,460 700,690 1000,510" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="10" stroke-linecap="round" filter="url(#edgeHighlight)" opacity="0.55"/>
        </svg>
      </div>

      <div class="hero__content hero__content--text col-xs-12 col-md-7">

        <?php if ( ! empty( $d['headline'] ) ) : ?>
          <div class="hero__text-block" style="margin-top:auto; margin-bottom:20vh;">
            <h1 class="curve curve--back"><?php echo $d['headline']; ?></h1>
            <?php if ( ! empty( $d['paragraph'] ) ) : ?>
              <p class="hero__paragraph"><?php echo esc_html( $d['paragraph'] ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $d['ctas'] ) ) : ?>
              <div class="c-button-pair">
                <?php foreach ( $d['ctas'] as $cta ) : ?>
                  <a class="c-button-pair__button" href="<?php echo esc_url( $cta['url'] ); ?>" target="<?php echo esc_attr( $cta['target'] ); ?>"><?php echo esc_html( $cta['title'] ); ?></a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>

  <?php if ( ! empty( $d['include_featured_post'] ) ) : ?>
    <?php
    $the_query = new WP_Query( array( 'tag' => 'featured', 'posts_per_page' => 1 ) );
    if ( $the_query->have_posts() ) :
    ?>
    <div class="hero__featured-post">
      <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?> role="article">
          <header class="article__header">
            <div class="article__meta"><p class="post-category">Update</p></div>
            <div class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></div>
          </header>
          <div class="hero--image">
            <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
              <?php the_post_thumbnail( 'gallery-image' ); ?>
            </a>
          </div>
        </article>
      <?php endwhile; ?>
    </div>
    <?php
    endif;
    wp_reset_postdata();
  endif; ?>

  </div>
<?php endif; ?>
