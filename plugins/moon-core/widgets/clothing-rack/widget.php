<?php
/**
 * MOON Interactive Clothing Rack.
 *
 * A rail of hanging garments: each piece swings with its own rhythm, lifts on
 * hover and, when selected, steps forward into a detail panel. Everything is
 * CSS transforms plus a few lines of JavaScript — no WebGL, no 3D library.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Clothing_Rack extends Widget_Base {

	protected function slug(): string {
		return 'clothing-rack';
	}

	public function get_title(): string {
		return __( 'MOON Clothing Rack', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-woocommerce';
	}

	public function get_keywords(): array {
		return array( 'rack', 'hanger', 'garments', 'interactive', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'The rail', 'moon-core' ),
				'title'       => __( 'Six pieces, one rail', 'moon-core' ),
				'description' => __( 'Hover to let a garment move. Select to lift it from the rail.', 'moon-core' ),
			)
		);

		$this->start_controls_section( 'section_garments', array( 'label' => __( 'Garments', 'moon-core' ) ) );

		$garment = new Repeater();
		$garment->add_control(
			'image',
			array(
				'label'   => __( 'Garment image (transparent PNG works best)', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);
		$garment->add_control(
			'name',
			array(
				'label'       => __( 'Name', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'The Coat', 'moon-core' ),
				'label_block' => true,
			)
		);
		$garment->add_control(
			'meta',
			array(
				'label'   => __( 'Detail (fabric, price…)', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Wool — €480', 'moon-core' ),
			)
		);
		$garment->add_control(
			'description',
			array(
				'label'       => __( 'Selected description', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Double-faced wool, unlined, cut long and narrow.', 'moon-core' ),
			)
		);
		$garment->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'moon-core' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'garments',
			array(
				'label'       => __( 'Garments', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $garment->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'name' => __( 'The Coat', 'moon-core' ), 'meta' => __( 'Wool — €480', 'moon-core' ), 'description' => __( 'Double-faced wool, unlined, cut long and narrow.', 'moon-core' ) ),
					array( 'name' => __( 'The Jacket', 'moon-core' ), 'meta' => __( 'Cotton twill — €320', 'moon-core' ), 'description' => __( 'A boxy workwear cut with a hand-finished collar.', 'moon-core' ) ),
					array( 'name' => __( 'The Shirt', 'moon-core' ), 'meta' => __( 'Silk — €210', 'moon-core' ), 'description' => __( 'Sandwashed silk, mother-of-pearl buttons.', 'moon-core' ) ),
					array( 'name' => __( 'The Hoodie', 'moon-core' ), 'meta' => __( 'Loopback — €180', 'moon-core' ), 'description' => __( 'Heavy loopback cotton, raw seams, unisex.', 'moon-core' ) ),
					array( 'name' => __( 'The Sweat', 'moon-core' ), 'meta' => __( 'Fleece — €160', 'moon-core' ), 'description' => __( 'Brushed fleece in a warm oatmeal tone.', 'moon-core' ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Detail panel', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'side',
				'options' => array(
					'side'  => __( 'Beside the rail', 'moon-core' ),
					'below' => __( 'Below the rail', 'moon-core' ),
				),
			)
		);

		$this->add_responsive_control(
			'garment_width',
			array(
				'label'      => __( 'Garment width', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 90, 'max' => 260 ) ),
				'default'    => array( 'size' => 150 ),
				'selectors'  => array( '{{WRAPPER}} .moon-rack__garment' => 'width: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'rail_height',
			array(
				'label'      => __( 'Rail height', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 260, 'max' => 700 ),
					'vh' => array( 'min' => 30, 'max' => 80 ),
				),
				'default'    => array( 'size' => 430, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .moon-rack__rail' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'show_hanger',
			array(
				'label'        => __( 'Show hangers', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'swing',
			array(
				'label'        => __( 'Ambient swing', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Disabled automatically for reduced-motion visitors.', 'moon-core' ),
			)
		);

		$this->add_control(
			'stage_bg',
			array(
				'label'     => __( 'Stage background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-rack__rail' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'rail_color',
			array(
				'label'     => __( 'Rail colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-rack__bar' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Stage radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .moon-rack__rail' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'name', __( 'Garment name', 'moon-core' ), '.moon-rack__name' );
		$this->moon_text_style( 'meta', __( 'Garment detail', 'moon-core' ), '.moon-rack__meta' );
		$this->moon_button_style( 'detail_btn', __( 'Detail button', 'moon-core' ), '.moon-rack__detail-btn' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'labels' => array( __( 'Garment labels', 'moon-core' ), '.moon-rack__label' ),
				'hanger' => array( __( 'Hangers', 'moon-core' ), '.moon-rack__hanger' ),
				'detail' => array( __( 'Detail description', 'moon-core' ), '.moon-rack__detail-desc' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$garments = (array) ( $settings['garments'] ?? array() );
		$layout   = 'below' === ( $settings['layout'] ?? 'side' ) ? 'below' : 'side';

		if ( empty( $garments ) ) {
			echo '<p class="moon-widget-notice">' . esc_html__( 'Add garments to build the rack.', 'moon-core' ) . '</p>';
			return;
		}
		?>
		<section
			class="moon-widget moon-rack moon-rack--<?php echo esc_attr( $layout ); ?><?php echo 'yes' === ( $settings['show_hanger'] ?? 'yes' ) ? ' has-hangers' : ''; ?><?php echo 'yes' === ( $settings['swing'] ?? 'yes' ) ? ' has-swing' : ''; ?>"
			data-moon-rack
			<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		>
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

			<div class="moon-rack__grid">
				<div class="moon-rack__rail">
					<span class="moon-rack__bar" aria-hidden="true"></span>
					<ul class="moon-rack__items" role="list">
						<?php foreach ( $garments as $index => $garment ) : ?>
							<?php
							$image_id = 'moon-rack-detail-' . $this->get_id() . '-' . $index;
							$image    = (array) ( $garment['image'] ?? array() );
							?>
							<li class="moon-rack__item" data-moon-rack-item style="--moon-sway-delay: <?php echo esc_attr( (string) ( $index * 0.6 ) ); ?>s;">
								<button
									type="button"
									class="moon-rack__garment"
									data-moon-rack-trigger
									aria-pressed="false"
									aria-controls="<?php echo esc_attr( $image_id ); ?>"
									data-moon-index="<?php echo esc_attr( (string) $index ); ?>"
								>
									<?php if ( 'yes' === ( $settings['show_hanger'] ?? 'yes' ) ) : ?>
										<span class="moon-rack__hanger" aria-hidden="true">
											<svg viewBox="0 0 80 44" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
												<path d="M40 6c0-3 2.4-5 5-5 2.4 0 4 1.6 4 3.6 0 2-1.6 3.4-4 4.4L40 26"/>
												<path d="M40 26 6 40h68L40 26z"/>
											</svg>
										</span>
									<?php endif; ?>
									<span class="moon-rack__cloth">
										<?php
										if ( $image ) {
											echo wp_kses_post( Helpers::image( $image, 'moon-portrait', array( 'alt' => (string) ( $garment['name'] ?? '' ) ) ) );
										}
										?>
									</span>
									<span class="moon-rack__label">
										<span class="moon-rack__name"><?php echo esc_html( (string) ( $garment['name'] ?? '' ) ); ?></span>
										<span class="moon-rack__meta"><?php echo esc_html( (string) ( $garment['meta'] ?? '' ) ); ?></span>
									</span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="moon-rack__detail-wrap">
					<?php foreach ( $garments as $index => $garment ) : ?>
						<?php
						$image_id = 'moon-rack-detail-' . $this->get_id() . '-' . $index;
						$image    = (array) ( $garment['image'] ?? array() );
						?>
						<article
							class="moon-rack__detail<?php echo 0 === $index ? ' is-active' : ''; ?>"
							id="<?php echo esc_attr( $image_id ); ?>"
							data-moon-rack-detail
							data-moon-index="<?php echo esc_attr( (string) $index ); ?>"
							<?php echo 0 === $index ? '' : 'aria-hidden="true"'; ?>
						>
							<div class="moon-rack__detail-media">
								<?php
								if ( $image ) {
									echo wp_kses_post( Helpers::image( $image, 'moon-product', array( 'alt' => (string) ( $garment['name'] ?? '' ), 'loading' => 0 === $index ? 'eager' : 'lazy' ) ) );
								}
								?>
							</div>
							<div class="moon-rack__detail-copy">
								<p class="moon-eyebrow"><?php esc_html_e( 'Selected from the rail', 'moon-core' ); ?></p>
								<h3 class="moon-rack__detail-title"><?php echo esc_html( (string) ( $garment['name'] ?? '' ) ); ?></h3>
								<p class="moon-rack__detail-meta"><?php echo esc_html( (string) ( $garment['meta'] ?? '' ) ); ?></p>
								<?php if ( '' !== trim( (string) ( $garment['description'] ?? '' ) ) ) : ?>
									<p class="moon-rack__detail-desc"><?php echo esc_html( $garment['description'] ); ?></p>
								<?php endif; ?>
								<div class="moon-rack__detail-actions">
									<?php
									$this->render_link(
										(array) ( $garment['link'] ?? array() ),
										'moon-btn moon-btn--solid moon-rack__detail-btn',
										__( 'View the piece', 'moon-core' ),
										'arrow-right'
									);
									?>
									<button type="button" class="moon-link-underline moon-rack__return" data-moon-rack-return>
										<?php esc_html_e( 'Return to rack', 'moon-core' ); ?>
									</button>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
