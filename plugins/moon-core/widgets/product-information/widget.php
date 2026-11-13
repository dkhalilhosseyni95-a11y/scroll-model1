<?php
/**
 * MOON Product Information — title, rating, excerpt and meta for a product.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Information extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-information';
	}

	public function get_title(): string {
		return __( 'MOON Product Information', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-info';
	}

	public function get_keywords(): array {
		return array( 'product', 'title', 'summary', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_product', array( 'label' => __( 'Product', 'moon-core' ) ) );
		$this->product_source_controls( 'current' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_elements', array( 'label' => __( 'Elements', 'moon-core' ) ) );

		$this->add_control(
			'show_category',
			array(
				'label'        => __( 'Categories', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_title',
			array(
				'label'        => __( 'Title', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => __( 'Title tag', 'moon-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h1',
				'options'   => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'div' => 'div',
				),
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'show_rating',
			array(
				'label'        => __( 'Rating', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Short description', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_meta',
			array(
				'label'        => __( 'SKU & tags', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'stock_status',
			array(
				'label'        => __( 'Stock status', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-pi__title' );
		$this->moon_text_style( 'excerpt', __( 'Short description', 'moon-core' ), '.moon-pi__excerpt' );
		$this->moon_text_style( 'meta', __( 'Meta', 'moon-core' ), '.moon-pi__meta' );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to show product information.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'Select a product, or place this widget in a single product template.', 'moon-core' ) );
			return;
		}

		$tag = $this->heading_tag( (string) ( $settings['title_tag'] ?? 'h1' ) );
		?>
		<div class="moon-widget moon-pi">
			<?php if ( 'yes' === ( $settings['show_category'] ?? 'yes' ) ) : ?>
				<?php $categories = wc_get_product_category_list( $product->get_id(), ', ' ); ?>
				<?php if ( $categories ) : ?>
					<p class="moon-pi__cat moon-eyebrow"><?php echo wp_kses_post( $categories ); ?></p>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_title'] ?? 'yes' ) ) : ?>
				<?php
				printf(
					'<%1$s class="moon-pi__title">%2$s</%1$s>',
					esc_html( $tag ),
					esc_html( $product->get_name() )
				);
				?>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_rating'] ?? 'yes' ) && $product->get_average_rating() > 0 ) : ?>
				<div class="moon-pi__rating">
					<?php echo wp_kses_post( wc_get_rating_html( (float) $product->get_average_rating() ) ); ?>
					<span><?php echo esc_html( sprintf( /* translators: %s: review count */ _n( '%s review', '%s reviews', (int) $product->get_review_count(), 'moon-core' ), number_format_i18n( (int) $product->get_review_count() ) ) ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_excerpt'] ?? 'yes' ) ) : ?>
				<div class="moon-pi__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['stock_status'] ?? 'yes' ) ) : ?>
				<p class="moon-pi__stock<?php echo $product->is_in_stock() ? ' is-in' : ' is-out'; ?>">
					<?php echo esc_html( $product->is_in_stock() ? __( 'In stock — ships within 48 hours', 'moon-core' ) : __( 'Currently sold out', 'moon-core' ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_meta'] ?? 'yes' ) ) : ?>
				<ul class="moon-pi__meta">
					<?php if ( $product->get_sku() ) : ?>
						<li><span><?php esc_html_e( 'SKU', 'moon-core' ); ?></span><?php echo esc_html( $product->get_sku() ); ?></li>
					<?php endif; ?>
					<?php $tags = wc_get_product_tag_list( $product->get_id(), ', ' ); ?>
					<?php if ( $tags ) : ?>
						<li><span><?php esc_html_e( 'Tags', 'moon-core' ); ?></span><?php echo wp_kses_post( $tags ); ?></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}
}
