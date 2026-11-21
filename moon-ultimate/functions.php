<?php
/**
 * MOON ULTIMATE theme bootstrap.
 *
 * Merged from MOON 2.2.0 (base) and MOON 2.1.2 (builders, design tokens,
 * compare, shop extras, dashboard statistics, full settings schema).
 *
 * Shop- and customer-critical code (payment gateway, phone login, checkout
 * account guard and wishlist sync) lives in the MOON Companion plugin so it
 * survives a theme switch. The theme keeps working without it and simply hides
 * those features.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MOON_THEME_VERSION' ) ) {
	define( 'MOON_THEME_VERSION', '3.0.1' );
}
if ( ! defined( 'MOON_THEME_DIR' ) ) {
	define( 'MOON_THEME_DIR', get_template_directory() );
}
if ( ! defined( 'MOON_THEME_URI' ) ) {
	define( 'MOON_THEME_URI', get_template_directory_uri() );
}
if ( ! defined( 'MOON_THEME_CORE_DIR' ) ) {
	define( 'MOON_THEME_CORE_DIR', MOON_THEME_DIR . '/inc/core/' );
}
if ( ! defined( 'MOON_THEME_CORE_URL' ) ) {
	define( 'MOON_THEME_CORE_URL', MOON_THEME_URI . '/inc/core/' );
}

require_once MOON_THEME_DIR . '/inc/helpers.php';
require_once MOON_THEME_DIR . '/inc/brand.php';
require_once MOON_THEME_DIR . '/inc/setup.php';
require_once MOON_THEME_DIR . '/inc/enqueue.php';
require_once MOON_THEME_DIR . '/inc/template-functions.php';
require_once MOON_THEME_DIR . '/inc/customizer.php';
require_once MOON_THEME_DIR . '/inc/newsletter.php';
require_once MOON_THEME_DIR . '/inc/navigation.php';
require_once MOON_THEME_DIR . '/inc/megamenu.php';
require_once MOON_THEME_DIR . '/inc/legal-pages.php';
require_once MOON_THEME_DIR . '/inc/integrations/woocommerce.php';
require_once MOON_THEME_DIR . '/inc/auto-setup.php';
require_once MOON_THEME_DIR . '/inc/cart-ajax.php';
require_once MOON_THEME_DIR . '/inc/ajax-nonce.php';
require_once MOON_THEME_DIR . '/inc/reviews.php';
require_once MOON_THEME_DIR . '/inc/checkout.php';
require_once MOON_THEME_DIR . '/inc/contact.php';
require_once MOON_THEME_DIR . '/inc/live-search.php';
require_once MOON_THEME_DIR . '/inc/seo.php';
require_once MOON_THEME_DIR . '/inc/security.php';
require_once MOON_THEME_DIR . '/inc/performance.php';
require_once MOON_THEME_DIR . '/inc/i18n-fa.php';

/*
 * Panel, builders, shop layer and compare modules.
 *
 * If the legacy "moon-core" plugin is still active it already provides the
 * same classes; loading them twice would be a fatal error, so the theme backs
 * off and asks the owner to deactivate the old plugin.
 */
if ( defined( 'MOON_CORE_VERSION' ) || class_exists( 'Moon_Core', false ) || function_exists( 'moon_core' ) || function_exists( 'moon_core_boot' ) ) {
	define( 'MOON_LEGACY_PLUGIN_ACTIVE', true );

	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p><strong>MOON:</strong> ' . esc_html__( 'افزونه قدیمی «MOON Core» هنوز فعال است. آن را غیرفعال و حذف کنید؛ تمام قابلیت‌ها اکنون داخل خود قالب هستند.', 'moon-theme' ) . '</p></div>';
	} );
} else {
	require_once MOON_THEME_CORE_DIR . 'helpers.php';
	require_once MOON_THEME_CORE_DIR . 'class-moon-core.php';

	/**
	 * Boot the core modules early enough for the `init` hooks they register.
	 */
	function moon_theme_boot_core() {
		return Moon_Core::instance();
	}
	add_action( 'after_setup_theme', 'moon_theme_boot_core', 5 );
}

/**
 * Theme activation: register post types, seed the homepage builder, make sure
 * the compare page exists and flush rewrites.
 */
function moon_theme_activate() {
	if ( ! class_exists( 'Moon_Post_Types', false ) ) {
		require_once MOON_THEME_CORE_DIR . 'post-types/class-moon-post-types.php';
	}

	Moon_Post_Types::register();

	if ( ! get_option( 'moon_sections' ) && class_exists( 'Moon_Sections' ) ) {
		update_option( 'moon_sections', Moon_Sections::default_rows() );
	}

	if ( class_exists( 'Moon_Compare' ) ) {
		Moon_Compare::ensure_page();
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'moon_theme_activate' );

/**
 * Show a notice when the companion plugin is missing, because payments and
 * phone login then do not exist.
 */
function moon_theme_companion_notice() {
	if ( class_exists( 'Moon_Companion' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p><strong>MOON:</strong> ' . esc_html__( 'برای درگاه پرداخت زیبال و ورود با کد پیامکی، افزونه «MOON Companion» را نصب و فعال کنید. بقیه قالب بدون آن هم کار می‌کند.', 'moon-theme' ) . '</p></div>';
}
add_action( 'admin_notices', 'moon_theme_companion_notice' );
