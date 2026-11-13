<?php
/**
 * Phone number login with a one-time code.
 *
 * The code is delivered through a pluggable sender so the site can be hooked to
 * any Iranian SMS gateway or to an SMS plugin:
 *
 * ```
 * add_filter( 'moon_core_send_sms', function ( $sent, $phone, $text ) {
 *     // send with your gateway and return true on success
 *     return my_gateway_send( $phone, $text ) ? true : $sent;
 * }, 10, 3 );
 * ```
 *
 * When the Digits plugin is active its own OTP screen and gateways take over;
 * `moon_core_phone_auth_mode()` then returns `digits`.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Phone authentication.
 */
class Moon_OTP {

	/**
	 * Code lifetime in seconds.
	 */
	const TTL = 180;

	/**
	 * Seconds between two requests for the same phone number.
	 */
	const COOLDOWN = 60;

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'wp_ajax_moon_otp_send', array( __CLASS__, 'ajax_send' ) );
		add_action( 'wp_ajax_nopriv_moon_otp_send', array( __CLASS__, 'ajax_send' ) );

		add_action( 'wp_ajax_moon_otp_verify', array( __CLASS__, 'ajax_verify' ) );
		add_action( 'wp_ajax_nopriv_moon_otp_verify', array( __CLASS__, 'ajax_verify' ) );

		add_action( 'wp_ajax_moon_phone_register', array( __CLASS__, 'ajax_register' ) );
		add_action( 'wp_ajax_nopriv_moon_phone_register', array( __CLASS__, 'ajax_register' ) );

		add_filter( 'woocommerce_login_redirect', array( __CLASS__, 'login_redirect' ), 10, 2 );
	}

	/**
	 * Active authentication mode.
	 *
	 * @return string `digits` when the Digits plugin handles logins, else `otp`.
	 */
	public static function mode() {
		$mode = 'otp';

		if ( self::digits_active() ) {
			$mode = 'digits';
		}

		return apply_filters( 'moon_core_phone_auth_mode', $mode );
	}

	/**
	 * Whether the Digits plugin is active.
	 *
	 * @return bool
	 */
	public static function digits_active() {
		return class_exists( 'Digits' ) || shortcode_exists( 'dm-page' ) || shortcode_exists( 'digits_login' );
	}

	/**
	 * Digits login screen markup, when available.
	 *
	 * @return string
	 */
	public static function digits_form() {
		if ( shortcode_exists( 'dm-page' ) ) {
			return do_shortcode( '[dm-page]' );
		}

		if ( shortcode_exists( 'digits_login' ) ) {
			return do_shortcode( '[digits_login]' );
		}

		return '';
	}

	/**
	 * Normalise a phone number to `09xxxxxxxxx`.
	 *
	 * @param string $raw Raw input (Persian digits allowed).
	 * @return string Empty string when invalid.
	 */
	public static function normalize_phone( $raw ) {
		$raw = strtr(
			(string) $raw,
			array(
				'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
				'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
				'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
				'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
			)
		);

		$digits = preg_replace( '/\D+/', '', $raw );

		if ( 0 === strpos( $digits, '0098' ) ) {
			$digits = substr( $digits, 4 );
		} elseif ( 0 === strpos( $digits, '98' ) && strlen( $digits ) > 10 ) {
			$digits = substr( $digits, 2 );
		}

		$digits = ltrim( $digits, '0' );

		if ( 10 !== strlen( $digits ) || '9' !== substr( $digits, 0, 1 ) ) {
			return '';
		}

		return '0' . $digits;
	}

	/**
	 * User that owns a phone number.
	 *
	 * @param string $phone Normalised phone.
	 * @return WP_User|null
	 */
	public static function find_user( $phone ) {
		$phone = self::normalize_phone( $phone );

		if ( ! $phone ) {
			return null;
		}

		$users = get_users( array(
			'meta_key'   => 'billing_phone', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => $phone, // phpcs:ignore WordPress.DB.SlowDBQuery
			'number'     => 1,
		) );

		if ( $users ) {
			return $users[0];
		}

		$user = get_user_by( 'login', $phone );

		if ( $user ) {
			return $user;
		}

		return get_user_by( 'email', $phone . '@phone.local' ) ?: null;
	}

	/**
	 * Send the code over an SMS gateway.
	 *
	 * @param string $phone Normalised phone.
	 * @param string $code  Code.
	 * @return bool
	 */
	public static function send_code( $phone, $code ) {
		$message = sprintf(
			/* translators: %s: verification code. */
			__( 'کد ورود شما به MOON: %s', 'moon-core' ),
			$code
		);

		/**
		 * Deliver an SMS. Return true once a gateway accepted the message.
		 *
		 * @param bool   $sent  Whether a gateway sent the message.
		 * @param string $phone Phone number.
		 * @param string $text  Message.
		 */
		$sent = (bool) apply_filters( 'moon_core_send_sms', false, $phone, $message );

		do_action( 'moon_core_otp_code', $phone, $code, $message );

		if ( ! $sent ) {
			// Development aid: keep the code in the debug log when no gateway is wired.
			error_log( sprintf( 'MOON OTP for %s: %s', $phone, $code ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return $sent;
	}

	/**
	 * AJAX: request a code.
	 */
	public static function ajax_send() {
		check_ajax_referer( 'moon_login', 'nonce' );

		$phone = self::normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );

		if ( ! $phone ) {
			wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹', 'moon-core' ) ) );
		}

		if ( get_transient( 'moon_otp_lock_' . $phone ) ) {
			wp_send_json_error( array( 'message' => __( 'کد قبلی هنوز معتبر است. چند لحظه صبر کنید.', 'moon-core' ) ) );
		}

		$code = (string) wp_rand( 100000, 999999 );

		set_transient( 'moon_otp_code_' . $phone, wp_hash_password( $code ), self::TTL );
		set_transient( 'moon_otp_tries_' . $phone, 0, self::TTL );
		set_transient( 'moon_otp_lock_' . $phone, 1, self::COOLDOWN );

		$sent = self::send_code( $phone, $code );

		$response = array(
			'message'  => __( 'کد تأیید ارسال شد.', 'moon-core' ),
			'cooldown' => self::COOLDOWN,
			'sent'     => $sent,
		);

		// Sandbox/development only: expose the code so the flow is testable.
		if ( ! $sent && 'local' === wp_get_environment_type() ) {
			$response['message'] = sprintf(
				/* translators: %s: code. */
				__( 'حالت آزمایشی: کد شما %s است.', 'moon-core' ),
				$code
			);
			$response['dev_code'] = $code;
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX: verify a code and sign the visitor in.
	 */
	public static function ajax_verify() {
		check_ajax_referer( 'moon_login', 'nonce' );

		$phone = self::normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
		$code  = preg_replace( '/\D+/', '', (string) wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ) );

		if ( ! $phone || ! $code ) {
			wp_send_json_error( array( 'message' => __( 'شماره موبایل یا کد را کامل وارد کنید.', 'moon-core' ) ) );
		}

		$stored = get_transient( 'moon_otp_code_' . $phone );
		$tries  = (int) get_transient( 'moon_otp_tries_' . $phone );

		if ( ! $stored ) {
			wp_send_json_error( array( 'message' => __( 'کد منقضی شده است. دوباره درخواست کنید.', 'moon-core' ) ) );
		}

		if ( $tries >= 5 ) {
			delete_transient( 'moon_otp_code_' . $phone );
			wp_send_json_error( array( 'message' => __( 'تعداد تلاش‌ها زیاد شد. کد جدید بگیرید.', 'moon-core' ) ) );
		}

		if ( ! wp_check_password( $code, $stored ) ) {
			set_transient( 'moon_otp_tries_' . $phone, $tries + 1, self::TTL );
			wp_send_json_error( array( 'message' => __( 'کد وارد شده درست نیست.', 'moon-core' ) ) );
		}

		delete_transient( 'moon_otp_code_' . $phone );
		delete_transient( 'moon_otp_tries_' . $phone );
		delete_transient( 'moon_otp_lock_' . $phone );

		$user = self::find_user( $phone );

		if ( ! $user ) {
			$user = self::create_user( $phone, '', '', '' );
		}

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( array( 'message' => $user->get_error_message() ) );
		}

		self::login( $user );

		wp_send_json_success( array(
			'message'  => __( 'خوش آمدید! در حال انتقال به حساب کاربری…', 'moon-core' ),
			'redirect' => self::redirect_url(),
		) );
	}

	/**
	 * AJAX: register with name, phone and optional email.
	 */
	public static function ajax_register() {
		check_ajax_referer( 'moon_login', 'nonce' );

		$phone      = self::normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
		$first_name = sanitize_text_field( wp_unslash( isset( $_POST['first_name'] ) ? $_POST['first_name'] : '' ) );
		$last_name  = sanitize_text_field( wp_unslash( isset( $_POST['last_name'] ) ? $_POST['last_name'] : '' ) );
		$email      = sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) );
		$password   = (string) wp_unslash( isset( $_POST['password'] ) ? $_POST['password'] : '' );

		if ( ! $phone ) {
			wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'moon-core' ) ) );
		}

		if ( ! $first_name ) {
			wp_send_json_error( array( 'message' => __( 'نام خود را وارد کنید.', 'moon-core' ) ) );
		}

		if ( strlen( $password ) < 6 ) {
			wp_send_json_error( array( 'message' => __( 'رمز عبور باید حداقل ۶ کاراکتر باشد.', 'moon-core' ) ) );
		}

		if ( self::find_user( $phone ) ) {
			wp_send_json_error( array( 'message' => __( 'این شماره قبلاً ثبت شده است. وارد شوید.', 'moon-core' ) ) );
		}

		$user = self::create_user( $phone, $first_name, $last_name, $email, $password );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( array( 'message' => $user->get_error_message() ) );
		}

		self::login( $user );

		wp_send_json_success( array(
			'message'  => __( 'حساب شما ساخته شد. خوش آمدید!', 'moon-core' ),
			'redirect' => self::redirect_url(),
		) );
	}

	/**
	 * Create a customer account for a phone number.
	 *
	 * @param string $phone      Normalised phone.
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 * @param string $email      Email.
	 * @param string $password   Password.
	 * @return WP_User|WP_Error
	 */
	public static function create_user( $phone, $first_name = '', $last_name = '', $email = '', $password = '' ) {
		$username = $phone;
		$suffix   = 1;

		while ( username_exists( $username ) ) {
			$username = $phone . '-' . $suffix;
			$suffix++;
		}

		if ( ! $email || email_exists( $email ) ) {
			$email = $phone . '@phone.local';
		}

		$args = array(
			'user_login'   => $username,
			'user_email'   => $email,
			'user_pass'    => $password ? $password : wp_generate_password( 20 ),
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => trim( $first_name . ' ' . $last_name ) ? trim( $first_name . ' ' . $last_name ) : $phone,
			'role'         => 'customer',
		);

		$user_id = wp_insert_user( $args );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, 'billing_phone', $phone );
		update_user_meta( $user_id, 'moon_phone_verified', 1 );

		/**
		 * Fires after a phone-first customer is created.
		 *
		 * @param int    $user_id User ID.
		 * @param string $phone   Phone number.
		 */
		do_action( 'moon_core_phone_user_created', $user_id, $phone );

		return get_user_by( 'id', $user_id );
	}

	/**
	 * Sign a user in.
	 *
	 * @param WP_User $user User.
	 */
	public static function login( $user ) {
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
		do_action( 'wp_login', $user->user_login, $user );
	}

	/**
	 * Where to go after signing in.
	 *
	 * @return string
	 */
	public static function redirect_url() {
		$redirect = '';

		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$redirect = wc_get_page_permalink( 'myaccount' );
		}

		return apply_filters( 'moon_core_login_redirect', $redirect );
	}

	/**
	 * Keep the redirect target on normal logins too.
	 *
	 * @param string  $redirect Redirect URL.
	 * @param WP_User $user     User.
	 * @return string
	 */
	public static function login_redirect( $redirect, $user ) {
		return $redirect;
	}
}
