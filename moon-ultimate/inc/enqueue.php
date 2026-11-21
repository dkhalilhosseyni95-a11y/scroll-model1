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

	wp_enqueue_style( 'moon-mobile', MOON_THEME_URI . '/assets/css/mobile.css', array( 'moon-base' ), $ver );

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

	// Builder output: header layouts, footer columns and the sections that do
	// not ship their own stylesheet. Loaded on every screen that uses them.
	wp_enqueue_style( 'moon-extensions', MOON_THEME_URI . '/assets/css/extensions.css', array( 'moon-base' ), $ver );
	wp_enqueue_style( 'moon-polish', MOON_THEME_URI . '/assets/css/polish.css', array( 'moon-base', 'moon-cards' ), $ver );
	wp_enqueue_style( 'moon-cards-ultimate', MOON_THEME_URI . '/assets/css/cards-ultimate.css', array( 'moon-cards', 'moon-polish' ), $ver );

	wp_enqueue_script( 'moon-chrome', MOON_THEME_URI . '/assets/js/chrome.js', array(), $ver, array( 'strategy' => 'defer', 'in_footer' => false ) );
	wp_enqueue_script( 'moon-ui', MOON_THEME_URI . '/assets/js/ui.js', array( 'moon-chrome' ), $ver, array( 'strategy' => 'defer', 'in_footer' => false ) );

	if ( moon_is_woo() && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_front_page() || is_singular( 'product' ) || is_page_template( 'page-compare.php' ) ) ) {
		wp_enqueue_style( 'moon-woo-extras', MOON_THEME_URI . '/assets/css/woo-extras.css', array( 'moon-base' ), $ver );
	}

	if ( moon_is_woo() && is_account_page() && is_user_logged_in() === false ) {
		wp_enqueue_script( 'moon-auth', MOON_THEME_URI . '/assets/js/auth.js', array( 'moon-chrome' ), $ver, array( 'strategy' => 'defer', 'in_footer' => false ) );
	}

	if ( moon_is_woo() && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_front_page() || is_singular( 'moon_outfit' ) ) ) {
		wp_enqueue_script( 'moon-shop', MOON_THEME_URI . '/assets/js/shop.js', array( 'moon-chrome' ), $ver, array( 'strategy' => 'defer', 'in_footer' => false ) );

		if ( wp_script_is( 'wc-add-to-cart-variation', 'registered' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}

		if ( wp_script_is( 'wc-single-product', 'registered' ) ) {
			wp_enqueue_script( 'wc-single-product' );
		}
	}

	if ( is_front_page() ) {
		wp_enqueue_script( 'moon-home', MOON_THEME_URI . '/assets/js/home.js', array( 'moon-chrome' ), $ver, array( 'strategy' => 'defer', 'in_footer' => false ) );
	}

	// Live search only runs when the header actually offers a search button;
	// switching it off in the panel removes the request entirely.
	if ( class_exists( 'Moon_Header' ) ? Moon_Header::has( 'search' ) : moon_opt( 'header_search' ) ) {
		wp_enqueue_script( 'moon-live-search', MOON_THEME_URI . '/assets/js/live-search.js', array( 'moon-chrome' ), $ver, array( 'strategy' => 'defer', 'in_footer' => false ) );
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
		'compareUrl'  => class_exists( 'Moon_Compare' ) ? Moon_Compare::page_url() : '',
		'compareKey'  => 'moon-compare',
		'compareMax'  => class_exists( 'Moon_Compare' ) ? Moon_Compare::LIMIT : 4,
		'shopView'    => class_exists( 'Moon_Settings' ) ? (string) Moon_Settings::get( 'shop_view', 'grid' ) : 'grid',
		'i18n'        => array(
			'added'    => 'به سبد خرید اضافه شد.',
			'adding'   => 'در حال افزودن…',
			'addError' => 'افزودن به سبد انجام نشد؛ دوباره تلاش کنید.',
			'qvError'  => 'این محصول در دسترس نیست.',
			'saved'    => 'در علاقه‌مندی‌های این مرورگر ذخیره شد.',
			'removed'  => 'از علاقه‌مندی‌ها حذف شد.',
			'empty'    => 'هنوز محصولی را نشان نکرده‌اید.',
			'wishlist' => 'علاقه‌مندی‌ها',
			'compareAdded'   => 'به مقایسه اضافه شد.',
			'compareRemoved' => 'از مقایسه حذف شد.',
			'compareFull'    => 'حداکثر ۴ محصول را می‌توانید مقایسه کنید.',
			'compareEmpty'   => 'هنوز محصولی برای مقایسه انتخاب نشده است.',
			'loadMore'       => 'محصولات بیشتر',
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
 * Editor styles so block content matches the front end.
 */
function moon_editor_assets() {
	add_editor_style( 'assets/css/base.css' );
}
add_action( 'after_setup_theme', 'moon_editor_assets' );
