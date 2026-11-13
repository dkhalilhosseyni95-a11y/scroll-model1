<?php
/**
 * MOON Product Variations — WooCommerce's own variation form.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Variations extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-variations';
	}

	public function get_title(): string {
		return __( 'MOON Product Variations', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-options';
	}

	public function get_keywords(): array {
		return array( 'product', 'variations', 'size', 'colour', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_product', array( 'label' => __( 'Product', 'moon-core' ) ) );
		$this->product_source_controls( 'current' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_note', array( 'label' => __( 'Notes', 'moon-core' ) ) );

		$this->add_control(
			'editor_note',
			array(
				'label'       => __( 'Note above the selector', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => __( 'Leave empty to hide. The selector itself is rendered by WooCommerce, so stock, prices and variation images stay accurate.', 'moon-core' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'woocommerce_variable_add_to_cart' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to show product variations.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'Select a variable product, or place this widget in a single product template.', 'moon-core' ) );
			return;
		}

		if ( ! $product->is_type( 'variable' ) ) {
			$this->render_notice( __( 'This product has no variations. Use the Add To Cart widget for simple products.', 'moon-core' ) );
			return;
		}

		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WooCommerce template context.

		echo '<div class="moon-widget moon-variations">';
		if ( '' !== trim( (string) ( $settings['editor_note'] ?? '' ) ) ) {
			echo '<p class="moon-variations__note">' . esc_html( $settings['editor_note'] ) . '</p>';
		}

		// WooCommerce renders the attribute selects, price updates and stock state.
		woocommerce_variable_add_to_cart();

		echo '</div>';
	}
}
