<?php
/**
 * Phone-first checkout: mobile number required, email optional, shorter form.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adjust billing fields.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function moon_checkout_fields( $fields ) {
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['required']     = true;
		$fields['billing']['billing_phone']['priority']     = 5;
		$fields['billing']['billing_phone']['type']         = 'tel';
		$fields['billing']['billing_phone']['label']        = __( 'شماره موبایل', 'moon-theme' );
		$fields['billing']['billing_phone']['placeholder']  = '09123456789';
		$fields['billing']['billing_phone']['autocomplete'] = 'tel';
		$fields['billing']['billing_phone']['input_class']  = array( 'moon-ltr' );
		$fields['billing']['billing_phone']['custom_attributes'] = array(
			'inputmode' => 'numeric',
			'dir'       => 'ltr',
		);
	}

	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['required'] = false;
		$fields['billing']['billing_email']['label']    = __( 'ایمیل', 'moon-theme' );
	}

	unset( $fields['billing']['billing_company'], $fields['billing']['billing_address_2'] );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'moon_checkout_fields', 20 );

/**
 * Validate and normalise the Iranian mobile number.
 */
function moon_checkout_validate_phone() {
	if ( empty( $_POST['billing_phone'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wc_add_notice( __( 'شماره موبایل الزامی است.', 'moon-theme' ), 'error' );
		return;
	}

	$raw = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$raw = strtr( $raw, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' ) );
	$num = preg_replace( '/\D+/', '', $raw );
	$num = preg_replace( '/^(0098|98)/', '0', $num );

	if ( ! preg_match( '/^09\d{9}$/', $num ) ) {
		wc_add_notice( __( 'شماره موبایل معتبر نیست. نمونه: 09123456789', 'moon-theme' ), 'error' );
		return;
	}

	$_POST['billing_phone'] = $num; // phpcs:ignore WordPress.Security.NonceVerification
}
add_action( 'woocommerce_checkout_process', 'moon_checkout_validate_phone' );

/**
 * Replace WooCommerce's email/password login box with a link to the SMS login.
 */
function moon_checkout_phone_login() {
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

	add_action( 'woocommerce_before_checkout_form', function () {
		if ( is_user_logged_in() || ! function_exists( 'wc_get_page_permalink' ) ) {
			return;
		}

		$url = add_query_arg( 'redirect', rawurlencode( wc_get_checkout_url() ), wc_get_page_permalink( 'myaccount' ) );

		echo '<div class="moon-checkout-login"><span>' . esc_html__( 'قبلاً ثبت‌نام کرده‌اید؟', 'moon-theme' ) . '</span> <a href="' . esc_url( $url ) . '">' . esc_html__( 'ورود با شماره موبایل', 'moon-theme' ) . '</a></div>';
	}, 5 );
}
add_action( 'wp', 'moon_checkout_phone_login' );

/**
 * The phone number is always required (WooCommerce 8+ has a setting that can make it optional).
 *
 * @return string
 */
add_filter( 'option_woocommerce_checkout_phone_field', function () {
	return 'required';
} );
