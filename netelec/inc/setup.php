<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme features.
 */
function netelec_setup() {
	load_theme_textdomain( 'netelec', NETELEC_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 6,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'    => 'منوی اصلی',
			'footer'     => 'منوی فوتر — دسترسی سریع',
			'footer_cat' => 'منوی فوتر — دسته‌بندی‌ها',
		)
	);

	// Uniform card images: square crop, no layout shift.
	add_image_size( 'netelec-card', 600, 600, true );
	add_image_size( 'netelec-blog', 900, 560, true );
	add_image_size( 'netelec-hero', 1200, 900, true );
}
add_action( 'after_setup_theme', 'netelec_setup' );

/**
 * Content width.
 */
function netelec_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'netelec_content_width', 1280 );
}
add_action( 'after_setup_theme', 'netelec_content_width', 0 );

/**
 * Square WooCommerce thumbnails so every card image has one aspect ratio.
 */
function netelec_woocommerce_thumbnail_cropping() {
	return '1:1';
}
add_filter( 'woocommerce_thumbnail_cropping', 'netelec_woocommerce_thumbnail_cropping' );

/**
 * Body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function netelec_body_classes( $classes ) {
	$classes[] = 'netelec-theme';

	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'netelec-singular';
	}

	if ( netelec_opt( 'sticky_header' ) ) {
		$classes[] = 'has-sticky-header';
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes[] = 'netelec-product-page';
	}

	return $classes;
}
add_filter( 'body_class', 'netelec_body_classes' );

/**
 * Excerpt tuning.
 *
 * @param int $length Word count.
 * @return int
 */
function netelec_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'netelec_excerpt_length' );

/**
 * Excerpt "read more" string.
 *
 * @return string
 */
function netelec_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'netelec_excerpt_more' );

/**
 * Widget areas.
 */
function netelec_widgets_init() {
	register_sidebar(
		array(
			'name'          => 'ستون کناری وبلاگ',
			'id'            => 'blog-sidebar',
			'description'   => 'ابزارک‌های کنار مطالب وبلاگ.',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => 'ستون کناری فروشگاه',
			'id'            => 'shop-sidebar',
			'description'   => 'ابزارک‌های فیلتر و کنار فروشگاه.',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'netelec_widgets_init' );

/**
 * Ask WordPress to keep the theme's own product markup instead of injecting
 * block styles that fight the design.
 */
function netelec_dequeue_block_styles() {
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'wc-blocks-vendors-style' );
}
add_action( 'wp_enqueue_scripts', 'netelec_dequeue_block_styles', 100 );

/**
 * Remove the WordPress emoji script (extra HTTP weight on every page).
 */
function netelec_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'netelec_disable_emojis' );
