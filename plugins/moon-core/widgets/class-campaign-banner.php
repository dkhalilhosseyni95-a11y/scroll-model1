<?php
/**
 * Campaign banner widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Editorial campaign banner with an optional real countdown.
 */
class Moon_Campaign_Banner extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-campaign-banner';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'بنر کمپین MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-banner';
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'محتوا', 'moon-core' ),
			)
		);

		$this->add_control(
			'image',
			array(
				'label' => __( 'تصویر پس‌زمینه', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'kicker',
			array(
				'label'   => __( 'برچسب', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'کمپین شب‌های ماه', 'moon-core' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'تا ۳۰٪ تخفیف روی کالکشن شب', 'moon-core' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'توضیح', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'انتخاب محدود از پارچه‌های پشمی و ابریشم مات، فقط تا پایان فصل.', 'moon-core' ),
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'   => __( 'متن دکمه', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'خرید کالکشن', 'moon-core' ),
			)
		);

		$this->add_link( 'cta_url', __( 'لینک دکمه', 'moon-core' ), '#' );

		$this->add_control(
			'show_countdown',
			array(
				'label'   => __( 'شمارش معکوس', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'countdown_end',
			array(
				'label'       => __( 'پایان کمپین', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'condition'   => array( 'show_countdown' => 'yes' ),
				'description' => __( 'تاریخ و ساعت پایان تخفیف.', 'moon-core' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'     => __( 'چیدمان', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'split',
				'options'   => array(
					'split'    => __( 'تصویر + متن', 'moon-core' ),
					'overlay'  => __( 'متن روی تصویر', 'moon-core' ),
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
			'bg',
			array(
				'label'     => __( 'رنگ پس‌زمینه', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1B1A18',
				'selectors' => array( '{{WRAPPER}} .moon-campaign' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'رنگ متن', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#F3EFE6',
				'selectors' => array( '{{WRAPPER}} .moon-campaign__body' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_typography( 'title_typography', __( 'عنوان', 'moon-core' ), '{{WRAPPER}} .moon-campaign__title', 'title' );
		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => __( 'حداقل ارتفاع', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 240,
						'max' => 900,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 480,
				),
				'selectors'  => array( '{{WRAPPER}} .moon-campaign' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_box( '{{WRAPPER}} .moon-campaign' );
		$this->add_motion( '{{WRAPPER}} .moon-campaign' );

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s       = $this->get_settings_for_display();
		$cta     = $this->link_data( 'cta_url' );
		$image   = isset( $s['image']['url'] ) ? $s['image']['url'] : '';
		$end     = isset( $s['countdown_end'] ) ? $s['countdown_end'] : '';
		$classes = 'moon-campaign moon-campaign--' . ( 'overlay' === $s['layout'] ? 'overlay' : 'split' );
		?>
		<section class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $image && 'split' === $s['layout'] ) : ?>
				<div class="moon-campaign__media">
					<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async" />
				</div>
			<?php elseif ( $image ) : ?>
				<img class="moon-campaign__bg" src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async" />
				<span class="moon-campaign__scrim" aria-hidden="true"></span>
			<?php endif; ?>

			<div class="moon-campaign__body">
				<?php if ( ! empty( $s['kicker'] ) ) : ?>
					<span class="moon-eyebrow"><?php echo esc_html( $s['kicker'] ); ?></span>
				<?php endif; ?>

				<h2 class="moon-campaign__title"><?php echo esc_html( $s['title'] ); ?></h2>

				<?php if ( ! empty( $s['text'] ) ) : ?>
					<p class="moon-campaign__text"><?php echo esc_html( $s['text'] ); ?></p>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_countdown'] && $end ) : ?>
					<?php $timestamp = strtotime( $end ); ?>
					<?php if ( $timestamp ) : ?>
						<div class="moon-countdown" data-moon-countdown="<?php echo esc_attr( (string) $timestamp ); ?>"
							aria-label="<?php esc_attr_e( 'زمان باقی‌مانده تا پایان کمپین', 'moon-core' ); ?>">
							<span class="moon-countdown__cell"><b data-days>00</b><i><?php esc_html_e( 'روز', 'moon-core' ); ?></i></span>
							<span class="moon-countdown__cell"><b data-hours>00</b><i><?php esc_html_e( 'ساعت', 'moon-core' ); ?></i></span>
							<span class="moon-countdown__cell"><b data-minutes>00</b><i><?php esc_html_e( 'دقیقه', 'moon-core' ); ?></i></span>
							<span class="moon-countdown__cell"><b data-seconds>00</b><i><?php esc_html_e( 'ثانیه', 'moon-core' ); ?></i></span>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( ! empty( $s['cta_text'] ) ) : ?>
					<a class="moon-btn moon-btn--gold" href="<?php echo esc_url( $cta['href'] ? $cta['href'] : '#' ); ?>"<?php echo $cta['attrs']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html( $s['cta_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
