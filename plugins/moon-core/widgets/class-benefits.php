<?php
/**
 * Shopping benefits widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * A row of service promises (shipping, returns, support…).
 */
class Moon_Benefits extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-benefits';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'مزایای خرید MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-icon-box';
	}

	/**
	 * Available icons.
	 *
	 * @return array
	 */
	private function icons() {
		return array(
			'truck'   => __( 'ارسال', 'moon-core' ),
			'shield'  => __( 'گارانتی', 'moon-core' ),
			'refresh' => __( 'مرجوعی', 'moon-core' ),
			'gift'    => __( 'هدیه', 'moon-core' ),
			'chat'    => __( 'پشتیبانی', 'moon-core' ),
			'star'    => __( 'ویژه', 'moon-core' ),
		);
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_items',
			array(
				'label' => __( 'موارد', 'moon-core' ),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'آیکون', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->icons(),
				'default' => 'truck',
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'ارسال رایگان', 'moon-core' ),
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label' => __( 'توضیح', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'مزایا', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon'  => 'truck',
						'title' => __( 'ارسال سریع', 'moon-core' ),
						'text'  => __( 'تهران ۲۴ ساعت، سایر شهرها ۴۸ ساعت' ),
					),
					array(
						'icon'  => 'refresh',
						'title' => __( '۷ روز مرجوعی', 'moon-core' ),
						'text'  => __( 'بدون پرسش، بدون هزینه' ),
					),
					array(
						'icon'  => 'shield',
						'title' => __( 'اصالت پارچه', 'moon-core' ),
						'text'  => __( 'تضمین کیفیت دوخت و جنس' ),
					),
					array(
						'icon'  => 'chat',
						'title' => __( 'مشاوره استایل', 'moon-core' ),
						'text'  => __( 'راهنمایی سایز توسط تیم MOON' ),
					),
				),
				'title_field' => '{{{ title }}}',
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
					'{{WRAPPER}} .moon-benefits__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
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
			'icon_color',
			array(
				'label'     => __( 'رنگ آیکون', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#B99A62',
				'selectors' => array( '{{WRAPPER}} .moon-benefit__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'رنگ متن', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-benefit' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_typography( 'title_typography', __( 'عنوان', 'moon-core' ), '{{WRAPPER}} .moon-benefit__title', 'title' );

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'فاصله', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array( 'size' => 28 ),
				'selectors'  => array( '{{WRAPPER}} .moon-benefits__grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_box( '{{WRAPPER}} .moon-benefit' );
		$this->add_motion( '{{WRAPPER}} .moon-benefits__grid' );

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( empty( $s['items'] ) ) {
			return;
		}
		?>
		<section class="moon-benefits">
			<div class="moon-benefits__grid">
				<?php foreach ( $s['items'] as $item ) : ?>
					<article class="moon-benefit">
						<span class="moon-benefit__icon"><?php Moon_Core_Header::icon( $item['icon'] ); ?></span>
						<h3 class="moon-benefit__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p class="moon-benefit__text"><?php echo esc_html( $item['text'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
