<?php
/**
 * Contact form endpoint.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX: send a contact message to the site admin.
 */
function netelec_ajax_contact() {
	netelec_ajax_verify();

	// Honeypot: real users never fill this field.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => 'پیام شما ارسال شد.' ) );
	}

	if ( ! netelec_rate_limit( 'contact', 5, HOUR_IN_SECONDS ) ) {
		netelec_ajax_fail( 'تعداد پیام‌های ارسالی زیاد است. لطفاً بعداً تلاش کنید.', 429 );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		netelec_ajax_fail( 'نام، ایمیل معتبر و متن پیام الزامی است.' );
	}

	$to      = get_option( 'admin_email' );
	$body    = "پیام جدید از فرم تماس وب‌سایت:\n\n";
	$body   .= 'نام: ' . $name . "\n";
	$body   .= 'ایمیل: ' . $email . "\n";
	$body   .= 'تلفن: ' . $phone . "\n";
	$body   .= 'موضوع: ' . $subject . "\n\n";
	$body   .= $message . "\n";

	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $to, 'تماس با ما: ' . ( $subject ? $subject : $name ), $body, $headers );

	if ( ! $sent ) {
		netelec_ajax_fail( 'ارسال پیام ناموفق بود. لطفاً بعداً تلاش کنید یا با شماره تماس در ارتباط باشید.', 500 );
	}

	wp_send_json_success( array( 'message' => 'پیام شما ارسال شد. به‌زودی پاسخ می‌دهیم.' ) );
}
add_action( 'wp_ajax_netelec_contact', 'netelec_ajax_contact' );
add_action( 'wp_ajax_nopriv_netelec_contact', 'netelec_ajax_contact' );
