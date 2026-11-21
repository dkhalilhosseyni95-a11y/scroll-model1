<?php
/**
 * One-time store setup that runs when the theme is activated.
 *
 * Creates the base pages, the four shop categories (women / men / kids /
 * scarves), the primary menu and the classic WooCommerce cart/checkout pages.
 * It never overwrites content the owner already edited, and it runs once per
 * SETUP_VERSION so re-activating the theme is safe.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_SETUP_VERSION', '2.0.0' );

/**
 * Shop categories shown as tabs on the shop page and in the menu.
 *
 * @return array slug => name
 */
function moon_shop_categories() {
	return array(
		'women'   => 'زنانه',
		'men'     => 'مردانه',
		'kids'    => 'کودکانه',
		'scarves' => 'شال و روسری',
	);
}

/**
 * Run the setup.
 */
function moon_run_auto_setup() {
	if ( get_option( 'moon_setup_version' ) === MOON_SETUP_VERSION ) {
		return;
	}

	// Pages.
	$pages = array(
		'home'     => array( 'خانه', '' ),
		'about'    => array( 'درباره ما', '' ),
		'contact'  => array( 'تماس با ما', '' ),
		'shipping' => array( 'ارسال و بازگشت', "<p>شرایط ارسال و مرجوعی کالا را اینجا بنویسید.</p>" ),
		'privacy'  => array( 'حریم خصوصی', "<p>اطلاعات ثبت‌شده فقط برای پردازش سفارش و پشتیبانی استفاده می‌شود.</p>" ),
	);

	foreach ( $pages as $slug => $page ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}

		$new_id = wp_insert_post( array(
			'post_title'   => $page[0],
			'post_name'    => $slug,
			'post_content' => $page[1],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );

		if ( $new_id && ! is_wp_error( $new_id ) && in_array( $slug, array( 'about', 'contact' ), true ) ) {
			update_post_meta( $new_id, '_wp_page_template', 'page-' . $slug . '.php' );
		}
	}

	// Comparison page: the header icon and product cards link here.
	if ( class_exists( 'Moon_Compare' ) ) {
		Moon_Compare::ensure_page();
	}

	$home = get_page_by_path( 'home' );

	if ( $home && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}

	// WooCommerce.
	if ( class_exists( 'WooCommerce' ) ) {
		update_option( 'woocommerce_coming_soon', 'no' );

		foreach ( array(
			'cart'     => array( '[woocommerce_cart]', 'سبد خرید' ),
			'checkout' => array( '[woocommerce_checkout]', 'تسویه حساب' ),
		) as $key => $def ) {
			$id = (int) get_option( 'woocommerce_' . $key . '_page_id' );

			if ( $id && get_post( $id ) && false !== strpos( (string) get_post_field( 'post_content', $id ), 'wp:woocommerce/' ) ) {
				wp_update_post( array( 'ID' => $id, 'post_content' => $def[0], 'post_title' => $def[1] ) );
			}
		}

		$shop = (int) get_option( 'woocommerce_shop_page_id' );

		if ( $shop && 'Shop' === get_the_title( $shop ) ) {
			wp_update_post( array( 'ID' => $shop, 'post_title' => 'فروشگاه' ) );
		}

		$acct = (int) get_option( 'woocommerce_myaccount_page_id' );

		if ( $acct && 'My account' === get_the_title( $acct ) ) {
			wp_update_post( array( 'ID' => $acct, 'post_title' => 'حساب کاربری' ) );
		}

		// Iranian store defaults (only while the shop is still on WooCommerce's factory settings).
		$country = (string) get_option( 'woocommerce_default_country', '' );

		if ( '' === $country || 0 === strpos( $country, 'US' ) || 0 === strpos( $country, 'GB' ) ) {
			update_option( 'woocommerce_default_country', 'IR' );
			update_option( 'woocommerce_allowed_countries', 'specific' );
			update_option( 'woocommerce_specific_allowed_countries', array( 'IR' ) );
			update_option( 'woocommerce_ship_to_countries', '' );
			update_option( 'woocommerce_specific_ship_to_countries', array( 'IR' ) );
		}

		if ( in_array( get_option( 'woocommerce_currency' ), array( 'USD', 'EUR', 'GBP' ), true ) ) {
			update_option( 'woocommerce_currency', 'IRT' );
			update_option( 'woocommerce_currency_pos', 'right_space' );
			update_option( 'woocommerce_price_num_decimals', 0 );
		}

		// Accounts stay optional: a shopper can register, but a first order must
		// never be blocked by an SMS code the store has not configured yet.
		update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );
		update_option( 'woocommerce_enable_reviews', 'yes' );
		update_option( 'woocommerce_review_rating_verification_required', 'no' );
		update_option( 'woocommerce_enable_review_rating', 'yes' );

		if ( taxonomy_exists( 'product_cat' ) ) {
			foreach ( moon_shop_categories() as $slug => $name ) {
				if ( ! term_exists( $slug, 'product_cat' ) ) {
					wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
				}
			}
		}
	}

	// Comments: product reviews need a logged-in (phone) account.
	update_option( 'comment_registration', 1 );

	// Primary menu.
	if ( ! wp_get_nav_menu_object( 'منوی اصلی' ) ) {
		$menu_id = wp_create_nav_menu( 'منوی اصلی' );

		if ( ! is_wp_error( $menu_id ) ) {
			$pos = 1;

			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => 'فروشگاه',
				'menu-item-url'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
				'menu-item-position' => $pos++,
			) );

			foreach ( array( 'about' => 'درباره ما', 'contact' => 'تماس با ما' ) as $slug => $title ) {
				$p = get_page_by_path( $slug );

				if ( $p ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => $title,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $p->ID,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $pos++,
					) );
				}
			}

			$locations            = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( 'moon_setup_version', MOON_SETUP_VERSION );
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'moon_run_auto_setup', 20 );

/**
 * If WooCommerce is activated after the theme, finish the store part then.
 */
function moon_setup_after_woo() {
	delete_option( 'moon_setup_version' );
	moon_run_auto_setup();
}
add_action( 'woocommerce_installed', 'moon_setup_after_woo' );
