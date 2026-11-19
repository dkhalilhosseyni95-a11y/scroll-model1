<?php
/**
 * Recently viewed products (cookie based).
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Record the current product view.
 */
function netelec_track_product_view() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	$product_id = get_queried_object_id();
	if ( ! $product_id ) {
		return;
	}

	$cookie = isset( $_COOKIE['netelec_recent'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['netelec_recent'] ) ) : '';
	$ids    = array_filter( array_map( 'absint', explode( ',', $cookie ) ) );
	$ids    = array_values( array_diff( $ids, array( $product_id ) ) );
	array_unshift( $ids, $product_id );
	$ids = array_slice( $ids, 0, 8 );

	$value = implode( ',', $ids );
	$path  = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	setcookie( 'netelec_recent', $value, time() + ( 30 * DAY_IN_SECONDS ), $path, COOKIE_DOMAIN, is_ssl(), false );
	$_COOKIE['netelec_recent'] = $value;
}
add_action( 'template_redirect', 'netelec_track_product_view' );

/**
 * Recently viewed ids, excluding the current product.
 *
 * @return int[]
 */
function netelec_recent_ids() {
	$cookie = isset( $_COOKIE['netelec_recent'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['netelec_recent'] ) ) : '';
	$ids    = array_filter( array_map( 'absint', explode( ',', $cookie ) ) );

	if ( is_singular( 'product' ) ) {
		$ids = array_diff( $ids, array( get_queried_object_id() ) );
	}

	return array_slice( array_values( $ids ), 0, 4 );
}
