<?php
/**
 * Fresh AJAX nonces for cached pages.
 *
 * Full-page caches (LiteSpeed, WP Rocket, CDN) serve HTML whose nonces have
 * expired, which made quick view, cart updates and OTP login fail with "-1".
 * The front end asks this uncached endpoint for new nonces and retries once.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the nonces used by the theme's public AJAX actions.
 */
function moon_ajax_fresh_nonce() {
	nocache_headers();

	wp_send_json_success( array(
		'moon_shop'         => wp_create_nonce( 'moon_shop' ),
		'moon_login'        => wp_create_nonce( 'moon_login' ),
		'moon_subscribe'    => wp_create_nonce( 'moon_subscribe' ),
		'moon_account_sync' => wp_create_nonce( 'moon_account_sync' ),
	) );
}
add_action( 'wp_ajax_moon_fresh_nonce', 'moon_ajax_fresh_nonce' );
add_action( 'wp_ajax_nopriv_moon_fresh_nonce', 'moon_ajax_fresh_nonce' );
