<?php
/**
 * Product grid widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Real WooCommerce products rendered with the store's product templates.
 */
class Moon_Product_Grid extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-product-grid';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'گرید محصولات MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * WooCommerce categories for the select control.
	 *
	 * @return array
	 */
	private function category_options() {
		$options = array( '' => __( 'همه دسته‌ها', 'moon-core' ) );

		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}

		return $options;
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_header',
			array(
				'label' => __( 'عنوان بخش', 'moon-core' ),
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'   => __( 'عنوان', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'جدیدترین‌ها', 'moon-core' ),
			)
		);

		$this->add_control(
			'subheading',
			array(
				'label' => __( 'توضیح', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$this->add_link( 'view_all', __( 'لینک «مشاهده همه»', 'moon-core' ), '' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			array(
				'label' => __( 'انتخاب محصولات', 'moon-core' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'recent',
				'options' => array(
					'recent'    => __( 'جدیدترین', 'moon-core' ),
					'featured'  => __( 'منتخب', 'moon-core' ),
					'sale'      => __( 'تخفیف‌دار', 'moon-core' ),
					'best'      => __( 'پرفروش‌ترین', 'moon-core' ),
					'top_rated' => __( 'بیشترین امتیاز', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'category',
			array(
				'label'   => __( 'دسته‌بندی', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->category_options(),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'تعداد محصول', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 24,
				'default' => 8,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'تعداد ستون', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default'   => '4',
				'selectors' => array(
					'{{WRAPPER}} .moon-products' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'استایل', 'moon-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'رنگ عنوان', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-section__title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_typography( 'heading_typography', __( 'عنوان', 'moon-core' ), '{{WRAPPER}} .moon-section__title', 'heading' );
		$this->add_typography( 'price_typography', __( 'قیمت', 'moon-core' ), '{{WRAPPER}} .moon-products .price', 'price' );

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'فاصله داخلی کارت', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .moon-products li.product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_box( '{{WRAPPER}} .moon-products li.product' );
		$this->add_motion( '{{WRAPPER}} .moon-products' );

		$this->end_controls_section();
	}

	/**
	 * Build the [products] shortcode string from the widget settings.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	private function shortcode( $s ) {
		$attributes = array(
			'limit'   => (int) $s['limit'],
			'columns' => (int) $s['columns'],
			'class'   => 'moon-products',
		);

		if ( ! empty( $s['category'] ) ) {
			$attributes['category'] = $s['category'];
		}

		switch ( $s['source'] ) {
			case 'featured':
				$attributes['visibility'] = 'featured';
				break;
			case 'sale':
				$attributes['on_sale'] = 'true';
				break;
			case 'best':
				$attributes['best_selling'] = 'true';
				break;
			case 'top_rated':
				$attributes['orderby'] = 'rating';
				break;
			default:
				$attributes['orderby'] = 'date';
				break;
		}

		$pairs = array();
		foreach ( $attributes as $key => $value ) {
			$pairs[] = sprintf( '%s="%s"', $key, esc_attr( (string) $value ) );
		}

		return '[products ' . implode( ' ', $pairs ) . ']';
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$all = $this->link_data( 'view_all' );
		?>
		<section class="moon-section moon-section--products">
			<header class="moon-section__head">
				<div>
					<?php if ( ! empty( $s['heading'] ) ) : ?>
						<h2 class="moon-section__title"><?php echo esc_html( $s['heading'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $s['subheading'] ) ) : ?>
						<p class="moon-section__sub"><?php echo esc_html( $s['subheading'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $all['href'] ) ) : ?>
					<a class="moon-link-arrow" href="<?php echo esc_url( $all['href'] ); ?>"<?php echo $all['attrs']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php esc_html_e( 'مشاهده همه', 'moon-core' ); ?>
					</a>
				<?php endif; ?>
			</header>

			<?php
			if ( ! class_exists( 'WooCommerce' ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					echo '<p class="moon-notice">' . esc_html__( 'برای نمایش محصولات، ووکامرس را فعال کنید.', 'moon-core' ) . '</p>';
				}
				return;
			}

			echo do_shortcode( $this->shortcode( $s ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce shortcode output.
			?>
		</section>
		<?php
	}
}
