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

	// Throttle per IP and cap the list so the endpoint cannot bloat the options table.
	$ip_key   = 'moon_sub_ip_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x' );
	$ip_count = (int) get_transient( $ip_key );

	if ( $ip_count >= 5 ) {
		wp_send_json_error( array( 'message' => __( 'تعداد درخواست‌ها زیاد است. بعداً تلاش کنید.', 'moon-theme' ) ), 429 );
	}

	set_transient( $ip_key, $ip_count + 1, HOUR_IN_SECONDS );

	$subscribers = get_option( 'moon_subscribers', array() );
	$subscribers = is_array( $subscribers ) ? $subscribers : array();

	if ( count( $subscribers ) >= 20000 ) {
		wp_send_json_error( array( 'message' => __( 'ظرفیت فهرست تکمیل است.', 'moon-theme' ) ), 400 );
	}

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
