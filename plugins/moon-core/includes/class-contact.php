<?php
/**
 * Minimal, real contact form (wp_mail). Gravity Forms, when installed, can be
 * used instead by placing its shortcode on the page — Moon Core never touches it.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact form shortcode and handler.
 */
class Moon_Core_Contact {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_shortcode( 'moon_contact_form', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_ajax_moon_contact', array( __CLASS__, 'send' ) );
		add_action( 'wp_ajax_nopriv_moon_contact', array( __CLASS__, 'send' ) );
	}

	/**
	 * Form markup.
	 *
	 * @return string
	 */
	public static function shortcode() {
		ob_start();
		?>
		<form class="moon-contact-form" data-moon-contact novalidate>
			<div class="moon-field">
				<label for="moon-contact-name"><?php esc_html_e( 'نام', 'moon-core' ); ?></label>
				<input id="moon-contact-name" type="text" name="name" required autocomplete="name" />
			</div>
			<div class="moon-field">
				<label for="moon-contact-email"><?php esc_html_e( 'ایمیل', 'moon-core' ); ?></label>
				<input id="moon-contact-email" type="email" name="email" required autocomplete="email" />
			</div>
			<div class="moon-field">
				<label for="moon-contact-message"><?php esc_html_e( 'پیام', 'moon-core' ); ?></label>
				<textarea id="moon-contact-message" name="message" rows="5" required></textarea>
			</div>
			<input type="text" name="moon_hp" class="moon-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
			<button class="moon-btn moon-btn--solid" type="submit"><?php esc_html_e( 'ارسال پیام', 'moon-core' ); ?></button>
			<p class="moon-form-msg" role="status" aria-live="polite"></p>
		</form>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * AJAX handler.
	 */
	public static function send() {
		check_ajax_referer( 'moon_core_nonce', 'nonce' );

		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$trap    = isset( $_POST['moon_hp'] ) ? sanitize_text_field( wp_unslash( $_POST['moon_hp'] ) ) : '';

		if ( '' !== $trap ) {
			wp_send_json_success( array( 'message' => __( 'پیام شما ارسال شد.', 'moon-core' ) ) );
		}

		if ( '' === $name || ! is_email( $email ) || '' === $message ) {
			wp_send_json_error( array( 'message' => __( 'همه فیلدها را کامل و صحیح وارد کنید.', 'moon-core' ) ), 400 );
		}

		$body = sprintf(
			/* translators: 1: sender name, 2: sender email, 3: message. */
			__( "نام: %1\$s\nایمیل: %2\$s\n\n%3\$s", 'moon-core' ),
			$name,
			$email,
			$message
		);

		$sent = wp_mail(
			antispambot( get_option( 'admin_email' ) ),
			sprintf(
				/* translators: %s: sender name. */
				__( 'پیام جدید از فرم تماس MOON — %s', 'moon-core' ),
				$name
			),
			$body,
			array( 'Reply-To: ' . $email )
		);

		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => __( 'ارسال ایمیل ممکن نشد. لطفاً تلفنی تماس بگیرید.', 'moon-core' ) ), 500 );
		}

		wp_send_json_success( array( 'message' => __( 'پیام شما ارسال شد. به‌زودی پاسخ می‌دهیم.', 'moon-core' ) ) );
	}
}
