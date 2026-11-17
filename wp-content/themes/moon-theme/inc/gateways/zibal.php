<?php
/**
 * Register the Zibal gateway (class is loaded lazily once WooCommerce exists).
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param array $gateways Gateway class names.
 * @return array
 */
function moon_register_zibal( $gateways ) {
	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once __DIR__ . '/class-moon-zibal-gateway.php';
		$gateways[] = 'Moon_Zibal_Gateway';
	}

	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'moon_register_zibal' );
