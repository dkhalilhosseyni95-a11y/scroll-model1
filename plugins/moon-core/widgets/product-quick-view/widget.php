<?php
/**
 * MOON Product Quick View — a trigger that opens the store's product summary in
 * a modal (handled by the site-wide Moon Core script, nonce-protected AJAX).
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Settings;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Quick_View extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-quick-view';
	}

	public function get_title(): string {
		return __( 'MOON Product Quick View', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-preview-medium';
	}

	public function get_keywords(): array {
		return array( 'quick view', 'product', 'modal', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_product', array( 'label' => __( 'Product', 'moon-core' ) ) );
		$this->product_source_controls( 'current' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_button', array( 'label' => __( 'Button', 'moon-core' ) ) );

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button text', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Quick view', 'moon-core' ),
			)
		);

		$this->add_control(
			'show_icon',
			array(
				'label'        => __( 'Show plus icon', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'moon-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => __( 'Start', 'moon-core' ), 'icon' => 'eicon-text-align-left' ),
					'center'     => array( 'title' => __( 'Centre', 'moon-core' ), 'icon' => 'eicon-text-align-center' ),
					'flex-end'   => array( 'title' => __( 'End', 'moon-core' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'flex-start',
				'selectors' => array( '{{WRAPPER}} .moon-widget' => 'display: flex; justify-content: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_button_style( 'button', __( 'Button', 'moon-core' ), '.moon-quick-view-btn' );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to use quick view.', 'moon-core' ) );
			return;
		}

		if ( Settings::get( 'quick_view' ) !== 'yes' ) {
			$this->render_notice( __( 'Quick view is disabled in Moon Core → Settings.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'Select a product, or place this widget in a single product template.', 'moon-core' ) );
			return;
		}

		$label = trim( (string) ( $settings['button_text'] ?? '' ) );
		if ( '' === $label ) {
			$label = __( 'Quick view', 'moon-core' );
		}
		?>
		<div class="moon-widget moon-quick-view-trigger-wrap">
			<button
				type="button"
				class="moon-btn moon-quick-view-btn"
				data-moon-quick-view="<?php echo esc_attr( (string) $product->get_id() ); ?>"
			>
				<?php if ( 'yes' === ( $settings['show_icon'] ?? 'yes' ) ) : ?>
					<?php echo \MoonCore\Helpers::svg( \MoonCore\Helpers::icon( 'plus', 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<span><?php echo esc_html( $label ); ?></span>
			</button>
		</div>
		<?php
	}
}
