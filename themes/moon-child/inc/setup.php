<?php
/**
 * Theme supports, menus, image sizes.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		load_child_theme_textdomain( 'moon-child', MOON_CHILD_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array( 'height' => 48, 'width' => 200, 'flex-width' => true, 'flex-height' => true ) );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );

		// WooCommerce: declare support instead of overriding templates.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		register_nav_menus(
			array(
				'primary' => __( 'Primary menu', 'moon-child' ),
				'mobile'  => __( 'Mobile menu', 'moon-child' ),
				'footer'  => __( 'Footer menu', 'moon-child' ),
			)
		);

		// Editorial crops used by the MOON widgets and the store loop.
		add_image_size( 'moon-portrait', 900, 1200, true );
		add_image_size( 'moon-editorial', 1600, 900, true );
		add_image_size( 'moon-product', 720, 900, true );
	}
);

/**
 * Content width used by oEmbeds and wide blocks.
 */
add_action(
	'after_setup_theme',
	static function () {
		if ( ! isset( $GLOBALS['content_width'] ) ) {
			$GLOBALS['content_width'] = 1320;
		}
	},
	0
);

/**
 * The MOON design is a dark-first editorial look; the body class lets CSS and
 * Elementor target the same state the user picked in the header switch.
 */
add_filter(
	'body_class',
	static function ( array $classes ): array {
		$classes[] = 'moon-theme';
		return $classes;
	}
);

/**
 * Allow the header/footer templates to use the full Elementor canvas.
 */
add_filter(
	'hello_elementor_page_title',
	static function ( $show ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			return false;
		}
		return $show;
	}
);
