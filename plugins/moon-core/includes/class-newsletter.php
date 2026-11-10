<?php
/**
 * Newsletter form and subscription handler.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * A lightweight, self-hosted newsletter: addresses are stored in one option and
 * can be exported by the site owner. No third-party service required.
 */
class Moon_Core_Newsletter {

	/**
	 * Option holding the subscriber list.
	 */
	const OPTION = 'moon_core_subscribers';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_shortcode( 'moon_newsletter', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_ajax_moon_subscribe', array( __CLASS__, 'subscribe' ) );
		add_action( 'wp_ajax_nopriv_moon_subscribe', array( __CLASS__, 'subscribe' ) );
	}

	/**
	 * `[moon_newsletter]` markup.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'heading' => __( 'عضویت در خبرنامه MOON', 'moon-core' ),
				'text'    => __( 'کالکشن‌های محدود، پیش از انتشار.', 'moon-core' ),
				'button'  => __( 'عضویت', 'moon-core' ),
				'layout'  => 'stack',
			),
			$atts,
			'moon_newsletter'
		);

		ob_start();
		?>
		<div class="moon-newsletter moon-newsletter--<?php echo esc_attr( $atts['layout'] ); ?>">
			<div class="moon-newsletter__copy">
				<?php if ( $atts['heading'] ) : ?>
					<h2 class="moon-newsletter__title"><?php echo esc_html( $atts['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $atts['text'] ) : ?>
					<p class="moon-newsletter__text"><?php echo esc_html( $atts['text'] ); ?></p>
				<?php endif; ?>
			</div>
			<form class="moon-newsletter__form" data-moon-newsletter novalidate>
				<label class="screen-reader-text" for="moon-newsletter-email"><?php esc_html_e( 'ایمیل', 'moon-core' ); ?></label>
				<input id="moon-newsletter-email" type="email" name="email" required
					placeholder="<?php esc_attr_e( 'ایمیل شما', 'moon-core' ); ?>" autocomplete="email" />
				<input type="text" name="moon_hp" class="moon-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
				<button class="moon-btn moon-btn--gold" type="submit"><?php echo esc_html( $atts['button'] ); ?></button>
				<p class="moon-newsletter__msg" role="status" aria-live="polite"></p>
			</form>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * AJAX: store an address.
	 */
	public static function subscribe() {
		check_ajax_referer( 'moon_core_nonce', 'nonce' );

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$trap  = isset( $_POST['moon_hp'] ) ? sanitize_text_field( wp_unslash( $_POST['moon_hp'] ) ) : '';

		if ( '' !== $trap ) {
			wp_send_json_success( array( 'message' => __( 'عضویت شما ثبت شد.', 'moon-core' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'ایمیل معتبر نیست.', 'moon-core' ) ), 400 );
		}

		$list = get_option( self::OPTION, array() );
		$list = is_array( $list ) ? $list : array();

		if ( in_array( $email, $list, true ) ) {
			wp_send_json_success( array( 'message' => __( 'این ایمیل قبلاً ثبت شده است.', 'moon-core' ) ) );
		}

		$list[] = $email;
		update_option( self::OPTION, array_slice( array_values( array_unique( $list ) ), 0, 5000 ), false );

		wp_send_json_success( array( 'message' => __( 'عضویت شما ثبت شد. خوش آمدید.', 'moon-core' ) ) );
	}
}
