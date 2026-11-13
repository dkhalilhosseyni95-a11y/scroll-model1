<?php
/**
 * Plugin boot: wires every module once Elementor is confirmed.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	private static ?Plugin $instance = null;

	/** @var array<string,object> */
	private array $modules = array();

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Load includes and start modules.
	 */
	public function boot(): void {
		$this->includes();

		$this->modules['settings']     = new Settings();
		$this->modules['registry']     = Widget_Registry::instance();
		$this->modules['assets']       = Assets::instance();
		$this->modules['subscribers']  = new Subscribers();

		if ( is_admin() ) {
			$this->modules['admin'] = new Admin();
		}

		$this->modules['ajax'] = new Ajax();

		if ( Requirements::woocommerce_active() ) {
			$this->modules['woocommerce'] = new Woo_Integration();
		}

		foreach ( $this->modules as $module ) {
			if ( method_exists( $module, 'init' ) ) {
				$module->init();
			}
		}

		Requirements::soft_notices();

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Include order matters: traits before classes that use them.
	 */
	private function includes(): void {
		require_once MOON_CORE_DIR . 'includes/class-settings.php';
		require_once MOON_CORE_DIR . 'includes/class-helpers.php';
		// Elementor-dependent classes (widget base, controls trait) are loaded by
		// Widget_Registry::register_widgets() once Elementor's autoloader is ready.
		require_once MOON_CORE_DIR . 'includes/class-widget-registry.php';
		require_once MOON_CORE_DIR . 'includes/class-assets.php';
		require_once MOON_CORE_DIR . 'includes/class-site-chrome.php';
		require_once MOON_CORE_DIR . 'includes/class-product-card.php';
		require_once MOON_CORE_DIR . 'includes/class-subscribers.php';
		require_once MOON_CORE_DIR . 'includes/class-ajax.php';
		require_once MOON_CORE_DIR . 'includes/class-starter-content.php';
		require_once MOON_CORE_DIR . 'includes/class-woo-integration.php';

		if ( is_admin() ) {
			require_once MOON_CORE_DIR . 'includes/class-admin.php';
		}
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'moon-core', false, dirname( MOON_CORE_BASENAME ) . '/languages' );
	}

	/**
	 * Access a booted module (used by templates and other plugins).
	 *
	 * @param string $name Module key.
	 */
	public function module( string $name ): ?object {
		return $this->modules[ $name ] ?? null;
	}
}
