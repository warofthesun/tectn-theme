<?php
/**
 * Theme includes.
 * @package tectn_theme
 * Shortcodes and password form text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*CUSTOM SHORTCODE TO PULL FORM POSTS */

add_shortcode( 'formposts', 'display_custom_post_type' ); 

function display_custom_post_type(){
    $args = array( 'post_type' => 'form', 'post_status' => 'publish' );
    $string = '';
    $query = new WP_Query( $args );
    // Loop through the posts and build your output (e.g., list titles with links)
    if ( $query->have_posts() ) {
        $string .= '<div class="forms forms__page">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $string .= '<div class="form__item"><a href="' . get_permalink() . '" class="form__link"><h4>' . get_the_title() . '</h4></a></div>';
        }
        $string .= '</div>';
        wp_reset_postdata(); // Important: Reset the query after using it
    } else {
        $string = 'No posts found';
    }
    return $string;
}

/* EDIT PASSWORD PROTECTED LANGUAGE */
add_filter( 'the_password_form', 'tectn_password_form' );
function tectn_password_form( $output ) {
	$output = str_replace(
		'This content is password protected. To view it please enter your password below:',
		'To simplify your login process, you now only need to enter your password. Your password remains the same as the one you were previously provided.',
		$output
	);

	return $output;
}
