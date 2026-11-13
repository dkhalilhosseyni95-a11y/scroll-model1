<?php
/**
 * MOON Product Gallery — the WooCommerce product gallery as a MOON stage.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Product;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Product_Gallery extends Widget_Base {

	use Product;

	protected function slug(): string {
		return 'product-gallery';
	}

	public function get_title(): string {
		return __( 'MOON Product Gallery', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-product-images';
	}

	public function get_keywords(): array {
		return array( 'product', 'gallery', 'images', 'woocommerce', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_product', array( 'label' => __( 'Product', 'moon-core' ) ) );
		$this->product_source_controls( 'current' );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Gallery', 'moon-core' ) ) );

		$this->add_control(
			'thumbs_position',
			array(
				'label'   => __( 'Thumbnails', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'below',
				'options' => array(
					'below' => __( 'Below the main image', 'moon-core' ),
					'start' => __( 'Left column', 'moon-core' ),
					'end'   => __( 'Right column', 'moon-core' ),
				),
			)
		);

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Main image ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.5, 'max' => 1.6, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 0.8 ),
				'selectors'  => array( '{{WRAPPER}} .moon-pg__stage' => 'aspect-ratio: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .moon-pg__stage, {{WRAPPER}} .moon-pg__thumb' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'show_badges',
			array(
				'label'        => __( 'Show sale / sold out badge', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->render_notice( __( 'Activate WooCommerce to show a product gallery.', 'moon-core' ) );
			return;
		}

		$product = $this->resolve_product( $settings );

		if ( ! $product ) {
			$this->render_notice( __( 'Select a product, or place this widget in a single product template.', 'moon-core' ) );
			return;
		}

		$main_id = (int) $product->get_image_id();
		$gallery = array_values( array_filter( array_map( 'absint', (array) $product->get_gallery_image_ids() ) ) );
		$ids     = array_values( array_unique( array_filter( array_merge( array( $main_id ), $gallery ) ) ) );
		$position = in_array( $settings['thumbs_position'] ?? 'below', array( 'below', 'start', 'end' ), true ) ? $settings['thumbs_position'] : 'below';
		?>
		<div class="moon-widget moon-pg moon-pg--thumbs-<?php echo esc_attr( $position ); ?>" data-moon-product-gallery>
			<div class="moon-pg__stage">
				<?php if ( 'yes' === ( $settings['show_badges'] ?? 'yes' ) ) : ?>
					<div class="moon-pg__badges">
						<?php if ( ! $product->is_in_stock() ) : ?>
							<span class="moon-badge moon-badge--muted"><?php esc_html_e( 'Sold out', 'moon-core' ); ?></span>
						<?php elseif ( $product->is_on_sale() ) : ?>
							<span class="moon-badge"><?php esc_html_e( 'Sale', 'moon-core' ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $main_id ) : ?>
					<?php echo wp_kses_post( wp_get_attachment_image( $main_id, 'moon-product', false, array( 'class' => 'moon-pg__image is-active', 'data-moon-pg-main' => '1', 'loading' => 'eager' ) ) ); ?>
				<?php else : ?>
					<?php echo wp_kses_post( $product->get_image( 'moon-product', array( 'class' => 'moon-pg__image is-active', 'data-moon-pg-main' => '1' ) ) ); ?>
				<?php endif; ?>

				<?php foreach ( $ids as $index => $id ) : ?>
					<?php if ( (int) $id === $main_id && 0 === $index ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<span class="moon-pg__slide" data-moon-pg-slide="<?php echo esc_attr( (string) $index ); ?>" aria-hidden="true">
						<?php echo wp_kses_post( wp_get_attachment_image( $id, 'moon-product', false, array( 'loading' => 'lazy' ) ) ); ?>
					</span>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $ids ) > 1 ) : ?>
				<div class="moon-pg__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Product images', 'moon-core' ); ?>">
					<?php foreach ( $ids as $index => $id ) : ?>
						<button
							type="button"
							class="moon-pg__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-moon-pg-thumb="<?php echo esc_attr( (string) $id ); ?>"
							data-moon-index="<?php echo esc_attr( (string) $index ); ?>"
							role="tab"
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %d: image number */ __( 'Image %d', 'moon-core' ), $index + 1 ) ); ?>"
						>
							<?php echo wp_kses_post( wp_get_attachment_image( $id, 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => '' ) ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
