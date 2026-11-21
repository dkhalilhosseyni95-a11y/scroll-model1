<?php
/**
 * Product reviews: sign-in with a phone number is required.
 *
 * Reviews are posted by logged-in customers (accounts are created through the
 * SMS-code login), which also keeps spam out. The "log in" prompt sends people
 * to the phone login and brings them straight back to the review form.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace the default "you must be logged in" text on the review form.
 *
 * @param array $args Comment form arguments.
 * @return array
 */
function moon_review_form_args( $args ) {
	if ( is_user_logged_in() || ! function_exists( 'wc_get_page_permalink' ) ) {
		return $args;
	}

	$back = get_permalink() . '#reviews';
	$url  = add_query_arg( 'redirect', rawurlencode( $back ), wc_get_page_permalink( 'myaccount' ) );

	$args['must_log_in'] = '<div class="moon-review-login"><p>' . esc_html__( 'برای ثبت نظر ابتدا با شماره موبایل وارد شوید.', 'moon-theme' ) . '</p><a class="moon-btn moon-btn--dark" href="' . esc_url( $url ) . '">' . esc_html__( 'ورود با شماره موبایل', 'moon-theme' ) . '</a></div>';

	return $args;
}
add_filter( 'woocommerce_product_review_comment_form_args', 'moon_review_form_args', 20 );

/**
 * Reviews posted by a signed-in account use the account's name, so no
 * email/name fields are needed in the form.
 *
 * @param array $fields Comment form fields.
 * @return array
 */
function moon_review_form_fields( $fields ) {
	unset( $fields['email'], $fields['url'], $fields['cookies'] );

	return $fields;
}
add_filter( 'comment_form_default_fields', 'moon_review_form_fields', 30 );
