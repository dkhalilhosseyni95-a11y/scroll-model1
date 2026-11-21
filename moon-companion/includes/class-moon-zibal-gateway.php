<?php
/**
 * Zibal payment gateway for WooCommerce (REST API v1).
 *
 * Flow: request → redirect to /start/{trackId} → callback → server-side verify.
 * The order is only marked paid after Zibal's verify call confirms the same
 * trackId AND the same amount; the callback query string is never trusted.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

class Moon_Zibal_Gateway extends WC_Payment_Gateway {

	const API = 'https://gateway.zibal.ir';

	/**
	 * Setup.
	 */
	public function __construct() {
		$this->id                 = 'moon_zibal';
		$this->method_title       = __( 'زیبال', 'moon-theme' );
		$this->method_description = __( 'پرداخت آنلاین با درگاه زیبال. برای تست مرچنت را zibal بگذارید.', 'moon-theme' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_moon_zibal', array( $this, 'handle_callback' ) );
	}

	/**
	 * Settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'فعال‌سازی', 'moon-theme' ),
				'type'    => 'checkbox',
				'label'   => __( 'پرداخت با زیبال', 'moon-theme' ),
				'default' => 'no',
			),
			'title'       => array(
				'title'   => __( 'عنوان', 'moon-theme' ),
				'type'    => 'text',
				'default' => __( 'پرداخت آنلاین (زیبال)', 'moon-theme' ),
			),
			'description' => array(
				'title'   => __( 'توضیح', 'moon-theme' ),
				'type'    => 'textarea',
				'default' => __( 'پرداخت امن با کارت‌های عضو شتاب.', 'moon-theme' ),
			),
			'merchant'    => array(
				'title'       => __( 'کد مرچنت', 'moon-theme' ),
				'type'        => 'text',
				'description' => __( 'کد مرچنت پنل زیبال. برای حالت آزمایشی بنویسید: zibal', 'moon-theme' ),
				'default'     => '',
			),
		);
	}

	/**
	 * Zibal works in Rials; convert from the store currency.
	 *
	 * @param float $amount Order total.
	 * @return int|false Rials, or false for unsupported currencies.
	 */
	private function to_rial( $amount ) {
		$factor = array(
			'IRR'  => 1,
			'IRT'  => 10,
			'IRHR' => 1000,
			'IRHT' => 10000,
		);

		$currency = get_woocommerce_currency();

		if ( ! isset( $factor[ $currency ] ) ) {
			return false;
		}

		return (int) round( (float) $amount * $factor[ $currency ] );
	}

	/**
	 * Hide the method when it cannot work.
	 *
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && '' !== trim( (string) $this->get_option( 'merchant' ) ) && false !== $this->to_rial( 1 );
	}

	/**
	 * Call the Zibal API.
	 *
	 * @param string $path Endpoint, e.g. v1/request.
	 * @param array  $body JSON body.
	 * @return array|WP_Error
	 */
	private function call( $path, array $body ) {
		$base = untrailingslashit( apply_filters( 'moon_zibal_api_base', self::API ) );

		$response = wp_remote_post( $base . '/' . $path, array(
			'timeout' => 20,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $data ) ? $data : new WP_Error( 'moon_zibal_bad_response', __( 'پاسخ نامعتبر از درگاه.', 'moon-theme' ) );
	}

	/**
	 * Start the payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order  = wc_get_order( $order_id );
		$amount = $order ? $this->to_rial( $order->get_total() ) : false;

		if ( ! $order || ! $amount || $amount < 1000 ) {
			wc_add_notice( __( 'مبلغ سفارش برای پرداخت آنلاین معتبر نیست.', 'moon-theme' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$callback = add_query_arg( array(
			'order' => $order->get_id(),
			'key'   => $order->get_order_key(),
		), WC()->api_request_url( 'moon_zibal' ) );

		$body = array(
			'merchant'    => trim( (string) $this->get_option( 'merchant' ) ),
			'amount'      => $amount,
			'callbackUrl' => $callback,
			'orderId'     => (string) $order->get_id(),
			/* translators: %s: order number. */
			'description' => sprintf( __( 'سفارش شماره %s', 'moon-theme' ), $order->get_order_number() ),
		);

		$phone = preg_replace( '/\D+/', '', (string) $order->get_billing_phone() );

		if ( preg_match( '/^09\d{9}$/', $phone ) ) {
			$body['mobile'] = $phone;
		}

		$result = $this->call( 'v1/request', $body );

		if ( is_wp_error( $result ) || ! isset( $result['result'] ) || 100 !== (int) $result['result'] || empty( $result['trackId'] ) ) {
			$detail = is_wp_error( $result ) ? $result->get_error_message() : ( isset( $result['message'] ) ? $result['message'] : '' );
			$order->add_order_note( sprintf( 'Zibal request failed: %s', $detail ) );
			wc_add_notice( __( 'اتصال به درگاه پرداخت برقرار نشد. لطفاً دوباره تلاش کنید.', 'moon-theme' ), 'error' );

			return array( 'result' => 'failure' );
		}

		$order->update_meta_data( '_moon_zibal_track_id', (string) $result['trackId'] );
		$order->update_meta_data( '_moon_zibal_amount', $amount );
		$order->save();

		return array(
			'result'   => 'success',
			'redirect' => untrailingslashit( apply_filters( 'moon_zibal_api_base', self::API ) ) . '/start/' . rawurlencode( (string) $result['trackId'] ),
		);
	}

	/**
	 * Customer returns from Zibal.
	 */
	public function handle_callback() {
		// phpcs:disable WordPress.Security.NonceVerification -- external gateway callback; authenticated by order key + server-side verify.
		$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
		$key      = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
		$track_id = isset( $_GET['trackId'] ) ? sanitize_text_field( wp_unslash( $_GET['trackId'] ) ) : '';
		$success  = isset( $_GET['success'] ) ? sanitize_text_field( wp_unslash( $_GET['success'] ) ) : '';
		// phpcs:enable

		$order = $order_id ? wc_get_order( $order_id ) : false;

		if ( ! $order || ! hash_equals( (string) $order->get_order_key(), $key ) || $this->id !== $order->get_payment_method() ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}

		if ( $order->is_paid() ) {
			wp_safe_redirect( $this->get_return_url( $order ) );
			exit;
		}

		$stored_track = (string) $order->get_meta( '_moon_zibal_track_id' );

		if ( '' === $stored_track || $stored_track !== $track_id ) {
			$this->fail( $order, __( 'شناسه پرداخت با سفارش مطابقت ندارد.', 'moon-theme' ) );
		}

		if ( '1' !== $success ) {
			$this->fail( $order, __( 'پرداخت توسط کاربر لغو شد یا ناموفق بود.', 'moon-theme' ) );
		}

		$verify = $this->call( 'v1/verify', array(
			'merchant' => trim( (string) $this->get_option( 'merchant' ) ),
			'trackId'  => (int) $track_id,
		) );

		$code = ( ! is_wp_error( $verify ) && isset( $verify['result'] ) ) ? (int) $verify['result'] : 0;

		// 100 = verified now, 201 = already verified earlier.
		if ( ! in_array( $code, array( 100, 201 ), true ) ) {
			$this->fail( $order, __( 'تأیید پرداخت انجام نشد.', 'moon-theme' ) );
		}

		// The verified transaction must belong to this very order.
		if ( isset( $verify['orderId'] ) && (string) $verify['orderId'] !== (string) $order->get_id() ) {
			$order->update_status( 'on-hold', sprintf( 'Zibal orderId mismatch. track=%s', $track_id ) );
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		// Serialise concurrent callbacks (browser return + retries) so payment_complete runs once.
		$lock = 'moon_zibal_lock_' . $order->get_id();

		if ( ! add_option( $lock, time(), '', 'no' ) ) {
			if ( (int) get_option( $lock ) < time() - 120 ) {
				update_option( $lock, time(), 'no' );
			} else {
				wp_safe_redirect( $this->get_return_url( $order ) );
				exit;
			}
		}

		$order = wc_get_order( $order->get_id() );

		if ( $order->is_paid() ) {
			delete_option( $lock );
			wp_safe_redirect( $this->get_return_url( $order ) );
			exit;
		}

		$expected = (int) $order->get_meta( '_moon_zibal_amount' );
		$paid     = isset( $verify['amount'] ) ? (int) $verify['amount'] : 0;

		if ( $expected < 1 || $paid !== $expected ) {
			$order->update_status( 'on-hold', sprintf( 'Zibal amount mismatch. expected=%d paid=%d track=%s', $expected, $paid, $track_id ) );
			wc_add_notice( __( 'مبلغ پرداختی با سفارش یکسان نیست. با پشتیبانی تماس بگیرید.', 'moon-theme' ), 'error' );
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		$ref = isset( $verify['refNumber'] ) ? sanitize_text_field( (string) $verify['refNumber'] ) : '';

		if ( ! empty( $verify['cardNumber'] ) ) {
			$order->update_meta_data( '_moon_zibal_card', sanitize_text_field( (string) $verify['cardNumber'] ) );
		}

		$order->update_meta_data( '_moon_zibal_ref', $ref );
		$order->payment_complete( $ref ? $ref : $track_id );
		/* translators: 1: reference number, 2: track id. */
		$order->add_order_note( sprintf( __( 'پرداخت زیبال موفق. شماره پیگیری: %1$s، trackId: %2$s', 'moon-theme' ), $ref, $track_id ) );

		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}

		delete_option( $lock );

		wp_safe_redirect( $this->get_return_url( $order ) );
		exit;
	}

	/**
	 * Fail an order and send the customer back to pay again.
	 *
	 * @param WC_Order $order   Order.
	 * @param string   $message Reason.
	 */
	private function fail( $order, $message ) {
		$order->update_status( 'failed', $message );
		wc_add_notice( $message, 'error' );
		wp_safe_redirect( $order->get_checkout_payment_url() );
		exit;
	}
}
