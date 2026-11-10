<?php
/**
 * Elementor integration: widget category and widgets.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Moon widget category and every Moon widget.
 */
class Moon_Core_Elementor {

	/**
	 * Register hooks. Widgets are only loaded when Elementor is available so the
	 * site keeps working if Elementor is disabled.
	 */
	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( __CLASS__, 'register_preview_styles' ) );
	}

	/**
	 * Add the MOON category to the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor manager.
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'moon-core',
			array(
				'title' => __( 'MOON', 'moon-core' ),
				'icon'  => 'eicon-nerd',
			)
		);
	}

	/**
	 * Register the plugin stylesheet for the editor preview iframe.
	 */
	public static function register_preview_styles() {
		wp_register_style( 'moon-core', MOON_CORE_URL . 'assets/css/moon-core.css', array(), MOON_CORE_VERSION );
		wp_register_style( 'moon-commerce', MOON_CORE_URL . 'assets/css/moon-commerce.css', array( 'moon-core' ), MOON_CORE_VERSION );
	}

	/**
	 * Instantiate each widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor manager.
	 */
	public static function register_widgets( $widgets_manager ) {
		// The base class extends Elementor's Widget_Base, so it may only be loaded
		// once Elementor itself is available.
		require_once MOON_CORE_PATH . 'includes/class-widget-base.php';
		require_once MOON_CORE_PATH . 'widgets/class-glass-header.php';
		require_once MOON_CORE_PATH . 'widgets/class-cinematic-hero.php';
		require_once MOON_CORE_PATH . 'widgets/class-category-cards.php';
		require_once MOON_CORE_PATH . 'widgets/class-product-grid.php';
		require_once MOON_CORE_PATH . 'widgets/class-campaign-banner.php';
		require_once MOON_CORE_PATH . 'widgets/class-brand-story.php';
		require_once MOON_CORE_PATH . 'widgets/class-benefits.php';

		$widgets_manager->register( new \Moon_Glass_Header() );
		$widgets_manager->register( new \Moon_Cinematic_Hero() );
		$widgets_manager->register( new \Moon_Category_Cards() );
		$widgets_manager->register( new \Moon_Product_Grid() );
		$widgets_manager->register( new \Moon_Campaign_Banner() );
		$widgets_manager->register( new \Moon_Brand_Story() );
		$widgets_manager->register( new \Moon_Benefits() );
	}
}
