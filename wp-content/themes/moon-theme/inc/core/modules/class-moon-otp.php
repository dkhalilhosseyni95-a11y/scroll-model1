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
 * Ownership of a number is proven only by a successful code check. Creating an
 * account (`ajax_register`) therefore requires the single-use token that
 * `ajax_verify` hands back, so the registration endpoint can never be used to
 * claim a phone number that was not verified.
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
	 * Requests allowed per phone number within an hour.
	 */
	const PHONE_CAP = 5;

	/**
	 * Requests allowed per IP address within an hour.
	 */
	const IP_CAP = 20;

	/**
	 * Wrong codes allowed per code.
	 */
	const MAX_TRIES = 5;

	/**
	 * Wrong codes allowed per IP address within an hour.
	 */
	const IP_TRIES_CAP = 20;

	/**
	 * Lifetime of the registration token handed out after a verified code.
	 */
	const TOKEN_TTL = 900;

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
	 * Whether new customers may be created on this site.
	 *
	 * The theme never creates an account when the store owner has turned customer
	 * registration off; a phone-first login on such a site only signs in existing
	 * customers.
	 *
	 * @return bool
	 */
	public static function registration_allowed() {
		$allowed = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) || get_option( 'users_can_register' );

		return (bool) apply_filters( 'moon_core_phone_registration_allowed', $allowed );
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
	 * Client IP, used only for rate limiting (hashed, never stored in clear).
	 *
	 * @return string
	 */
	private static function ip_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';

		return md5( $ip );
	}

	/**
	 * Increment a counter transient.
	 *
	 * @param string $key Transient key.
	 * @param int    $ttl Lifetime.
	 * @return int New value.
	 */
	private static function bump( $key, $ttl = HOUR_IN_SECONDS ) {
		$value = (int) get_transient( $key ) + 1;
		set_transient( $key, $value, $ttl );

		return $value;
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
			__( 'کد ورود شما به MOON: %s', 'moon-theme' ),
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

		/**
		 * Fires for every code issued, so a site can wire its own gateway.
		 *
		 * The code and phone are intentionally not written to any log by the theme.
		 *
		 * @param string $phone   Phone number.
		 * @param string $code    Code.
		 * @param string $message Message text.
		 */
		do_action( 'moon_core_otp_code', $phone, $code, $message );

		return $sent;
	}

	/**
	 * AJAX: request a code.
	 */
	public static function ajax_send() {
		check_ajax_referer( 'moon_login', 'nonce' );

		$phone = self::normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );

		if ( ! $phone ) {
			wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹', 'moon-theme' ) ) );
		}

		// Per-IP and per-number caps: the form must not be usable to flood a
		// number with SMS or to grind through the code space.
		$ip_count = (int) get_transient( 'moon_otp_ip_' . self::ip_key() );

		if ( $ip_count >= self::IP_CAP ) {
			wp_send_json_error( array( 'message' => __( 'تعداد درخواست‌ها زیاد است. کمی بعد دوباره تلاش کنید.', 'moon-theme' ) ), 429 );
		}

		$phone_sends = (int) get_transient( 'moon_otp_sends_' . $phone );

		if ( $phone_sends >= self::PHONE_CAP ) {
			wp_send_json_error( array( 'message' => __( 'برای این شماره تعداد درخواست کد زیاد بوده است. یک ساعت دیگر تلاش کنید.', 'moon-theme' ) ), 429 );
		}

		if ( get_transient( 'moon_otp_lock_' . $phone ) ) {
			wp_send_json_error( array( 'message' => __( 'کد قبلی هنوز معتبر است. چند لحظه صبر کنید.', 'moon-theme' ) ) );
		}

		$code = (string) wp_rand( 100000, 999999 );

		self::bump( 'moon_otp_ip_' . self::ip_key() );
		self::bump( 'moon_otp_sends_' . $phone );

		set_transient( 'moon_otp_code_' . $phone, wp_hash_password( $code ), self::TTL );
		set_transient( 'moon_otp_tries_' . $phone, 0, self::TTL );
		set_transient( 'moon_otp_lock_' . $phone, 1, self::COOLDOWN );

		$sent = self::send_code( $phone, $code );

		// Sandbox/development only: expose the code so the flow is testable
		// without an SMS gateway. Never in production, and never in a log.
		if ( ! $sent && 'local' === wp_get_environment_type() ) {
			wp_send_json_success( array(
				'message'  => sprintf(
					/* translators: %s: code. */
					__( 'حالت آزمایشی: کد شما %s است.', 'moon-theme' ),
					$code
				),
				'cooldown' => self::COOLDOWN,
				'sent'     => false,
				'dev_code' => $code,
			) );
		}

		if ( ! $sent ) {
			// No gateway accepted the message: drop the unused code so the visitor
			// is not left with a code that can never arrive.
			delete_transient( 'moon_otp_code_' . $phone );
			delete_transient( 'moon_otp_tries_' . $phone );
			delete_transient( 'moon_otp_lock_' . $phone );

			wp_send_json_error( array( 'message' => __( 'ارسال پیامک در حال حاضر ممکن نیست. کمی بعد دوباره تلاش کنید.', 'moon-theme' ) ), 503 );
		}

		wp_send_json_success( array(
			'message'  => __( 'کد تأیید ارسال شد.', 'moon-theme' ),
			'cooldown' => self::COOLDOWN,
			'sent'     => true,
		) );
	}

	/**
	 * AJAX: verify a code.
	 *
	 * With an existing customer the visitor is signed in. Without one, the
	 * response carries a single-use `register_token` that `ajax_register`
	 * requires, and the visitor is signed in only after registering.
	 */
	public static function ajax_verify() {
		check_ajax_referer( 'moon_login', 'nonce' );

		$phone = self::normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
		$code  = preg_replace( '/\D+/', '', (string) wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ) );

		if ( ! $phone || ! $code ) {
			wp_send_json_error( array( 'message' => __( 'شماره موبایل یا کد را کامل وارد کنید.', 'moon-theme' ) ) );
		}

		$ip_tries = (int) get_transient( 'moon_otp_ip_tries_' . self::ip_key() );

		if ( $ip_tries >= self::IP_TRIES_CAP ) {
			wp_send_json_error( array( 'message' => __( 'تلاش‌های ناموفق زیاد بوده است. یک ساعت دیگر تلاش کنید.', 'moon-theme' ) ), 429 );
		}

		$stored = get_transient( 'moon_otp_code_' . $phone );
		$tries  = (int) get_transient( 'moon_otp_tries_' . $phone );

		if ( ! $stored ) {
			wp_send_json_error( array( 'message' => __( 'کد منقضی شده است. دوباره درخواست کنید.', 'moon-theme' ) ) );
		}

		if ( $tries >= self::MAX_TRIES ) {
			delete_transient( 'moon_otp_code_' . $phone );
			wp_send_json_error( array( 'message' => __( 'تعداد تلاش‌ها زیاد شد. کد جدید بگیرید.', 'moon-theme' ) ) );
		}

		if ( ! wp_check_password( $code, $stored ) ) {
			set_transient( 'moon_otp_tries_' . $phone, $tries + 1, self::TTL );
			self::bump( 'moon_otp_ip_tries_' . self::ip_key() );
			wp_send_json_error( array( 'message' => __( 'کد وارد شده درست نیست.', 'moon-theme' ) ) );
		}

		// The code is single-use.
		delete_transient( 'moon_otp_code_' . $phone );
		delete_transient( 'moon_otp_tries_' . $phone );
		delete_transient( 'moon_otp_lock_' . $phone );

		$user = self::find_user( $phone );

		if ( $user ) {
			self::login( $user );

			wp_send_json_success( array(
				'message'  => __( 'خوش آمدید! در حال انتقال به حساب کاربری…', 'moon-theme' ),
				'redirect' => self::redirect_url(),
				'verified' => true,
			) );
		}

		if ( ! self::registration_allowed() ) {
			wp_send_json_error( array( 'message' => __( 'حسابی با این شماره پیدا نشد و ثبت‌نام در این فروشگاه غیرفعال است.', 'moon-theme' ) ) );
		}

		// Ownership proven: issue the single-use token registration needs.
		$token = wp_generate_password( 32, false, false );

		set_transient( 'moon_otp_verified_' . $phone, wp_hash_password( $token ), self::TOKEN_TTL );

		wp_send_json_success( array(
			'message'        => __( 'شماره شما تأیید شد. نام خود را کامل کنید.', 'moon-theme' ),
			'verified'       => true,
			'phone'          => $phone,
			'register_token' => $token,
			'redirect'       => self::redirect_url(),
		) );
	}

	/**
	 * AJAX: register with name, phone and the token from a verified code.
	 */
	public static function ajax_register() {
		check_ajax_referer( 'moon_login', 'nonce' );

		$phone      = self::normalize_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
		$token      = sanitize_text_field( wp_unslash( isset( $_POST['token'] ) ? $_POST['token'] : '' ) );
		$first_name = sanitize_text_field( wp_unslash( isset( $_POST['first_name'] ) ? $_POST['first_name'] : '' ) );
		$last_name  = sanitize_text_field( wp_unslash( isset( $_POST['last_name'] ) ? $_POST['last_name'] : '' ) );
		$email      = sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) );
		$password   = (string) wp_unslash( isset( $_POST['password'] ) ? $_POST['password'] : '' );

		if ( ! $phone ) {
			wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'moon-theme' ) ) );
		}

		// A verified number is the only way to create an account from this endpoint.
		$proof = get_transient( 'moon_otp_verified_' . $phone );

		if ( ! $proof || '' === $token || ! wp_check_password( $token, $proof ) ) {
			wp_send_json_error( array( 'message' => __( 'برای ساخت حساب، ابتدا شماره موبایل خود را با کد تأیید کنید.', 'moon-theme' ) ), 403 );
		}

		if ( ! self::registration_allowed() ) {
			wp_send_json_error( array( 'message' => __( 'ثبت‌نام در این فروشگاه غیرفعال است.', 'moon-theme' ) ), 403 );
		}

		if ( ! $first_name ) {
			wp_send_json_error( array( 'message' => __( 'نام خود را وارد کنید.', 'moon-theme' ) ) );
		}

		if ( '' !== $password && strlen( $password ) < 8 ) {
			wp_send_json_error( array( 'message' => __( 'رمز عبور باید حداقل ۸ کاراکتر باشد.', 'moon-theme' ) ) );
		}

		if ( self::find_user( $phone ) ) {
			wp_send_json_error( array( 'message' => __( 'این شماره قبلاً ثبت شده است. وارد شوید.', 'moon-theme' ) ) );
		}

		$user = self::create_user( $phone, $first_name, $last_name, $email, $password );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( array( 'message' => $user->get_error_message() ) );
		}

		delete_transient( 'moon_otp_verified_' . $phone );

		self::login( $user );

		wp_send_json_success( array(
			'message'  => __( 'حساب شما ساخته شد. خوش آمدید!', 'moon-theme' ),
			'redirect' => self::redirect_url(),
		) );
	}

	/**
	 * Create a customer account for a verified phone number.
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
			'user_pass'    => $password ? $password : wp_generate_password( 24 ),
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
		wp_set_auth_cookie(
			$user->ID,
			! empty( $_POST['remember'] ), // phpcs:ignore WordPress.Security.NonceVerification -- nonce checked by the caller.
			is_ssl()
		);
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

		// Return the visitor to where they started (e.g. a product's review form),
		// but only to this site: wp_validate_redirect rejects external hosts.
		if ( ! empty( $_POST['redirect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$redirect = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['redirect'] ) ), $redirect ); // phpcs:ignore WordPress.Security.NonceVerification
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
