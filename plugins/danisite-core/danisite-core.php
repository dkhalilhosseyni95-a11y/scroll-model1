<?php
/**
 * Plugin Name: DaniSite Core
 * Plugin URI: https://danisite.ir
 * Description: هسته دنی سایت — نوع‌های محتوا، ویجت‌های اختصاصی المنتور، تنظیمات سایت، سئو، فرم تماس و درون‌ریز کامل سایت.
 * Version: 1.0.2
 * Author: DaniSite Studio
 * Author URI: https://danisite.ir
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: danisite-core
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package DaniSite_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'DANI_CORE_VERSION', '1.0.2' );
define( 'DANI_CORE_FILE', __FILE__ );
define( 'DANI_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DANI_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once DANI_CORE_PATH . 'includes/class-dani-post-types.php';
require_once DANI_CORE_PATH . 'includes/class-dani-fields.php';
require_once DANI_CORE_PATH . 'includes/class-dani-query.php';
require_once DANI_CORE_PATH . 'includes/blocks/class-dani-blocks.php';
require_once DANI_CORE_PATH . 'includes/blocks/class-dani-sections.php';
require_once DANI_CORE_PATH . 'includes/class-dani-shortcodes.php';
require_once DANI_CORE_PATH . 'includes/class-dani-settings.php';
require_once DANI_CORE_PATH . 'includes/class-dani-seo.php';
require_once DANI_CORE_PATH . 'includes/class-dani-forms.php';
require_once DANI_CORE_PATH . 'includes/class-dani-importer.php';
require_once DANI_CORE_PATH . 'includes/class-dani-elementor.php';

/**
 * Boot the plugin.
 *
 * @return void
 */
function dani_core_boot() {
	Dani_Post_Types::init();
	Dani_Fields::init();
	Dani_Shortcodes::init();
	Dani_Settings::init();
	Dani_Seo::init();
	Dani_Forms::init();
	Dani_Importer::init();
	Dani_Elementor::init();
}
add_action( 'plugins_loaded', 'dani_core_boot' );

/**
 * Activation: register content types once so permalinks can be flushed.
 *
 * @return void
 */
function dani_core_activate() {
	Dani_Post_Types::register();
	flush_rewrite_rules();

	update_option( 'dani_core_version', DANI_CORE_VERSION );
}
register_activation_hook( __FILE__, 'dani_core_activate' );

/**
 * Deactivation: drop rewrite rules only.
 *
 * @return void
 */
function dani_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'dani_core_deactivate' );
