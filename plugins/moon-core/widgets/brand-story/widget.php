<?php
/**
 * MOON Brand Story — text paired with a two-image editorial collage.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Brand_Story extends Widget_Base {

	protected function slug(): string {
		return 'brand-story';
	}

	public function get_title(): string {
		return __( 'MOON Brand Story', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-image-rollover';
	}

	public function get_keywords(): array {
		return array( 'story', 'about', 'atelier', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Story copy', 'moon-core' ),
			array(
				'eyebrow'     => __( 'The atelier', 'moon-core' ),
				'title'       => __( 'Cut slowly,\nworn for years', 'moon-core' ),
				'description' => __( 'Every MOON piece begins as a paper pattern on a long oak table. We work with two family-run mills in Portugal, cut in small runs and finish each garment by hand.', 'moon-core' ),
			)
		);

		$this->start_controls_section( 'section_media', array( 'label' => __( 'Images', 'moon-core' ) ) );

		$this->add_control(
			'image_main',
			array(
				'label'   => __( 'Main portrait', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'image_detail',
			array(
				'label'   => __( 'Detail image', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'image_position',
			array(
				'label'   => __( 'Images side', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'start',
				'options' => array(
					'start' => __( 'Before the text', 'moon-core' ),
					'end'   => __( 'After the text', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'caption',
			array(
				'label'       => __( 'Image caption', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Atelier, Porto — spring sample room', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_stats', array( 'label' => __( 'Numbers & signature', 'moon-core' ) ) );

		$stat = new Repeater();
		$stat->add_control(
			'value',
			array(
				'label'   => __( 'Value', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '12',
			)
		);
		$stat->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Years of cutting', 'moon-core' ),
			)
		);

		$this->add_control(
			'stats',
			array(
				'label'       => __( 'Numbers', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $stat->get_controls(),
				'title_field' => '{{{ value }}} — {{{ label }}}',
				'default'     => array(
					array( 'value' => '2', 'label' => __( 'Family mills', 'moon-core' ) ),
					array( 'value' => '100%', 'label' => __( 'Traceable wool', 'moon-core' ) ),
					array( 'value' => '48', 'label' => __( 'Pieces per season', 'moon-core' ) ),
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'     => __( 'Button text', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Read the journal', 'moon-core' ),
				'separator' => 'before',
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

		$this->add_control(
			'signature',
			array(
				'label'       => __( 'Signature', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Moon Atelier', 'moon-core' ),
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_panel',
			array(
				'label' => __( 'Section surface', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'surface',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-story' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'surface_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .moon-story' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'surface_radius',
			array(
				'label'      => __( 'Radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .moon-story' => 'border-radius: {{SIZE}}px; overflow: hidden;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-story__title' );
		$this->moon_text_style( 'desc', __( 'Description', 'moon-core' ), '.moon-story__desc' );
		$this->moon_text_style( 'stat_value', __( 'Number', 'moon-core' ), '.moon-stat__value' );
		$this->moon_button_style( 'button', __( 'Button', 'moon-core' ), '.moon-story__btn' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'stats'     => array( __( 'Numbers', 'moon-core' ), '.moon-story__stats' ),
				'caption'   => array( __( 'Image caption', 'moon-core' ), '.moon-story__caption' ),
				'signature' => array( __( 'Signature', 'moon-core' ), '.moon-story__signature' ),
				'detail'    => array( __( 'Detail image', 'moon-core' ), '.moon-story__detail' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$position = 'end' === ( $settings['image_position'] ?? 'start' ) ? 'end' : 'start';
		$main     = (array) ( $settings['image_main'] ?? array() );
		$detail   = (array) ( $settings['image_detail'] ?? array() );
		$stats    = (array) ( $settings['stats'] ?? array() );
		?>
		<section class="moon-widget moon-story moon-story--media-<?php echo esc_attr( $position ); ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="moon-story__inner">
				<div class="moon-story__media" data-moon-reveal-item>
					<figure class="moon-story__main">
						<?php
						if ( $main ) {
							echo wp_kses_post( Helpers::image( $main, 'moon-portrait', array( 'alt' => '' ) ) );
						}
						?>
					</figure>
					<?php if ( $detail ) : ?>
						<figure class="moon-story__detail">
							<?php echo wp_kses_post( Helpers::image( $detail, 'medium', array( 'alt' => '' ) ) ); ?>
						</figure>
					<?php endif; ?>
					<?php if ( '' !== trim( (string) ( $settings['caption'] ?? '' ) ) ) : ?>
						<figcaption class="moon-story__caption"><?php echo esc_html( $settings['caption'] ); ?></figcaption>
					<?php endif; ?>
				</div>

				<div class="moon-story__copy" data-moon-reveal-item>
					<?php if ( '' !== trim( (string) ( $settings['heading_eyebrow'] ?? '' ) ) ) : ?>
						<p class="moon-eyebrow"><?php echo esc_html( $settings['heading_eyebrow'] ); ?></p>
					<?php endif; ?>

					<h2 class="moon-story__title"><?php echo wp_kses_post( nl2br( (string) ( $settings['heading_title'] ?? '' ) ) ); ?></h2>

					<p class="moon-story__desc"><?php echo esc_html( (string) ( $settings['heading_description'] ?? '' ) ); ?></p>

					<?php if ( ! empty( $stats ) ) : ?>
						<ul class="moon-story__stats">
							<?php foreach ( $stats as $stat ) : ?>
								<li class="moon-stat">
									<span class="moon-stat__value"><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?></span>
									<span class="moon-stat__label"><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="moon-story__foot">
						<?php
						if ( '' !== trim( (string) ( $settings['button_text'] ?? '' ) ) ) {
							$this->render_link( (array) ( $settings['button_link'] ?? array() ), 'moon-btn moon-story__btn', (string) $settings['button_text'], 'arrow-right' );
						}
						if ( '' !== trim( (string) ( $settings['signature'] ?? '' ) ) ) {
							echo '<span class="moon-story__signature">' . esc_html( $settings['signature'] ) . '</span>';
						}
						?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
