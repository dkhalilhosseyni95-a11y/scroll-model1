<?php
/**
 * MOON Related Products — WooCommerce related products for the current product.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Product_Card;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Related_Products extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'related-products';
	}

	public function get_title(): string {
		return __( 'MOON Related Products', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-related';
	}

	public function get_keywords(): array {
		return array( 'related', 'products', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Complete the look', 'moon-core' ),
				'title'       => __( 'Worn with', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_query', array( 'label' => __( 'Products', 'moon-core' ) ) );

		$this->add_control(
			'product_source',
			array(
				'label'   => __( 'Base product', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'current',
				'options' => array(
					'current'  => __( 'Current product (single product template)', 'moon-core' ),
					'specific' => __( 'Choose a product', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'       => __( 'Product', 'moon-core' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->product_options(),
				'condition'   => array( 'product_source' => 'specific' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Number of products', 'moon-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 12,
				'default' => 4,
			)
		);

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
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .moon-product-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
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

		$this->end_controls_section();

		$this->moon_motion_controls();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to show related products.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'This widget needs a product context. Choose a product or use a single product template.', 'moon-core' ) );
			return;
		}

		$limit = max( 1, (int) ( $settings['limit'] ?? 4 ) );
		$ids   = wc_get_related_products( $product->get_id(), $limit, $product->get_upsell_ids() );

		if ( empty( $ids ) ) {
			$this->render_notice( __( 'No related products yet. Add products to the same categories or tags.', 'moon-core' ) );
			return;
		}

		$products = array_filter( array_map( 'wc_get_product', $ids ) );

		if ( empty( $products ) ) {
			return;
		}
		?>
		<section class="moon-widget moon-related"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			$this->render_heading(
				array(
					'heading_eyebrow'     => $settings['heading_eyebrow'] ?? '',
					'heading_title'       => $settings['heading_title'] ?? '',
					'heading_description' => $settings['heading_description'] ?? '',
					'heading_title_tag'   => $settings['heading_title_tag'] ?? 'h2',
				)
			);
			?>
			<div class="moon-product-grid">
				<?php foreach ( $products as $related ) : ?>
					<?php
					Product_Card::render(
						$related,
						array(
							'show_quick_view' => 'yes' === ( $settings['show_quick_view'] ?? 'yes' ),
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
