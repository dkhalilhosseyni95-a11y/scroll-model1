<?php
/**
 * MOON Product Carousel — WooCommerce products in a scroll-snap track.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Product_Card;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Carousel extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-carousel';
	}

	public function get_title(): string {
		return __( 'MOON Product Carousel', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-carousel';
	}

	public function get_keywords(): array {
		return array( 'products', 'carousel', 'slider', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Selected', 'moon-core' ),
				'title'       => __( 'Pieces to keep', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_query', array( 'label' => __( 'Products', 'moon-core' ) ) );
		$this->product_query_controls( '' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Carousel', 'moon-core' ) ) );

		$this->add_responsive_control(
			'slides',
			array(
				'label'          => __( 'Slides visible', 'moon-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 56 ) ),
				'default'    => array( 'size' => 22 ),
				'selectors'  => array( '{{WRAPPER}} .moon-carousel__track' => 'gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => __( 'Arrows', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'progress',
			array(
				'label'        => __( 'Progress bar', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_quick_view',
			array(
				'label'        => __( 'Show quick view', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_category',
			array(
				'label'        => __( 'Show category', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->moon_motion_controls();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to use the product carousel.', 'moon-core' ) );
			return;
		}

		$products = $this->query_products( $settings );

		if ( empty( $products ) ) {
			printf(
				'<p class="moon-widget moon-empty-state">%s</p>',
				esc_html__( 'No products match this selection yet.', 'moon-core' )
			);
			return;
		}

		$slides = max( 1, (int) ( $settings['slides'] ?? 3 ) );
		?>
		<section class="moon-widget moon-carousel" data-moon-carousel style="--moon-slides: <?php echo esc_attr( (string) $slides ); ?>;"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="moon-carousel__head">
				<?php
				$this->render_heading(
					array(
						'heading_eyebrow'     => $settings['heading_eyebrow'] ?? '',
						'heading_title'       => $settings['heading_title'] ?? '',
						'heading_description' => $settings['heading_description'] ?? '',
						'heading_title_tag'   => $settings['heading_title_tag'] ?? 'h2',
					),
					'heading',
					'moon-section__head--inline'
				);
				?>
				<?php if ( 'yes' === ( $settings['arrows'] ?? 'yes' ) ) : ?>
					<div class="moon-carousel__arrows">
						<button type="button" class="moon-icon-btn" data-moon-carousel-prev aria-label="<?php esc_attr_e( 'Previous products', 'moon-core' ); ?>">
							<?php echo \MoonCore\Helpers::svg( \MoonCore\Helpers::icon( 'arrow-left', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
						<button type="button" class="moon-icon-btn" data-moon-carousel-next aria-label="<?php esc_attr_e( 'Next products', 'moon-core' ); ?>">
							<?php echo \MoonCore\Helpers::svg( \MoonCore\Helpers::icon( 'arrow-right', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<div class="moon-carousel__viewport">
				<div class="moon-carousel__track" data-moon-carousel-track>
					<?php foreach ( $products as $product ) : ?>
						<div class="moon-carousel__slide">
							<?php
							Product_Card::render(
								$product,
								array(
									'show_category'   => 'yes' === ( $settings['show_category'] ?? 'yes' ),
									'show_quick_view' => 'yes' === ( $settings['show_quick_view'] ?? 'yes' ),
								)
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( 'yes' === ( $settings['progress'] ?? 'yes' ) ) : ?>
				<div class="moon-carousel__progress" aria-hidden="true">
					<span data-moon-carousel-progress></span>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}
