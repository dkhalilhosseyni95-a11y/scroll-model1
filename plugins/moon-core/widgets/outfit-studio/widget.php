<?php
/**
 * MOON Outfit Studio — one stable model, prepared outfit frames, crossfade.
 *
 * The images are matching photographs of the same model/pose; the widget never
 * fakes a garment transformation, it crossfades between prepared frames.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Outfit_Studio extends Widget_Base {

	protected function slug(): string {
		return 'outfit-studio';
	}

	public function get_title(): string {
		return __( 'MOON Outfit Studio', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-slider-album';
	}

	public function get_keywords(): array {
		return array( 'outfit', 'look', 'studio', 'model', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Outfit studio', 'moon-core' ),
				'title'       => __( 'Build the look', 'moon-core' ),
				'description' => __( 'Five ways to wear the season, photographed on the same model in the same light.', 'moon-core' ),
			)
		);

		$this->start_controls_section( 'section_outfits', array( 'label' => __( 'Outfits', 'moon-core' ) ) );

		$outfit = new Repeater();
		$outfit->add_control(
			'image',
			array(
				'label'       => __( 'Outfit image', 'moon-core' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Use frames with identical framing and scale for a seamless transition.', 'moon-core' ),
			)
		);
		$outfit->add_control(
			'title',
			array(
				'label'       => __( 'Outfit name', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Look 01 — The Ivory Set', 'moon-core' ),
				'label_block' => true,
			)
		);
		$outfit->add_control(
			'color',
			array(
				'label'   => __( 'Colour', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Ivory / Camel', 'moon-core' ),
			)
		);
		$outfit->add_control(
			'price',
			array(
				'label'   => __( 'Price', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( '€640', 'moon-core' ),
			)
		);
		$outfit->add_control(
			'note',
			array(
				'label'   => __( 'One-line note', 'moon-core' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'Silk shirt, wide wool trouser, leather belt.', 'moon-core' ),
			)
		);
		$outfit->add_control(
			'link',
			array(
				'label'   => __( 'Shop this look', 'moon-core' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'outfits',
			array(
				'label'       => __( 'Outfits', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $outfit->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'Look 01 — The Ivory Set', 'moon-core' ), 'color' => __( 'Ivory / Camel', 'moon-core' ), 'price' => __( '€640', 'moon-core' ), 'note' => __( 'Silk shirt, wide wool trouser, leather belt.', 'moon-core' ) ),
					array( 'title' => __( 'Look 02 — Charcoal Tailoring', 'moon-core' ), 'color' => __( 'Charcoal', 'moon-core' ), 'price' => __( '€780', 'moon-core' ), 'note' => __( 'Oversized blazer over a fine knit.', 'moon-core' ) ),
					array( 'title' => __( 'Look 03 — Camel Coat', 'moon-core' ), 'color' => __( 'Camel / Black', 'moon-core' ), 'price' => __( '€920', 'moon-core' ), 'note' => __( 'Long coat, silk shirt, tailored trouser.', 'moon-core' ) ),
					array( 'title' => __( 'Look 04 — Evening Silk', 'moon-core' ), 'color' => __( 'Black', 'moon-core' ), 'price' => __( '€560', 'moon-core' ), 'note' => __( 'Bias-cut silk with a narrow belt.', 'moon-core' ) ),
					array( 'title' => __( 'Look 05 — Knit & Leather', 'moon-core' ), 'color' => __( 'Oat / Black', 'moon-core' ), 'price' => __( '€480', 'moon-core' ), 'note' => __( 'Cropped knit over leather tailoring.', 'moon-core' ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_behaviour', array( 'label' => __( 'Studio', 'moon-core' ) ) );

		$this->add_control(
			'transition',
			array(
				'label'   => __( 'Transition', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'mask',
				'options' => array(
					'mask'      => __( 'Masked wipe', 'moon-core' ),
					'crossfade' => __( 'Crossfade', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'thumbnails',
			array(
				'label'   => __( 'Outfit selector', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'below',
				'options' => array(
					'below' => __( 'Thumbnails under the stage', 'moon-core' ),
					'side'  => __( 'Thumbnails beside the copy', 'moon-core' ),
					'dots'  => __( 'Dots only', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'        => __( 'Show arrows', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Stage ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.5, 'max' => 1.6, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 0.75 ),
				'selectors'  => array( '{{WRAPPER}} .moon-studio__stage' => 'aspect-ratio: {{SIZE}};' ),
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
				'selectors'  => array( '{{WRAPPER}} .moon-studio__stage' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Outfit name', 'moon-core' ), '.moon-studio__title' );
		$this->moon_text_style( 'meta', __( 'Colour & price', 'moon-core' ), '.moon-studio__meta' );
		$this->moon_button_style( 'link', __( 'Shop link', 'moon-core' ), '.moon-studio__btn' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'note'    => array( __( 'Note', 'moon-core' ), '.moon-studio__note' ),
				'arrows'  => array( __( 'Arrows', 'moon-core' ), '.moon-studio__arrow' ),
				'counter' => array( __( 'Counter', 'moon-core' ), '.moon-studio__counter' ),
				'thumbs'  => array( __( 'Thumbnails', 'moon-core' ), '.moon-studio__thumbs' ),
			)
		);
	}

	protected function render(): void {
		$settings   = $this->get_settings_for_display();
		$outfits    = (array) ( $settings['outfits'] ?? array() );
		$transition = 'crossfade' === ( $settings['transition'] ?? 'mask' ) ? 'crossfade' : 'mask';
		$thumbs     = in_array( $settings['thumbnails'] ?? 'below', array( 'below', 'side', 'dots' ), true ) ? $settings['thumbnails'] : 'below';

		if ( empty( $outfits ) ) {
			echo '<p class="moon-widget-notice">' . esc_html__( 'Add at least one outfit frame.', 'moon-core' ) . '</p>';
			return;
		}

		$first = (array) $outfits[0];
		$total = count( $outfits );
		?>
		<section
			class="moon-widget moon-studio moon-studio--thumbs-<?php echo esc_attr( $thumbs ); ?>"
			data-moon-studio
			data-transition="<?php echo esc_attr( $transition ); ?>"
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

			<div class="moon-studio__grid">
				<div class="moon-studio__stage-col">
					<div class="moon-studio__stage" data-moon-studio-stage>
						<?php foreach ( $outfits as $index => $outfit ) : ?>
							<figure class="moon-studio__frame<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-studio-frame="<?php echo esc_attr( (string) $index ); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>">
								<?php
								$image = (array) ( $outfit['image'] ?? array() );
								if ( $image ) {
									echo wp_kses_post(
										Helpers::image(
											$image,
											'moon-portrait',
											array(
												'alt'     => (string) ( $outfit['title'] ?? '' ),
												'loading' => 0 === $index ? 'eager' : 'lazy',
											)
										)
									);
								}
								?>
							</figure>
						<?php endforeach; ?>

						<?php if ( 'yes' === ( $settings['show_arrows'] ?? 'yes' ) ) : ?>
							<button type="button" class="moon-icon-btn moon-studio__arrow moon-studio__arrow--prev" data-moon-studio-prev aria-label="<?php esc_attr_e( 'Previous outfit', 'moon-core' ); ?>">
								<?php echo Helpers::svg( Helpers::icon( 'arrow-left', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
							<button type="button" class="moon-icon-btn moon-studio__arrow moon-studio__arrow--next" data-moon-studio-next aria-label="<?php esc_attr_e( 'Next outfit', 'moon-core' ); ?>">
								<?php echo Helpers::svg( Helpers::icon( 'arrow-right', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
						<?php endif; ?>

						<span class="moon-studio__counter" aria-hidden="true">
							<span data-moon-studio-current>01</span><span class="moon-studio__counter-sep">/</span><span><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
						</span>
					</div>

					<?php if ( 'dots' === $thumbs ) : ?>
						<div class="moon-studio__dots" data-moon-studio-dots role="tablist" aria-label="<?php esc_attr_e( 'Choose outfit', 'moon-core' ); ?>"></div>
					<?php endif; ?>
				</div>

				<div class="moon-studio__panel">
					<p class="moon-eyebrow"><?php esc_html_e( 'Outfit studio', 'moon-core' ); ?></p>
					<h3 class="moon-studio__title" data-moon-studio-title><?php echo esc_html( (string) ( $first['title'] ?? '' ) ); ?></h3>
					<p class="moon-studio__meta">
						<span data-moon-studio-color><?php echo esc_html( (string) ( $first['color'] ?? '' ) ); ?></span>
						<span class="moon-studio__sep" aria-hidden="true">—</span>
						<span data-moon-studio-price><?php echo esc_html( (string) ( $first['price'] ?? '' ) ); ?></span>
					</p>
					<p class="moon-studio__note" data-moon-studio-note><?php echo esc_html( (string) ( $first['note'] ?? '' ) ); ?></p>

					<?php
					$first_link = (string) ( $first['link']['url'] ?? '' );
					printf(
						'<a class="moon-btn moon-btn--solid moon-studio__btn" href="%1$s" data-moon-studio-link>%2$s</a>',
						esc_url( $first_link ? $first_link : '#' ),
						esc_html__( 'Shop this look', 'moon-core' )
					);
					?>

					<?php if ( 'dots' !== $thumbs ) : ?>
						<div class="moon-studio__thumbs" data-moon-studio-thumbs role="tablist" aria-label="<?php esc_attr_e( 'Outfits', 'moon-core' ); ?>">
							<?php foreach ( $outfits as $index => $outfit ) : ?>
								<?php
								$image = (array) ( $outfit['image'] ?? array() );
								$link  = (string) ( $outfit['link']['url'] ?? '' );
								?>
								<button
									type="button"
									class="moon-studio__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
									data-moon-studio-thumb="<?php echo esc_attr( (string) $index ); ?>"
									data-title="<?php echo esc_attr( (string) ( $outfit['title'] ?? '' ) ); ?>"
									data-color="<?php echo esc_attr( (string) ( $outfit['color'] ?? '' ) ); ?>"
									data-price="<?php echo esc_attr( (string) ( $outfit['price'] ?? '' ) ); ?>"
									data-note="<?php echo esc_attr( (string) ( $outfit['note'] ?? '' ) ); ?>"
									data-link="<?php echo esc_url( $link ); ?>"
									role="tab"
									aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
									aria-label="<?php echo esc_attr( (string) ( $outfit['title'] ?? '' ) ); ?>"
								>
									<?php
									if ( $image ) {
										echo wp_kses_post( Helpers::image( $image, 'thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ) );
									}
									?>
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
