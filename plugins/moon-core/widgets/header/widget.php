<?php
/**
 * MOON Header widget — the floating capsule header.
 *
 * The markup is shared with the theme chrome (MoonCore\Site_Chrome) so the site
 * header looks identical whether it is configured here or in Moon Core settings.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use MoonCore\Site_Chrome;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Header extends Widget_Base {

	protected function slug(): string {
		return 'header';
	}

	public function get_title(): string {
		return __( 'MOON Header', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-header';
	}

	public function get_keywords(): array {
		return array( 'header', 'menu', 'navigation', 'capsule', 'moon' );
	}

	/**
	 * Registered menu locations for the location select.
	 *
	 * @return array<string,string>
	 */
	private function menu_options(): array {
		$options = array( 'primary' => __( 'Primary menu', 'moon-core' ) );

		foreach ( get_registered_nav_menus() as $location => $label ) {
			$options[ $location ] = $label;
		}

		return $options;
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_brand', array( 'label' => __( 'Brand', 'moon-core' ) ) );

		$this->add_control(
			'brand_name',
			array(
				'label'       => __( 'Brand name', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'MOON',
				'label_block' => true,
			)
		);

		$this->add_control(
			'tagline',
			array(
				'label'       => __( 'Tagline', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Modern wardrobe', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'logo',
			array(
				'label'   => __( 'Logo (optional)', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'announcement',
			array(
				'label'       => __( 'Announcement line', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Complimentary shipping on orders over €150', 'moon-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'menu_location',
			array(
				'label'   => __( 'Menu location', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => $this->menu_options(),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_elements', array( 'label' => __( 'Header elements', 'moon-core' ) ) );

		foreach (
			array(
				'search'   => __( 'Search', 'moon-core' ),
				'account'  => __( 'Account', 'moon-core' ),
				'wishlist' => __( 'Wishlist (when a wishlist plugin is active)', 'moon-core' ),
				'cart'     => __( 'Cart with mini cart drawer', 'moon-core' ),
				'scheme'   => __( 'Dark / light switch', 'moon-core' ),
			) as $key => $label
		) {
			$this->add_control(
				'show_' . $key,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'label_off'    => __( 'Hide', 'moon-core' ),
					'label_on'     => __( 'Show', 'moon-core' ),
					'return_value' => 'yes',
				)
			);
		}

		$this->add_control(
			'cta_text',
			array(
				'label'       => __( 'Button text', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'New in', 'moon-core' ),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'cta_link',
			array(
				'label'     => __( 'Button link', 'moon-core' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'cta_text!' => '' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_behaviour', array( 'label' => __( 'Behaviour', 'moon-core' ) ) );

		$this->add_control(
			'sticky',
			array(
				'label'        => __( 'Stick to the top', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'hide_on_scroll',
			array(
				'label'        => __( 'Hide when scrolling down', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		/* Style ---------------------------------------------------------- */

		$this->start_controls_section(
			'style_shell',
			array(
				'label' => __( 'Capsule shell', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'shell_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-header__shell' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'shell_blur',
			array(
				'label'      => __( 'Backdrop blur (px)', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'default'    => array( 'size' => 20 ),
				'selectors'  => array(
					'{{WRAPPER}} .moon-header__shell' => '-webkit-backdrop-filter: blur({{SIZE}}px); backdrop-filter: blur({{SIZE}}px);',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'shell_border',
				'selector' => '{{WRAPPER}} .moon-header__shell',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'shell_shadow',
				'selector' => '{{WRAPPER}} .moon-header__shell',
			)
		);

		$this->add_control(
			'shell_radius',
			array(
				'label'      => __( 'Radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .moon-header__shell' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'shell_padding',
			array(
				'label'      => __( 'Inner padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .moon-header__shell' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'brand', __( 'Brand', 'moon-core' ), '.moon-brand__word' );
		$this->moon_text_style( 'tagline', __( 'Tagline', 'moon-core' ), '.moon-brand__tag' );
		$this->moon_text_style( 'nav', __( 'Navigation', 'moon-core' ), '.moon-nav__list > li > a' );
		$this->moon_text_style( 'announcement', __( 'Announcement', 'moon-core' ), '.moon-announcement' );
		$this->moon_button_style( 'cta', __( 'Header button', 'moon-core' ), '.moon-header__cta' );

		$this->start_controls_section(
			'style_icons',
			array(
				'label' => __( 'Icon buttons', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-header__actions .moon-icon-btn' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_hover_bg',
			array(
				'label'     => __( 'Hover background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-header__actions .moon-icon-btn:hover' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'brand'        => array( __( 'Brand', 'moon-core' ), '.moon-brand' ),
				'nav'          => array( __( 'Navigation', 'moon-core' ), '.moon-nav' ),
				'tagline'      => array( __( 'Tagline', 'moon-core' ), '.moon-brand__tag' ),
				'announcement' => array( __( 'Announcement', 'moon-core' ), '.moon-announcement' ),
				'search'       => array( __( 'Search button', 'moon-core' ), '[data-moon-open-search]' ),
				'account'      => array( __( 'Account link', 'moon-core' ), '.moon-header__actions a[href*="my-account"]' ),
				'cart'         => array( __( 'Cart button', 'moon-core' ), '.moon-cart-trigger' ),
				'scheme'       => array( __( 'Colour switch', 'moon-core' ), '.moon-scheme-toggle' ),
				'cta'          => array( __( 'Header button', 'moon-core' ), '.moon-header__cta' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		Site_Chrome::render_header(
			array(
				'brand_name'     => (string) ( $settings['brand_name'] ?? '' ),
				'tagline'        => (string) ( $settings['tagline'] ?? '' ),
				'logo'           => (array) ( $settings['logo'] ?? array() ),
				'menu_location'  => (string) ( $settings['menu_location'] ?? 'primary' ),
				'show_search'    => 'yes' === ( $settings['show_search'] ?? 'yes' ),
				'show_account'   => 'yes' === ( $settings['show_account'] ?? 'yes' ),
				'show_cart'      => 'yes' === ( $settings['show_cart'] ?? 'yes' ),
				'show_scheme'    => 'yes' === ( $settings['show_scheme'] ?? 'yes' ),
				'show_wishlist'  => 'yes' === ( $settings['show_wishlist'] ?? 'yes' ),
				'sticky'         => 'yes' === ( $settings['sticky'] ?? 'yes' ),
				'hide_on_scroll' => 'yes' === ( $settings['hide_on_scroll'] ?? 'yes' ),
				'cta_text'       => (string) ( $settings['cta_text'] ?? '' ),
				'cta_link'       => (array) ( $settings['cta_link'] ?? array() ),
				'announcement'   => (string) ( $settings['announcement'] ?? '' ),
				'classes'        => 'moon-header--widget',
			)
		);
	}
}
