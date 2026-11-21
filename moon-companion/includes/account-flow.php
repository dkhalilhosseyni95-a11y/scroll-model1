<?php
/**
 * Optional sign-in before checkout, and wishlist sync into the customer's account.
 *
 * The gate follows WooCommerce's own "guest checkout" setting instead of
 * overriding it: when the store allows guest checkout (the default) anyone can
 * complete an order, and phone login stays available for customers who want an
 * account. Turn guest checkout off in WooCommerce → Settings → Accounts to
 * require the SMS sign-in again. On the first signed-in page view the browser
 * wishlist is merged into the account so it follows the customer across
 * devices.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_WISHLIST_META', '_moon_wishlist_product_ids' );

/**
 * Whether the store requires an account to order.
 *
 * @return bool
 */
function moon_acct_requires_account() {
	return 'yes' !== get_option( 'woocommerce_enable_guest_checkout', 'yes' );
}

/**
 * Send guests who open the checkout to the phone login, then bring them back.
 */
function moon_acct_guard_checkout() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() || is_wc_endpoint_url( 'order-pay' ) || is_user_logged_in() ) {
		return;
	}

	if ( ! moon_acct_requires_account() ) {
		return;
	}

	$url = add_query_arg(
		array(
			'redirect'           => rawurlencode( wc_get_checkout_url() ),
			'moon_auth_required' => '1',
		),
		wc_get_page_permalink( 'myaccount' )
	);

	wp_safe_redirect( $url );
	exit;
}
add_action( 'template_redirect', 'moon_acct_guard_checkout', 1 );

/**
 * Explain the redirect on the account page.
 */
function moon_acct_notice() {
	if ( isset( $_GET['moon_auth_required'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['moon_auth_required'] ) ) && function_exists( 'wc_add_notice' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wc_add_notice( __( 'سبد خرید شما محفوظ است. برای ثبت سفارش با شماره موبایل وارد شوید.', 'moon-theme' ), 'notice' );
	}
}
add_action( 'wp', 'moon_acct_notice', 20 );

/**
 * A signed-out order attempt (e.g. a stale form) is refused.
 */
function moon_acct_validate_checkout() {
	if ( ! is_user_logged_in() && moon_acct_requires_account() ) {
		wc_add_notice( __( 'برای ثبت سفارش ابتدا با شماره موبایل وارد شوید.', 'moon-theme' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'moon_acct_validate_checkout' );

/**
 * Load the sync script for signed-in visitors.
 */
function moon_acct_enqueue() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	wp_enqueue_script( 'moon-account-sync', plugins_url( 'assets/js/account-sync.js', MOON_COMPANION_DIR . 'moon-companion.php' ), array(), MOON_COMPANION_VERSION, array( 'strategy' => 'defer', 'in_footer' => false ) );
	wp_localize_script( 'moon-account-sync', 'moonAccountSync', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'moon_account_sync' ),
		'key'     => 'moon-wishlist',
	) );
}
add_action( 'wp_enqueue_scripts', 'moon_acct_enqueue', 30 );

/**
 * Merge / replace the account wishlist.
 */
function moon_acct_sync_ajax() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'auth' ), 401 );
	}

	check_ajax_referer( 'moon_account_sync', 'nonce' );

	$incoming = array();

	if ( isset( $_POST['wishlist_ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$raw      = wp_unslash( $_POST['wishlist_ids'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$incoming = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	}

	$incoming = is_array( $incoming ) ? $incoming : array();
	$user_id  = get_current_user_id();
	$existing = get_user_meta( $user_id, MOON_WISHLIST_META, true );
	$existing = is_array( $existing ) ? $existing : array();
	$mode     = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'merge'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$source   = 'replace' === $mode ? $incoming : array_merge( $existing, $incoming );
	$ids      = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $source ) ) ) ), -200 );

	$valid = array();
	$items = array();

	foreach ( $ids as $id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : false;

		if ( ! $product || 'publish' !== $product->get_status() || 'hidden' === $product->get_catalog_visibility() ) {
			continue;
		}

		$valid[] = $id;
		$image   = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );

		// Same shape the theme's own wishlist uses.
		$items[] = array(
			'id'    => (string) $id,
			'name'  => wp_strip_all_tags( $product->get_name() ),
			'url'   => get_permalink( $id ),
			'image' => $image ? esc_url_raw( $image ) : '',
		);
	}

	update_user_meta( $user_id, MOON_WISHLIST_META, $valid );
	wp_send_json_success( array( 'wishlist' => $items, 'count' => count( $items ) ) );
}
add_action( 'wp_ajax_moon_sync_account_state', 'moon_acct_sync_ajax' );
