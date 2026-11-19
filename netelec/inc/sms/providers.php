<?php
/**
 * SMS providers for the OTP login.
 *
 * Credentials are stored in the WordPress options table and are only ever used
 * server-side. Nothing from this file is exposed to the browser.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base provider.
 */
abstract class Netelec_SMS_Provider {

	/**
	 * Provider settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @param array $settings Provider settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Human label.
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Send the verification code.
	 *
	 * @param string $phone Normalized phone number.
	 * @param string $code  Verification code.
	 * @return true|WP_Error
	 */
	abstract public function send( $phone, $code );

	/**
	 * Check that the credentials work. Performs a real API call.
	 *
	 * @return true|WP_Error
	 */
	abstract public function test();

	/**
	 * Configured value.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	protected function get( $key ) {
		return isset( $this->settings[ $key ] ) ? trim( (string) $this->settings[ $key ] ) : '';
	}

	/**
	 * Message body for plain-text sending.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	protected function message( $code ) {
		/* translators: %s: verification code. */
		return sprintf( 'کد ورود شما به %s: %s', netelec_opt( 'brand_name' ), $code );
	}
}

/**
 * Kavenegar (verify/lookup + sms/send).
 */
class Netelec_SMS_Provider_Kavenegar extends Netelec_SMS_Provider {

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'کاوه‌نگار (Kavenegar)';
	}

	/**
	 * Send.
	 *
	 * @param string $phone Phone.
	 * @param string $code  Code.
	 * @return true|WP_Error
	 */
	public function send( $phone, $code ) {
		$api_key = $this->get( 'sms_api_key' );
		if ( ! $api_key ) {
			return new WP_Error( 'netelec_sms', 'کلید API کاوه‌نگار تنظیم نشده است.' );
		}

		$pattern = $this->get( 'sms_pattern' );

		if ( $pattern ) {
			$url = sprintf(
				'https://api.kavenegar.com/v1/%s/verify/lookup.json?receptor=%s&token=%s&template=%s',
				rawurlencode( $api_key ),
				rawurlencode( $phone ),
				rawurlencode( $code ),
				rawurlencode( $pattern )
			);
			$response = wp_remote_get( $url, array( 'timeout' => 20 ) );
		} else {
			$response = wp_remote_post(
				'https://api.kavenegar.com/v1/' . rawurlencode( $api_key ) . '/sms/send.json',
				array(
					'timeout' => 20,
					'body'    => array(
						'receptor' => $phone,
						'sender'   => $this->get( 'sms_sender' ),
						'message'  => $this->message( $code ),
					),
				)
			);
		}

		return $this->check_response( $response );
	}

	/**
	 * Test the credentials.
	 *
	 * @return true|WP_Error
	 */
	public function test() {
		$api_key = $this->get( 'sms_api_key' );
		if ( ! $api_key ) {
			return new WP_Error( 'netelec_sms', 'کلید API کاوه‌نگار تنظیم نشده است.' );
		}

		$response = wp_remote_get( 'https://api.kavenegar.com/v1/' . rawurlencode( $api_key ) . '/account/info.json', array( 'timeout' => 20 ) );
		return $this->check_response( $response );
	}

	/**
	 * Validate a Kavenegar response.
	 *
	 * @param array|WP_Error $response HTTP response.
	 * @return true|WP_Error
	 */
	private function check_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'netelec_sms', 'اتصال به کاوه‌نگار برقرار نشد: ' . $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || ! isset( $body['return']['status'] ) || 200 !== (int) $body['return']['status'] ) {
			$message = isset( $body['return']['message'] ) ? $body['return']['message'] : 'پاسخ نامعتبر از سرویس';
			return new WP_Error( 'netelec_sms', 'کاوه‌نگار: ' . $message );
		}

		return true;
	}
}

/**
 * Melipayamak.
 */
class Netelec_SMS_Provider_Melipayamak extends Netelec_SMS_Provider {

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'ملی‌پیامک (Melipayamak)';
	}

	/**
	 * Send.
	 *
	 * @param string $phone Phone.
	 * @param string $code  Code.
	 * @return true|WP_Error
	 */
	public function send( $phone, $code ) {
		$username = $this->get( 'sms_username' );
		$password = $this->get( 'sms_password' );

		if ( ! $username || ! $password ) {
			return new WP_Error( 'netelec_sms', 'نام کاربری و رمز عبور ملی‌پیامک تنظیم نشده است.' );
		}

		$pattern = $this->get( 'sms_pattern' );

		$url  = $pattern
			? 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber'
			: 'https://rest.payamak-panel.com/api/SendSMS/SendSMS';
		$body = $pattern
			? array(
				'username' => $username,
				'password' => $password,
				'text'     => $code,
				'to'       => $phone,
				'bodyId'   => $pattern,
			)
			: array(
				'username' => $username,
				'password' => $password,
				'to'       => $phone,
				'from'     => $this->get( 'sms_sender' ),
				'text'     => $this->message( $code ),
				'isflash'  => 'false',
			);

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 20,
				'body'    => $body,
			)
		);

		return $this->check_response( $response );
	}

	/**
	 * Test the credentials.
	 *
	 * @return true|WP_Error
	 */
	public function test() {
		$username = $this->get( 'sms_username' );
		$password = $this->get( 'sms_password' );

		if ( ! $username || ! $password ) {
			return new WP_Error( 'netelec_sms', 'نام کاربری و رمز عبور ملی‌پیامک تنظیم نشده است.' );
		}

		$response = wp_remote_post(
			'https://rest.payamak-panel.com/api/SendSMS/GetCredit',
			array(
				'timeout' => 20,
				'body'    => array(
					'username' => $username,
					'password' => $password,
				),
			)
		);

		return $this->check_response( $response );
	}

	/**
	 * Validate a Melipayamak response.
	 *
	 * @param array|WP_Error $response HTTP response.
	 * @return true|WP_Error
	 */
	private function check_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'netelec_sms', 'اتصال به ملی‌پیامک برقرار نشد: ' . $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || ! isset( $body['RetStatus'] ) || 1 !== (int) $body['RetStatus'] ) {
			$message = isset( $body['StrRetStatus'] ) ? $body['StrRetStatus'] : 'پاسخ نامعتبر از سرویس';
			return new WP_Error( 'netelec_sms', 'ملی‌پیامک: ' . $message );
		}

		return true;
	}
}

/**
 * IPPanel.
 */
class Netelec_SMS_Provider_IPPanel extends Netelec_SMS_Provider {

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'آی‌پی‌پنل (IPPanel)';
	}

	/**
	 * Send.
	 *
	 * @param string $phone Phone.
	 * @param string $code  Code.
	 * @return true|WP_Error
	 */
	public function send( $phone, $code ) {
		$api_key = $this->get( 'sms_api_key' );
		if ( ! $api_key ) {
			return new WP_Error( 'netelec_sms', 'کلید API آی‌پی‌پنل تنظیم نشده است.' );
		}

		$pattern = $this->get( 'sms_pattern' );

		$url  = $pattern
			? 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send'
			: 'https://api2.ippanel.com/api/v1/sms/send/webservice/single';
		$body = $pattern
			? array(
				'code'      => $pattern,
				'sender'    => $this->get( 'sms_sender' ),
				'recipient' => $phone,
				'variable'  => array( 'code' => $code ),
			)
			: array(
				'recipient' => array( $phone ),
				'sender'    => $this->get( 'sms_sender' ),
				'message'   => $this->message( $code ),
			);

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'apikey'       => $api_key,
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		return $this->check_response( $response );
	}

	/**
	 * Test the credentials.
	 *
	 * @return true|WP_Error
	 */
	public function test() {
		$api_key = $this->get( 'sms_api_key' );
		if ( ! $api_key ) {
			return new WP_Error( 'netelec_sms', 'کلید API آی‌پی‌پنل تنظیم نشده است.' );
		}

		$response = wp_remote_get(
			'https://api2.ippanel.com/api/v1/sms/accounting/credit/show',
			array(
				'timeout' => 20,
				'headers' => array( 'apikey' => $api_key ),
			)
		);

		return $this->check_response( $response );
	}

	/**
	 * Validate an IPPanel response.
	 *
	 * @param array|WP_Error $response HTTP response.
	 * @return true|WP_Error
	 */
	private function check_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'netelec_sms', 'اتصال به آی‌پی‌پنل برقرار نشد: ' . $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$message = isset( $body['message'] ) ? $body['message'] : 'کد پاسخ ' . $code;
			return new WP_Error( 'netelec_sms', 'آی‌پی‌پنل: ' . $message );
		}

		return true;
	}
}

/**
 * Local development provider: writes the code to the PHP error log instead of
 * sending an SMS. Only for testing the flow on a development install.
 */
class Netelec_SMS_Provider_Log extends Netelec_SMS_Provider {

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'حالت آزمایشی — فقط ثبت در لاگ سرور (بدون ارسال پیامک)';
	}

	/**
	 * Send.
	 *
	 * @param string $phone Phone.
	 * @param string $code  Code.
	 * @return true|WP_Error
	 */
	public function send( $phone, $code ) {
		error_log( sprintf( '[NETELEC OTP] phone=%s code=%s', $phone, $code ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return true;
	}

	/**
	 * Test.
	 *
	 * @return true|WP_Error
	 */
	public function test() {
		return new WP_Error( 'netelec_sms_log', 'حالت آزمایشی است: پیامکی ارسال نمی‌شود و کد در لاگ سرور ثبت می‌شود.' );
	}
}

/**
 * Provider choices for the settings panel.
 *
 * @return array
 */
function netelec_sms_provider_choices() {
	$choices = array(
		'none'       => '— انتخاب نشده —',
		'kavenegar'  => 'کاوه‌نگار (Kavenegar)',
		'melipayamak' => 'ملی‌پیامک (Melipayamak)',
		'ippanel'    => 'آی‌پی‌پنل (IPPanel)',
		'log'        => 'حالت آزمایشی (فقط توسعه)',
	);

	/**
	 * Allow other Iranian providers to be registered.
	 *
	 * @param array $choices slug => label.
	 */
	return apply_filters( 'netelec_sms_provider_choices', $choices );
}

/**
 * Build the configured provider instance.
 *
 * @return Netelec_SMS_Provider|null
 */
function netelec_sms_provider() {
	$settings = array(
		'sms_api_key'  => netelec_opt( 'sms_api_key' ),
		'sms_username' => netelec_opt( 'sms_username' ),
		'sms_password' => netelec_opt( 'sms_password' ),
		'sms_sender'   => netelec_opt( 'sms_sender' ),
		'sms_pattern'  => netelec_opt( 'sms_pattern' ),
	);

	$providers = array(
		'kavenegar'   => 'Netelec_SMS_Provider_Kavenegar',
		'melipayamak' => 'Netelec_SMS_Provider_Melipayamak',
		'ippanel'     => 'Netelec_SMS_Provider_IPPanel',
		'log'         => 'Netelec_SMS_Provider_Log',
	);

	$slug = netelec_opt( 'sms_provider' );

	/**
	 * Allow registering a custom provider class.
	 *
	 * @param array $providers slug => class name.
	 */
	$providers = apply_filters( 'netelec_sms_providers', $providers );

	if ( ! isset( $providers[ $slug ] ) || ! class_exists( $providers[ $slug ] ) ) {
		return null;
	}

	return new $providers[ $slug ]( $settings );
}
