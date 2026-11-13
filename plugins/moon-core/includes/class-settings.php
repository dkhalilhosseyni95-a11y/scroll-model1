<?php
/**
 * Plugin settings: one option, one sanitiser, one schema.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Settings {

	public const OPTION = 'moon_core_settings';

	/**
	 * Default settings. Widgets read these so a fresh install still looks MOON.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults(): array {
		return array(
			'brand_name'        => 'MOON',
			'brand_tagline'     => __( 'Modern wardrobe, editorial spirit', 'moon-core' ),
			'default_scheme'    => 'auto',
			'header_sticky'     => 'yes',
			'header_hide'       => 'yes',
			'accent'            => '#c6a875',
			'radius'            => 4,
			'container'         => 1320,
			'quick_view'        => 'yes',
			'newsletter'        => 'store',
			'newsletter_action' => '',
			'disabled_widgets'  => array(),
		);
	}

	/**
	 * All settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function all(): array {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * One setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback when the key is missing.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		$all = self::all();

		return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	/**
	 * Persist a partial update. Capability checks belong to the caller.
	 *
	 * @param array<string,mixed> $values Raw values.
	 */
	public static function update( array $values ): void {
		$current = self::all();
		$merged  = array_merge( $current, $values );
		update_option( self::OPTION, self::sanitize( $merged ) );
	}

	/**
	 * Sanitise the whole settings array.
	 *
	 * @param array<string,mixed> $raw Raw input.
	 * @return array<string,mixed>
	 */
	public static function sanitize( array $raw ): array {
		$defaults = self::defaults();

		$clean = array(
			'brand_name'        => sanitize_text_field( (string) ( $raw['brand_name'] ?? $defaults['brand_name'] ) ),
			'brand_tagline'     => sanitize_text_field( (string) ( $raw['brand_tagline'] ?? '' ) ),
			'default_scheme'    => in_array( $raw['default_scheme'] ?? '', array( 'auto', 'light', 'dark' ), true ) ? (string) $raw['default_scheme'] : 'auto',
			'header_sticky'     => ( $raw['header_sticky'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
			'header_hide'       => ( $raw['header_hide'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
			'accent'            => sanitize_hex_color( (string) ( $raw['accent'] ?? '' ) ) ?: $defaults['accent'],
			'radius'            => max( 0, min( 32, absint( $raw['radius'] ?? 4 ) ) ),
			'container'         => max( 960, min( 1920, absint( $raw['container'] ?? 1320 ) ) ),
			'quick_view'        => ( $raw['quick_view'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
			'newsletter'        => in_array( $raw['newsletter'] ?? '', array( 'store', 'remote' ), true ) ? (string) $raw['newsletter'] : 'store',
			'newsletter_action' => esc_url_raw( (string) ( $raw['newsletter_action'] ?? '' ) ),
			'disabled_widgets'  => array_values(
				array_filter(
					array_map( 'sanitize_key', (array) ( $raw['disabled_widgets'] ?? array() ) )
				)
			),
		);

		if ( 'remote' === $clean['newsletter'] && '' === $clean['newsletter_action'] ) {
			$clean['newsletter'] = 'store';
		}

		return $clean;
	}

	/**
	 * Design tokens as CSS custom properties for inline output.
	 */
	public static function token_css(): string {
		$settings = self::all();

		return sprintf(
			':root{--moon-accent:%1$s;--moon-radius:%2$dpx;--moon-container:%3$dpx;}',
			esc_attr( $settings['accent'] ),
			(int) $settings['radius'],
			(int) $settings['container']
		);
	}
}
