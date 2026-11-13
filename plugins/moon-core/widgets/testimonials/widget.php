<?php
/**
 * MOON Testimonials — customer voices as an editorial slider or grid.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Testimonials extends Widget_Base {

	protected function slug(): string {
		return 'testimonials';
	}

	public function get_title(): string {
		return __( 'MOON Testimonials', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-testimonial';
	}

	public function get_keywords(): array {
		return array( 'testimonials', 'reviews', 'quotes', 'slider', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Worn & reviewed', 'moon-core' ),
				'title'       => __( 'Words from the wardrobe', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_items', array( 'label' => __( 'Testimonials', 'moon-core' ) ) );

		$item = new Repeater();
		$item->add_control(
			'quote',
			array(
				'label'       => __( 'Quote', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'The coat arrived in three days and feels like something my mother would have kept for thirty years.', 'moon-core' ),
				'label_block' => true,
			)
		);
		$item->add_control(
			'author',
			array(
				'label'   => __( 'Author', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Elise M.', 'moon-core' ),
			)
		);
		$item->add_control(
			'role',
			array(
				'label'   => __( 'Detail', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Verified purchase — Paris', 'moon-core' ),
			)
		);
		$item->add_control(
			'rating',
			array(
				'label'   => __( 'Rating', 'moon-core' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array( 'px' => array( 'min' => 0, 'max' => 5, 'step' => 1 ) ),
				'default' => array( 'size' => 5 ),
			)
		);
		$item->add_control(
			'image',
			array(
				'label'   => __( 'Customer photo (optional)', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $item->get_controls(),
				'title_field' => '{{{ author }}}',
				'default'     => array(
					array( 'quote' => __( 'The coat arrived in three days and feels like something my mother would have kept for thirty years.', 'moon-core' ), 'author' => __( 'Elise M.', 'moon-core' ), 'role' => __( 'Verified purchase — Paris', 'moon-core' ) ),
					array( 'quote' => __( 'I own three of the silk shirts. Nothing else in my wardrobe gets this much wear.', 'moon-core' ), 'author' => __( 'Nadia K.', 'moon-core' ), 'role' => __( 'Verified purchase — Amsterdam', 'moon-core' ), 'rating' => array( 'size' => 5 ) ),
					array( 'quote' => __( 'Small runs, honest fabric, and a studio that answers emails like a person.', 'moon-core' ), 'author' => __( 'Jonas R.', 'moon-core' ), 'role' => __( 'Verified purchase — Berlin', 'moon-core' ), 'rating' => array( 'size' => 4 ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slider',
				'options' => array(
					'slider' => __( 'Slider', 'moon-core' ),
					'grid'   => __( 'Grid', 'moon-core' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns (grid)', 'moon-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3' ),
				'condition'      => array( 'layout' => 'grid' ),
				'selectors'      => array( '{{WRAPPER}} .moon-testi--grid .moon-testi__track' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => __( 'Auto-advance', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'slider' ),
				'description'  => __( 'Ignored when the visitor prefers reduced motion.', 'moon-core' ),
			)
		);

		$this->add_control(
			'show_rating',
			array(
				'label'        => __( 'Show stars', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_items',
			array(
				'label' => __( 'Cards', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-testi__card' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'star_color',
			array(
				'label'     => __( 'Star colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-testi__stars' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .moon-testi__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'quote', __( 'Quote', 'moon-core' ), '.moon-testi__quote' );
		$this->moon_text_style( 'author', __( 'Author', 'moon-core' ), '.moon-testi__author' );
		$this->moon_text_style( 'role', __( 'Detail', 'moon-core' ), '.moon-testi__role' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'rating' => array( __( 'Stars', 'moon-core' ), '.moon-testi__stars' ),
				'role'   => array( __( 'Detail line', 'moon-core' ), '.moon-testi__role' ),
				'photo'  => array( __( 'Customer photo', 'moon-core' ), '.moon-testi__photo' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$items    = (array) ( $settings['items'] ?? array() );
		$layout   = 'grid' === ( $settings['layout'] ?? 'slider' ) ? 'grid' : 'slider';

		if ( empty( $items ) ) {
			return;
		}

		$show_rating = 'yes' === ( $settings['show_rating'] ?? 'yes' );
		?>
		<section class="moon-widget moon-testi moon-testi--<?php echo esc_attr( $layout ); ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<div class="moon-testi__viewport" data-moon-testi data-autoplay="<?php echo 'yes' === ( $settings['autoplay'] ?? '' ) ? '1' : '0'; ?>">
				<div class="moon-testi__track" data-moon-testi-track>
					<?php foreach ( $items as $item ) : ?>
						<?php
						$rating = isset( $item['rating']['size'] ) ? (int) $item['rating']['size'] : 0;
						$image  = (array) ( $item['image'] ?? array() );
						?>
						<figure class="moon-testi__card" data-moon-reveal-item>
							<?php if ( $show_rating && $rating > 0 ) : ?>
								<div class="moon-testi__stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating out of 5 */ __( '%d out of 5 stars', 'moon-core' ), $rating ) ); ?>">
									<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
										<span class="<?php echo $i <= $rating ? 'is-on' : ''; ?>" aria-hidden="true">★</span>
									<?php endfor; ?>
								</div>
							<?php endif; ?>
							<blockquote class="moon-testi__quote"><?php echo esc_html( (string) ( $item['quote'] ?? '' ) ); ?></blockquote>
							<figcaption class="moon-testi__meta">
								<?php if ( $image ) : ?>
									<span class="moon-testi__photo"><?php echo wp_kses_post( Helpers::image( $image, 'thumbnail', array( 'alt' => (string) ( $item['author'] ?? '' ) ) ) ); ?></span>
								<?php endif; ?>
								<span>
									<span class="moon-testi__author"><?php echo esc_html( (string) ( $item['author'] ?? '' ) ); ?></span>
									<?php if ( '' !== trim( (string) ( $item['role'] ?? '' ) ) ) : ?>
										<span class="moon-testi__role"><?php echo esc_html( $item['role'] ); ?></span>
									<?php endif; ?>
								</span>
							</figcaption>
						</figure>
					<?php endforeach; ?>
				</div>

				<?php if ( 'slider' === $layout && count( $items ) > 1 ) : ?>
					<div class="moon-testi__controls">
						<button type="button" class="moon-icon-btn" data-moon-testi-prev aria-label="<?php esc_attr_e( 'Previous testimonial', 'moon-core' ); ?>">
							<?php echo Helpers::svg( Helpers::icon( 'arrow-left', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
						<div class="moon-testi__dots" data-moon-testi-dots role="tablist" aria-label="<?php esc_attr_e( 'Choose testimonial', 'moon-core' ); ?>"></div>
						<button type="button" class="moon-icon-btn" data-moon-testi-next aria-label="<?php esc_attr_e( 'Next testimonial', 'moon-core' ); ?>">
							<?php echo Helpers::svg( Helpers::icon( 'arrow-right', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
