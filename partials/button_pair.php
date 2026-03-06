<?php
// Button Pair: repeater buttons (per-button style class) + optional parent color class.
// When $buttons_data is set (e.g. from footer Site Settings CTA), uses that array instead of have_rows.

$use_passed_data = isset( $buttons_data ) && is_array( $buttons_data ) && ! empty( $buttons_data );
$has_buttons     = $use_passed_data || have_rows( 'buttons' );

if ( $has_buttons ) :

    if ( $use_passed_data ) {
        $pair_color = isset( $button_color ) ? $button_color : '';
    } else {
        $pair_color = get_field( 'button_color' );
    }

    $pair_classes = array( 'c-button-pair' );
    if ( ! empty( $pair_color ) ) {
        foreach ( preg_split( '/\s+/', trim( (string) $pair_color ) ) as $cls ) {
            if ( $cls !== '' ) {
                $pair_classes[] = 'c-button-pair--' . sanitize_html_class( $cls );
            }
        }
    }
?>
    <div class="<?php echo esc_attr( implode( ' ', array_filter( $pair_classes ) ) ); ?>">
        <?php
        if ( $use_passed_data ) {
            foreach ( $buttons_data as $row ) {
                $link   = isset( $row['button'] ) && is_array( $row['button'] ) ? $row['button'] : array();
                $url    = isset( $link['url'] ) ? $link['url'] : '';
                $title  = isset( $link['title'] ) ? $link['title'] : '';
                $target = isset( $link['target'] ) ? $link['target'] : '_self';
                if ( ! $url || ! $title ) {
                    continue;
                }
                $style       = isset( $row['button_style'] ) ? $row['button_style'] : '';
                $btn_classes = array( 'c-button-pair__button' );
                if ( ! empty( $style ) ) {
                    foreach ( preg_split( '/\s+/', trim( (string) $style ) ) as $cls ) {
                        if ( $cls !== '' ) {
                            $btn_classes[] = 'c-button-pair__button--' . sanitize_html_class( $cls );
                        }
                    }
                }
                ?>
                <a class="<?php echo esc_attr( implode( ' ', array_filter( $btn_classes ) ) ); ?>"
                    href="<?php echo esc_url( $url ); ?>"
                    target="<?php echo esc_attr( $target ); ?>"
                    <?php echo ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html( $title ); ?>
                </a>
            <?php
            }
        } else {
            while ( have_rows( 'buttons' ) ) :
                the_row();
                $style = get_sub_field( 'button_style' );
                $link  = get_sub_field( 'button' );
                if ( ! $link ) {
                    continue;
                }
                $url    = isset( $link['url'] ) ? $link['url'] : '';
                $title  = isset( $link['title'] ) ? $link['title'] : '';
                $target = isset( $link['target'] ) ? $link['target'] : '_self';
                if ( ! $url || ! $title ) {
                    continue;
                }
                $btn_classes = array( 'c-button-pair__button' );
                if ( ! empty( $style ) ) {
                    foreach ( preg_split( '/\s+/', trim( (string) $style ) ) as $cls ) {
                        if ( $cls !== '' ) {
                            $btn_classes[] = 'c-button-pair__button--' . sanitize_html_class( $cls );
                        }
                    }
                }
                ?>
                <a class="<?php echo esc_attr( implode( ' ', array_filter( $btn_classes ) ) ); ?>"
                    href="<?php echo esc_url( $url ); ?>"
                    target="<?php echo esc_attr( $target ); ?>"
                    <?php echo ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html( $title ); ?>
                </a>
            <?php
            endwhile;
        }
        ?>
    </div>
<?php endif; ?>