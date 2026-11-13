<?php
/**
 * Asset loading.
 *
 * Fonts and tokens load everywhere; WooCommerce styling only on store pages and
 * Elementor editor previews so the front page stays lean.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		$parent = 'hello-elementor';

		wp_enqueue_style( 'moon-child', get_stylesheet_uri(), array( $parent ), MOON_CHILD_VERSION );
		wp_enqueue_style( 'moon-fonts', MOON_CHILD_URL . '/assets/css/fonts.css', array(), MOON_CHILD_VERSION );
		wp_enqueue_style( 'moon-tokens', MOON_CHILD_URL . '/assets/css/tokens.css', array( 'moon-fonts' ), MOON_CHILD_VERSION );
		wp_enqueue_style( 'moon-base', MOON_CHILD_URL . '/assets/css/base.css', array( 'moon-tokens' ), MOON_CHILD_VERSION );
		wp_enqueue_style( 'moon-layout', MOON_CHILD_URL . '/assets/css/layout.css', array( 'moon-base' ), MOON_CHILD_VERSION );

		if ( is_rtl() ) {
			wp_enqueue_style( 'moon-rtl', MOON_CHILD_URL . '/assets/css/rtl.css', array( 'moon-layout' ), MOON_CHILD_VERSION );
		}

		if ( moon_child_is_store_context() ) {
			wp_enqueue_style( 'moon-woocommerce', MOON_CHILD_URL . '/assets/css/woocommerce.css', array( 'moon-layout' ), MOON_CHILD_VERSION );
		}

		wp_enqueue_script( 'moon-theme', MOON_CHILD_URL . '/assets/js/theme.js', array(), MOON_CHILD_VERSION, true );
		wp_localize_script(
			'moon-theme',
			'moonTheme',
			array(
				'restUrl'   => esc_url_raw( rest_url() ),
				'homeUrl'   => esc_url_raw( home_url( '/' ) ),
				'isRtl'     => is_rtl(),
				'cartUrl'   => function_exists( 'wc_get_cart_url' ) ? esc_url_raw( wc_get_cart_url() ) : '',
				'i18n'      => array(
					'openMenu'   => __( 'Open menu', 'moon-child' ),
					'closeMenu'  => __( 'Close menu', 'moon-child' ),
					'lightMode'  => __( 'Switch to light mode', 'moon-child' ),
					'darkMode'   => __( 'Switch to dark mode', 'moon-child' ),
				),
			)
		);

		// Preload the two display fonts that paint above the fold.
		add_action(
			'wp_head',
			static function () {
				printf(
					'<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( MOON_CHILD_URL . '/assets/fonts/cormorant-garamond-normal-400-latin.woff2' )
				);
				printf(
					'<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( MOON_CHILD_URL . '/assets/fonts/vazirmatn-normal-400-latin.woff2' )
				);
			},
			1
		);
	},
	20
);

/**
 * Apply the stored colour scheme before first paint (no flash of light theme).
 */
add_action(
	'wp_head',
	static function () {
		?>
		<script>(function(){try{var s=localStorage.getItem('moon-scheme')||'auto';document.documentElement.setAttribute('data-moon-scheme',s);}catch(e){document.documentElement.setAttribute('data-moon-scheme','auto');}})();</script>
		<?php
	},
	1
);

/**
 * Editor styles so the Elementor canvas matches the front end.
 */
add_action(
	'elementor/editor/after_enqueue_styles',
	static function () {
		wp_enqueue_style( 'moon-fonts-editor', MOON_CHILD_URL . '/assets/css/fonts.css', array(), MOON_CHILD_VERSION );
		wp_enqueue_style( 'moon-tokens-editor', MOON_CHILD_URL . '/assets/css/tokens.css', array(), MOON_CHILD_VERSION );
		wp_enqueue_style( 'moon-base-editor', MOON_CHILD_URL . '/assets/css/base.css', array( 'moon-tokens-editor' ), MOON_CHILD_VERSION );
	}
);

/**
 * Is the current request a store page (or the Elementor editor previewing one)?
 */
function moon_child_is_store_context(): bool {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}

	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return true;
	}

	// Elementor's editor loads widgets in an iframe; keep store styles available.
	if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}

	return false;
}
