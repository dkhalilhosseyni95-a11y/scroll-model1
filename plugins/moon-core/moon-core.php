<?php
/**
 * Plugin Name:       Moon Core
 * Plugin URI:        https://example.com/moon
 * Description:       MOON fashion widgets and storefront behaviour for Elementor and WooCommerce. Provides the editable MOON section library, the floating header/footer chrome, the interactive clothing rack and outfit studio, and WooCommerce product widgets.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Requires Plugins:  elementor
 * Author:            MOON
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       moon-core
 * Domain Path:       /languages
 *
 * Elementor tested up to: 3.24
 * Elementor Pro tested up to: 3.24
 * WC requires at least: 8.0
 * WC tested up to: 9.4
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_CORE_VERSION', '1.0.1' );
define( 'MOON_CORE_FILE', __FILE__ );
define( 'MOON_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MOON_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'MOON_CORE_BASENAME', plugin_basename( __FILE__ ) );

// Minimum supported versions — derived from the APIs actually used.
define( 'MOON_CORE_MIN_PHP', '8.1' );
define( 'MOON_CORE_MIN_WP', '6.4' );
define( 'MOON_CORE_MIN_ELEMENTOR', '3.18' );
define( 'MOON_CORE_MIN_WOOCOMMERCE', '8.0' );

require_once MOON_CORE_DIR . 'includes/class-requirements.php';

if ( ! \MoonCore\Requirements::environment_ok() ) {
	return;
}

/**
 * Boot once Elementor is available.
 *
 * Elementor registers its class autoloader during its own initialisation, so Moon
 * Core boots on `plugins_loaded` after Elementor — never at plugin include time,
 * whatever the plugin folder order is.
 *
 * @return void
 */
function moon_core_boot(): void {
	static $booted = false;

	if ( $booted || ! defined( 'ELEMENTOR_VERSION' ) || ! \MoonCore\Requirements::elementor_ok() ) {
		return;
	}

	$booted = true;

	require_once MOON_CORE_DIR . 'includes/class-plugin.php';
	\MoonCore\Plugin::instance()->boot();
}

// Elementor registers its class autoloader while it initialises on plugins_loaded,
// so Moon Core boots after that (never at plugin include time) and keeps working
// regardless of plugin folder order.
add_action( 'plugins_loaded', 'moon_core_boot', 99 );
add_action( 'elementor/loaded', 'moon_core_boot' );

// Explain a missing Elementor install instead of fataling.
add_action(
	'plugins_loaded',
	static function () {
		if ( did_action( 'elementor/loaded' ) ) {
			return;
		}

		\MoonCore\Requirements::notice(
			static function (): string {
				return __( 'Moon Core needs the Elementor plugin. Install and activate Elementor to use the MOON widgets.', 'moon-core' );
			}
		);
	},
	30
);
