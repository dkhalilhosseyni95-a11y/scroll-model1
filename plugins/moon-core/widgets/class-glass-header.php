<?php
/**
 * Glass floating header widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the same capsule header used site-wide, with per-instance controls.
 */
class Moon_Glass_Header extends Moon_Core_Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'moon-glass-header';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'هدر شیشه‌ای MOON', 'moon-core' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-header';
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
			'brand',
			array(
				'label'   => __( 'نام برند', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => (string) moon_core_setting( 'brand_name', 'MOON' ),
			)
		);

		$this->add_control(
			'logo',
			array(
				'label' => __( 'لوگو (حالت روز)', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'logo_dark',
			array(
				'label' => __( 'لوگو (حالت شب)', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$menus = array( '' => __( 'دسته‌بندی‌های محصول', 'moon-core' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$menus[ $menu->slug ] = $menu->name;
		}

		$this->add_control(
			'menu',
			array(
				'label'   => __( 'منو', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $menus,
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'   => __( 'جست‌وجو', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_account',
			array(
				'label'   => __( 'حساب کاربری', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_wishlist',
			array(
				'label'   => __( 'علاقه‌مندی‌ها', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_cart',
			array(
				'label'   => __( 'سبد خرید', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_toggle',
			array(
				'label'   => __( 'دکمه روشن/تاریک', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_cta',
			array(
				'label' => __( 'دکمه CTA', 'moon-core' ),
				'type'  => \Elementor\Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'     => __( 'متن CTA', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_link( 'cta_url', __( 'لینک CTA', 'moon-core' ), '#new' );

		$this->add_control(
			'sticky',
			array(
				'label'   => __( 'چسبیدن هنگام اسکرول', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'shrink',
			array(
				'label'   => __( 'جمع‌شدن هنگام اسکرول', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_glass',
			array(
				'label' => __( 'شیشه و شکل', 'moon-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'glass_blur',
			array(
				'label'     => __( 'شدت Blur (px)', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'   => array( 'size' => 18 ),
				'selectors' => array(
					'{{WRAPPER}} .moon-header' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				),
			)
		);

		$this->add_control(
			'glass_light',
			array(
				'label'     => __( 'رنگ شیشه — روز', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(250,249,246,0.72)',
				'selectors' => array(
					'{{WRAPPER}} .moon-header' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'glass_dark',
			array(
				'label'     => __( 'رنگ شیشه — شب', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(17,17,15,0.62)',
				'selectors' => array(
					'html[data-theme="dark"] {{WRAPPER}} .moon-header' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'header_border',
				'label'    => __( 'حاشیه', 'moon-core' ),
				'selector' => '{{WRAPPER}} .moon-header',
			)
		);

		$this->add_responsive_control(
			'header_radius',
			array(
				'label'      => __( 'گردی گوشه‌ها', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => '999',
					'right'    => '999',
					'bottom'   => '999',
					'left'     => '999',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .moon-header' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'header_shadow',
				'label'    => __( 'سایه', 'moon-core' ),
				'selector' => '{{WRAPPER}} .moon-header',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => __( 'تایپوگرافی', 'moon-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_typography( 'brand_typography', __( 'نام برند', 'moon-core' ), '{{WRAPPER}} .moon-header__wordmark', 'brand' );
		$this->add_typography( 'menu_typography', __( 'منو', 'moon-core' ), '{{WRAPPER}} .moon-menu__link', 'menu' );

		$this->add_control(
			'menu_color',
			array(
				'label'     => __( 'رنگ منو', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-menu__link' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the header.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$cta = $this->link_data( 'cta_url' );

		Moon_Core_Header::render(
			array(
				'brand'         => isset( $s['brand'] ) ? $s['brand'] : '',
				'logo_id'       => isset( $s['logo']['id'] ) ? (int) $s['logo']['id'] : 0,
				'logo_dark_id'  => isset( $s['logo_dark']['id'] ) ? (int) $s['logo_dark']['id'] : 0,
				'menu'          => isset( $s['menu'] ) ? $s['menu'] : '',
				'show_search'   => 'yes' === $s['show_search'] ? 1 : 0,
				'show_account'  => 'yes' === $s['show_account'] ? 1 : 0,
				'show_wishlist' => 'yes' === $s['show_wishlist'] ? 1 : 0,
				'show_cart'     => 'yes' === $s['show_cart'] ? 1 : 0,
				'show_toggle'   => 'yes' === $s['show_toggle'] ? 1 : 0,
				'show_cta'      => 'yes' === $s['show_cta'] ? 1 : 0,
				'cta_text'      => isset( $s['cta_text'] ) ? $s['cta_text'] : '',
				'cta_url'       => $cta['href'],
				'sticky'        => 'yes' === $s['sticky'] ? 1 : 0,
				'shrink'        => 'yes' === $s['shrink'] ? 1 : 0,
				'extra_class'   => 'moon-header-wrap--widget',
			)
		);
	}
}
