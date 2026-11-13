<?php
/**
 * MOON Footer widget — premium editorial footer with configurable columns.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use MoonCore\Site_Chrome;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Footer extends Widget_Base {

	protected function slug(): string {
		return 'footer';
	}

	public function get_title(): string {
		return __( 'MOON Footer', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-footer';
	}

	public function get_keywords(): array {
		return array( 'footer', 'moon', 'links' );
	}

	private function menu_options(): array {
		$options = array( '' => __( 'Use the default MOON links', 'moon-core' ) );

		foreach ( get_registered_nav_menus() as $location => $label ) {
			$options[ $location ] = $label;
		}

		return $options;
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'section_brand', array( 'label' => __( 'Brand', 'moon-core' ) ) );

		$this->add_control(
			'brand_name',
			array(
				'label'   => __( 'Brand name', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'MOON',
			)
		);

		$this->add_control(
			'blurb',
			array(
				'label'       => __( 'Short paragraph', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'Considered garments, made in small runs. Designed in the studio, worn everywhere else.', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'menu_location',
			array(
				'label'   => __( 'Footer menu', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->menu_options(),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_columns', array( 'label' => __( 'Link columns', 'moon-core' ) ) );

		$columns = new Repeater();
		$columns->add_control(
			'column_title',
			array(
				'label'       => __( 'Column title', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Shop', 'moon-core' ),
				'label_block' => true,
			)
		);

		$links = new Repeater();
		$links->add_control(
			'link_label',
			array(
				'label'       => __( 'Label', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'New in', 'moon-core' ),
				'label_block' => true,
			)
		);
		$links->add_control(
			'link_url',
			array(
				'label'       => __( 'Link', 'moon-core' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'default'     => array( 'url' => '' ),
			)
		);

		$columns->add_control(
			'column_links',
			array(
				'label'       => __( 'Links', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $links->get_controls(),
				'title_field' => '{{{ link_label }}}',
				'default'     => array(
					array( 'link_label' => __( 'Women', 'moon-core' ) ),
					array( 'link_label' => __( 'Men', 'moon-core' ) ),
					array( 'link_label' => __( 'Accessories', 'moon-core' ) ),
				),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'       => __( 'Columns', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $columns->get_controls(),
				'title_field' => '{{{ column_title }}}',
				'default'     => array(
					array(
						'column_title' => __( 'Shop', 'moon-core' ),
						'column_links' => array(
							array( 'link_label' => __( 'Women', 'moon-core' ) ),
							array( 'link_label' => __( 'Men', 'moon-core' ) ),
							array( 'link_label' => __( 'Accessories', 'moon-core' ) ),
						),
					),
					array(
						'column_title' => __( 'Atelier', 'moon-core' ),
						'column_links' => array(
							array( 'link_label' => __( 'Our story', 'moon-core' ) ),
							array( 'link_label' => __( 'Journal', 'moon-core' ) ),
							array( 'link_label' => __( 'Contact', 'moon-core' ) ),
						),
					),
				),
			)
		);

		$socials = new Repeater();
		$socials->add_control(
			'label',
			array(
				'label' => __( 'Network', 'moon-core' ),
				'type'  => Controls_Manager::TEXT,
				'default' => 'Instagram',
			)
		);
		$socials->add_control(
			'url',
			array(
				'label'   => __( 'URL', 'moon-core' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => 'https://instagram.com/' ),
			)
		);

		$this->add_control(
			'socials',
			array(
				'label'       => __( 'Social links', 'moon-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $socials->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'label' => 'Instagram', 'url' => array( 'url' => 'https://instagram.com/' ) ),
					array( 'label' => 'Pinterest', 'url' => array( 'url' => 'https://pinterest.com/' ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_newsletter', array( 'label' => __( 'Newsletter strip', 'moon-core' ) ) );

		$this->add_control(
			'show_newsletter',
			array(
				'label'        => __( 'Show newsletter', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'newsletter_title',
			array(
				'label'     => __( 'Title', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Join the list', 'moon-core' ),
				'condition' => array( 'show_newsletter' => 'yes' ),
			)
		);

		$this->add_control(
			'newsletter_note',
			array(
				'label'     => __( 'Note', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'New drops, studio notes, no noise.', 'moon-core' ),
				'condition' => array( 'show_newsletter' => 'yes' ),
			)
		);

		$this->add_control(
			'note',
			array(
				'label'     => __( 'Legal line', 'moon-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => sprintf( /* translators: %s: year */ __( '© %s MOON. All rights reserved.', 'moon-core' ), gmdate( 'Y' ) ),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		/* Style ---------------------------------------------------------- */

		$this->start_controls_section(
			'style_footer',
			array(
				'label' => __( 'Footer panel', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'footer_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-footer' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'footer_ink',
			array(
				'label'     => __( 'Text colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-footer' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'footer_padding',
			array(
				'label'      => __( 'Padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .moon-footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'footer_brand', __( 'Brand', 'moon-core' ), '.moon-footer .moon-brand__word' );
		$this->moon_text_style( 'footer_note', __( 'Legal line', 'moon-core' ), '.moon-footer__bottom' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'brand'      => array( __( 'Brand block', 'moon-core' ), '.moon-footer__brand' ),
				'columns'    => array( __( 'Link columns', 'moon-core' ), '.moon-footer__cols' ),
				'newsletter' => array( __( 'Newsletter strip', 'moon-core' ), '.moon-footer__newsletter' ),
				'legal'      => array( __( 'Legal line', 'moon-core' ), '.moon-footer__bottom' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$columns = array();
		foreach ( (array) ( $settings['columns'] ?? array() ) as $column ) {
			$links = array();
			foreach ( (array) ( $column['column_links'] ?? array() ) as $link ) {
				$links[] = array(
					'label' => (string) ( $link['link_label'] ?? '' ),
					'url'   => (string) ( $link['link_url']['url'] ?? '' ),
				);
			}
			$columns[] = array(
				'title' => (string) ( $column['column_title'] ?? '' ),
				'links' => $links,
			);
		}

		$socials = array();
		foreach ( (array) ( $settings['socials'] ?? array() ) as $social ) {
			$socials[] = array(
				'label' => (string) ( $social['label'] ?? '' ),
				'url'   => (string) ( $social['url']['url'] ?? '' ),
			);
		}

		Site_Chrome::render_footer(
			array(
				'brand_name'       => (string) ( $settings['brand_name'] ?? '' ),
				'blurb'            => (string) ( $settings['blurb'] ?? '' ),
				'menu_location'    => (string) ( $settings['menu_location'] ?? '' ),
				'columns'          => $columns,
				'socials'          => $socials,
				'note'             => (string) ( $settings['note'] ?? '' ),
				'show_newsletter'  => 'yes' === ( $settings['show_newsletter'] ?? 'yes' ),
				'newsletter_title' => (string) ( $settings['newsletter_title'] ?? '' ),
				'newsletter_note'  => (string) ( $settings['newsletter_note'] ?? '' ),
			)
		);
	}
}
