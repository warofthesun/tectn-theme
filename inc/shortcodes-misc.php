<?php
/**
 * Theme includes.
 * @package tectn_theme
 * Shortcodes and password form text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
