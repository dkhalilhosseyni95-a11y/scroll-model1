<?php
/**
 * WooCommerce integration for the child theme.
 *
 * WooCommerce keeps ownership of every store template, cart and checkout flow.
 * The theme only adjusts markup wrappers and adds MOON classes so the store
 * inherits the editorial design without forking templates.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Give the store a container that matches the MOON grid.
 */
add_action(
	'woocommerce_before_main_content',
	static function () {
		echo '<div class="moon-store"><div class="moon-container">';
	},
	5
);

add_action(
	'woocommerce_after_main_content',
	static function () {
		echo '</div></div>';
	},
	50
);

add_filter(
	'woocommerce_post_class',
	static function ( $classes, $product ) {
		$classes[] = 'moon-product';
		if ( $product instanceof WC_Product && $product->is_on_sale() ) {
			$classes[] = 'is-on-sale';
		}
		return $classes;
	},
	10,
	2
);

/**
 * Keep the header cart count in sync with WooCommerce fragments.
 */
add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( $fragments ) {
		$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$fragments['span.moon-cart-count'] = sprintf(
			'<span class="moon-cart-count" data-count="%1$d">%2$s</span>',
			(int) $count,
			esc_html( $count > 99 ? '99+' : (string) $count )
		);
		return $fragments;
	}
);

/**
 * Product card badges (Sale / Sold out) rendered by the MOON loop styling.
 */
add_action(
	'woocommerce_before_shop_loop_item_title',
	static function () {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		echo '<div class="moon-product__badges">';
		if ( ! $product->is_in_stock() ) {
			echo '<span class="moon-badge moon-badge--muted">' . esc_html__( 'Sold out', 'moon-child' ) . '</span>';
		} elseif ( $product->is_on_sale() ) {
			echo '<span class="moon-badge">' . esc_html__( 'Sale', 'moon-child' ) . '</span>';
		}
		echo '</div>';
	},
	9
);

/**
 * Declare compatibility with WooCommerce features that change storage.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MOON_CHILD_DIR . '/functions.php', true );
		}
	}
);
