<?php
/**
 * MOON Editorial Banner — campaign image with an overlapping content card.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Editorial_Banner extends Widget_Base {

	protected function slug(): string {
		return 'editorial-banner';
	}

	public function get_title(): string {
		return __( 'MOON Editorial Banner', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-image-box';
	}

	public function get_keywords(): array {
		return array( 'editorial', 'campaign', 'banner', 'image', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Copy', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Campaign 02', 'moon-core' ),
				'title'       => __( 'The desert hours', 'moon-core' ),
				'description' => __( 'Sun-bleached tailoring photographed at first light. A study in warmth, movement and restraint.', 'moon-core' ),
			)
		);

		$this->start_controls_section( 'section_media', array( 'label' => __( 'Image & link', 'moon-core' ) ) );

		$this->add_control(
			'image',
			array(
				'label'   => __( 'Campaign image', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => __( 'Image size', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'moon-editorial',
				'options' => Helpers::image_size_options(),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button text', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Discover the campaign', 'moon-core' ),
			)
		);

		$this->add_control(
			'button_link',
			array(
				'label'     => __( 'Button link', 'moon-core' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'button_text!' => '' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_control(
			'card_position',
			array(
				'label'   => __( 'Content card', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'start',
				'options' => array(
					'start' => __( 'Overlap bottom-start', 'moon-core' ),
					'end'   => __( 'Overlap bottom-end', 'moon-core' ),
					'over'  => __( 'On top of the image, centred', 'moon-core' ),
					'below' => __( 'Below the image', 'moon-core' ),
				),
			)
		);

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Image ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.5, 'max' => 2.2, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1.35 ),
				'selectors'  => array( '{{WRAPPER}} .moon-editorial__media' => 'aspect-ratio: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .moon-editorial__media, {{WRAPPER}} .moon-editorial__card' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'card_width',
			array(
				'label'      => __( 'Card width (%)', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 30, 'max' => 80 ) ),
				'default'    => array( 'size' => 46, 'unit' => '%' ),
				'selectors'  => array( '{{WRAPPER}} .moon-editorial__card' => 'max-width: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'card_position' => array( 'start', 'end' ) ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_card',
			array(
				'label' => __( 'Content card', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.92)',
				'selectors' => array( '{{WRAPPER}} .moon-editorial__card' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array( 'top' => '2', 'right' => '2.2', 'bottom' => '2', 'left' => '2.2', 'unit' => 'rem', 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .moon-editorial__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_image',
			array(
				'label' => __( 'Image', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'image_zoom',
			array(
				'label'        => __( 'Zoom on hover', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'prefix_class' => 'moon-zoom-',
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => __( 'Minimum height', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 200, 'max' => 900 ),
					'vh' => array( 'min' => 20, 'max' => 90 ),
				),
				'selectors'  => array( '{{WRAPPER}} .moon-editorial__media' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'eyebrow', __( 'Eyebrow', 'moon-core' ), '.moon-editorial__card .moon-eyebrow' );
		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-editorial__card .moon-section__title' );
		$this->moon_text_style( 'desc', __( 'Description', 'moon-core' ), '.moon-editorial__card .moon-section__desc' );
		$this->moon_button_style( 'button', __( 'Button', 'moon-core' ), '.moon-editorial__btn' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'eyebrow' => array( __( 'Eyebrow', 'moon-core' ), '.moon-editorial__card .moon-eyebrow' ),
				'desc'    => array( __( 'Description', 'moon-core' ), '.moon-editorial__card .moon-section__desc' ),
				'button'  => array( __( 'Button', 'moon-core' ), '.moon-editorial__btn' ),
				'media'   => array( __( 'Image', 'moon-core' ), '.moon-editorial__media' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$position = in_array( $settings['card_position'] ?? 'start', array( 'start', 'end', 'over', 'below' ), true ) ? $settings['card_position'] : 'start';
		$image    = (array) ( $settings['image'] ?? array() );
		?>
		<section class="moon-widget moon-editorial moon-editorial--<?php echo esc_attr( $position ); ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="moon-editorial__inner">
				<figure class="moon-editorial__media" data-moon-reveal-item>
					<?php
					if ( $image ) {
						echo wp_kses_post( Helpers::image( $image, (string) ( $settings['image_size'] ?? 'moon-editorial' ), array( 'alt' => '' ) ) );
					}
					?>
				</figure>

				<div class="moon-editorial__card" data-moon-reveal-item>
					<?php
					$this->render_heading(
						array(
							'heading_eyebrow'     => $settings['heading_eyebrow'] ?? '',
							'heading_title'       => $settings['heading_title'] ?? '',
							'heading_description' => $settings['heading_description'] ?? '',
							'heading_title_tag'   => $settings['heading_title_tag'] ?? 'h2',
						)
					);

					if ( '' !== trim( (string) ( $settings['button_text'] ?? '' ) ) ) {
						$this->render_link( (array) ( $settings['button_link'] ?? array() ), 'moon-btn moon-btn--solid moon-editorial__btn', (string) $settings['button_text'], 'arrow-right' );
					}
					?>
				</div>
			</div>
		</section>
		<?php
	}
}
