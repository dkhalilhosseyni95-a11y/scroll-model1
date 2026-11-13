<?php
/**
 * MOON Mini Cart — WooCommerce mini cart in an editorial panel.
 *
 * Can be used in an Elementor header/footer template as a second cart surface;
 * the floating capsule header already opens the cart drawer site-wide.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Mini_Cart extends Widget_Base {

	protected function slug(): string {
		return 'mini-cart';
	}

	public function get_title(): string {
		return __( 'MOON Mini Cart', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-cart-medium';
	}

	public function get_keywords(): array {
		return array( 'cart', 'mini cart', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => '',
				'title'       => __( 'Your selection', 'moon-core' ),
				'description' => '',
				'tag'         => 'h3',
			)
		);

		$this->start_controls_section( 'section_panel', array( 'label' => __( 'Panel', 'moon-core' ) ) );

		$this->add_control(
			'panel',
			array(
				'label'        => __( 'Bordered panel', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'panel_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-mini-cart' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .moon-mini-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .moon-mini-cart' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-mini-cart__title' );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'woocommerce_mini_cart' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to show the mini cart.', 'moon-core' ) );
			return;
		}

		$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		?>
		<div class="moon-widget moon-mini-cart-wrap">
			<div class="moon-mini-cart<?php echo 'yes' === ( $settings['panel'] ?? 'yes' ) ? ' has-panel' : ''; ?>">
				<?php if ( '' !== trim( (string) ( $settings['heading_title'] ?? '' ) ) ) : ?>
					<p class="moon-mini-cart__title">
						<?php echo esc_html( $settings['heading_title'] ); ?>
						<span class="moon-mini-cart__count"><?php echo esc_html( sprintf( /* translators: %s: item count */ _n( '%s item', '%s items', $count, 'moon-core' ), number_format_i18n( $count ) ) ); ?></span>
					</p>
				<?php endif; ?>

				<div class="widget_shopping_cart_content">
					<?php
					// WooCommerce renders the list, subtotal and its cart/checkout links.
					woocommerce_mini_cart();
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
