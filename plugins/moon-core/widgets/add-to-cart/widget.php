<?php
/**
 * MOON Add To Cart — WooCommerce's single add-to-cart form.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Add_To_Cart extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'add-to-cart';
	}

	public function get_title(): string {
		return __( 'MOON Add To Cart', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-add-to-cart';
	}

	public function get_keywords(): array {
		return array( 'add to cart', 'buy', 'product', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_product', array( 'label' => __( 'Product', 'moon-core' ) ) );
		$this->product_source_controls( 'current' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_elements', array( 'label' => __( 'Elements', 'moon-core' ) ) );

		$this->add_control(
			'show_stock',
			array(
				'label'        => __( 'Stock line', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'stock_text',
			array(
				'label'     => __( 'In-stock line', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Ready to ship in 48 hours', 'moon-core' ),
				'condition' => array( 'show_stock' => 'yes' ),
			)
		);

		$this->add_control(
			'out_of_stock_text',
			array(
				'label'     => __( 'Sold-out line', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Join the waitlist — restocking soon.', 'moon-core' ),
				'condition' => array( 'show_stock' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_button',
			array(
				'label' => __( 'Button', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => __( 'Button text', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .single_add_to_cart_button' => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_control(
			'button_bg',
			array(
				'label'     => __( 'Button background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .single_add_to_cart_button' => 'background-color: {{VALUE}} !important; border-color: {{VALUE}} !important;' ),
			)
		);

		$this->add_control(
			'button_radius',
			array(
				'label'      => __( 'Radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .single_add_to_cart_button' => 'border-radius: {{SIZE}}px !important;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_visibility_controls(
			array(
				'stock'  => array( __( 'Stock line', 'moon-core' ), '.moon-atc__stock' ),
				'button' => array( __( 'Add to cart button', 'moon-core' ), '.single_add_to_cart_button' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'woocommerce_template_single_add_to_cart' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to use add to cart.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'Select a product, or place this widget in a single product template.', 'moon-core' ) );
			return;
		}

		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WooCommerce template context.
		?>
		<div class="moon-widget moon-atc">
			<?php if ( 'yes' === ( $settings['show_stock'] ?? 'yes' ) ) : ?>
				<p class="moon-atc__stock <?php echo $product->is_in_stock() ? 'is-in' : 'is-out'; ?>">
					<?php
					echo esc_html(
						$product->is_in_stock()
							? (string) ( $settings['stock_text'] ?? '' )
							: (string) ( $settings['out_of_stock_text'] ?? '' )
					);
					?>
				</p>
			<?php endif; ?>

			<?php
			// WooCommerce renders quantity, variations and the button with its own nonces.
			woocommerce_template_single_add_to_cart();
			?>
		</div>
		<?php
	}
}
