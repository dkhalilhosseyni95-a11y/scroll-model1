<?php
/**
 * Front-end assets, loaded conditionally.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the current request a WooCommerce screen?
 *
 * @return bool
 */
function netelec_is_woocommerce_context() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Enqueue styles and scripts.
 */
function netelec_assets() {
	wp_enqueue_style( 'netelec-base', NETELEC_URI . '/assets/css/base.css', array(), NETELEC_VERSION );
	wp_add_inline_style( 'netelec-base', netelec_dynamic_css() );

	$deps = array( 'netelec-base' );

	if ( netelec_is_woocommerce_context() ) {
		$deps[] = 'woocommerce-general';
		$deps[] = 'woocommerce-layout';
		wp_enqueue_style( 'netelec-woocommerce', NETELEC_URI . '/assets/css/woocommerce.css', $deps, NETELEC_VERSION );
	}

	if ( is_singular( 'post' ) || is_page_template( 'page-templates/template-contact.php' ) ) {
		wp_enqueue_style( 'netelec-forms', NETELEC_URI . '/assets/css/forms.css', array( 'netelec-base' ), NETELEC_VERSION );
	}

	wp_enqueue_script( 'netelec-main', NETELEC_URI . '/assets/js/main.js', array(), NETELEC_VERSION, true );
	wp_script_add_data( 'netelec-main', 'strategy', 'defer' );

	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );

	wp_localize_script(
		'netelec-main',
		'netelecData',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'netelec_nonce' ),
			'cartUrl'    => $cart_url,
			'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '',
			'isCart'     => function_exists( 'is_cart' ) ? is_cart() : false,
			'isCheckout' => function_exists( 'is_checkout' ) ? is_checkout() : false,
			'quickView'  => (bool) netelec_opt( 'quick_view' ),
			'liveSearch' => (bool) netelec_opt( 'show_search' ),
			'stickyBar'  => (bool) netelec_opt( 'single_sticky_bar' ),
			'i18n'       => array(
				'added'       => 'به سبد خرید اضافه شد',
				'error'       => 'خطایی رخ داد. دوباره تلاش کنید.',
				'wishlistAdd' => 'به علاقه‌مندی‌ها اضافه شد',
				'wishlistDel' => 'از علاقه‌مندی‌ها حذف شد',
				'compareAdd'  => 'به مقایسه اضافه شد',
				'compareDel'  => 'از مقایسه حذف شد',
				'compareFull' => 'حداکثر ۴ محصول را می‌توانید مقایسه کنید.',
				'noResults'   => 'محصولی پیدا نشد.',
				'searching'   => 'در حال جستجو…',
				'copied'      => 'کپی شد',
				'otpSent'     => 'کد تأیید ارسال شد',
				'otpError'    => 'ارسال کد ناموفق بود',
				'otpInvalid'  => 'کد وارد شده صحیح نیست',
				'required'    => 'این فیلد الزامی است',
				'resendIn'    => 'ارسال مجدد تا %s ثانیه دیگر',
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'netelec_assets' );


