<?php
/**
 * Environment and dependency checks with admin notices.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Requirements {

	/**
	 * PHP + WordPress versions. Runs at plugin load; never touches Elementor.
	 */
	public static function environment_ok(): bool {
		if ( version_compare( PHP_VERSION, MOON_CORE_MIN_PHP, '<' ) ) {
			self::notice(
				static function (): string {
					/* translators: %s: PHP version */
					return sprintf( __( 'Moon Core requires PHP %s or newer.', 'moon-core' ), MOON_CORE_MIN_PHP );
				}
			);
			return false;
		}

		$wp_version = isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : '0';

		if ( version_compare( $wp_version, MOON_CORE_MIN_WP, '<' ) ) {
			self::notice(
				static function (): string {
					/* translators: %s: WordPress version */
					return sprintf( __( 'Moon Core requires WordPress %s or newer.', 'moon-core' ), MOON_CORE_MIN_WP );
				}
			);
			return false;
		}

		return true;
	}

	/**
	 * Elementor presence and minimum version (checked on elementor/loaded).
	 */
	public static function elementor_ok(): bool {
		if ( ! defined( 'ELEMENTOR_VERSION' ) || version_compare( ELEMENTOR_VERSION, MOON_CORE_MIN_ELEMENTOR, '<' ) ) {
			self::notice(
				static function (): string {
					/* translators: %s: Elementor version */
					return sprintf( __( 'Moon Core requires Elementor %s or newer.', 'moon-core' ), MOON_CORE_MIN_ELEMENTOR );
				}
			);
			return false;
		}

		return true;
	}

	/**
	 * True when WooCommerce is active.
	 */
	public static function woocommerce_active(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * True when Elementor Pro is active (Theme Builder, WooCommerce Builder).
	 */
	public static function elementor_pro_active(): bool {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * Soft-dependency warnings shown to administrators only.
	 */
	public static function soft_notices(): void {
		add_action(
			'admin_notices',
			static function () {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				if ( ! self::woocommerce_active() ) {
					self::print_notice(
						__( 'The MOON store widgets (product grid, quick view, mini cart and the other WooCommerce widgets) are inactive until WooCommerce is installed and activated.', 'moon-core' ),
						'warning'
					);
				}

				if ( self::woocommerce_active() && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, MOON_CORE_MIN_WOOCOMMERCE, '<' ) ) {
					self::print_notice(
						sprintf(
							/* translators: %s: WooCommerce version */
							__( 'Moon Core targets WooCommerce %s or newer. Some store hooks may behave differently on this version.', 'moon-core' ),
							MOON_CORE_MIN_WOOCOMMERCE
						),
						'warning'
					);
				}

				if ( ! self::elementor_pro_active() ) {
					self::print_notice(
						__( 'Elementor Pro is recommended for Theme Builder templates (site header/footer, single product and shop archive). Without it, MOON falls back to the theme chrome and the WooCommerce templates.', 'moon-core' ),
						'info'
					);
				}
			}
		);
	}

	/**
	 * Queue a notice; the message is a callable so translations run at render time.
	 *
	 * @param callable $message Returns the translated message.
	 * @param string   $type    error|warning|info|success.
	 */
	public static function notice( callable $message, string $type = 'error' ): void {
		add_action(
			'admin_notices',
			static function () use ( $message, $type ) {
				if ( current_user_can( 'activate_plugins' ) ) {
					self::print_notice( (string) $message(), $type );
				}
			}
		);
	}

	private static function print_notice( string $message, string $type ): void {
		printf(
			'<div class="notice notice-%1$s"><p>%2$s</p></div>',
			esc_attr( $type ),
			esc_html( $message )
		);
	}
}
