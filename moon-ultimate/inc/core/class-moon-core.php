<?php
/**
 * Core bootstrap.
 *
 * Loads and starts every module of the theme's built-in framework: the panel,
 * the design tokens, the homepage/header/footer builders, the shop layer and
 * product compare.
 *
 * Payment gateway and phone authentication live in the MOON Companion plugin
 * (they must survive a theme switch), so this loader only starts them when the
 * plugin provides the classes.
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
		Moon_Tokens::init();
		Moon_Sections::init();
		Moon_Header::init();
		Moon_Footer::init();
		Moon_Admin::init();
		Moon_Colors::init();
		Moon_Shop::init();
		Moon_Compare::init();
		Moon_Woo_Extras::init();

		if ( class_exists( 'Moon_OTP' ) ) {
			Moon_OTP::init();
		}
	}

	/**
	 * Load classes.
	 */
	private function includes() {
		// Guard against a legacy "moon-core" plugin that ships the same classes.
		foreach ( array(
			'post-types/class-moon-post-types.php',
			'modules/class-moon-fields.php',
			'settings/class-moon-settings.php',
			'design/class-moon-tokens.php',
			'modules/class-moon-sections.php',
			'modules/class-moon-header.php',
			'modules/class-moon-footer.php',
			'admin/class-moon-admin.php',
			'admin/class-moon-stats.php',
			'modules/class-moon-colors.php',
			'modules/class-moon-shop.php',
			'modules/class-moon-compare.php',
			'modules/class-moon-woo-extras.php',
		) as $file ) {
			require_once MOON_THEME_CORE_DIR . $file;
		}
	}
}
