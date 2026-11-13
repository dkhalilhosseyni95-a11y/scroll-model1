<?php
/**
 * MOON Campaign Storytelling — chapters with a sticky image that follows the scroll.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Campaign_Storytelling extends Widget_Base {

	protected function slug(): string {
		return 'campaign-storytelling';
	}

	public function get_title(): string {
		return __( 'MOON Campaign Storytelling', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-slider-vertical';
	}

	public function get_keywords(): array {
		return array( 'campaign', 'story', 'scroll', 'chapters', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Campaign', 'moon-core' ),
				'title'       => __( 'Notes from the campaign trail', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_chapters', array( 'label' => __( 'Chapters', 'moon-core' ) ) );

		$chapter = new Repeater();
		$chapter->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);
		$chapter->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Chapter 01 — first light', 'moon-core' ),
			)
		);
		$chapter->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'The desert hours', 'moon-core' ),
				'label_block' => true,
			)
		);
		$chapter->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'default'     => __( 'We drove out before sunrise, coats and silk packed flat in the back of the car.', 'moon-core' ),
				'label_block' => true,
			)
		);
		$chapter->add_control(
			'link',
			array(
				'label'   => __( 'Link (optional)', 'moon-core' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'chapters',
			array(
				'label'       => __( 'Chapters', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $chapter->get_controls(),
				'title_field' => '{{{ eyebrow }}}',
				'default'     => array(
					array( 'eyebrow' => __( 'Chapter 01 — first light', 'moon-core' ), 'title' => __( 'The desert hours', 'moon-core' ), 'text' => __( 'We drove out before sunrise, coats and silk packed flat in the back of the car.', 'moon-core' ) ),
					array( 'eyebrow' => __( 'Chapter 02 — the long walk', 'moon-core' ), 'title' => __( 'Movement over pose', 'moon-core' ), 'text' => __( 'Tailoring photographed mid-stride, because the cut only reveals itself in motion.', 'moon-core' ) ),
					array( 'eyebrow' => __( 'Chapter 03 — evening', 'moon-core' ), 'title' => __( 'Silk in low light', 'moon-core' ), 'text' => __( 'The last frames were shot as the light fell, with the ivory set catching the blue hour.', 'moon-core' ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_control(
			'media_position',
			array(
				'label'   => __( 'Media column', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'start',
				'options' => array(
					'start' => __( 'Before the chapters', 'moon-core' ),
					'end'   => __( 'After the chapters', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'sticky',
			array(
				'label'        => __( 'Stick the media while scrolling', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Media ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.6, 'max' => 1.6, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 0.8 ),
				'selectors'  => array( '{{WRAPPER}} .moon-campaign__stage' => 'aspect-ratio: {{SIZE}};' ),
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
				'selectors'  => array( '{{WRAPPER}} .moon-campaign__stage' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'chapter_title', __( 'Chapter title', 'moon-core' ), '.moon-campaign__chapter-title' );
		$this->moon_text_style( 'chapter_text', __( 'Chapter text', 'moon-core' ), '.moon-campaign__chapter-text' );
		$this->moon_button_style( 'chapter_link', __( 'Chapter link', 'moon-core' ), '.moon-campaign__chapter-link' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'numbers' => array( __( 'Chapter numbers', 'moon-core' ), '.moon-campaign__chapter-num' ),
				'text'    => array( __( 'Chapter text', 'moon-core' ), '.moon-campaign__chapter-text' ),
				'progress' => array( __( 'Progress bar', 'moon-core' ), '.moon-campaign__progress' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$chapters = (array) ( $settings['chapters'] ?? array() );

		if ( empty( $chapters ) ) {
			return;
		}

		$position = 'end' === ( $settings['media_position'] ?? 'start' ) ? 'end' : 'start';
		$sticky   = 'yes' === ( $settings['sticky'] ?? 'yes' );
		?>
		<section class="moon-widget moon-campaign moon-campaign--media-<?php echo esc_attr( $position ); ?><?php echo $sticky ? ' is-sticky' : ''; ?>" data-moon-campaign<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			$this->render_heading(
				array(
					'heading_eyebrow'     => $settings['heading_eyebrow'] ?? '',
					'heading_title'       => $settings['heading_title'] ?? '',
					'heading_description' => $settings['heading_description'] ?? '',
					'heading_title_tag'   => $settings['heading_title_tag'] ?? 'h2',
				)
			);
			?>
			<div class="moon-campaign__body">
				<div class="moon-campaign__media">
					<div class="moon-campaign__stage">
						<?php foreach ( $chapters as $index => $chapter ) : ?>
							<figure class="moon-campaign__frame<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-campaign-frame="<?php echo esc_attr( (string) $index ); ?>">
								<?php
								$image = (array) ( $chapter['image'] ?? array() );
								if ( $image ) {
									echo wp_kses_post( Helpers::image( $image, 'moon-portrait', array( 'alt' => '', 'loading' => 0 === $index ? 'eager' : 'lazy' ) ) );
								}
								?>
							</figure>
						<?php endforeach; ?>
						<span class="moon-campaign__progress" aria-hidden="true"><span data-moon-campaign-bar></span></span>
					</div>
				</div>

				<div class="moon-campaign__chapters">
					<?php foreach ( $chapters as $index => $chapter ) : ?>
						<article class="moon-campaign__chapter" data-moon-campaign-chapter="<?php echo esc_attr( (string) $index ); ?>">
							<span class="moon-campaign__chapter-num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<?php if ( '' !== trim( (string) ( $chapter['eyebrow'] ?? '' ) ) ) : ?>
								<p class="moon-eyebrow"><?php echo esc_html( $chapter['eyebrow'] ); ?></p>
							<?php endif; ?>
							<h3 class="moon-campaign__chapter-title"><?php echo wp_kses_post( nl2br( (string) ( $chapter['title'] ?? '' ) ) ); ?></h3>
							<?php if ( '' !== trim( (string) ( $chapter['text'] ?? '' ) ) ) : ?>
								<p class="moon-campaign__chapter-text"><?php echo esc_html( $chapter['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== trim( (string) ( $chapter['link']['url'] ?? '' ) ) ) : ?>
								<?php $this->render_link( (array) ( $chapter['link'] ?? array() ), 'moon-link-underline moon-campaign__chapter-link', __( 'Read the chapter', 'moon-core' ), 'arrow-right' ); ?>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
