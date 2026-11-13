<?php
/**
 * MOON Category Cards — women/men (or any product category) editorial tiles.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Category_Cards extends Widget_Base {

	protected function slug(): string {
		return 'category-cards';
	}

	public function get_title(): string {
		return __( 'MOON Category Cards', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	public function get_keywords(): array {
		return array( 'categories', 'women', 'men', 'collection', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Heading', 'moon-core' ),
			array(
				'eyebrow'     => __( 'Shop by world', 'moon-core' ),
				'title'       => __( 'Wardrobe directions', 'moon-core' ),
				'description' => '',
			)
		);

		$this->start_controls_section( 'section_cards', array( 'label' => __( 'Cards', 'moon-core' ) ) );

		$card = new Repeater();
		$card->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'moon-core' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);
		$card->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Women', 'moon-core' ),
				'label_block' => true,
			)
		);
		$card->add_control(
			'subtitle',
			array(
				'label'       => __( 'Subtitle', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '42 pieces', 'moon-core' ),
				'label_block' => true,
			)
		);
		$card->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'moon-core' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);
		$card->add_control(
			'wide',
			array(
				'label'        => __( 'Wide tile', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'cards',
			array(
				'label'       => __( 'Cards', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $card->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'Women', 'moon-core' ), 'subtitle' => __( 'The winter edit', 'moon-core' ) ),
					array( 'title' => __( 'Men', 'moon-core' ), 'subtitle' => __( 'Tailoring & knitwear', 'moon-core' ) ),
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
				'default'        => '2',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .moon-cats__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_responsive_control(
			'ratio',
			array(
				'label'      => __( 'Tile ratio', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0.6, 'max' => 1.8, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1.18 ),
				'selectors'  => array( '{{WRAPPER}} .moon-cat' => 'aspect-ratio: {{SIZE}};' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'default'    => array( 'size' => 20 ),
				'selectors'  => array( '{{WRAPPER}} .moon-cats__grid' => 'gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .moon-cat' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_overlay',
			array(
				'label' => __( 'Overlay', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'overlay_strength',
			array(
				'label'     => __( 'Gradient strength', 'moon-core' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 62 ),
				'selectors' => array( '{{WRAPPER}} .moon-cat__overlay' => 'opacity: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Gradient colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#101010',
				'selectors' => array( '{{WRAPPER}} .moon-cat__overlay' => 'background: linear-gradient(180deg, transparent 35%, {{VALUE}}e6 100%);' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'card_title', __( 'Card title', 'moon-core' ), '.moon-cat__title' );
		$this->moon_text_style( 'card_sub', __( 'Card subtitle', 'moon-core' ), '.moon-cat__sub' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'subtitle' => array( __( 'Subtitles', 'moon-core' ), '.moon-cat__sub' ),
				'cta'      => array( __( 'Card link label', 'moon-core' ), '.moon-cat__cta' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$cards    = (array) ( $settings['cards'] ?? array() );

		if ( empty( $cards ) ) {
			echo '<p class="moon-widget-notice">' . esc_html__( 'Add at least one category card.', 'moon-core' ) . '</p>';
			return;
		}
		?>
		<section class="moon-widget moon-cats"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<div class="moon-cats__grid">
				<?php foreach ( $cards as $card ) : ?>
					<?php
					$link  = (array) ( $card['link'] ?? array() );
					$url   = $link['url'] ?? '#';
					$wide  = 'yes' === ( $card['wide'] ?? '' );
					$image = (array) ( $card['image'] ?? array() );
					?>
					<a class="moon-cat<?php echo $wide ? ' moon-cat--wide' : ''; ?>" href="<?php echo esc_url( $url ); ?>" data-moon-reveal-item>
						<span class="moon-cat__media">
							<?php
							if ( $image ) {
								echo wp_kses_post( Helpers::image( $image, 'moon-portrait', array( 'alt' => (string) ( $card['title'] ?? '' ) ) ) );
							}
							?>
						</span>
						<span class="moon-cat__overlay" aria-hidden="true"></span>
						<span class="moon-cat__body">
							<?php if ( '' !== trim( (string) ( $card['subtitle'] ?? '' ) ) ) : ?>
								<span class="moon-cat__sub"><?php echo esc_html( $card['subtitle'] ); ?></span>
							<?php endif; ?>
							<span class="moon-cat__title"><?php echo esc_html( (string) ( $card['title'] ?? '' ) ); ?></span>
							<span class="moon-cat__cta"><?php esc_html_e( 'Shop now', 'moon-core' ); ?>
								<?php echo Helpers::svg( Helpers::icon( 'arrow-right', 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
