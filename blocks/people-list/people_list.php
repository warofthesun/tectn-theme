<?php
/**
 * People List (ACF Block)
 *
 * Allows editors to pick Person posts (post type 'people') via a Relationship
 * field and render them as a simple list. Layout/visuals can be refined later.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$block_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'people-list-' . $block['id'];
$classes  = array( 'c-peopleList' );
if ( ! empty( $block['className'] ) ) {
  $classes[] = $block['className'];
}

$people = function_exists( 'get_field' ) ? get_field( 'people' ) : array();
if ( ! is_array( $people ) ) {
  $people = array();
}
?>

<section id="<?php echo esc_attr( $block_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
  <?php if ( ! empty( $people ) ) : ?>
    <ul class="c-peopleList__items">
      <?php foreach ( $people as $person ) : ?>
        <?php
        $person_id = is_object( $person ) ? $person->ID : (int) $person;
        if ( ! $person_id ) {
          continue;
        }
        $name        = get_the_title( $person_id );
        $role        = function_exists( 'get_field' ) ? get_field( 'role', $person_id ) : '';
        $title       = function_exists( 'get_field' ) ? get_field( 'title', $person_id ) : '';
        $email       = function_exists( 'get_field' ) ? get_field( 'email', $person_id ) : '';
        $bio         = get_post_field( 'post_content', $person_id );
        $social_rows = function_exists( 'get_field' ) ? get_field( 'social_sites', $person_id ) : array();
        if ( ! is_array( $social_rows ) ) {
          $social_rows = array();
        }
        ?>
        <li class="c-peopleList__item">
          <div class="c-peopleCard">
            <div class="c-peopleCard__media">
              <?php if ( has_post_thumbnail( $person_id ) ) : ?>
                <div class="c-peopleCard__photo">
                  <?php echo get_the_post_thumbnail( $person_id, 'tectn_slider_square' ); ?>
                  <div class="c-peopleCard__wave">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/library/images/bio-wave.svg' ); ?>" alt="" loading="lazy" />
                  </div>
                </div>
              <?php endif; ?>

              <?php if ( ! empty( $social_rows ) ) : ?>
                <div class="c-peopleCard__social">
                  <span class="c-peopleCard__socialLabel"><?php esc_html_e( 'Find me on', 'tectn_theme' ); ?></span>
                  <ul class="c-peopleCard__socialList" aria-label="<?php esc_attr_e( 'Social profiles', 'tectn_theme' ); ?>">
                    <?php foreach ( $social_rows as $row ) : ?>
                      <?php
                      $network = isset( $row['network'] ) ? $row['network'] : '';
                      $url     = isset( $row['url'] ) ? $row['url'] : '';
                      $icon    = isset( $row['icon'] ) ? $row['icon'] : '';

                      if ( ! $url ) {
                        continue;
                      }

                      $icon_html = '';
                      if ( $network === 'twitter' ) {
                        $icon_html = '<i class="fa-brands fa-x-twitter" aria-hidden="true"></i>';
                      } elseif ( $network === 'linkedin' ) {
                        $icon_html = '<i class="fa-brands fa-linkedin-in" aria-hidden="true"></i>';
                      } elseif ( $network === 'facebook' ) {
                        $icon_html = '<i class="fa-brands fa-facebook-f" aria-hidden="true"></i>';
                      } elseif ( $icon ) {
                        $icon_html = $icon;
                      }

                      if ( ! $icon_html ) {
                        continue;
                      }
                      ?>
                      <li class="c-peopleCard__socialItem">
                        <a href="<?php echo esc_url( $url ); ?>" class="c-peopleCard__socialLink" target="_blank" rel="noopener noreferrer">
                          <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>

            <div class="c-peopleCard__content">
              <h2 class="hero c-peopleCard__name"><?php echo esc_html( $name ); ?></h2>
              <ul class="c-peopleCard__meta">
                <?php if ( $role ) : ?>
                  <li class="c-peopleCard__metaItem c-peopleCard__metaItem--role"><?php echo esc_html( $role ); ?></li>
                <?php endif; ?>
                <?php if ( $title ) : ?>
                  <li class="c-peopleCard__metaItem c-peopleCard__metaItem--title"><?php echo esc_html( $title ); ?></li>
                <?php endif; ?>
                <?php if ( $email ) : ?>
                  <li class="c-peopleCard__metaItem c-peopleCard__metaItem--email">
                    <a href="mailto:<?php echo esc_attr( $email ); ?>" class="c-peopleCard__email"><?php echo esc_html( $email ); ?></a>
                  </li>
                <?php endif; ?>
              </ul>
              <?php if ( $bio ) : ?>
                <div class="c-peopleCard__bio">
                  <?php echo wp_kses_post( wpautop( $bio ) ); ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <p class="c-peopleList__empty"><?php esc_html_e( 'No people selected yet.', 'tectn_theme' ); ?></p>
  <?php endif; ?>
</section>

