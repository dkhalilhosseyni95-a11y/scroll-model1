<?php
/**
 * Theme setup.
 *
 * @package DaniSite
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DANI_VERSION' ) ) {
	define( 'DANI_VERSION', '1.0.2' );
}

/**
 * Register theme supports, menus and image sizes.
 *
 * @return void
 */
function dani_setup() {
	load_theme_textdomain( 'danisite', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 64,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'elementor' );
	add_theme_support( 'custom-header' );
	add_editor_style( 'assets/css/dani-base.css' );

	register_nav_menus( array(
		'primary' => __( 'منوی اصلی', 'danisite' ),
		'footer'  => __( 'منوی فوتر', 'danisite' ),
		'legal'   => __( 'منوی قوانین و حریم خصوصی', 'danisite' ),
	) );

	add_image_size( 'dani-card', 800, 500, true );
	add_image_size( 'dani-wide', 1600, 900, true );
	add_image_size( 'dani-portrait', 800, 1000, true );
	add_image_size( 'dani-square', 600, 600, true );

	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1120;
	}
}
add_action( 'after_setup_theme', 'dani_setup' );

/**
 * Widget areas.
 *
 * @return void
 */
function dani_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'ستون کنار وبلاگ', 'danisite' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'ابزارک‌های کنار مطالب وبلاگ.', 'danisite' ),
		'before_widget' => '<section id="%1$s" class="dani-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="dani-widget__title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'dani_widgets_init' );

/**
 * Body classes used by the design system.
 *
 * @param array $classes Existing classes.
 * @return array
 */
function dani_body_classes( $classes ) {
	$classes[] = 'dani-body';

	if ( is_singular() && dani_is_built_with_elementor( get_the_ID() ) ) {
		$classes[] = 'dani-elementor-page';
	}
	if ( is_front_page() ) {
		$classes[] = 'dani-front-page';
	}

	return $classes;
}
add_filter( 'body_class', 'dani_body_classes' );

/**
 * Excerpt tuning.
 *
 * @return string
 */
function dani_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'dani_excerpt_more' );

/**
 * Excerpt length.
 *
 * @param int $length Current length.
 * @return int
 */
function dani_excerpt_length( $length ) {
	return 26;
}
add_filter( 'excerpt_length', 'dani_excerpt_length' );

/**
 * Detect whether a post is edited with Elementor.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function dani_is_built_with_elementor( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return false;
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		$elementor = \Elementor\Plugin::instance();
		if ( isset( $elementor->documents ) && $elementor->documents->get( $post_id ) ) {
			return $elementor->documents->get( $post_id )->is_built_with_elementor();
		}
	}

	return 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

/**
 * Elementor locations so headers/footers built with Elementor can replace the theme ones.
 *
 * @return void
 */
function dani_register_elementor_locations() {
	if ( ! function_exists( 'elementor_theme_do_location' ) ) {
		return;
	}
	register_nav_menus( array() );
}
add_action( 'init', 'dani_register_elementor_locations' );

/**
 * Add the editor format styles so the classic editor matches the front end.
 *
 * @return void
 */
function dani_add_editor_styles() {
	add_theme_support( 'editor-styles' );
}
add_action( 'after_setup_theme', 'dani_add_editor_styles', 20 );
