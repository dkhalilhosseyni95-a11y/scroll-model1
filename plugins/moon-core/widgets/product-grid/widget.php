<?php
/**
 * MOON Product Grid — WooCommerce products from a real query.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Product_Card;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Grid extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-grid';
	}

	public function get_title(): string {
		return __( 'MOON Product Grid', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-products';
	}

	public function get_keywords(): array {
		return array( 'products', 'grid', 'woocommerce', 'shop', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'New in', 'moon-core' ),
				'title'       => __( 'The latest pieces', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_query', array( 'label' => __( 'Products', 'moon-core' ) ) );
		$this->product_query_controls( '' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'moon-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
				'selectors'      => array( '{{WRAPPER}} .moon-product-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'default'    => array( 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .moon-product-grid' => 'gap: {{SIZE}}px;' ),
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

		$this->add_control(
			'show_rating',
			array(
				'label'        => __( 'Show rating', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
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
				'description'  => __( 'Requires quick view to be enabled in Moon Core settings.', 'moon-core' ),
			)
		);

		$this->add_control(
			'empty_text',
			array(
				'label'       => __( 'Empty state text', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'No products match this selection yet.', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'heading'  => array( __( 'Heading', 'moon-core' ), '.moon-section__head' ),
				'category' => array( __( 'Product categories', 'moon-core' ), '.moon-product-card__cat' ),
				'price'    => array( __( 'Prices', 'moon-core' ), '.moon-product-card__price' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to use the product grid.', 'moon-core' ) );
			return;
		}

		$products = $this->query_products( $settings );
		?>
		<section class="moon-widget moon-products"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			$this->render_heading(
				array(
					'heading_eyebrow'     => $settings['heading_eyebrow'] ?? '',
					'heading_title'       => $settings['heading_title'] ?? '',
					'heading_description' => $settings['heading_description'] ?? '',
					'heading_title_tag'   => $settings['heading_title_tag'] ?? 'h2',
				)
			);

			if ( empty( $products ) ) {
				printf(
					'<p class="moon-empty-state">%s</p>',
					esc_html( (string) ( $settings['empty_text'] ?? __( 'No products found.', 'moon-core' ) ) )
				);
			} else {
				echo '<div class="moon-product-grid">';
				foreach ( $products as $product ) {
					Product_Card::render(
						$product,
						array(
							'show_category'   => 'yes' === ( $settings['show_category'] ?? 'yes' ),
							'show_rating'     => 'yes' === ( $settings['show_rating'] ?? '' ),
							'show_quick_view' => 'yes' === ( $settings['show_quick_view'] ?? 'yes' ),
						)
					);
				}
				echo '</div>';
			}
			?>
		</section>
		<?php
	}
}
