<?php
/**
 * MOON Fashion Lookbook — staggered editorial image grid with a lightbox.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Lookbook extends Widget_Base {

	protected function slug(): string {
		return 'lookbook';
	}

	public function get_title(): string {
		return __( 'MOON Lookbook', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-gallery-masonry';
	}

	public function get_keywords(): array {
		return array( 'lookbook', 'gallery', 'editorial', 'lightbox', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Lookbook', 'moon-core' ),
				'title'       => __( 'Field notes', 'moon-core' ),
				'description' => __( 'Photographed between the studio and the street. Select an image to view it larger.', 'moon-core' ),
			)
		);

		$this->start_controls_section( 'section_items', array( 'label' => __( 'Images', 'moon-core' ) ) );

		$item = new Repeater();
		$item->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);
		$item->add_control(
			'caption',
			array(
				'label'       => __( 'Caption', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Look 01 — atelier doorway', 'moon-core' ),
				'label_block' => true,
			)
		);
		$item->add_control(
			'span',
			array(
				'label'   => __( 'Tile size', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'normal',
				'options' => array(
					'normal' => __( 'Normal', 'moon-core' ),
					'wide'   => __( 'Wide', 'moon-core' ),
					'tall'   => __( 'Tall', 'moon-core' ),
				),
			)
		);
		$item->add_control(
			'link',
			array(
				'label'   => __( 'Optional link', 'moon-core' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Images', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $item->get_controls(),
				'title_field' => '{{{ caption }}}',
				'default'     => array(
					array( 'caption' => __( 'Look 01 — atelier doorway', 'moon-core' ), 'span' => 'tall' ),
					array( 'caption' => __( 'Look 02 — the ivory set', 'moon-core' ) ),
					array( 'caption' => __( 'Detail — hand-finished seam', 'moon-core' ) ),
					array( 'caption' => __( 'Look 03 — camel coat', 'moon-core' ), 'span' => 'wide' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'moon-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .moon-look__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'    => array( 'size' => 14 ),
				'selectors'  => array( '{{WRAPPER}} .moon-look__grid' => 'gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'lightbox',
			array(
				'label'        => __( 'Open in lightbox', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 10 ),
				'selectors'  => array( '{{WRAPPER}} .moon-look__tile' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'caption', __( 'Caption', 'moon-core' ), '.moon-look__caption' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'captions' => array( __( 'Captions', 'moon-core' ), '.moon-look__caption' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$items    = (array) ( $settings['items'] ?? array() );
		$lightbox = 'yes' === ( $settings['lightbox'] ?? 'yes' );

		if ( empty( $items ) ) {
			return;
		}
		?>
		<section class="moon-widget moon-look" data-moon-lookbook="<?php echo $lightbox ? 'lightbox' : 'inline'; ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<div class="moon-look__grid">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$image   = (array) ( $item['image'] ?? array() );
					$caption = (string) ( $item['caption'] ?? '' );
					$span    = in_array( $item['span'] ?? 'normal', array( 'wide', 'tall' ), true ) ? $item['span'] : 'normal';
					$url     = (string) ( $item['link']['url'] ?? '' );
					$full    = (string) ( $image['url'] ?? '' );
					?>
					<figure class="moon-look__tile moon-look__tile--<?php echo esc_attr( $span ); ?>" data-moon-reveal-item>
						<?php if ( $lightbox ) : ?>
							<button type="button" class="moon-look__button" data-moon-look-trigger data-src="<?php echo esc_url( $full ); ?>" data-caption="<?php echo esc_attr( $caption ); ?>" aria-label="<?php echo esc_attr( $caption ?: __( 'View image', 'moon-core' ) ); ?>">
								<?php echo wp_kses_post( Helpers::image( $image, 'moon-portrait', array( 'alt' => $caption ) ) ); ?>
							</button>
						<?php elseif ( '' !== $url ) : ?>
							<a class="moon-look__button" href="<?php echo esc_url( $url ); ?>">
								<?php echo wp_kses_post( Helpers::image( $image, 'moon-portrait', array( 'alt' => $caption ) ) ); ?>
							</a>
						<?php else : ?>
							<div class="moon-look__button">
								<?php echo wp_kses_post( Helpers::image( $image, 'moon-portrait', array( 'alt' => $caption ) ) ); ?>
							</div>
						<?php endif; ?>
						<?php if ( '' !== $caption ) : ?>
							<figcaption class="moon-look__caption"><?php echo esc_html( $caption ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
