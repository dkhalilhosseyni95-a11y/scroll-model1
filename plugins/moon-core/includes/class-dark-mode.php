<?php
/**
 * Day / Night theme switching.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stores the visitor preference and prevents a flash of the wrong theme.
 */
class Moon_Core_Dark_Mode {

	/**
	 * Cookie name used as the pre-paint fallback when localStorage is blocked.
	 */
	const COOKIE = 'moon_theme';

	/**
	 * User meta key.
	 */
	const META = 'moon_theme';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'print_bootstrap_script' ), 1 );
		add_action( 'wp_ajax_moon_set_theme', array( __CLASS__, 'ajax_set_theme' ) );
		add_action( 'wp_ajax_nopriv_moon_set_theme', array( __CLASS__, 'ajax_set_theme' ) );
	}

	/**
	 * The theme preference configured by the site owner.
	 *
	 * @return string light|dark|system
	 */
	public static function default_mode() {
		$mode = (string) moon_core_setting( 'dark_default', 'system' );

		return in_array( $mode, array( 'light', 'dark', 'system' ), true ) ? $mode : 'system';
	}

	/**
	 * The visitor preference already known on the server (logged-in users).
	 *
	 * @return string
	 */
	public static function stored_mode() {
		if ( is_user_logged_in() ) {
			$stored = get_user_meta( get_current_user_id(), self::META, true );
			if ( in_array( $stored, array( 'light', 'dark' ), true ) ) {
				return $stored;
			}
		}

		if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
			$cookie = sanitize_key( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
			if ( in_array( $cookie, array( 'light', 'dark' ), true ) ) {
				return $cookie;
			}
		}

		return '';
	}

	/**
	 * Inline, render-blocking script: sets the attribute before first paint.
	 */
	public static function print_bootstrap_script() {
		$default = self::default_mode();
		$stored  = self::stored_mode();

		$script = '(function(d){try{var k="moonTheme",s=null;try{s=localStorage.getItem(k);}catch(e){}' .
			'if(!s){s=' . wp_json_encode( '' !== $stored ? $stored : '' ) . ';}' .
			'var def=' . wp_json_encode( $default ) . ';' .
			'if(!s&&def!=="system"){s=def;}' .
			'if(!s){s=window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";}' .
			'if(s!=="dark"&&s!=="light"){s="light";}' .
			'd.setAttribute("data-theme",s);d.style.colorScheme=s;' .
			'}catch(e){d.setAttribute("data-theme","light");}})(document.documentElement);';

		printf(
			'<script id="moon-theme-boot">%s</script>' . "\n",
			$script // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, pre-escaped JSON.
		);
	}

	/**
	 * Persist the choice for the current visitor.
	 */
	public static function ajax_set_theme() {
		check_ajax_referer( 'moon_core_nonce', 'nonce' );

		$mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
		if ( ! in_array( $mode, array( 'light', 'dark' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'حالت نامعتبر است.', 'moon-core' ) ), 400 );
		}

		setcookie( self::COOKIE, $mode, time() + MONTH_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), false );

		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), self::META, $mode );
		}

		wp_send_json_success( array( 'mode' => $mode ) );
	}
}
