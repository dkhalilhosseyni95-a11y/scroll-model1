<?php
/**
 * WordPress Customizer integration.
 *
 * The customizer edits the same `netelec_settings` option as the theme panel,
 * so there is a single source of truth and live preview keeps working.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a hex color for the customizer.
 *
 * @param string $value Raw value.
 * @return string
 */
function netelec_sanitize_color_setting( $value ) {
	$color = sanitize_hex_color( $value );
	return $color ? $color : '#2563EB';
}

/**
 * Register customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function netelec_customize_register( $wp_customize ) {
	$defaults = netelec_defaults();

	$wp_customize->add_section(
		'netelec_hero',
		array(
			'title'       => 'NETELEC — بنر صفحه اصلی',
			'priority'    => 30,
			'description' => 'برای تنظیمات کامل‌تر از پیشخوان > قالب NETELEC استفاده کنید.',
		)
	);

	$text_fields = array(
		'hero_badge'     => 'برچسب بالای عنوان',
		'hero_title'     => 'عنوان اصلی',
		'hero_desc'      => 'توضیحات',
		'hero_btn_text'  => 'متن دکمه اصلی',
		'hero_btn_url'   => 'لینک دکمه اصلی',
		'hero_btn2_text' => 'متن دکمه دوم',
		'hero_btn2_url'  => 'لینک دکمه دوم',
	);

	foreach ( $text_fields as $key => $label ) {
		$wp_customize->add_setting(
			'netelec_settings[' . $key . ']',
			array(
				'type'              => 'option',
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'netelec_settings[' . $key . ']',
			array(
				'label'   => $label,
				'section' => 'netelec_hero',
				'type'    => in_array( $key, array( 'hero_desc' ), true ) ? 'textarea' : 'text',
			)
		);
	}

	$wp_customize->add_section(
		'netelec_colors',
		array(
			'title'    => 'NETELEC — رنگ‌ها',
			'priority' => 31,
		)
	);

	$colors = array(
		'primary_color' => 'رنگ اصلی',
		'dark_color'    => 'رنگ تیره',
		'bg_color'      => 'پس‌زمینه سایت',
		'text_color'    => 'رنگ متن',
	);

	foreach ( $colors as $key => $label ) {
		$wp_customize->add_setting(
			'netelec_settings[' . $key . ']',
			array(
				'type'              => 'option',
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'netelec_sanitize_color_setting',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'netelec_settings[' . $key . ']',
				array(
					'label'   => $label,
					'section' => 'netelec_colors',
				)
			)
		);
	}

	// Keep the "Site Identity" logo in sync with the theme panel logo.
	$wp_customize->add_setting(
		'netelec_settings[brand_name]',
		array(
			'type'              => 'option',
			'default'           => $defaults['brand_name'],
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'netelec_settings[brand_name]',
		array(
			'label'   => 'نام برند',
			'section' => 'title_tagline',
			'type'    => 'text',
		)
	);
}
add_action( 'customize_register', 'netelec_customize_register' );
