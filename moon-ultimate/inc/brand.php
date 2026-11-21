<?php
/**
 * MOON brand identity.
 *
 * The mark is an abstract geometric M whose right leg descends into a
 * crescent. It is drawn inline with `currentColor`, so the same markup reads
 * correctly on the obsidian and pearl surfaces; the standalone SVG files in
 * assets/brand are the deliverables for packaging and social use.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * The crescent-M mark as inline SVG.
 *
 * @param int    $size  Height in pixels.
 * @param string $class Extra class names.
 * @return string
 */
function moon_brand_mark( $size = 32, $class = '' ) {
	$size  = max( 12, (int) $size );
	$class = trim( 'moon-brand__mark ' . $class );

	return sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 64 64" role="img" aria-label="MOON" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="miter" stroke-linecap="square"><path d="M8 50V14l18 24 18-24"/><path d="M44 14a18 18 0 0 1 0 36 22 22 0 0 0 0-36Z" fill="currentColor" stroke="none"/></svg>',
		esc_attr( $class ),
		$size
	);
}

/**
 * Print the mark.
 *
 * @param int    $size  Height in pixels.
 * @param string $class Extra class names.
 */
function moon_the_brand_mark( $size = 32, $class = '' ) {
	echo moon_brand_mark( $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- local SVG.
}

/**
 * Favicon for installs that have not uploaded a site icon.
 *
 * The crescent-M ships as SVG, which every current browser accepts as a
 * favicon and stays sharp on every density.
 */
function moon_brand_favicon() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" type="image/svg+xml" href="%s">' . "\n",
		esc_url( MOON_THEME_URI . '/assets/brand/moon-favicon.svg' )
	);
	printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( moon_theme_color() ) );
}
add_action( 'wp_head', 'moon_brand_favicon', 4 );

/**
 * Browser chrome colour for the active mode.
 *
 * @return string
 */
function moon_theme_color() {
	$mode = class_exists( 'Moon_Settings' ) ? (string) Moon_Settings::get( 'theme_mode', 'dark' ) : 'dark';

	return 'light' === $mode ? '#FDFDFD' : '#0A0A0A';
}
