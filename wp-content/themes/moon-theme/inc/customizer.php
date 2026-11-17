<?php
/**
 * Customizer bridge.
 *
 * The MOON panel (Appearance → MOON) owns every theme setting. The Customizer
 * keeps the native Site Identity controls live for the wordmark, and points
 * administrators to the panel.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wordmark text: panel setting, then the site title.
 *
 * @return string
 */
function moon_brand_text() {
	$brand = moon_opt( 'brand_name' );

	if ( '' === trim( (string) $brand ) ) {
		$brand = get_bloginfo( 'name' );
	}

	return $brand ? $brand : 'MOON';
}

/**
 * Tagline: panel setting, then the site tagline.
 *
 * @return string
 */
function moon_brand_tagline() {
	$tagline = moon_opt( 'brand_tagline' );

	if ( '' === trim( (string) $tagline ) ) {
		$tagline = get_bloginfo( 'description' );
	}

	return $tagline;
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function moon_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'moon_wordmark', array(
			'selector'        => '.moon-brand__text',
			'render_callback' => 'moon_brand_text',
		) );
	}

	$wp_customize->add_section( 'moon_panel_link', array(
		'title'       => __( 'MOON', 'moon-theme' ),
		'priority'    => 5,
		'description' => sprintf(
			/* translators: %s: settings page URL. */
			__( 'تنظیمات کامل قالب (رنگ برند، بخش‌های صفحه اصلی، هدر و فوتر، رگال و استودیو) در پنل اختصاصی MOON است: %s', 'moon-theme' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=moon-theme' ) ) . '">' . esc_html__( 'باز کردن پنل MOON', 'moon-theme' ) . '</a>'
		),
	) );
}
add_action( 'customize_register', 'moon_customize_register' );

/**
 * Live preview for the wordmark.
 */
function moon_customize_preview_js() {
	wp_enqueue_script(
		'moon-customizer',
		MOON_THEME_URI . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		MOON_THEME_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'moon_customize_preview_js' );

/**
 * Whether text is Persian/Arabic script (letter-spacing must be off: it breaks joined letters).
 *
 * @param string $text Text.
 * @return bool
 */
function moon_is_arabic_script( $text ) {
	return (bool) preg_match( '/\p{Arabic}/u', (string) $text );
}
