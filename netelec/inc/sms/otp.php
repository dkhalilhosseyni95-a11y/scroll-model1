<?php
/**
 * Passwordless mobile login (OTP).
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once NETELEC_DIR . '/inc/sms/providers.php';

/**
 * Normalize an Iranian mobile number to 09xxxxxxxxx.
 *
 * @param string $raw Raw input (Persian digits allowed).
 * @return string Empty string when invalid.
 */
function netelec_normalize_phone( $raw ) {
	$digits = strtr(
		(string) $raw,
		array(
			'۰' => '0',
			'۱' => '1',
			'۲' => '2',
			'۳' => '3',
			'۴' => '4',
			'۵' => '5',
			'۶' => '6',
			'۷' => '7',
			'۸' => '8',
			'۹' => '9',
			'٠' => '0',
			'١' => '1',
			'٢' => '2',
			'٣' => '3',
			'٤' => '4',
			'٥' => '5',
			'٦' => '6',
			'٧' => '7',
			'٨' => '8',
			'٩' => '9',
		)
	);

	$digits = preg_replace( '/[^0-9]/', '', $digits );

	if ( 0 === strpos( $digits, '0098' ) ) {
		$digits = '0' . substr( $digits, 4 );
	} elseif ( 0 === strpos( $digits, '98' ) && 12 === strlen( $digits ) ) {
		$digits = '0' . substr( $digits, 2 );
	} elseif ( 0 === strpos( $digits, '9' ) && 10 === strlen( $digits ) ) {
		$digits = '0' . $digits;
	}

	if ( ! preg_match( '/^09[0-9]{9}$/', $digits ) ) {
		return '';
	}

	return $digits;
}

/**
 * Transient key for a phone number (no raw PII in option names).
 *
 * @param string $phone Normalized phone.
 * @param string $suffix Suffix.
 * @return string
 */
function netelec_otp_key( $phone, $suffix = 'code' ) {
	return 'nc_otp_' . $suffix . '_' . substr( hash_hmac( 'sha256', $phone, wp_salt( 'nonce' ) ), 0, 24 );
}

/**
 * Hash a verification code.
 *
 * @param string $phone Phone.
 * @param string $code  Code.
 * @return string
 */
function netelec_otp_hash( $phone, $code ) {
	return hash_hmac( 'sha256', $phone . '|' . $code, wp_salt( 'auth' ) );
}

/**
 * AJAX: send an OTP.
 */
function netelec_ajax_otp_send() {
	netelec_ajax_verify();

	if ( ! netelec_opt( 'otp_enabled' ) ) {
		netelec_ajax_fail( 'ورود با موبایل در حال حاضر فعال نیست.' );
	}

	$phone = netelec_normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );

	if ( ! $phone ) {
		netelec_ajax_fail( 'شماره موبایل معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹' );
	}

	$hourly_limit = max( 3, (int) netelec_opt( 'otp_hourly_limit' ) );

	if ( ! netelec_rate_limit( 'otp_phone_' . $phone, $hourly_limit, HOUR_IN_SECONDS ) ) {
		netelec_ajax_fail( 'تعداد درخواست‌های این شماره زیاد است. یک ساعت دیگر تلاش کنید.', 429 );
	}

	$wait_key = netelec_otp_key( $phone, 'wait' );
	$wait     = (int) get_transient( $wait_key );

	if ( $wait > 0 ) {
		netelec_ajax_fail( sprintf( 'لطفاً %s ثانیه دیگر برای ارسال مجدد صبر کنید.', number_format_i18n( $wait ) ), 429 );
	}

	$provider = netelec_sms_provider();
	if ( ! $provider ) {
		netelec_ajax_fail( 'سرویس پیامک تنظیم نشده است. با مدیر سایت تماس بگیرید.', 503 );
	}

	$length = min( 8, max( 4, (int) netelec_opt( 'otp_length' ) ) );
	$code   = (string) random_int( (int) str_pad( '1', $length, '0' ), (int) str_repeat( '9', $length ) );

	$expiry = max( 1, (int) netelec_opt( 'otp_expiry' ) ) * MINUTE_IN_SECONDS;

	set_transient(
		netelec_otp_key( $phone ),
		array(
			'hash'     => netelec_otp_hash( $phone, $code ),
			'attempts' => 0,
		),
		$expiry
	);

	$sent = $provider->send( $phone, $code );

	if ( is_wp_error( $sent ) ) {
		delete_transient( netelec_otp_key( $phone ) );
		netelec_ajax_fail( $sent->get_error_message(), 502 );
	}

	$resend = max( 30, (int) netelec_opt( 'otp_resend_delay' ) );
	set_transient( $wait_key, $resend, $resend );

	wp_send_json_success(
		array(
			'message' => 'کد تأیید ارسال شد.',
			'resend'  => $resend,
			'expiry'  => (int) netelec_opt( 'otp_expiry' ),
		)
	);
}
add_action( 'wp_ajax_netelec_otp_send', 'netelec_ajax_otp_send' );
add_action( 'wp_ajax_nopriv_netelec_otp_send', 'netelec_ajax_otp_send' );

/**
 * Find a user by phone number.
 *
 * @param string $phone Normalized phone.
 * @return WP_User|null
 */
function netelec_find_user_by_phone( $phone ) {
	$users = get_users(
		array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'   => 'billing_phone',
					'value' => $phone,
				),
				array(
					'key'   => 'netelec_phone',
					'value' => $phone,
				),
			),
			'number'     => 1,
			'fields'     => 'all',
		)
	);

	if ( $users ) {
		return $users[0];
	}

	$by_login = get_user_by( 'login', $phone );
	return $by_login ? $by_login : null;
}

/**
 * AJAX: verify the OTP and sign the customer in (creating the account if needed).
 */
function netelec_ajax_otp_verify() {
	netelec_ajax_verify();

	if ( ! netelec_opt( 'otp_enabled' ) ) {
		netelec_ajax_fail( 'ورود با موبایل در حال حاضر فعال نیست.' );
	}

	$phone = netelec_normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
	$code  = preg_replace( '/[^0-9]/', '', (string) ( isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : '' ) );

	if ( ! $phone || ! $code ) {
		netelec_ajax_fail( 'شماره موبایل و کد تأیید را وارد کنید.' );
	}

	if ( ! netelec_rate_limit( 'otp_verify_' . $phone, 30, HOUR_IN_SECONDS ) ) {
		netelec_ajax_fail( 'تلاش‌های ناموفق زیاد است. یک ساعت دیگر تلاش کنید.', 429 );
	}

	$key  = netelec_otp_key( $phone );
	$data = get_transient( $key );

	if ( ! $data ) {
		netelec_ajax_fail( 'کد منقضی شده است. کد جدید دریافت کنید.' );
	}

	$max_attempts = max( 3, (int) netelec_opt( 'otp_max_attempts' ) );

	if ( (int) $data['attempts'] >= $max_attempts ) {
		delete_transient( $key );
		netelec_ajax_fail( 'تعداد تلاش‌ها بیش از حد مجاز است. کد جدید دریافت کنید.' );
	}

	if ( ! hash_equals( (string) $data['hash'], netelec_otp_hash( $phone, $code ) ) ) {
		$data['attempts'] = (int) $data['attempts'] + 1;
		set_transient( $key, $data, (int) netelec_opt( 'otp_expiry' ) * MINUTE_IN_SECONDS );
		netelec_ajax_fail( 'کد وارد شده صحیح نیست.' );
	}

	delete_transient( $key );
	delete_transient( netelec_otp_key( $phone, 'wait' ) );

	$user = netelec_find_user_by_phone( $phone );

	if ( ! $user ) {
		$user_id = wp_insert_user(
			array(
				'user_login'   => $phone,
				'user_email'   => $phone . '@no-email.local',
				'user_pass'    => wp_generate_password( 32 ),
				'display_name' => 'کاربر ' . substr( $phone, -4 ),
				'role'         => 'customer',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			netelec_ajax_fail( 'ایجاد حساب کاربری ممکن نشد. با پشتیبانی تماس بگیرید.' );
		}

		update_user_meta( $user_id, 'billing_phone', $phone );
		update_user_meta( $user_id, 'netelec_phone', $phone );

		$user = get_user_by( 'id', $user_id );
	} else {
		update_user_meta( $user->ID, 'netelec_phone', $phone );
	}

	if ( ! $user ) {
		netelec_ajax_fail( 'ورود ممکن نشد.' );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	do_action( 'wp_login', $user->user_login, $user );

	wp_send_json_success(
		array(
			'message'  => 'ورود انجام شد.',
			'redirect' => wc_get_page_permalink( 'myaccount' ),
		)
	);
}
add_action( 'wp_ajax_netelec_otp_verify', 'netelec_ajax_otp_verify' );
add_action( 'wp_ajax_nopriv_netelec_otp_verify', 'netelec_ajax_otp_verify' );

/**
 * AJAX: test the SMS provider credentials (admin only).
 */
function netelec_ajax_sms_test() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی مجاز نیست.' ), 403 );
	}

	check_ajax_referer( 'netelec_admin', 'nonce' );

	$provider = netelec_sms_provider();

	if ( ! $provider ) {
		wp_send_json_error( array( 'message' => 'ابتدا سرویس‌دهنده پیامک را انتخاب و ذخیره کنید.' ) );
	}

	$result = $provider->test();

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => 'اتصال به سرویس پیامک برقرار است.' ) );
}
add_action( 'wp_ajax_netelec_sms_test', 'netelec_ajax_sms_test' );

/**
 * Mobile login block above the WooCommerce login form.
 */
function netelec_otp_login_block() {
	if ( ! netelec_opt( 'otp_enabled' ) ) {
		return;
	}
	?>
	<div class="otp-login">
		<div class="otp-login__head">
			<span class="otp-login__icon"><?php netelec_icon( 'phone' ); ?></span>
			<div>
				<h2>ورود یا ثبت‌نام با موبایل</h2>
				<p>شماره موبایل خود را وارد کنید؛ کد تأیید برای شما پیامک می‌شود.</p>
			</div>
		</div>

		<form class="otp-form" id="netelec-otp-form" novalidate>
			<div class="otp-step" data-step="phone">
				<label for="otp-phone">شماره موبایل</label>
				<div class="otp-row">
					<input type="tel" id="otp-phone" name="phone" inputmode="numeric" autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" required>
					<button type="submit" class="btn btn--primary">دریافت کد</button>
				</div>
			</div>

			<div class="otp-step" data-step="code" hidden>
				<label for="otp-code">کد تأیید</label>
				<div class="otp-row">
					<input type="text" id="otp-code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="8" placeholder="کد پیامک‌شده" dir="ltr" required>
					<button type="submit" class="btn btn--primary">ورود</button>
				</div>
				<div class="otp-links">
					<button type="button" class="otp-resend" disabled>ارسال مجدد کد</button>
					<button type="button" class="otp-change">تغییر شماره</button>
				</div>
			</div>

			<p class="otp-message" role="status" aria-live="polite"></p>
		</form>
	</div>

	<div class="login-divider"><span>یا ورود با رمز عبور</span></div>
	<?php
}
add_action( 'woocommerce_before_customer_login_form', 'netelec_otp_login_block' );
