<?php
/**
 * Cinematic hero widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Full-bleed MP4 hero with a poster fallback.
 */
class Moon_Cinematic_Hero extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-cinematic-hero';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'هیرو سینمایی MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-video-camera';
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_media',
			array(
				'label' => __( 'رسانه', 'moon-core' ),
			)
		);

		$this->add_control(
			'video_url',
			array(
				'label'       => __( 'ویدئو MP4', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'description' => __( 'اگر خالی بماند فقط تصویر Poster نمایش داده می‌شود.', 'moon-core' ),
			)
		);

		$this->add_control(
			'poster',
			array(
				'label' => __( 'تصویر Poster', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'   => __( 'پخش خودکار (بی‌صدا)', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'disable_mobile_video',
			array(
				'label'   => __( 'غیرفعال کردن ویدئو در موبایل', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'overlay',
			array(
				'label'     => __( 'شفافیت Overlay', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'   => array( 'size' => 46 ),
				'selectors' => array(
					'{{WRAPPER}} .moon-hero__overlay' => 'opacity: calc({{SIZE}} / 100);',
				),
			)
		);

		$this->add_responsive_control(
			'hero_height',
			array(
				'label'      => __( 'ارتفاع هیرو', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'vh', 'px' ),
				'range'      => array(
					'vh' => array(
						'min' => 40,
						'max' => 100,
					),
					'px' => array(
						'min' => 320,
						'max' => 1200,
					),
				),
				'default'    => array(
					'unit' => 'vh',
					'size' => 92,
				),
				'selectors'  => array(
					'{{WRAPPER}} .moon-hero' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'محتوا', 'moon-core' ),
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'متن بالای عنوان', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'کالکشن پاییز / زمستان', 'moon-core' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان اصلی', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'شب را با MOON بپوش', 'moon-core' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'توضیح کوتاه', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'برش‌های ساده، پارچه‌های سنگین و سکوتی که حرف می‌زند.', 'moon-core' ),
			)
		);

		$this->add_control(
			'cta_one_text',
			array(
				'label'   => __( 'متن CTA اول', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'کالکشن زنانه', 'moon-core' ),
			)
		);

		$this->add_link( 'cta_one_url', __( 'لینک CTA اول', 'moon-core' ), '#women' );

		$this->add_control(
			'cta_two_text',
			array(
				'label'   => __( 'متن CTA دوم', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'کالکشن مردانه', 'moon-core' ),
			)
		);

		$this->add_link( 'cta_two_url', __( 'لینک CTA دوم', 'moon-core' ), '#men' );

		$this->add_responsive_control(
			'content_position',
			array(
				'label'     => __( 'موقعیت افقی محتوا', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array(
						'title' => __( 'شروع', 'moon-core' ),
						'icon'  => is_rtl() ? 'eicon-text-align-right' : 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'وسط', 'moon-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'پایان', 'moon-core' ),
						'icon'  => is_rtl() ? 'eicon-text-align-left' : 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .moon-hero__content' => 'justify-items: {{VALUE}}; text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_valign',
			array(
				'label'     => __( 'موقعیت عمودی محتوا', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'بالا', 'moon-core' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'وسط', 'moon-core' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => __( 'پایین', 'moon-core' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .moon-hero' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'scroll_hint',
			array(
				'label'   => __( 'نمایش نشانگر اسکرول', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
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
			'overlay_color',
			array(
				'label'     => __( 'رنگ Overlay', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#11110F',
				'selectors' => array( '{{WRAPPER}} .moon-hero__overlay' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#FAF9F6',
				'selectors' => array( '{{WRAPPER}} .moon-hero__title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'رنگ متن', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(250,249,246,0.86)',
				'selectors' => array( '{{WRAPPER}} .moon-hero__subtitle, {{WRAPPER}} .moon-hero__eyebrow' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_typography( 'title_typography', __( 'عنوان', 'moon-core' ), '{{WRAPPER}} .moon-hero__title', 'title' );
		$this->add_typography( 'subtitle_typography', __( 'توضیح', 'moon-core' ), '{{WRAPPER}} .moon-hero__subtitle', 'subtitle' );

		$this->add_responsive_control(
			'content_width',
			array(
				'label'      => __( 'عرض محتوا', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 280,
						'max' => 1200,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 760,
				),
				'selectors'  => array( '{{WRAPPER}} .moon-hero__content' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_spacing( '{{WRAPPER}} .moon-hero__content', __( 'فاصله محتوا', 'moon-core' ) );
		$this->add_motion( '{{WRAPPER}} .moon-hero__content' );

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$video = isset( $s['video_url']['url'] ) ? $s['video_url']['url'] : '';
		$poster = isset( $s['poster']['url'] ) ? $s['poster']['url'] : '';

		if ( ! $poster && (int) moon_core_setting( 'hero_poster_id', 0 ) ) {
			$poster = wp_get_attachment_image_url( (int) moon_core_setting( 'hero_poster_id', 0 ), 'full' );
		}
		if ( ! $video && moon_core_setting( 'hero_video', '' ) ) {
			$video = (string) moon_core_setting( 'hero_video', '' );
		}

		$one = $this->link_data( 'cta_one_url' );
		$two = $this->link_data( 'cta_two_url' );

		$motion  = isset( $s['motion'] ) ? $s['motion'] : 'fade-up';
		$classes = array( 'moon-hero', 'moon-motion--' . $motion );
		?>
		<section class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-moon-hero
			data-mobile-video="<?php echo 'yes' === $s['disable_mobile_video'] ? 'off' : 'on'; ?>">
			<div class="moon-hero__media">
				<?php if ( $video ) : ?>
					<video class="moon-hero__video" poster="<?php echo esc_url( $poster ); ?>"
						preload="metadata" muted loop playsinline
						<?php echo 'yes' === $s['autoplay'] ? 'autoplay' : ''; ?>
						aria-hidden="true" tabindex="-1">
						<source src="<?php echo esc_url( $video ); ?>" type="video/mp4" />
					</video>
				<?php elseif ( $poster ) : ?>
					<img class="moon-hero__poster" src="<?php echo esc_url( $poster ); ?>" alt=""
						loading="eager" decoding="async" />
				<?php endif; ?>
			</div>
			<div class="moon-hero__overlay" aria-hidden="true"></div>

			<div class="moon-hero__content">
				<?php if ( ! empty( $s['eyebrow'] ) ) : ?>
					<p class="moon-hero__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
				<?php endif; ?>

				<h1 class="moon-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>

				<?php if ( ! empty( $s['subtitle'] ) ) : ?>
					<p class="moon-hero__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
				<?php endif; ?>

				<div class="moon-hero__actions">
					<?php if ( ! empty( $s['cta_one_text'] ) ) : ?>
						<a class="moon-btn moon-btn--solid" href="<?php echo esc_url( $one['href'] ? $one['href'] : '#' ); ?>"<?php echo $one['attrs']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo esc_html( $s['cta_one_text'] ); ?>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $s['cta_two_text'] ) ) : ?>
						<a class="moon-btn moon-btn--outline" href="<?php echo esc_url( $two['href'] ? $two['href'] : '#' ); ?>"<?php echo $two['attrs']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo esc_html( $s['cta_two_text'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( 'yes' === $s['scroll_hint'] ) : ?>
				<span class="moon-hero__scroll" aria-hidden="true"><span></span></span>
			<?php endif; ?>
		</section>
		<?php
	}
}
