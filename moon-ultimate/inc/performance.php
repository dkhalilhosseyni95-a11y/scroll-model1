<?php
/**
 * Performance switches from the panel.
 *
 * Each rule is a documented WordPress default that most stores do not need.
 * Every one can be switched back on in MOON → عملکرد, and nothing here changes
 * application behaviour.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Panel switch helper.
 *
 * @param string $key Panel key.
 * @return bool
 */
function moon_perf_on( $key ) {
	if ( class_exists( 'Moon_Settings' ) ) {
		return (bool) Moon_Settings::get( $key, '1' );
	}

	return true;
}

/**
 * Remove the emoji script and its inline styles.
 */
function moon_perf_emojis() {
	if ( ! moon_perf_on( 'perf_disable_emojis' ) ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'moon_perf_emojis' );

/**
 * Remove the wp-embed script (no MOON screen embeds WordPress posts).
 */
function moon_perf_embed() {
	if ( ! moon_perf_on( 'perf_disable_embed' ) ) {
		return;
	}

	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	add_filter( 'embed_oembed_discover', '__return_false' );
}
add_action( 'init', 'moon_perf_embed' );

/**
 * Skip the block library stylesheet on screens with no block content.
 */
function moon_perf_block_css() {
	if ( ! moon_perf_on( 'perf_disable_blocks_css' ) ) {
		return;
	}

	$keep = is_singular( array( 'post', 'page', 'moon_outfit' ) ) || is_front_page() || is_home();

	if ( $keep ) {
		return;
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'moon_perf_block_css', 100 );

/**
 * Lazy-load images the theme prints itself (WordPress handles content images).
 *
 * @param array $attr Image attributes.
 * @return array
 */
function moon_perf_lazy( $attr ) {
	if ( ! moon_perf_on( 'perf_lazy_images' ) ) {
		return $attr;
	}

	if ( empty( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}

	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'moon_perf_lazy' );

/**
 * Drop WooCommerce's Photoswipe/zoom assets when the zoom option is off.
 *
 * @param array $scripts Registered scripts.
 */
function moon_perf_drop_zoom( $scripts ) {
	if ( is_admin() || ! class_exists( 'Moon_Settings' ) ) {
		return;
	}

	if ( Moon_Settings::get( 'product_zoom', '1' ) ) {
		return;
	}

	foreach ( array( 'photoswipe', 'photoswipe-ui-default', 'wc-single-product', 'flexslider', 'zoom' ) as $handle ) {
		if ( isset( $scripts->registered[ $handle ] ) ) {
			unset( $scripts->registered[ $handle ] );
		}
	}
}
add_action( 'wp_default_scripts', 'moon_perf_drop_zoom' );
