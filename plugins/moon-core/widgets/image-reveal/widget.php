<?php
/**
 * MOON Image Reveal — a single editorial image with a curtain reveal and
 * optional gentle parallax.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Image_Reveal extends Widget_Base {

	protected function slug(): string {
		return 'image-reveal';
	}

	public function get_title(): string {
		return __( 'MOON Image Reveal', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-image';
	}

	public function get_keywords(): array {
		return array( 'image', 'reveal', 'parallax', 'editorial', 'moon' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_media', array( 'label' => __( 'Image', 'moon-core' ) ) );

		$this->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'moon-core' ),
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

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Aspect ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.4, 'max' => 2.4, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1.6 ),
				'selectors'  => array( '{{WRAPPER}} .moon-reveal-img__frame' => 'aspect-ratio: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'parallax',
			array(
				'label'        => __( 'Gentle parallax', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Disabled for reduced-motion visitors.', 'moon-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_copy', array( 'label' => __( 'Overlay copy', 'moon-core' ) ) );

		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Campaign', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'The shape of winter', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'caption',
			array(
				'label'       => __( 'Caption', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Photography — Studio MOON', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'copy_position',
			array(
				'label'   => __( 'Copy position', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'overlay',
				'options' => array(
					'overlay' => __( 'Over the image', 'moon-core' ),
					'below'   => __( 'Under the image', 'moon-core' ),
				),
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
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .moon-reveal-img' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'scrim',
			array(
				'label'      => __( 'Text scrim', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'size' => 55 ),
				'selectors'  => array( '{{WRAPPER}} .moon-reveal-img__scrim' => 'opacity: calc({{SIZE}} / 100);' ),
				'condition'  => array( 'copy_position' => 'overlay' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-reveal-img__title' );
		$this->moon_text_style( 'caption', __( 'Caption', 'moon-core' ), '.moon-reveal-img__caption' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'eyebrow' => array( __( 'Eyebrow', 'moon-core' ), '.moon-reveal-img__eyebrow' ),
				'title'   => array( __( 'Title', 'moon-core' ), '.moon-reveal-img__title' ),
				'caption' => array( __( 'Caption', 'moon-core' ), '.moon-reveal-img__caption' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$image    = (array) ( $settings['image'] ?? array() );
		$overlay  = 'overlay' === ( $settings['copy_position'] ?? 'overlay' );

		if ( ! $image ) {
			return;
		}

		$classes = 'moon-widget moon-reveal-img';
		$classes .= $overlay ? ' moon-reveal-img--overlay' : ' moon-reveal-img--below';
		if ( 'yes' === ( $settings['parallax'] ?? 'yes' ) ) {
			$classes .= ' has-parallax';
		}
		?>
		<figure class="<?php echo esc_attr( $classes ); ?>" data-moon-image-reveal="<?php echo 'yes' === ( $settings['parallax'] ?? 'yes' ) ? 'parallax' : 'none'; ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="moon-reveal-img__frame">
				<?php
				echo wp_kses_post(
					Helpers::image(
						$image,
						(string) ( $settings['image_size'] ?? 'moon-editorial' ),
						array(
							'alt'   => (string) ( $settings['title'] ?? '' ),
							'class' => 'moon-reveal-img__img',
						)
					)
				);
				?>
			</span>

			<?php if ( $overlay ) : ?>
				<span class="moon-reveal-img__scrim" aria-hidden="true"></span>
			<?php endif; ?>

			<figcaption class="moon-reveal-img__copy">
				<?php if ( '' !== trim( (string) ( $settings['eyebrow'] ?? '' ) ) ) : ?>
					<span class="moon-reveal-img__eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) ( $settings['title'] ?? '' ) ) ) : ?>
					<span class="moon-reveal-img__title"><?php echo wp_kses_post( nl2br( (string) $settings['title'] ) ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) ( $settings['caption'] ?? '' ) ) ) : ?>
					<span class="moon-reveal-img__caption"><?php echo esc_html( $settings['caption'] ); ?></span>
				<?php endif; ?>
			</figcaption>
		</figure>
		<?php
	}
}
