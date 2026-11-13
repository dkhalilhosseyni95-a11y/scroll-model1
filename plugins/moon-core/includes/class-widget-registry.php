<?php
/**
 * Widget catalogue.
 *
 * Adding a widget = one folder under /widgets plus one entry in definitions().
 * Folder convention: widgets/<slug>/widget.php (class MoonCore\Widgets\<Class>),
 * optional <slug>.css and <slug>.js next to it.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Widget_Registry {

	private static ?Widget_Registry $instance = null;

	/** @var array<string,array<string,mixed>>|null */
	private ?array $cache = null;

	public static function instance(): Widget_Registry {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	public function init(): void {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Category definitions for the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $manager Elements manager.
	 */
	public function register_categories( $manager ): void {
		$categories = array(
			'moon-brand'   => __( 'MOON — Brand', 'moon-core' ),
			'moon-fashion' => __( 'MOON — Fashion', 'moon-core' ),
			'moon-shop'    => __( 'MOON — Store', 'moon-core' ),
		);

		foreach ( $categories as $slug => $label ) {
			$manager->add_category(
				$slug,
				array(
					'title' => $label,
					'icon'  => 'eicon-nerd',
				)
			);
		}
	}

	/**
	 * Full widget catalogue.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function definitions(): array {
		$definitions = array(
			// Brand & layout.
			'header'               => array( 'class' => 'Header', 'css' => false, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'footer'               => array( 'class' => 'Footer', 'css' => false, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'hero'                 => array( 'class' => 'Hero', 'css' => true, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'editorial-banner'     => array( 'class' => 'Editorial_Banner', 'css' => true, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'category-cards'       => array( 'class' => 'Category_Cards', 'css' => true, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'brand-story'          => array( 'class' => 'Brand_Story', 'css' => true, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'benefits'             => array( 'class' => 'Benefits', 'css' => true, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),
			'testimonials'         => array( 'class' => 'Testimonials', 'css' => true, 'js' => true, 'category' => 'moon-brand', 'requires' => array() ),
			'newsletter'           => array( 'class' => 'Newsletter', 'css' => true, 'js' => false, 'category' => 'moon-brand', 'requires' => array() ),

			// Fashion interaction.
			'clothing-rack'        => array( 'class' => 'Clothing_Rack', 'css' => true, 'js' => true, 'category' => 'moon-fashion', 'requires' => array() ),
			'outfit-studio'        => array( 'class' => 'Outfit_Studio', 'css' => true, 'js' => true, 'category' => 'moon-fashion', 'requires' => array() ),
			'lookbook'             => array( 'class' => 'Lookbook', 'css' => true, 'js' => true, 'category' => 'moon-fashion', 'requires' => array() ),
			'campaign-storytelling' => array( 'class' => 'Campaign_Storytelling', 'css' => true, 'js' => true, 'category' => 'moon-fashion', 'requires' => array() ),
			'image-reveal'         => array( 'class' => 'Image_Reveal', 'css' => true, 'js' => true, 'category' => 'moon-fashion', 'requires' => array() ),

			// WooCommerce.
			'product-grid'         => array( 'class' => 'Product_Grid', 'css' => true, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-carousel'     => array( 'class' => 'Product_Carousel', 'css' => true, 'js' => true, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-categories'   => array( 'class' => 'Product_Categories', 'css' => true, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-quick-view'   => array( 'class' => 'Product_Quick_View', 'css' => false, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-gallery'      => array( 'class' => 'Product_Gallery', 'css' => true, 'js' => true, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-information'  => array( 'class' => 'Product_Information', 'css' => true, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-price'        => array( 'class' => 'Product_Price', 'css' => true, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'product-variations'   => array( 'class' => 'Product_Variations', 'css' => false, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'add-to-cart'          => array( 'class' => 'Add_To_Cart', 'css' => false, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'related-products'     => array( 'class' => 'Related_Products', 'css' => true, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
			'mini-cart'            => array( 'class' => 'Mini_Cart', 'css' => true, 'js' => false, 'category' => 'moon-shop', 'requires' => array( 'woocommerce' ) ),
		);

		/**
		 * Filter the widget catalogue (third parties can add or remove widgets).
		 *
		 * @param array<string,array<string,mixed>> $definitions Widget definitions.
		 */
		return (array) apply_filters( 'moon_core_widgets', $definitions );
	}

	/**
	 * Enabled widgets: catalogue minus the ones switched off in settings.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function all(): array {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$definitions = $this->definitions();
		$disabled    = (array) Settings::get( 'disabled_widgets', array() );

		foreach ( $disabled as $slug ) {
			unset( $definitions[ $slug ] );
		}

		$this->cache = $definitions;

		return $this->cache;
	}

	/**
	 * Register every available widget with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $manager Widgets manager.
	 */
	public function register_widgets( $manager ): void {
		require_once MOON_CORE_DIR . 'includes/traits/trait-controls.php';
		require_once MOON_CORE_DIR . 'includes/traits/trait-product.php';
		require_once MOON_CORE_DIR . 'includes/class-widget-base.php';

		foreach ( $this->all() as $slug => $config ) {
			if ( in_array( 'woocommerce', (array) ( $config['requires'] ?? array() ), true ) && ! class_exists( 'WooCommerce' ) ) {
				continue;
			}

			$file = MOON_CORE_DIR . 'widgets/' . $slug . '/widget.php';
			if ( ! is_readable( $file ) ) {
				continue;
			}

			require_once $file;

			$class = __NAMESPACE__ . '\\Widgets\\' . $config['class'];
			if ( class_exists( $class ) ) {
				$manager->register( new $class() );
			}
		}
	}

	/**
	 * Slugs of widgets that are registered (used by the admin screen).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function available(): array {
		$available = array();

		foreach ( $this->all() as $slug => $config ) {
			if ( in_array( 'woocommerce', (array) ( $config['requires'] ?? array() ), true ) && ! class_exists( 'WooCommerce' ) ) {
				continue;
			}
			$available[ $slug ] = $config;
		}

		return $available;
	}
}
