<?php
/**
 * Small helpers shared by the theme templates.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the site header. When Elementor Pro Theme Builder provides a header
 * location it wins; otherwise the Moon Core header (editable via Moon Core
 * settings) or a minimal fallback is printed.
 */
function moon_child_render_header(): void {
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		return;
	}

	if ( class_exists( '\MoonCore\Site_Chrome' ) && \MoonCore\Site_Chrome::has_header() ) {
		\MoonCore\Site_Chrome::render_header();
		return;
	}

	get_template_part( 'template-parts/header-default' );
}

/**
 * Renders the site footer, with the same precedence rules as the header.
 */
function moon_child_render_footer(): void {
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		return;
	}

	if ( class_exists( '\MoonCore\Site_Chrome' ) && \MoonCore\Site_Chrome::has_footer() ) {
		\MoonCore\Site_Chrome::render_footer();
		return;
	}

	get_template_part( 'template-parts/footer-default' );
}

/**
 * Menu fallback used when no menu is assigned yet.
 *
 * @param array $args wp_nav_menu args.
 */
function moon_child_menu_fallback( array $args = array() ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$items = array(
		__( 'Shop', 'moon-child' )     => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		__( 'Women', 'moon-child' )    => home_url( '/' ),
		__( 'Men', 'moon-child' )      => home_url( '/' ),
		__( 'Editorial', 'moon-child' ) => home_url( '/' ),
	);

	echo '<ul class="' . esc_attr( $args['menu_class'] ?? 'moon-nav__list' ) . '">';
	foreach ( $items as $label => $url ) {
		printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Reading time for journal posts.
 */
function moon_child_reading_time( ?int $post_id = null ): string {
	$post_id = $post_id ?: get_the_ID();
	$words   = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	$minutes = max( 1, (int) ceil( $words / 220 ) );

	/* translators: %d: minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'moon-child' ), $minutes );
}
