<?php
/**
 * Newsletter subscription handler.
 *
 * Stores subscribers in the `moon_subscribers` option. Registered by the theme
 * so the newsletter keeps working when MOON Core is deactivated.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle an AJAX subscription.
 */
function moon_ajax_subscribe() {
	check_ajax_referer( 'moon_subscribe', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'ایمیل معتبر نیست.', 'moon-theme' ) ), 400 );
	}

	$subscribers = get_option( 'moon_subscribers', array() );
	$subscribers = is_array( $subscribers ) ? $subscribers : array();

	foreach ( $subscribers as $subscriber ) {
		if ( isset( $subscriber['email'] ) && strtolower( $subscriber['email'] ) === strtolower( $email ) ) {
			wp_send_json_success( array( 'message' => moon_opt( 'news_note', __( 'ممنون! به زودی خبرتان می‌کنیم.', 'moon-theme' ) ) ) );
		}
	}

	$subscribers[] = array(
		'email' => $email,
		'date'  => current_time( 'mysql' ),
	);

	update_option( 'moon_subscribers', $subscribers, false );

	wp_send_json_success( array( 'message' => moon_opt( 'news_note', __( 'ممنون! به زودی خبرتان می‌کنیم.', 'moon-theme' ) ) ) );
}
add_action( 'wp_ajax_moon_subscribe', 'moon_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_moon_subscribe', 'moon_ajax_subscribe' );
