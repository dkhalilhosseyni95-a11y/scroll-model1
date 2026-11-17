<?php
/**
 * Hardening that is safe to ship inside a theme.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Response headers.
 */
function moon_security_headers() {
	if ( headers_sent() || is_admin() ) {
		return;
	}

	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );

	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=15552000' );
	}
}
add_action( 'send_headers', 'moon_security_headers' );

/**
 * Remove version and discovery clutter from <head>.
 */
function moon_remove_head_clutter() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'moon_remove_head_clutter' );

add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Block ?author=N username enumeration.
 */
function moon_block_author_enumeration() {
	if ( ! is_admin() && isset( $_GET['author'] ) && ! is_user_logged_in() ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'moon_block_author_enumeration', 1 );

/**
 * Hide the users REST endpoint from anonymous visitors.
 *
 * @param array $endpoints REST endpoints.
 * @return array
 */
function moon_rest_hide_users( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}

	return $endpoints;
}
add_filter( 'rest_endpoints', 'moon_rest_hide_users' );
