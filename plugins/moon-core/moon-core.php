<?php
/**
 * Plugin Name:       Moon Core
 * Plugin URI:        https://example.com/moon
 * Description:       هستهٔ فروشگاه MOON: هدر شیشه‌ای شناور، هیرو سینمایی، ویجت‌های واقعی المنتور، حالت شب، فیلترهای ووکامرس و پنل تنظیمات.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            MOON Studio
 * Author URI:        https://example.com/moon
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       moon-core
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:   9.5
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_CORE_VERSION', '1.0.0' );
define( 'MOON_CORE_FILE', __FILE__ );
define( 'MOON_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'MOON_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'MOON_CORE_TD', 'moon-core' );

require_once MOON_CORE_PATH . 'includes/class-settings.php';
require_once MOON_CORE_PATH . 'includes/class-assets.php';
require_once MOON_CORE_PATH . 'includes/class-dark-mode.php';
require_once MOON_CORE_PATH . 'includes/class-header.php';
require_once MOON_CORE_PATH . 'includes/class-elementor.php';
require_once MOON_CORE_PATH . 'includes/class-woocommerce.php';
require_once MOON_CORE_PATH . 'includes/class-newsletter.php';
require_once MOON_CORE_PATH . 'includes/class-contact.php';
require_once MOON_CORE_PATH . 'includes/class-demo.php';

/**
 * Main plugin controller.
 */
final class Moon_Core {

	/**
	 * Singleton instance.
	 *
	 * @var Moon_Core|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Moon_Core
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
		// Loaded before `init` so Elementor's widget registration (which reads our
		// translated titles) never triggers just-in-time translation loading.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 5 );
		add_action( 'init', array( 'Moon_Core_Header', 'init' ) );
		add_action( 'init', array( 'Moon_Core_Dark_Mode', 'init' ) );
		add_action( 'init', array( 'Moon_Core_Newsletter', 'init' ) );
		add_action( 'init', array( 'Moon_Core_Contact', 'init' ) );

		Moon_Core_Settings::instance();
		Moon_Core_Assets::init();
		Moon_Core_Elementor::init();
		// WooCommerce loads after this plugin (alphabetical order), so its hooks can
		// only be registered once every plugin file has been included.
		add_action( 'plugins_loaded', array( 'Moon_Core_WooCommerce', 'init' ), 20 );

		if ( is_admin() ) {
			Moon_Core_Demo::init();
		}

		add_action( 'before_woocommerce_init', array( $this, 'declare_woocommerce_compatibility' ) );
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( MOON_CORE_TD, false, dirname( plugin_basename( MOON_CORE_FILE ) ) . '/languages' );
	}

	/**
	 * Declare compatibility with WooCommerce HPOS and blocks.
	 */
	public function declare_woocommerce_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MOON_CORE_FILE, true );
		}
	}
}

/**
 * Shared helper: read a Moon Core setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function moon_core_setting( $key, $default = null ) {
	return Moon_Core_Settings::get( $key, $default );
}

Moon_Core::instance();

register_activation_hook(
	MOON_CORE_FILE,
	static function () {
		// Nothing persistent beyond WordPress option defaults; the options are
		// created lazily from Moon_Core_Settings::defaults().
		if ( false === get_option( Moon_Core_Settings::OPTION ) ) {
			add_option( Moon_Core_Settings::OPTION, Moon_Core_Settings::defaults() );
		}
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	MOON_CORE_FILE,
	static function () {
		flush_rewrite_rules();
	}
);
