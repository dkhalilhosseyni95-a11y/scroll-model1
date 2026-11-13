<?php
/**
 * Front-end assets.
 *
 * Styles and scripts are loaded conditionally: the homepage bundle only on the
 * front page, the WooCommerce bundle only on shop screens.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue public styles and scripts.
 */
function moon_enqueue_assets() {
	$ver = MOON_THEME_VERSION;

	wp_enqueue_style( 'moon-base', MOON_THEME_URI . '/assets/css/base.css', array(), $ver );
	wp_enqueue_style( 'moon-cards', MOON_THEME_URI . '/assets/css/cards.css', array( 'moon-base' ), $ver );
	wp_enqueue_style( 'moon-chrome', MOON_THEME_URI . '/assets/css/header-footer.css', array( 'moon-base' ), $ver );

	if ( ! is_front_page() ) {
		wp_enqueue_style( 'moon-pages', MOON_THEME_URI . '/assets/css/pages.css', array( 'moon-base' ), $ver );
	}

	if ( is_front_page() ) {
		wp_enqueue_style( 'moon-home', MOON_THEME_URI . '/assets/css/homepage.css', array( 'moon-base' ), $ver );
	}

	if ( moon_is_woo() && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		wp_enqueue_style( 'moon-woo', MOON_THEME_URI . '/assets/css/woocommerce.css', array( 'moon-base' ), $ver );
	}

	if ( moon_is_woo() && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_front_page() || is_singular( 'moon_outfit' ) || is_page( 'size-guide' ) ) ) {
		wp_enqueue_style( 'moon-shop', MOON_THEME_URI . '/assets/css/shop.css', array( 'moon-base' ), $ver );
	}

	wp_enqueue_script( 'moon-chrome', MOON_THEME_URI . '/assets/js/chrome.js', array(), $ver, true );

	if ( moon_is_woo() && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_front_page() || is_singular( 'moon_outfit' ) ) ) {
		wp_enqueue_script( 'moon-shop', MOON_THEME_URI . '/assets/js/shop.js', array( 'moon-chrome' ), $ver, true );

		if ( wp_script_is( 'wc-add-to-cart-variation', 'registered' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}

		if ( wp_script_is( 'wc-single-product', 'registered' ) ) {
			wp_enqueue_script( 'wc-single-product' );
		}
	}

	if ( is_front_page() ) {
		wp_enqueue_script( 'moon-home', MOON_THEME_URI . '/assets/js/home.js', array( 'moon-chrome' ), $ver, true );
	}

	wp_localize_script( 'moon-chrome', 'MOON', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'homeUrl'     => home_url( '/' ),
		'wcAjax'      => home_url( '/' ),
		'cartUrl'     => moon_cart_url(),
		'checkoutUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'checkout' ) : home_url( '/' ),
		'nonce'       => wp_create_nonce( 'moon_shop' ),
		'loginNonce'  => wp_create_nonce( 'moon_login' ),
		'isRtl'       => is_rtl(),
		'wishlistKey' => 'moon-wishlist',
		'i18n'        => array(
			'added'    => 'به سبد خرید اضافه شد.',
			'adding'   => 'در حال افزودن…',
			'addError' => 'افزودن به سبد انجام نشد؛ دوباره تلاش کنید.',
			'qvError'  => 'این محصول در دسترس نیست.',
			'saved'    => 'در علاقه‌مندی‌های این مرورگر ذخیره شد.',
			'removed'  => 'از علاقه‌مندی‌ها حذف شد.',
			'empty'    => 'هنوز محصولی را نشان نکرده‌اید.',
			'wishlist' => 'علاقه‌مندی‌ها',
		),
	) );

	wp_register_style( 'moon-adminbar', false, array(), $ver );
	wp_enqueue_style( 'moon-adminbar' );
	wp_add_inline_style( 'moon-adminbar', 'body.admin-bar .moon-header{top:32px}@media screen and (max-width:782px){body.admin-bar .moon-header{top:46px}}' );
}
add_action( 'wp_enqueue_scripts', 'moon_enqueue_assets' );

/**
 * Load WooCommerce's cart fragments on every page.
 *
 * The header quick-cart drawer renders the mini cart outside the cart widget,
 * where WooCommerce does not enqueue the fragment script by itself. Without it
 * the drawer would not refresh after AJAX add-to-cart or item removal.
 */
function moon_enqueue_cart_fragments() {
	if ( moon_is_woo() && wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'moon_enqueue_cart_fragments', 99 );

/**
 * Preload the primary Persian font to reduce first-paint layout shift.
 */
function moon_preload_fonts() {
	$font = MOON_THEME_URI . '/assets/fonts/vazirmatn-arabic-normal-100_900.woff2';
	echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'moon_preload_fonts', 1 );

/**
 * Editor styles so block content matches the front end.
 */
function moon_editor_assets() {
	add_editor_style( 'assets/css/base.css' );
}
add_action( 'after_setup_theme', 'moon_editor_assets' );

/**
 * Admin styles for the theme panel bridge.
 *
 * @param string $hook Current admin page.
 */
function moon_admin_assets( $hook ) {
	$screen = get_current_screen();

	if ( 'appearance_page_moon-theme' !== $hook && ( ! $screen || 'post' !== $screen->base || 'moon_rack' !== $screen->post_type ) ) {
		return;
	}

	wp_enqueue_style( 'moon-admin', MOON_THEME_URI . '/assets/css/admin.css', array(), MOON_THEME_VERSION );
}
add_action( 'admin_enqueue_scripts', 'moon_admin_assets' );
