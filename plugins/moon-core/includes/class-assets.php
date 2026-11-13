<?php
/**
 * Asset registration and loading.
 *
 * Widget assets are only *registered* here. Elementor enqueues them through
 * get_style_depends()/get_script_depends() when a widget is on the page, so the
 * front end never loads unused widget CSS or JS.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Assets {

	private static ?Assets $instance = null;

	public static function instance(): Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_chrome' ), 15 );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_head', array( $this, 'inline_tokens' ), 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Colour/geometry tokens from the settings page, printed before the theme CSS.
	 */
	public function inline_tokens(): void {
		printf( '<style id="moon-core-tokens">%s</style>' . "\n", Settings::token_css() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values sanitised in Settings.
	}

	/**
	 * Header/footer chrome + reveal engine are needed site-wide (the theme prints
	 * the header outside Elementor content).
	 */
	public function enqueue_chrome(): void {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style( 'moon-core-base', MOON_CORE_URL . 'assets/css/base.css', array( 'moon-tokens' ), MOON_CORE_VERSION );
		wp_enqueue_style( 'moon-core-chrome', MOON_CORE_URL . 'assets/css/chrome.css', array( 'moon-core-base' ), MOON_CORE_VERSION );
		wp_enqueue_script( 'moon-core-base', MOON_CORE_URL . 'assets/js/base.js', array(), MOON_CORE_VERSION, true );
		wp_enqueue_script( 'moon-core-chrome', MOON_CORE_URL . 'assets/js/chrome.js', array( 'moon-core-base' ), MOON_CORE_VERSION, true );

		wp_localize_script(
			'moon-core-chrome',
			'moonCore',
			array(
				'ajaxUrl'   => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'restUrl'   => esc_url_raw( rest_url() ),
				'cartUrl'   => function_exists( 'wc_get_cart_url' ) ? esc_url_raw( wc_get_cart_url() ) : '',
				'isRtl'     => is_rtl(),
				'quickView' => Settings::get( 'quick_view' ) === 'yes',
				'nonces'    => array(
					'quickView'  => wp_create_nonce( Ajax::NONCE_QUICK_VIEW ),
					'newsletter' => wp_create_nonce( Ajax::NONCE_NEWSLETTER ),
					'search'     => wp_create_nonce( Ajax::NONCE_SEARCH ),
				),
				'i18n'      => array(
					'added'      => __( 'Added to cart', 'moon-core' ),
					'viewCart'   => __( 'View cart', 'moon-core' ),
					'loading'    => __( 'Loading…', 'moon-core' ),
					'error'      => __( 'Something went wrong. Please try again.', 'moon-core' ),
					'close'      => __( 'Close', 'moon-core' ),
					'noProducts' => __( 'No products found.', 'moon-core' ),
				),
			)
		);
	}

	public function register_styles(): void {
		wp_register_style( 'moon-core-base', MOON_CORE_URL . 'assets/css/base.css', array(), MOON_CORE_VERSION );
		wp_register_style( 'moon-core-chrome', MOON_CORE_URL . 'assets/css/chrome.css', array( 'moon-core-base' ), MOON_CORE_VERSION );

		foreach ( Widget_Registry::instance()->all() as $slug => $config ) {
			if ( empty( $config['css'] ) ) {
				continue;
			}

			wp_register_style(
				'moon-core-' . $slug,
				MOON_CORE_URL . 'widgets/' . $slug . '/' . $slug . '.css',
				array( 'moon-core-base' ),
				MOON_CORE_VERSION
			);
		}
	}

	public function register_scripts(): void {
		$base_deps = array( 'moon-core-base' );

		wp_register_script( 'moon-core-base', MOON_CORE_URL . 'assets/js/base.js', array(), MOON_CORE_VERSION, true );
		wp_register_script( 'moon-core-chrome', MOON_CORE_URL . 'assets/js/chrome.js', array( 'moon-core-base' ), MOON_CORE_VERSION, true );

		foreach ( Widget_Registry::instance()->all() as $slug => $config ) {
			if ( empty( $config['js'] ) ) {
				continue;
			}

			$deps = $config['script_deps'] ?? array();
			$deps = array_merge( $base_deps, $deps );

			if ( ! empty( $config['requires'] ) && in_array( 'woocommerce', (array) $config['requires'], true ) && class_exists( 'WooCommerce' ) ) {
				$deps[] = 'wc-add-to-cart';
			}

			wp_register_script(
				'moon-core-' . $slug,
				MOON_CORE_URL . 'widgets/' . $slug . '/' . $slug . '.js',
				array_values( array_unique( $deps ) ),
				MOON_CORE_VERSION,
				true
			);
		}
	}

	public function admin_assets( string $hook = '' ): void {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'moon-core' !== $page ) {
			return;
		}

		wp_enqueue_style(
			'moon-core-admin',
			MOON_CORE_URL . 'assets/css/admin.css',
			array(),
			MOON_CORE_VERSION
		);
	}
}
