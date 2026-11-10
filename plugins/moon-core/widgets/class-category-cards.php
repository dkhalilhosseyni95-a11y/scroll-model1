<?php
/**
 * Editorial category cards widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Women / men (or any) category tiles with a full-bleed image.
 */
class Moon_Category_Cards extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-category-cards';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'کارت‌های دسته‌بندی MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_cards',
			array(
				'label' => __( 'کارت‌ها', 'moon-core' ),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'image',
			array(
				'label' => __( 'تصویر', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'kicker',
			array(
				'label'   => __( 'برچسب کوچک', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'کالکشن', 'moon-core' ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'زنانه', 'moon-core' ),
			)
		);

		$repeater->add_control(
			'subtitle',
			array(
				'label' => __( 'توضیح کوتاه', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'لینک', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => array( 'url' => '#' ),
				'placeholder' => home_url( '/' ),
			)
		);

		$repeater->add_control(
			'cta_text',
			array(
				'label'   => __( 'متن دکمه', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'مشاهده کالکشن', 'moon-core' ),
			)
		);

		$this->add_control(
			'cards',
			array(
				'label'       => __( 'کارت‌های دسته‌بندی', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'kicker' => __( 'کالکشن', 'moon-core' ),
						'title'  => __( 'زنانه', 'moon-core' ),
					),
					array(
						'kicker' => __( 'کالکشن', 'moon-core' ),
						'title'  => __( 'مردانه', 'moon-core' ),
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
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default'   => '2',
				'selectors' => array(
					'{{WRAPPER}} .moon-categories__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'فاصله بین کارت‌ها', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array( 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .moon-categories__grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'ratio',
			array(
				'label'     => __( 'نسبت تصویر', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'3 / 4'  => '3:4',
					'4 / 5'  => '4:5',
					'1 / 1'  => '1:1',
					'16 / 9' => '16:9',
				),
				'default'   => '3 / 4',
				'selectors' => array( '{{WRAPPER}} .moon-categories__media' => 'aspect-ratio: {{VALUE}};' ),
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
			'text_color',
			array(
				'label'     => __( 'رنگ متن', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#FAF9F6',
				'selectors' => array( '{{WRAPPER}} .moon-category-card' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'overlay',
			array(
				'label'     => __( 'شدت Overlay', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'   => array( 'size' => 38 ),
				'selectors' => array( '{{WRAPPER}} .moon-category-card__overlay' => 'opacity: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_typography( 'title_typography', __( 'عنوان', 'moon-core' ), '{{WRAPPER}} .moon-category-card__title', 'title' );
		$this->add_box( '{{WRAPPER}} .moon-category-card' );
		$this->add_motion( '{{WRAPPER}} .moon-categories__grid' );

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( empty( $s['cards'] ) ) {
			return;
		}
		?>
		<section class="moon-categories">
			<div class="moon-categories__grid">
				<?php foreach ( $s['cards'] as $card ) : ?>
					<?php
					$url  = isset( $card['link']['url'] ) ? $card['link']['url'] : '';
					$attr = '';
					if ( ! empty( $card['link']['is_external'] ) ) {
						$attr .= ' target="_blank"';
					}
					if ( ! empty( $card['link']['nofollow'] ) ) {
						$attr .= ' rel="nofollow"';
					}
					$image_id = isset( $card['image']['id'] ) ? (int) $card['image']['id'] : 0;
					?>
					<article class="moon-category-card">
						<div class="moon-categories__media">
							<?php
							if ( $image_id ) {
								echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'moon-category-card__img', 'loading' => 'lazy' ) );
							}
							?>
							<span class="moon-category-card__overlay" aria-hidden="true"></span>
						</div>
						<div class="moon-category-card__body">
							<?php if ( ! empty( $card['kicker'] ) ) : ?>
								<span class="moon-eyebrow"><?php echo esc_html( $card['kicker'] ); ?></span>
							<?php endif; ?>
							<h2 class="moon-category-card__title"><?php echo esc_html( $card['title'] ); ?></h2>
							<?php if ( ! empty( $card['subtitle'] ) ) : ?>
								<p class="moon-category-card__subtitle"><?php echo esc_html( $card['subtitle'] ); ?></p>
							<?php endif; ?>
							<?php if ( $url ) : ?>
								<a class="moon-btn moon-btn--outline" href="<?php echo esc_url( $url ); ?>"<?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									<?php echo esc_html( $card['cta_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
						<?php if ( $url ) : ?>
							<a class="moon-category-card__link" href="<?php echo esc_url( $url ); ?>"<?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<span class="screen-reader-text"><?php echo esc_html( $card['title'] ); ?></span>
							</a>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
