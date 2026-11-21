<?php
/**
 * Plugin Name:       MOON Companion
 * Plugin URI:        https://example.com/moon
 * Description:       Shop- and customer-critical features for the MOON theme: Zibal payment gateway, phone (OTP) login and registration, checkout account guard and account wishlist sync. Kept in a plugin so orders and customers keep working if the theme is switched.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            MOON
 * Text Domain:       moon-companion
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   9.9
 *
 * @package Moon_Companion
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_COMPANION_VERSION', '1.0.0' );
define( 'MOON_COMPANION_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Boots the companion modules.
 */
final class Moon_Companion {

	/**
	 * Singleton.
	 *
	 * @var Moon_Companion|null
	 */
	private static $instance = null;

	/**
	 * Instance accessor.
	 *
	 * @return Moon_Companion
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wire the modules.
	 */
	private function __construct() {
		// Phone login / registration.
		if ( ! class_exists( 'Moon_OTP', false ) ) {
			require_once MOON_COMPANION_DIR . 'includes/class-moon-otp.php';
		}

		if ( class_exists( 'Moon_OTP' ) ) {
			Moon_OTP::init();
		}

		// Checkout account guard + wishlist sync.
		require_once MOON_COMPANION_DIR . 'includes/account-flow.php';

		// Payment gateway.
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos' ) );
	}

	/**
	 * Register the Zibal gateway once WooCommerce is loaded.
	 *
	 * @param array $gateways Gateway class names.
	 * @return array
	 */
	public function register_gateway( $gateways ) {
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			require_once MOON_COMPANION_DIR . 'includes/class-moon-zibal-gateway.php';

			if ( class_exists( 'Moon_Zibal_Gateway' ) ) {
				$gateways[] = 'Moon_Zibal_Gateway';
			}
		}

		return $gateways;
	}

	/**
	 * Declare compatibility with WooCommerce High-Performance Order Storage.
	 */
	public function declare_hpos() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}

/**
 * Whether WooCommerce is active (used by the admin notice).
 *
 * @return bool
 */
function moon_companion_woo_active() {
	return class_exists( 'WooCommerce' );
}

add_action( 'plugins_loaded', function () {
	Moon_Companion::instance();
}, 5 );

add_action( 'admin_notices', function () {
	if ( moon_companion_woo_active() ) {
		return;
	}

	echo '<div class="notice notice-warning"><p><strong>MOON Companion:</strong> ' . esc_html__( 'برای درگاه پرداخت و بخش فروشگاه، افزونه WooCommerce را نصب و فعال کنید.', 'moon-companion' ) . '</p></div>';
} );

register_activation_hook( __FILE__, function () {
	if ( ! get_option( 'moon_companion_installed' ) ) {
		update_option( 'moon_companion_installed', gmdate( 'c' ), false );
	}
} );
