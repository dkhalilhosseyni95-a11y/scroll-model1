<?php
/**
 * Brand story widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Two-column editorial brand introduction.
 */
class Moon_Brand_Story extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-brand-story';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'داستان برند MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-image-box';
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
				'label' => __( 'تصویر', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'label',
			array(
				'label'   => __( 'برچسب کوچک', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'درباره MOON', 'moon-core' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'سکوت، بهترین جنس لباس است', 'moon-core' ),
			)
		);

		$this->add_control(
			'body',
			array(
				'label'   => __( 'متن', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( '<p>MOON از یک کارگاه کوچک شروع شد؛ با این باور که لباس لوکس نباید پرصدا باشد. خط‌های ساده، پارچه‌های دست‌چین و دوخت آرام، امضای ما هستند.</p>', 'moon-core' ),
			)
		);

		$this->add_control(
			'signature',
			array(
				'label'   => __( 'امضا / نقل‌قول', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'MOON ATELIER — EST. 2019', 'moon-core' ),
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'   => __( 'متن دکمه', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'درباره ما', 'moon-core' ),
			)
		);

		$this->add_link( 'cta_url', __( 'لینک دکمه', 'moon-core' ), '#' );

		$this->add_control(
			'image_side',
			array(
				'label'     => __( 'سمت تصویر', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'start',
				'options'   => array(
					'start' => __( 'شروع (راست در فارسی)', 'moon-core' ),
					'end'   => __( 'پایان (چپ در فارسی)', 'moon-core' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .moon-story' => '--moon-story-image-order: {{VALUE}};',
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
				'selectors' => array( '{{WRAPPER}} .moon-story__title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'رنگ متن', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-story__body' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_typography( 'title_typography', __( 'عنوان', 'moon-core' ), '{{WRAPPER}} .moon-story__title', 'title' );
		$this->add_typography( 'body_typography', __( 'متن', 'moon-core' ), '{{WRAPPER}} .moon-story__body', 'body' );

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => __( 'ارتفاع تصویر', 'moon-core' ),
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
					'size' => 560,
				),
				'selectors'  => array( '{{WRAPPER}} .moon-story__media' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_box( '{{WRAPPER}} .moon-story__media' );
		$this->add_spacing( '{{WRAPPER}} .moon-story__body', __( 'فاصله متن', 'moon-core' ) );
		$this->add_motion( '{{WRAPPER}} .moon-story' );

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$cta  = $this->link_data( 'cta_url' );
		$href = isset( $s['image']['url'] ) ? $s['image']['url'] : '';
		?>
		<section class="moon-story">
			<?php if ( $href ) : ?>
				<figure class="moon-story__media">
					<img src="<?php echo esc_url( $href ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" loading="lazy" decoding="async" />
				</figure>
			<?php endif; ?>

			<div class="moon-story__body">
				<?php if ( ! empty( $s['label'] ) ) : ?>
					<span class="moon-eyebrow"><?php echo esc_html( $s['label'] ); ?></span>
				<?php endif; ?>

				<h2 class="moon-story__title"><?php echo esc_html( $s['title'] ); ?></h2>

				<div class="moon-story__body-text"><?php echo wp_kses_post( $s['body'] ); ?></div>

				<?php if ( ! empty( $s['signature'] ) ) : ?>
					<p class="moon-story__signature"><?php echo esc_html( $s['signature'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $s['cta_text'] ) && ! empty( $cta['href'] ) ) : ?>
					<a class="moon-link-arrow" href="<?php echo esc_url( $cta['href'] ); ?>"<?php echo $cta['attrs']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html( $s['cta_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
