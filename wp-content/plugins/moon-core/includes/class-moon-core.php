<?php
/**
 * Plugin bootstrap.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads every module.
 */
final class Moon_Core {

	/**
	 * Singleton instance.
	 *
	 * @var Moon_Core|null
	 */
	private static $instance = null;

	/**
	 * Instance accessor.
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
	 * Wire everything up.
	 */
	private function __construct() {
		$this->includes();

		Moon_Post_Types::init();
		Moon_Fields::init();
		Moon_Settings::init();
		Moon_Admin::init();
		Moon_Demo_Importer::init();

		add_action( 'admin_notices', array( $this, 'theme_notice' ) );
	}

	/**
	 * Load classes.
	 */
	private function includes() {
		require_once MOON_CORE_DIR . 'includes/post-types/class-moon-post-types.php';
		require_once MOON_CORE_DIR . 'includes/modules/class-moon-fields.php';
		require_once MOON_CORE_DIR . 'includes/settings/class-moon-settings.php';
		require_once MOON_CORE_DIR . 'includes/admin/class-moon-admin.php';
		require_once MOON_CORE_DIR . 'includes/modules/class-moon-demo-importer.php';
	}

	/**
	 * Notice when the theme is not active.
	 */
	public function theme_notice() {
		if ( moon_core_theme_active() || ! current_user_can( 'install_themes' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'افزونه MOON Core برای نمایش کامل سایت به قالب MOON نیاز دارد. قالب را از بخش نمایش → پوسته‌ها فعال کنید.', 'moon-core' ) . '</p></div>';
	}
}
