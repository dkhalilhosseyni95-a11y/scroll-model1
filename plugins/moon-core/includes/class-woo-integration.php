<?php
/**
 * WooCommerce integration for Moon Core.
 *
 * Moon Core never replaces WooCommerce templates, cart or checkout. It declares
 * feature compatibility, extends WooCommerce with the MOON loop card classes and
 * keeps the header cart counter in sync through the official fragment API.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Woo_Integration {

	public function init(): void {
		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'loop_button_args' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'woocommerce_product_loop_start', array( $this, 'loop_start' ) );
	}

	/**
	 * Declare HPOS and product block compatibility (we only read via APIs).
	 */
	public function declare_compatibility(): void {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MOON_CORE_FILE, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', MOON_CORE_FILE, true );
	}

	/**
	 * Keep the capsule header counter and mini cart in sync.
	 *
	 * @param array<string,string> $fragments Fragment map.
	 * @return array<string,string>
	 */
	public function cart_fragments( array $fragments ): array {
		$count = Site_Chrome::cart_count();

		$fragments['span.moon-cart-count'] = Site_Chrome::cart_count_markup( $count );

		return $fragments;
	}

	/**
	 * Add MOON classes to WooCommerce's own loop button.
	 *
	 * @param array<string,mixed> $args    Button args.
	 * @param \WC_Product         $product Product.
	 * @return array<string,mixed>
	 */
	public function loop_button_args( array $args, $product ): array {
		// WooCommerce prints this value through esc_attr(), so it must stay a string.
		$classes = isset( $args['class'] ) && is_string( $args['class'] ) ? $args['class'] : 'button';

		if ( false === strpos( $classes, 'moon-loop-button' ) ) {
			$classes .= ' moon-loop-button';
		}

		$args['class'] = trim( $classes );

		return $args;
	}

	/**
	 * Body classes for store styling hooks.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		if ( function_exists( 'is_product' ) && is_product() ) {
			$classes[] = 'moon-is-product';
		}

		if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
			$classes[] = 'moon-is-archive';
		}

		return $classes;
	}

	/**
	 * Mark the native WooCommerce product loop so the theme can target it.
	 *
	 * @param string $html Loop opening markup.
	 */
	public function loop_start( $html ): string {
		return str_replace( 'class="products', 'class="products moon-product-loop', (string) $html );
	}
}
