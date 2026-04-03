<?php
/**
 * Card Block
 * Short headline + repeater of cards. Each card: image, title text, details text, headline, body text.
 * Single card centers; multiple cards in grid.
 *
 * @package tectn_theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_data = ( ! empty( $block ) && is_array( $block ) && ! empty( $block['data'] ) && is_array( $block['data'] ) ) ? $block['data'] : array();

$is_inserter_preview =
	! empty( $block['mode'] ) &&
	$block['mode'] === 'preview' &&
	! empty( $block_data['inserter_preview'] );

if ( $is_inserter_preview ) {
	$variant = ! empty( $block_data['preview_variant'] )
		? sanitize_key( $block_data['preview_variant'] )
		: 'default';

	$map = array(
		'default' => 'preview.png',
	);

	$file = $map[ $variant ] ?? $map['default'];
	$src  = get_template_directory_uri() . '/blocks/card-block/' . $file;

	echo '<img src="' . esc_url( $src ) . '" style="width:100%;height:auto;display:block;" alt="">';
	return;
}

$is_editor_context =
	is_admin() ||
	( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST );

$cards = get_field( 'cards' );
if ( ! is_array( $cards ) ) {
	$cards = array();
}

if ( $is_editor_context && empty( $cards ) && empty( $block_data['inserter_preview'] ) ) {
	echo '<section class="c-card-block alignfull">';
	echo '  <div class="c-card-block__body">';
	echo '    <div class="c-card-block__placeholder">';
	echo '      <strong>' . esc_html__( 'Card Block', 'tectn_theme' ) . '</strong><br>';
	echo '      ' . esc_html__( 'Add a section headline and one or more cards in the block settings, or click the pencil icon to edit in place.', 'tectn_theme' );
	echo '    </div>';
	echo '  </div>';
	echo '</section>';
	return;
}

$block_id   = ! empty( $block['anchor'] ) ? $block['anchor'] : 'card-block-' . $block['id'];
$headline   = get_field( 'section_headline' );
$card_count = count( $cards );

$classes = ['c-card-block'];
if (!empty($block['className'])) $classes[] = $block['className'];
$align = !empty($block['align']) ? 'align' . $block['align'] : '';
if ($align) $classes[] = $align;

$grid_class = '';
if ( $card_count >= 1 && $card_count <= 5 ) {
	$grid_class = ' c-card-block__grid--count-' . $card_count;
}
?>

<section id="<?= esc_attr( $block_id ); ?>" class="<?= esc_attr( implode( ' ', $classes ) ); ?>">
  <?php if ($headline !== ''): ?>
    <h2 class="c-card-block__headline"><?= esc_html($headline); ?></h2>
  <?php endif; ?>

  <div class="c-card-block__body">
    <?php if ($card_count > 0): ?>
      <div class="c-card-block__grid<?= esc_attr($grid_class); ?>">
        <?php foreach ($cards as $card):
          $img     = isset($card['image']) && is_array($card['image']) ? $card['image'] : [];
          $img_id  = !empty($img['ID']) ? (int) $img['ID'] : (isset($img['id']) ? (int) $img['id'] : 0);
          $title   = isset($card['title_text']) ? $card['title_text'] : '';
          $details = isset($card['details_text']) ? $card['details_text'] : '';
          $head    = isset($card['headline']) ? $card['headline'] : '';
          $body    = isset($card['body_text']) ? $card['body_text'] : '';
        ?>
          <article class="c-card-block__card">
            <div class="c-card-block__card-content">
              <?php if ($title !== '' || $details !== ''): ?>
                <div class="c-card-block__card-top">
                  <?php if ($title !== ''): ?>
                    <span class="c-card-block__card-title"><?= esc_html($title); ?></span>
                  <?php endif; ?>
                  <?php if ($details !== ''): ?>
                    <span class="c-card-block__card-details"><?= esc_html($details); ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <div class="c-card-block__card-bottom">
                <div class="c-card-block__card-text">
                  <?php if ($head !== ''): ?>
                    <h3 class="c-card-block__card-headline"><?= esc_html($head); ?></h3>
                  <?php endif; ?>
                  <?php if ($body !== ''): ?>
                    <div class="c-card-block__card-body"><?= wp_kses_post(wpautop($body)); ?></div>
                  <?php endif; ?>
                </div>
                <?php if ($img_id): ?>
                  <div class="c-card-block__card-media">
                    <?= wp_get_attachment_image( $img_id, 'card-block', false, ['loading' => 'lazy'] ); ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
