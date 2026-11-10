<?php
/**
 * Shared base class for every Moon Elementor widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Small helper surface so each widget stays focused on its own controls.
 */
abstract class Moon_Core_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Elementor categories for every Moon widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'moon-core' );
	}

	/**
	 * Do not load the widget's CSS globally in the editor preview.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'moon-core' );
	}

	/**
	 * Scripts needed by interactive widgets.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'moon-core' );
	}

	/**
	 * Keyword search terms shown in the Elementor panel.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'moon', 'moon core', 'فروشگاه', 'لوکس' );
	}

	/**
	 * Add a typography control bound to a selector.
	 *
	 * @param string $id         Control id.
	 * @param string $label      Control label.
	 * @param string $selector   CSS selector.
	 * @param string $name       Section name.
	 * @param array  $conditions Display conditions.
	 */
	protected function add_typography( $id, $label, $selector, $name, $conditions = array() ) {
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'      => $id,
				'label'     => $label,
				'selector'  => $selector,
				'condition' => $conditions,
			)
		);
	}

	/**
	 * Add spacing (padding + margin) controls.
	 *
	 * @param string $selector CSS selector.
	 * @param string $label    Section label.
	 */
	protected function add_spacing( $selector, $label ) {
		$this->add_responsive_control(
			'spacing_padding',
			array(
				'label'      => $label,
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'spacing_margin',
			array(
				'label'      => __( 'فاصله بیرونی', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
	}

	/**
	 * Add border + radius + shadow controls.
	 *
	 * @param string $selector CSS selector.
	 */
	protected function add_box( $selector ) {
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'box_border',
				'label'    => __( 'حاشیه', 'moon-core' ),
				'selector' => $selector,
			)
		);
		$this->add_responsive_control(
			'box_radius',
			array(
				'label'      => __( 'گردی گوشه‌ها', 'moon-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'label'    => __( 'سایه', 'moon-core' ),
				'selector' => $selector,
			)
		);
	}

	/**
	 * Add entrance animation controls.
	 *
	 * @param string $selector CSS selector of the animated element.
	 */
	protected function add_motion( $selector ) {
		$this->add_control(
			'motion',
			array(
				'label'     => __( 'انیمیشن ورود', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'fade-up',
				'options'   => array(
					''        => __( 'بدون انیمیشن', 'moon-core' ),
					'fade'    => __( 'محو', 'moon-core' ),
					'fade-up' => __( 'محو از پایین', 'moon-core' ),
					'fade-in' => __( 'ظهور نرم', 'moon-core' ),
					'scale'   => __( 'بزرگ‌نمایی ملایم', 'moon-core' ),
				),
				'prefix_class' => 'moon-motion--',
				'separator'    => 'before',
			)
		);
		$this->add_control(
			'motion_delay',
			array(
				'label'   => __( 'تأخیر انیمیشن (ms)', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 2000,
				'step'    => 50,
				'default' => 0,
				'selectors' => array( $selector => '--moon-motion-delay: {{VALUE}}ms;' ),
			)
		);
	}

	/**
	 * A URL control that keeps Elementor's expected array default.
	 *
	 * @param string $id      Control id.
	 * @param string $label   Label.
	 * @param string $default Default URL.
	 */
	protected function add_link( $id, $label, $default = '' ) {
		$this->add_control(
			$id,
			array(
				'label'       => $label,
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => home_url( '/' ),
				'default'     => array(
					'url'         => $default,
					'is_external' => false,
					'nofollow'    => false,
				),
			)
		);
	}

	/**
	 * Read a link control into a safe href + attributes string.
	 *
	 * @param string $id Control id.
	 * @return array{href:string,attrs:string}
	 */
	protected function link_data( $id ) {
		$link = $this->get_settings_for_display( $id );

		if ( ! is_array( $link ) || empty( $link['url'] ) ) {
			return array(
				'href'  => '',
				'attrs' => '',
			);
		}

		$attrs = '';
		if ( ! empty( $link['is_external'] ) ) {
			$attrs .= ' target="_blank"';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$attrs .= ' rel="nofollow"';
		}

		return array(
			'href'  => $link['url'],
			'attrs' => $attrs,
		);
	}
}
