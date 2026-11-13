<?php
/**
 * MOON Hero widget — cinematic editorial opening.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Hero extends Widget_Base {

	protected function slug(): string {
		return 'hero';
	}

	public function get_title(): string {
		return __( 'MOON Hero', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-banner';
	}

	public function get_keywords(): array {
		return array( 'hero', 'banner', 'cover', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'moon-core' ) ) );

		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Autumn / Winter — The Atelier Edit', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Quiet luxury,', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_accent',
			array(
				'label'       => __( 'Italic second line', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'loudly cut', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'description',
			array(
				'label'       => __( 'Description', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'Tailored coats, silk shirting and the quiet architecture of a wardrobe built to outlast the season.', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'primary_text',
			array(
				'label'     => __( 'Primary button', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Shop the collection', 'moon-core' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'primary_link',
			array(
				'label'     => __( 'Primary link', 'moon-core' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'default'   => array( 'url' => '' ),
				'condition' => array( 'primary_text!' => '' ),
			)
		);

		$this->add_control(
			'secondary_text',
			array(
				'label'     => __( 'Secondary button', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'The atelier story', 'moon-core' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'secondary_link',
			array(
				'label'     => __( 'Secondary link', 'moon-core' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'default'   => array( 'url' => '' ),
				'condition' => array( 'secondary_text!' => '' ),
			)
		);

		$this->add_control(
			'side_label',
			array(
				'label'       => __( 'Vertical side label', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Made in Portugal', 'moon-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_media', array( 'label' => __( 'Media', 'moon-core' ) ) );

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Main image', 'moon-core' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array( 'active' => true ),
				'default'   => array( 'url' => '' ),
			)
		);

		$this->add_control(
			'image_mobile',
			array(
				'label'       => __( 'Mobile image (optional)', 'moon-core' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'A tighter crop for narrow screens. Falls back to the main image.', 'moon-core' ),
			)
		);

		$this->add_control(
			'image_fit',
			array(
				'label'   => __( 'Image fit', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => array(
					'cover'   => __( 'Cover the frame', 'moon-core' ),
					'contain' => __( 'Contain the whole image', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'image_position',
			array(
				'label'     => __( 'Image focal point', 'moon-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center',
				'options'   => array(
					'center' => __( 'Centre', 'moon-core' ),
					'top'    => __( 'Top', 'moon-core' ),
					'bottom' => __( 'Bottom', 'moon-core' ),
					'left'   => __( 'Left', 'moon-core' ),
					'right'  => __( 'Right', 'moon-core' ),
				),
				'selectors' => array( '{{WRAPPER}} .moon-hero__bg' => 'object-position: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'priority',
			array(
				'label'        => __( 'Load as priority image (LCP)', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'overlay',
				'options' => array(
					'overlay'  => __( 'Full-bleed image, text over it', 'moon-core' ),
					'split'    => __( 'Split: text beside image', 'moon-core' ),
					'centered' => __( 'Centred image with text below', 'moon-core' ),
				),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'      => __( 'Height', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'vh', 'px' ),
				'range'      => array(
					'vh' => array( 'min' => 40, 'max' => 100 ),
					'px' => array( 'min' => 320, 'max' => 1100 ),
				),
				'default'    => array( 'size' => 88, 'unit' => 'vh' ),
				'selectors'  => array( '{{WRAPPER}} .moon-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'content_align',
			array(
				'label'     => __( 'Content alignment', 'moon-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => __( 'Start', 'moon-core' ), 'icon' => 'eicon-text-align-left' ),
					'center'     => array( 'title' => __( 'Centre', 'moon-core' ), 'icon' => 'eicon-text-align-center' ),
					'flex-end'   => array( 'title' => __( 'End', 'moon-core' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'flex-start',
				'selectors' => array( '{{WRAPPER}} .moon-hero__inner' => 'align-items: {{VALUE}}; text-align: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'      => __( 'Split ratio', 'moon-core' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => '1.1fr_1fr',
				'options'    => array(
					'1.1fr_1fr' => __( 'Text wider', 'moon-core' ),
					'1fr_1fr'   => __( 'Equal', 'moon-core' ),
					'1fr_1.2fr' => __( 'Image wider', 'moon-core' ),
				),
				'condition'  => array( 'layout' => 'split' ),
				'selectors'  => array( '{{WRAPPER}} .moon-hero--split .moon-hero__grid' => 'grid-template-columns: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Section corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'selectors'  => array( '{{WRAPPER}} .moon-hero' => 'border-radius: {{SIZE}}px; overflow: hidden;' ),
			)
		);

		$this->end_controls_section();

		/* Style ---------------------------------------------------------- */

		$this->start_controls_section(
			'style_overlay',
			array(
				'label' => __( 'Image overlay', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'overlay_opacity',
			array(
				'label'      => __( 'Scrim strength', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'size' => 58 ),
				'selectors'  => array( '{{WRAPPER}} .moon-hero__scrim' => 'opacity: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Scrim colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#101010',
				'selectors' => array(
					'{{WRAPPER}} .moon-hero__scrim' => 'background: linear-gradient(180deg, {{VALUE}}00 0%, {{VALUE}}cc 62%, {{VALUE}}f2 100%);',
				),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'eyebrow', __( 'Eyebrow', 'moon-core' ), '.moon-hero__eyebrow' );
		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-hero__title' );
		$this->moon_text_style( 'accent', __( 'Italic line', 'moon-core' ), '.moon-hero__accent' );
		$this->moon_text_style( 'desc', __( 'Description', 'moon-core' ), '.moon-hero__desc' );
		$this->moon_button_style( 'primary', __( 'Primary button', 'moon-core' ), '.moon-hero__btn--primary' );
		$this->moon_button_style( 'secondary', __( 'Secondary button', 'moon-core' ), '.moon-hero__btn--secondary' );
		$this->moon_image_style( 'image', __( 'Image', 'moon-core' ), '.moon-hero__media' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'eyebrow'   => array( __( 'Eyebrow', 'moon-core' ), '.moon-hero__eyebrow' ),
				'desc'      => array( __( 'Description', 'moon-core' ), '.moon-hero__desc' ),
				'secondary' => array( __( 'Secondary button', 'moon-core' ), '.moon-hero__btn--secondary' ),
				'side'      => array( __( 'Side label', 'moon-core' ), '.moon-hero__side' ),
				'media'     => array( __( 'Image', 'moon-core' ), '.moon-hero__media' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$layout   = in_array( $settings['layout'] ?? 'overlay', array( 'overlay', 'split', 'centered' ), true ) ? $settings['layout'] : 'overlay';

		$image        = (array) ( $settings['image'] ?? array() );
		$mobile_image = (array) ( $settings['image_mobile'] ?? array() );
		$priority     = 'yes' === ( $settings['priority'] ?? 'yes' );
		$fit          = 'contain' === ( $settings['image_fit'] ?? 'cover' ) ? 'contain' : 'cover';

		$image_markup = $image
			? Helpers::image(
				$image,
				'full',
				array(
					'class'         => 'moon-hero__bg',
					'alt'           => '',
					'fetchpriority' => $priority ? 'high' : 'auto',
					'decoding'      => $priority ? 'sync' : 'async',
					'style'         => 'object-fit: ' . $fit . ';',
				),
				! $priority
			)
			: '';

		$mobile_markup = $mobile_image ? Helpers::image( $mobile_image, 'large', array( 'class' => 'moon-hero__bg moon-hero__bg--mobile', 'alt' => '' ), false ) : '';
		?>
		<section class="moon-widget moon-hero moon-hero--<?php echo esc_attr( $layout ); ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_attr. ?>>
			<?php if ( 'split' === $layout ) : ?>
				<div class="moon-hero__grid moon-container">
					<div class="moon-hero__inner">
						<?php $this->render_copy( $settings ); ?>
					</div>
					<div class="moon-hero__media" data-moon-reveal-item>
						<?php echo wp_kses_post( $image_markup ); ?>
					</div>
				</div>
			<?php elseif ( 'centered' === $layout ) : ?>
				<div class="moon-container">
					<div class="moon-hero__media moon-hero__media--wide" data-moon-reveal-item>
						<?php echo wp_kses_post( $image_markup ); ?>
					</div>
					<div class="moon-hero__inner moon-hero__inner--center">
						<?php $this->render_copy( $settings ); ?>
					</div>
				</div>
			<?php else : ?>
				<div class="moon-hero__media<?php echo $mobile_markup ? ' moon-hero__media--has-mobile' : ''; ?>">
					<?php echo wp_kses_post( $image_markup ); ?>
					<?php echo wp_kses_post( $mobile_markup ); ?>
				</div>
				<div class="moon-hero__scrim" aria-hidden="true"></div>
				<div class="moon-hero__inner moon-hero__inner--overlay moon-container">
					<?php $this->render_copy( $settings ); ?>
				</div>

				<?php if ( '' !== trim( (string) ( $settings['side_label'] ?? '' ) ) ) : ?>
					<span class="moon-hero__side"><?php echo esc_html( $settings['side_label'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Eyebrow, title, description and buttons.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 */
	private function render_copy( array $settings ): void {
		if ( '' !== trim( (string) ( $settings['eyebrow'] ?? '' ) ) ) {
			echo '<p class="moon-eyebrow moon-hero__eyebrow" data-moon-reveal-item>' . esc_html( $settings['eyebrow'] ) . '</p>';
		}

		echo '<h1 class="moon-hero__title" data-moon-reveal-item>' . wp_kses_post( nl2br( (string) ( $settings['title'] ?? '' ) ) );
		if ( '' !== trim( (string) ( $settings['title_accent'] ?? '' ) ) ) {
			echo ' <em class="moon-hero__accent">' . esc_html( $settings['title_accent'] ) . '</em>';
		}
		echo '</h1>';

		if ( '' !== trim( (string) ( $settings['description'] ?? '' ) ) ) {
			echo '<p class="moon-hero__desc" data-moon-reveal-item>' . esc_html( $settings['description'] ) . '</p>';
		}

		$actions = '';
		if ( '' !== trim( (string) ( $settings['primary_text'] ?? '' ) ) ) {
			$actions .= Helpers::link( (array) ( $settings['primary_link'] ?? array() ), 'moon-btn moon-btn--solid moon-hero__btn moon-hero__btn--primary', esc_html( $settings['primary_text'] ) );
		}
		if ( '' !== trim( (string) ( $settings['secondary_text'] ?? '' ) ) ) {
			$actions .= Helpers::link( (array) ( $settings['secondary_link'] ?? array() ), 'moon-btn moon-hero__btn moon-hero__btn--secondary', esc_html( $settings['secondary_text'] ) );
		}

		if ( '' !== $actions ) {
			echo '<div class="moon-hero__actions" data-moon-reveal-item>' . $actions . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Helpers::link.
		}
	}
}
