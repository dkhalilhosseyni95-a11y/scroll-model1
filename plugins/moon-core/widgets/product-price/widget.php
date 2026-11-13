<?php
/**
 * MOON Product Price — WooCommerce price HTML with an optional saving note.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Price extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-price';
	}

	public function get_title(): string {
		return __( 'MOON Product Price', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-price';
	}

	public function get_keywords(): array {
		return array( 'product', 'price', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_product', array( 'label' => __( 'Product', 'moon-core' ) ) );
		$this->product_source_controls( 'current' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_elements', array( 'label' => __( 'Elements', 'moon-core' ) ) );

		$this->add_control(
			'show_badge',
			array(
				'label'        => __( 'Sale badge', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_saving',
			array(
				'label'        => __( 'Saving percentage', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'tax_note',
			array(
				'label'       => __( 'Tax note', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Taxes included. Shipping calculated at checkout.', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_price',
			array(
				'label' => __( 'Price', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => __( 'Colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-price' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'price_size',
			array(
				'label'      => __( 'Size', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 64 ) ),
				'selectors'  => array( '{{WRAPPER}} .moon-price' => 'font-size: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_visibility_controls(
			array(
				'badge' => array( __( 'Sale badge', 'moon-core' ), '.moon-price__badge' ),
				'note'  => array( __( 'Tax note', 'moon-core' ), '.moon-price__note' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to show a price.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'Select a product, or place this widget in a single product template.', 'moon-core' ) );
			return;
		}

		$saving = '';
		if ( 'yes' === ( $settings['show_saving'] ?? 'yes' ) && $product->is_on_sale() && $product->get_regular_price() > 0 ) {
			$percent = round( ( ( (float) $product->get_regular_price() - (float) $product->get_price() ) / (float) $product->get_regular_price() ) * 100 );
			if ( $percent > 0 ) {
				$saving = sprintf( /* translators: %s: percentage */ __( 'Save %s%%', 'moon-core' ), number_format_i18n( $percent ) );
			}
		}
		?>
		<div class="moon-widget moon-price-wrap">
			<p class="moon-price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
				<?php if ( 'yes' === ( $settings['show_badge'] ?? 'yes' ) && $product->is_on_sale() ) : ?>
					<span class="moon-badge moon-price__badge"><?php echo esc_html( $saving ?: __( 'Sale', 'moon-core' ) ); ?></span>
				<?php endif; ?>
			</p>
			<?php if ( '' !== trim( (string) ( $settings['tax_note'] ?? '' ) ) ) : ?>
				<p class="moon-price__note"><?php echo esc_html( $settings['tax_note'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
