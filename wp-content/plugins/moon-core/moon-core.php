<?php
/**
 * Plugin Name: MOON Core
 * Plugin URI: https://example.com/moon
 * Description: قابلیت‌های اختصاصی قالب MOON: پنل مدیریت، بخش‌های صفحه اصلی، رگال تعاملی، استودیو استایل و نصب‌کننده محتوای اولیه.
 * Version: 1.0.1
 * Author: MOON Studio
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: moon-core
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_CORE_VERSION', '1.0.1' );
define( 'MOON_CORE_FILE', __FILE__ );
define( 'MOON_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MOON_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once MOON_CORE_DIR . 'includes/helpers.php';
require_once MOON_CORE_DIR . 'includes/class-moon-core.php';

/**
 * Boot the plugin.
 *
 * @return Moon_Core
 */
function moon_core() {
	return Moon_Core::instance();
}
add_action( 'plugins_loaded', 'moon_core' );

/**
 * Activation: register post types, then flush rewrite rules.
 */
function moon_core_activate() {
	require_once MOON_CORE_DIR . 'includes/post-types/class-moon-post-types.php';

	Moon_Post_Types::register();

	// Seed the default homepage order so the builder starts from the source design.
	if ( ! get_option( 'moon_sections' ) ) {
		$sections = array();

		foreach ( array( 'hero', 'categories', 'rack', 'studio', 'products', 'campaign', 'story', 'scrollstory', 'testimonials' ) as $id ) {
			$sections[] = array(
				'id'      => $id,
				'enabled' => 1,
			);
		}

		update_option( 'moon_sections', $sections );
	}

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'moon_core_activate' );

/**
 * Deactivation: flush rewrite rules.
 */
function moon_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'moon_core_deactivate' );
