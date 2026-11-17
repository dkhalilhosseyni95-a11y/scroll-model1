<?php
/**
 * Instant cart endpoints.
 *
 * `?wc-ajax=moon_cart_qty` sets an absolute quantity (0 removes the line) and
 * answers with the refreshed WooCommerce fragments in one round-trip. The
 * browser updates the drawer optimistically and only reconciles with this
 * response, so the interface never waits on the network.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stepper controls inside each mini-cart line.
 *
 * @param string $html     Existing quantity markup.
 * @param array  $item     Cart item.
 * @param string $item_key Cart item key.
 * @return string
 */
function moon_mini_cart_stepper( $html, $item = array(), $item_key = '' ) {
	if ( ! $item_key || empty( $item['quantity'] ) ) {
		return $html;
	}

	$qty   = (int) $item['quantity'];
	$price = ( isset( $item['data'] ) && function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_product_price( $item['data'] ) : '';

	$html  = '<span class="quantity">';
	$html .= '<span class="moon-mq" data-moon-line="' . esc_attr( $item_key ) . '" data-qty="' . esc_attr( (string) $qty ) . '">';
	$html .= '<button type="button" class="moon-mq__btn" data-moon-qty="-1" aria-label="' . esc_attr__( 'کاهش تعداد', 'moon-theme' ) . '">−</button>';
	$html .= '<span class="moon-mq__n" aria-live="polite">' . esc_html( moon_fa_digits( (string) $qty ) ) . '</span>';
	$html .= '<button type="button" class="moon-mq__btn" data-moon-qty="1" aria-label="' . esc_attr__( 'افزایش تعداد', 'moon-theme' ) . '">+</button>';
	$html .= '</span>';
	$html .= $price ? ' <span class="moon-mq__x">×</span> ' . moon_fa_digits( $price ) : '';
	$html .= '</span>';

	return $html;
}
add_filter( 'woocommerce_widget_cart_item_quantity', 'moon_mini_cart_stepper', 20, 3 );

/**
 * Set a line quantity and return fragments.
 */
function moon_wc_ajax_cart_qty() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'moon_shop' ) ) {
		wp_send_json_error( array( 'message' => 'nonce' ), 403 );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => 'cart' ), 400 );
	}

	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0;

	$cart = WC()->cart->get_cart();

	if ( ! $key || ! isset( $cart[ $key ] ) ) {
		wp_send_json_error( array( 'message' => 'line' ), 404 );
	}

	// Respect stock and a sane ceiling.
	if ( $qty > 0 && ! empty( $cart[ $key ]['data'] ) ) {
		$max = (int) $cart[ $key ]['data']->get_max_purchase_quantity();
		$qty = min( $qty, 99, $max > 0 ? $max : 99 );
	}

	if ( 0 === $qty ) {
		WC()->cart->remove_cart_item( $key );
	} else {
		WC()->cart->set_quantity( $key, $qty, true );
	}

	WC()->cart->calculate_totals();

	ob_start();
	woocommerce_mini_cart();
	$mini = ob_get_clean();

	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array( 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini . '</div>' ) );

	wp_send_json_success( array(
		'fragments' => $fragments,
		'count'     => WC()->cart->get_cart_contents_count(),
		'cart_hash' => WC()->cart->get_cart_hash(),
	) );
}
add_action( 'wc_ajax_moon_cart_qty', 'moon_wc_ajax_cart_qty' );
