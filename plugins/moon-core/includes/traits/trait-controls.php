<?php
/**
 * Reusable control groups: headings, buttons, images, motion and visibility.
 *
 * Every MOON widget uses these so the editor feels consistent and every section
 * exposes the same editable surface (content, typography, colour, spacing,
 * responsive settings, animation and visibility).
 *
 * @package MoonCore
 */

namespace MoonCore;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

trait Controls {

	/**
	 * Eyebrow / title / description group.
	 *
	 * @param string              $prefix   Control id prefix.
	 * @param string              $label    Section label.
	 * @param array<string,mixed> $defaults Default copy.
	 */
	protected function moon_heading_controls( string $prefix, string $label, array $defaults = array() ): void {
		$defaults = wp_parse_args(
			$defaults,
			array(
				'eyebrow'     => __( 'The edit', 'moon-core' ),
				'title'       => __( 'Section title', 'moon-core' ),
				'description' => '',
				'tag'         => 'h2',
			)
		);

		$this->start_controls_section(
			'section_' . $prefix,
			array( 'label' => $label )
		);

		$this->add_control(
			$prefix . '_eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['eyebrow'],
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			$prefix . '_title',
			array(
				'label'       => __( 'Title', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => $defaults['title'],
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			$prefix . '_title_tag',
			array(
				'label'   => __( 'Title HTML tag', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['tag'],
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'div'  => 'div',
					'span' => 'span',
				),
			)
		);

		$this->add_control(
			$prefix . '_description',
			array(
				'label'       => __( 'Description', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'default'     => $defaults['description'],
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_responsive_control(
			$prefix . '_align',
			array(
				'label'     => __( 'Alignment', 'moon-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array( 'title' => __( 'Start', 'moon-core' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'moon-core' ), 'icon' => 'eicon-text-align-center' ),
					'end'    => array( 'title' => __( 'End', 'moon-core' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'start',
				'selectors' => array(
					'{{WRAPPER}} .moon-section__head' => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style section for text elements: colour, typography, spacing.
	 *
	 * @param string $id       Control prefix.
	 * @param string $label    Section label.
	 * @param string $selector CSS selector relative to the widget wrapper.
	 */
	protected function moon_text_style( string $id, string $label, string $selector ): void {
		$this->start_controls_section(
			'style_' . $id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			$id . '_color',
			array(
				'label'     => __( 'Colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typography',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);

		$this->add_responsive_control(
			$id . '_margin',
			array(
				'label'      => __( 'Space below', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 120 ),
				),
				'selectors'  => array( '{{WRAPPER}} ' . $selector => 'margin-block-end: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Button styling (normal + hover) for a selector.
	 *
	 * @param string $id       Control prefix.
	 * @param string $label    Section label.
	 * @param string $selector CSS selector of the button.
	 */
	protected function moon_button_style( string $id, string $label, string $selector ): void {
		$sel = '{{WRAPPER}} ' . $selector;

		$this->start_controls_section(
			'style_' . $id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( $id . '_tabs' );

		$this->start_controls_tab(
			$id . '_normal',
			array( 'label' => __( 'Normal', 'moon-core' ) )
		);

		$this->add_control(
			$id . '_color',
			array(
				'label'     => __( 'Text colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$id . '_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$id . '_border_color',
			array(
				'label'     => __( 'Border colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			$id . '_hover',
			array( 'label' => __( 'Hover', 'moon-core' ) )
		);

		$this->add_control(
			$id . '_hover_color',
			array(
				'label'     => __( 'Text colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel . ':hover, ' . $sel . ':focus-visible' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$id . '_hover_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel . ':hover, ' . $sel . ':focus-visible' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typography',
				'selector' => $sel,
			)
		);

		$this->add_responsive_control(
			$id . '_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			$id . '_radius',
			array(
				'label'      => __( 'Border radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( $sel => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Image presentation controls (radius, ratio, fit, hover zoom).
	 *
	 * @param string $id       Control prefix.
	 * @param string $label    Section label.
	 * @param string $selector Selector of the image element.
	 */
	protected function moon_image_style( string $id, string $label, string $selector ): void {
		$sel = '{{WRAPPER}} ' . $selector;

		$this->start_controls_section(
			'style_' . $id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			$id . '_radius',
			array(
				'label'      => __( 'Border radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( $sel => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;' ),
			)
		);

		$this->add_responsive_control(
			$id . '_ratio',
			array(
				'label'      => __( 'Aspect ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.5, 'max' => 2, 'step' => 0.01 ) ),
				'selectors'  => array( $sel => 'aspect-ratio: {{SIZE}};' ),
			)
		);

		$this->add_control(
			$id . '_filter',
			array(
				'label'     => __( 'Image filter', 'moon-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'                                 => __( 'None', 'moon-core' ),
					'grayscale(1)'                         => __( 'Desaturated', 'moon-core' ),
					'contrast(1.1) saturate(0.94)'         => __( 'Editorial contrast', 'moon-core' ),
					'sepia(0.14) saturate(1.05) contrast(1.02)' => __( 'Warm film', 'moon-core' ),
				),
				'selectors' => array(
					$sel . ' img' => 'filter: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			$id . '_zoom',
			array(
				'label'        => __( 'Zoom on hover', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'selectors'    => array(),
				'prefix_class' => 'moon-zoom-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Motion: reveal on scroll + optional parallax, all disabled for users who
	 * prefer reduced motion (handled in CSS/JS).
	 */
	protected function moon_motion_controls(): void {
		$this->start_controls_section(
			'section_motion',
			array(
				'label' => __( 'Motion', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$this->add_control(
			'reveal',
			array(
				'label'   => __( 'Reveal on scroll', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade-up',
				'options' => array(
					'none'    => __( 'None', 'moon-core' ),
					'fade'    => __( 'Fade', 'moon-core' ),
					'fade-up' => __( 'Fade up', 'moon-core' ),
					'scale'   => __( 'Scale in', 'moon-core' ),
					'mask'    => __( 'Curtain reveal', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'reveal_delay',
			array(
				'label'      => __( 'Delay (ms)', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 800, 'step' => 20 ) ),
				'default'    => array( 'size' => 0 ),
				'condition'  => array( 'reveal!' => 'none' ),
			)
		);

		$this->add_control(
			'reveal_stagger',
			array(
				'label'       => __( 'Stagger children (ms)', 'moon-core' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 300, 'step' => 10 ) ),
				'default'     => array( 'size' => 90 ),
				'description' => __( 'Applies to the items the widget marks as animated children.', 'moon-core' ),
				'condition'   => array( 'reveal!' => 'none' ),
			)
		);

		$this->add_control(
			'hover_lift',
			array(
				'label'        => __( 'Lift on hover', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'prefix_class' => 'moon-lift-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Per-element visibility for desktop / tablet / mobile.
	 *
	 * @param array<string,array{0:string,1:string}> $elements key => [label, selector].
	 */
	protected function moon_visibility_controls( array $elements ): void {
		if ( empty( $elements ) ) {
			return;
		}

		$this->start_controls_section(
			'section_visibility',
			array(
				'label' => __( 'Visibility', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		foreach ( $elements as $key => $config ) {
			list( $label, $selector ) = $config;

			$this->add_responsive_control(
				'visibility_' . $key,
				array(
					'label'     => $label,
					'type'      => Controls_Manager::SELECT,
					'default'   => '',
					'options'   => array(
						''     => __( 'Show', 'moon-core' ),
						'none' => __( 'Hide', 'moon-core' ),
					),
					'selectors' => array(
						'{{WRAPPER}} ' . $selector => 'display: {{VALUE}};',
					),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Surface styling for an inner panel (background, border, shadow, padding).
	 *
	 * @param string $id       Control prefix.
	 * @param string $label    Section label.
	 * @param string $selector Panel selector.
	 */
	protected function moon_panel_style( string $id, string $label, string $selector ): void {
		$sel = '{{WRAPPER}} ' . $selector;

		$this->start_controls_section(
			'style_' . $id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			$id . '_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $id . '_border',
				'selector' => $sel,
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => $id . '_shadow',
				'selector' => $sel,
			)
		);

		$this->add_responsive_control(
			$id . '_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			$id . '_radius',
			array(
				'label'      => __( 'Border radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'selectors'  => array( $sel => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}
}
