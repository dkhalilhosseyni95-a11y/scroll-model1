<?php
/**
 * MOON Product Categories — WooCommerce product category tiles.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Categories extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-categories';
	}

	public function get_title(): string {
		return __( 'MOON Product Categories', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-categories';
	}

	public function get_keywords(): array {
		return array( 'categories', 'product', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Categories', 'moon-core' ),
				'title'       => __( 'Shop the departments', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_categories', array( 'label' => __( 'Categories', 'moon-core' ) ) );

		$this->add_control(
			'categories',
			array(
				'label'       => __( 'Only these categories', 'moon-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->product_category_options(),
				'description' => __( 'Leave empty to show every category with products.', 'moon-core' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'name',
				'options' => array(
					'name'  => __( 'Name', 'moon-core' ),
					'count' => __( 'Product count', 'moon-core' ),
					'slug'  => __( 'Slug', 'moon-core' ),
					'term_id' => __( 'Creation order', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Maximum categories', 'moon-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 24,
				'default' => 6,
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Show product count', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'moon-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .moon-cats-woo' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Tile ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.5, 'max' => 1.8, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1.25 ),
				'selectors'  => array( '{{WRAPPER}} .moon-cat-woo' => 'aspect-ratio: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 10 ),
				'selectors'  => array( '{{WRAPPER}} .moon-cat-woo' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'overlay_strength',
			array(
				'label'     => __( 'Overlay strength', 'moon-core' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 58 ),
				'selectors' => array( '{{WRAPPER}} .moon-cat-woo__overlay' => 'opacity: calc({{SIZE}} / 100);' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'name', __( 'Category name', 'moon-core' ), '.moon-cat-woo__name' );
		$this->moon_text_style( 'count', __( 'Product count', 'moon-core' ), '.moon-cat-woo__count' );

		$this->moon_motion_controls();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to list product categories.', 'moon-core' ) );
			return;
		}

		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => max( 1, (int) ( $settings['limit'] ?? 6 ) ),
			'orderby'    => in_array( $settings['orderby'] ?? 'name', array( 'name', 'count', 'slug', 'term_id' ), true ) ? $settings['orderby'] : 'name',
			'order'      => 'ASC',
		);

		$selected = array_filter( (array) ( $settings['categories'] ?? array() ) );
		if ( ! empty( $selected ) ) {
			$args['slug'] = array_map( 'sanitize_title', $selected );
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			$this->render_notice( __( 'No product categories found yet. Create categories in Products → Categories.', 'moon-core' ) );
			return;
		}
		?>
		<section class="moon-widget moon-cats-woo-wrap"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<div class="moon-cats-woo">
				<?php foreach ( $terms as $term ) : ?>
					<?php
					$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
					$link         = get_term_link( $term );
					?>
					<a class="moon-cat-woo" href="<?php echo esc_url( is_wp_error( $link ) ? '#' : $link ); ?>" data-moon-reveal-item>
						<?php if ( $thumbnail_id ) : ?>
							<?php echo wp_kses_post( wp_get_attachment_image( $thumbnail_id, 'moon-portrait', false, array( 'alt' => $term->name, 'loading' => 'lazy' ) ) ); ?>
						<?php endif; ?>
						<span class="moon-cat-woo__overlay" aria-hidden="true"></span>
						<span class="moon-cat-woo__body">
							<span class="moon-cat-woo__name"><?php echo esc_html( $term->name ); ?></span>
							<?php if ( 'yes' === ( $settings['show_count'] ?? 'yes' ) ) : ?>
								<span class="moon-cat-woo__count">
									<?php
									printf(
										/* translators: %s: product count */
										esc_html( _n( '%s piece', '%s pieces', (int) $term->count, 'moon-core' ) ),
										esc_html( number_format_i18n( (int) $term->count ) )
									);
									?>
								</span>
							<?php endif; ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
