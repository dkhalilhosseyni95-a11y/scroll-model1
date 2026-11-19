<?php
/**
 * Shared AJAX helpers.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify the theme AJAX nonce.
 */
function netelec_ajax_verify() {
	check_ajax_referer( 'netelec_nonce', 'nonce' );
}

/**
 * Send a JSON error.
 *
 * @param string $message Message.
 * @param int    $status  HTTP status.
 */
function netelec_ajax_fail( $message, $status = 400 ) {
	wp_send_json_error( array( 'message' => $message ), $status );
}

/**
 * Simple per-IP rate limiter backed by transients.
 *
 * @param string $bucket Bucket name.
 * @param int    $limit  Max hits.
 * @param int    $window Window in seconds.
 * @return bool True when the request is allowed.
 */
function netelec_rate_limit( $bucket, $limit, $window ) {
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key   = 'nc_rl_' . md5( $bucket . '|' . $ip );
	$hits  = (int) get_transient( $key );
	if ( $hits >= $limit ) {
		return false;
	}
	set_transient( $key, $hits + 1, $window );
	return true;
}
