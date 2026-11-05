<?php
/**
 * Elementor widget base: shared category, helper controls and argument mapping.
 *
 * @package DaniSite_Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Shared behaviour for every DaniSite widget.
 */
abstract class Dani_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Dani_Sections renderer to call.
	 *
	 * @var string
	 */
	protected $section_type = '';

	/**
	 * Widget category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'danisite' );
	}

	/**
	 * Common keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'danisite', 'دنی سایت', 'section' );
	}

	/**
	 * Render the widget with Elementor's settings.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$args     = $this->build_args( $settings );

		if ( ! $this->section_type ) {
			return;
		}

		echo Dani_Sections::render( $this->section_type, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the renderer.
	}

	/**
	 * Map Elementor settings to renderer arguments.
	 *
	 * @param array $settings Settings.
	 * @return array
	 */
	protected function build_args( $settings ) {
		return $settings;
	}

	/**
	 * Copy simple settings onto renderer arguments.
	 *
	 * @param array $settings Settings.
	 * @param array $map      Elementor key => argument key.
	 * @return array
	 */
	protected function map( $settings, $map ) {
		$args = array();

		foreach ( $map as $from => $to ) {
			if ( isset( $settings[ $from ] ) && '' !== $settings[ $from ] && null !== $settings[ $from ] ) {
				$args[ $to ] = $settings[ $from ];
			}
		}

		return $args;
	}

	/**
	 * Read a media control URL.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Control key.
	 * @return string
	 */
	protected function media_url( $settings, $key ) {
		if ( empty( $settings[ $key ] ) ) {
			return '';
		}
		if ( is_array( $settings[ $key ] ) ) {
			return isset( $settings[ $key ]['url'] ) ? $settings[ $key ]['url'] : '';
		}

		return (string) $settings[ $key ];
	}

	/**
	 * Read a URL control value.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Control key.
	 * @return string
	 */
	protected function link_url( $settings, $key ) {
		if ( empty( $settings[ $key ] ) ) {
			return '';
		}
		if ( is_array( $settings[ $key ] ) ) {
			return isset( $settings[ $key ]['url'] ) ? $settings[ $key ]['url'] : '';
		}

		return (string) $settings[ $key ];
	}

	/**
	 * Convert a repeater control into renderer items.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Repeater key.
	 * @param array  $map      Repeater field => argument key.
	 * @return array
	 */
	protected function repeater( $settings, $key, $map ) {
		if ( empty( $settings[ $key ] ) || ! is_array( $settings[ $key ] ) ) {
			return array();
		}

		$items = array();
		foreach ( $settings[ $key ] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$item = array();
			foreach ( $map as $from => $to ) {
				if ( isset( $row[ $from ] ) && '' !== $row[ $from ] ) {
					$item[ $to ] = $row[ $from ];
				}
			}
			if ( ! empty( $item ) ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Standard content section opener.
	 *
	 * @param string $label Section label.
	 * @param string $name  Section name.
	 * @return void
	 */
	protected function content_section( $label, $name = 'content' ) {
		$this->start_controls_section(
			$name,
			array(
				'label' => $label,
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
	}

	/**
	 * Add a text control.
	 *
	 * @param string $id      Control id.
	 * @param string $label   Label.
	 * @param string $default Default value.
	 * @param string $type    Control type.
	 * @return void
	 */
	protected function text( $id, $label, $default = '', $type = 'text' ) {
		$types = array(
			'text'     => \Elementor\Controls_Manager::TEXT,
			'textarea' => \Elementor\Controls_Manager::TEXTAREA,
			'url'      => \Elementor\Controls_Manager::URL,
			'number'   => \Elementor\Controls_Manager::NUMBER,
		);

		$args = array(
			'label' => $label,
			'type'  => isset( $types[ $type ] ) ? $types[ $type ] : \Elementor\Controls_Manager::TEXT,
		);

		if ( 'url' === $type ) {
			// A URL control's default is an array; a plain string crashes
			// Elementor's control registration (array_merge of string).
			$args['default']       = array( 'url' => (string) $default );
			$args['show_external'] = false;
			$args['options']       = array( 'url', 'nofollow' );
		} else {
			$args['default'] = $default;
		}

		$this->add_control( $id, $args );
	}

	/**
	 * Add a switcher control.
	 *
	 * @param string $id      Control id.
	 * @param string $label   Label.
	 * @param bool   $default Default state.
	 * @return void
	 */
	protected function toggle( $id, $label, $default = false ) {
		$this->add_control(
			$id,
			array(
				'label'        => $label,
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => 'بله',
				'label_off'    => 'خیر',
				'return_value' => 'yes',
				'default'      => $default ? 'yes' : '',
			)
		);
	}

	/**
	 * Add a media control.
	 *
	 * @param string $id    Control id.
	 * @param string $label Label.
	 * @param string $types Media types.
	 * @return void
	 */
	protected function media( $id, $label, $types = array( 'image' ) ) {
		$this->add_control(
			$id,
			array(
				'label' => $label,
				'type'  => \Elementor\Controls_Manager::MEDIA,
				'media_types' => $types,
			)
		);
	}

	/**
	 * Shared style controls: rhythm, background and heading typography.
	 *
	 * @param string $selector Section selector inside the wrapper.
	 * @return void
	 */
	protected function style_section( $selector = '.dani-section' ) {
		$this->start_controls_section(
			'dani_style',
			array(
				'label' => 'استایل بخش',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'dani_bg',
			array(
				'label'     => 'رنگ پس‌زمینه',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $selector => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'dani_padding',
			array(
				'label'      => 'فاصله داخلی',
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dani_radius',
			array(
				'label'      => 'شعاع گوشه',
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'dani_title_color',
			array(
				'label'     => 'رنگ عنوان‌ها',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dani-head__title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dani-hero__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'dani_title_typo',
				'label'    => 'تایپوگرافی عنوان',
				'selector' => '{{WRAPPER}} .dani-head__title, {{WRAPPER}} .dani-hero__title',
			)
		);

		$this->add_control(
			'dani_text_color',
			array(
				'label'     => 'رنگ متن',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .dani-head__text, {{WRAPPER}} .dani-hero__text' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}
}
