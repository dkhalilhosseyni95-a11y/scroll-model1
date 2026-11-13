<?php
/**
 * MOON Benefits — shopping reassurances (shipping, returns, craft, service).
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Benefits extends Widget_Base {

	protected function slug(): string {
		return 'benefits';
	}

	public function get_title(): string {
		return __( 'MOON Benefits', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	public function get_keywords(): array {
		return array( 'benefits', 'shipping', 'returns', 'icons', 'moon' );
	}

	/**
	 * Icon choices shared by icon-based widgets.
	 *
	 * @return array<string,string>
	 */
	public static function icon_options(): array {
		return array(
			'bag'      => __( 'Shopping bag', 'moon-core' ),
			'check'    => __( 'Check', 'moon-core' ),
			'heart'    => __( 'Heart', 'moon-core' ),
			'arrow-up' => __( 'Arrow up (returns)', 'moon-core' ),
			'play'     => __( 'Play', 'moon-core' ),
			'search'   => __( 'Search', 'moon-core' ),
			'moon'     => __( 'Moon', 'moon-core' ),
			'sun'      => __( 'Sun', 'moon-core' ),
			'plus'     => __( 'Plus', 'moon-core' ),
		);
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_items', array( 'label' => __( 'Benefits', 'moon-core' ) ) );

		$item = new Repeater();
		$item->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bag',
				'options' => self::icon_options(),
			)
		);
		$item->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Complimentary shipping', 'moon-core' ),
				'label_block' => true,
			)
		);
		$item->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'On all orders over €150, worldwide.', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $item->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'bag', 'title' => __( 'Complimentary shipping', 'moon-core' ), 'text' => __( 'On all orders over €150, worldwide.', 'moon-core' ) ),
					array( 'icon' => 'arrow-up', 'title' => __( '30-day returns', 'moon-core' ), 'text' => __( 'Post it back free, keep the packaging.', 'moon-core' ) ),
					array( 'icon' => 'heart', 'title' => __( 'Made in small runs', 'moon-core' ), 'text' => __( 'Family mills, traceable wool, no overproduction.', 'moon-core' ) ),
					array( 'icon' => 'check', 'title' => __( 'Personal styling', 'moon-core' ), 'text' => __( 'Our studio answers within a day.', 'moon-core' ) ),
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
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .moon-benefits__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards'    => __( 'Cards', 'moon-core' ),
					'dividers' => __( 'Divided columns', 'moon-core' ),
					'plain'    => __( 'Plain', 'moon-core' ),
				),
				'prefix_class' => 'moon-benefits--',
			)
		);

		$this->add_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'moon-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array( 'title' => __( 'Start', 'moon-core' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Centre', 'moon-core' ), 'icon' => 'eicon-text-align-center' ),
				),
				'default'   => 'start',
				'selectors' => array( '{{WRAPPER}} .moon-benefit' => 'align-items: {{VALUE}}; text-align: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'      => __( 'Icon size', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 16, 'max' => 48 ) ),
				'default'    => array( 'size' => 22 ),
				'selectors'  => array( '{{WRAPPER}} .moon-benefit__icon svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_items',
			array(
				'label' => __( 'Item styling', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Card background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-benefit' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-benefit__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .moon-benefit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-benefit__title' );
		$this->moon_text_style( 'text', __( 'Text', 'moon-core' ), '.moon-benefit__text' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'icons' => array( __( 'Icons', 'moon-core' ), '.moon-benefit__icon' ),
				'text'  => array( __( 'Descriptions', 'moon-core' ), '.moon-benefit__text' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$items    = (array) ( $settings['items'] ?? array() );

		if ( empty( $items ) ) {
			return;
		}
		?>
		<section class="moon-widget moon-benefits"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="moon-benefits__grid">
				<?php foreach ( $items as $item ) : ?>
					<article class="moon-benefit" data-moon-reveal-item>
						<span class="moon-benefit__icon" aria-hidden="true">
							<?php echo Helpers::svg( Helpers::icon( (string) ( $item['icon'] ?? 'check' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						</span>
						<h3 class="moon-benefit__title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></h3>
						<?php if ( '' !== trim( (string) ( $item['text'] ?? '' ) ) ) : ?>
							<p class="moon-benefit__text"><?php echo esc_html( $item['text'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
