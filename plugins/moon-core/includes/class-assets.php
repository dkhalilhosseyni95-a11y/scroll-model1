<?php
/**
 * Asset registration, design tokens and conditional loading.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the plugin styles and scripts.
 */
class Moon_Core_Assets {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_preloads' ), 2 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * True when the current request shows WooCommerce templates.
	 *
	 * @return bool
	 */
	public static function is_commerce_context() {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return false;
		}

		return is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url( 'order-tracking' );
	}

	/**
	 * Enqueue front-end assets.
	 */
	public static function enqueue() {
		// The RTL file contains overrides only, so it is appended rather than
		// replacing the base stylesheet (wp_style_add_data 'replace' would drop it).
		wp_enqueue_style(
			'moon-core',
			MOON_CORE_URL . 'assets/css/moon-core.css',
			array(),
			MOON_CORE_VERSION
		);
		if ( is_rtl() ) {
			wp_enqueue_style(
				'moon-core-rtl',
				MOON_CORE_URL . 'assets/css/moon-core-rtl.css',
				array( 'moon-core' ),
				MOON_CORE_VERSION
			);
		}
		wp_add_inline_style( 'moon-core', self::inline_tokens() );

		if ( self::is_commerce_context() ) {
			wp_enqueue_style(
				'moon-commerce',
				MOON_CORE_URL . 'assets/css/moon-commerce.css',
				array( 'moon-core' ),
				MOON_CORE_VERSION
			);
			if ( is_rtl() ) {
				wp_enqueue_style(
					'moon-commerce-rtl',
					MOON_CORE_URL . 'assets/css/moon-commerce-rtl.css',
					array( 'moon-commerce' ),
					MOON_CORE_VERSION
				);
			}
		}

		wp_enqueue_script(
			'moon-core',
			MOON_CORE_URL . 'assets/js/moon-core.js',
			array(),
			MOON_CORE_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_localize_script(
			'moon-core',
			'moonCore',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'moon_core_nonce' ),
				'isRtl'        => is_rtl(),
				'animations'   => (int) moon_core_setting( 'animations' ),
				'animDuration' => (int) moon_core_setting( 'anim_duration' ),
				'loadMore'     => (int) moon_core_setting( 'enable_load_more' ),
				'heroMobileVideo' => 1 === (int) moon_core_setting( 'hero_disable_mobile_video' ) ? 0 : 1,
				'strings'      => array(
					'addToCart'    => __( 'افزودن به سبد خرید', 'moon-core' ),
					'added'        => __( 'افزوده شد', 'moon-core' ),
					'loadMore'     => __( 'نمایش محصولات بیشتر', 'moon-core' ),
					'loading'      => __( 'در حال بارگذاری…', 'moon-core' ),
					'noResults'    => __( 'محصولی با این فیلترها پیدا نشد.', 'moon-core' ),
					'wishlistAdd'  => __( 'افزودن به علاقه‌مندی‌ها', 'moon-core' ),
					'wishlistRemove' => __( 'حذف از علاقه‌مندی‌ها', 'moon-core' ),
					'error'        => __( 'خطایی رخ داد. دوباره تلاش کنید.', 'moon-core' ),
				),
			)
		);
	}

	/**
	 * CSS custom properties generated from the settings page.
	 *
	 * Both themes are declared on the document so switching only swaps an
	 * attribute — no extra request, no flash of unstyled content.
	 *
	 * @return string
	 */
	public static function inline_tokens() {
		$s  = Moon_Core_Settings::all();
		$fa = self::font_stack( $s['font_fa'], array( 'Vazirmatn', 'Tahoma', 'sans-serif' ) );
		$en_display = self::font_stack( $s['font_en_display'], array( 'Jost', 'Georgia', 'serif' ) );
		$en_text    = self::font_stack( $s['font_en_text'], array( 'Jost', 'Helvetica Neue', 'Arial', 'sans-serif' ) );

		$shared = sprintf(
			'--moon-gold:%1$s;--moon-gold-soft:%2$s;--moon-font-fa:%3$s;--moon-font-display:%4$s;--moon-font-text:%5$s;--moon-fs-base:%6$spx;--moon-anim:%7$sms;',
			esc_attr( $s['color_gold'] ),
			esc_attr( $s['dark_gold'] ),
			esc_attr( $fa ),
			esc_attr( $en_display ),
			esc_attr( $en_text ),
			(int) $s['font_size_base'],
			(int) $s['anim_duration']
		);

		$light = sprintf(
			':root{--moon-bg:%1$s;--moon-surface:#ffffff;--moon-surface-2:%2$s;--moon-ink:%3$s;--moon-text:%3$s;--moon-text-muted:%4$s;--moon-line:rgba(17,17,15,.13);--moon-line-strong:rgba(17,17,15,.28);--moon-accent:%5$s;--moon-on-accent:%3$s;--moon-header-bg:rgba(250,249,246,.72);--moon-header-line:rgba(17,17,15,.10);--moon-glass-highlight:rgba(255,255,255,.55);--moon-shadow:0 18px 60px -28px rgba(17,17,15,.35);%6$s}',
			esc_attr( $s['color_white'] ),
			esc_attr( $s['color_cream'] ),
			esc_attr( $s['color_ink'] ),
			esc_attr( $s['color_gray'] ),
			esc_attr( $s['color_gold'] ),
			$shared
		);

		$dark = sprintf(
			'html[data-theme="dark"]{--moon-bg:%1$s;--moon-surface:%2$s;--moon-surface-2:#0B0B0A;--moon-ink:%3$s;--moon-text:%3$s;--moon-text-muted:%4$s;--moon-line:rgba(243,239,230,.14);--moon-line-strong:rgba(243,239,230,.30);--moon-accent:%5$s;--moon-on-accent:#11110F;--moon-header-bg:rgba(17,17,15,.62);--moon-header-line:rgba(243,239,230,.12);--moon-glass-highlight:rgba(255,255,255,.08);--moon-shadow:0 22px 70px -30px rgba(0,0,0,.85);color-scheme:dark;}',
			esc_attr( $s['dark_bg'] ),
			esc_attr( $s['dark_surface'] ),
			esc_attr( $s['dark_text'] ),
			esc_attr( $s['dark_muted'] ),
			esc_attr( $s['dark_gold'] )
		);

		return $light . $dark;
	}

	/**
	 * Build a quoted CSS font stack.
	 *
	 * @param string $family   Primary family name.
	 * @param array  $fallback Fallback families.
	 * @return string
	 */
	private static function font_stack( $family, $fallback ) {
		$family = trim( (string) $family );
		$stack  = array();

		if ( '' !== $family ) {
			$stack[] = '"' . str_replace( '"', '', $family ) . '"';
		}

		return implode( ', ', array_merge( $stack, $fallback ) );
	}

	/**
	 * Preload the Persian webfont so text paints without a flash of fallback.
	 */
	public static function print_preloads() {
		$fonts = array( 'assets/fonts/vazirmatn-wght.woff2' );

		foreach ( $fonts as $relative ) {
			if ( file_exists( MOON_CORE_PATH . $relative ) ) {
				printf(
					'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( MOON_CORE_URL . $relative )
				);
			}
		}
	}

	/**
	 * Add helper body classes used by the stylesheet.
	 *
	 * @param array $classes Existing classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		$classes[] = 'moon-core';
		$classes[] = is_rtl() ? 'moon-rtl' : 'moon-ltr';

		if ( (int) moon_core_setting( 'animations' ) === 1 ) {
			$classes[] = 'moon-animations';
		}

		if ( Moon_Core_Header::is_active() ) {
			$classes[] = 'moon-core-header-active';
			$classes[] = 'moon-core-footer-active';
		}

		return $classes;
	}
}
