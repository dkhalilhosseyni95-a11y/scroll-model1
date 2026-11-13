<?php
/**
 * Shared helpers: images, links, icons and small formatting utilities.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Helpers {

	/**
	 * Registered image sizes for Elementor selects.
	 *
	 * @return array<string,string>
	 */
	public static function image_size_options(): array {
		$options = array(
			'thumbnail'      => __( 'Thumbnail', 'moon-core' ),
			'medium'         => __( 'Medium', 'moon-core' ),
			'medium_large'   => __( 'Medium large', 'moon-core' ),
			'large'          => __( 'Large', 'moon-core' ),
			'full'           => __( 'Full', 'moon-core' ),
			'moon-portrait'  => __( 'MOON portrait (4:5 crop)', 'moon-core' ),
			'moon-editorial' => __( 'MOON editorial (16:9 crop)', 'moon-core' ),
			'moon-product'   => __( 'MOON product (4:5 crop)', 'moon-core' ),
		);

		/**
		 * Filter the available image sizes.
		 *
		 * @param array<string,string> $options Size slug => label.
		 */
		return (array) apply_filters( 'moon_core_image_sizes', $options );
	}

	/**
	 * Render an Elementor MEDIA control value as an <img>.
	 *
	 * @param array<string,mixed> $media     Elementor media control value.
	 * @param string              $size      Image size slug.
	 * @param array<string,mixed> $attr      Extra attributes.
	 * @param bool                $lazy      Whether to lazy-load.
	 */
	public static function image( array $media, string $size = 'large', array $attr = array(), bool $lazy = true ): string {
		$id  = isset( $media['id'] ) ? (int) $media['id'] : 0;
		$url = isset( $media['url'] ) ? (string) $media['url'] : '';

		$attr['loading'] = $lazy ? 'lazy' : 'eager';
		$attr['decoding'] = 'async';

		if ( $id ) {
			$html = wp_get_attachment_image( $id, $size, false, $attr );
			if ( $html ) {
				return $html;
			}
		}

		if ( '' === $url ) {
			return '';
		}

		$attr['src'] = esc_url( $url );
		$attr['alt'] = $attr['alt'] ?? '';

		$attributes = '';
		foreach ( $attr as $name => $value ) {
			$attributes .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( (string) $value ) );
		}

		return '<img' . $attributes . ' />';
	}

	/**
	 * URL from an Elementor media control value.
	 *
	 * @param array<string,mixed> $media Elementor media value.
	 */
	public static function media_url( array $media ): string {
		return isset( $media['url'] ) ? (string) $media['url'] : '';
	}

	/**
	 * Open a link built from an Elementor URL control, sanitised.
	 *
	 * @param array<string,mixed> $link    Elementor URL control value.
	 * @param string              $class   CSS classes.
	 * @param string              $inner   Already-escaped inner HTML.
	 * @param string              $default Fallback href.
	 */
	public static function link( array $link, string $class, string $inner, string $default = '#' ): string {
		$url = isset( $link['url'] ) && '' !== $link['url'] ? $link['url'] : $default;
		if ( '' === $url ) {
			return '<span class="' . esc_attr( $class ) . '">' . $inner . '</span>';
		}

		$target = ! empty( $link['is_external'] ) ? ' target="_blank"' : '';
		$rel    = ! empty( $link['nofollow'] ) ? ' rel="nofollow noopener"' : ( $target ? ' rel="noopener"' : '' );

		return sprintf(
			'<a class="%1$s" href="%2$s"%3$s%4$s>%5$s</a>',
			esc_attr( $class ),
			esc_url( $url ),
			$target,
			$rel,
			$inner
		);
	}

	/**
	 * Inline icon (single source of truth, no icon font).
	 *
	 * @param string $name  Icon slug.
	 * @param int    $size  Pixel size.
	 * @param string $class Extra classes.
	 */
	public static function icon( string $name, int $size = 18, string $class = '' ): string {
		$paths = array(
			'arrow-right'  => '<path d="M4 12h15M13 6l6 6-6 6"/>',
			'arrow-left'   => '<path d="M20 12H5M11 6l-6 6 6 6"/>',
			'chevron-down' => '<path d="M6 9l6 6 6-6"/>',
			'search'       => '<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-3.6-3.6"/>',
			'bag'          => '<path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
			'user'         => '<circle cx="12" cy="8.5" r="3.5"/><path d="M5 20c0-3.6 3.1-6.5 7-6.5s7 2.9 7 6.5"/>',
			'heart'        => '<path d="M12 20s-7-4.4-7-9a3.9 3.9 0 0 1 7-2.4A3.9 3.9 0 0 1 19 11c0 4.6-7 9-7 9z"/>',
			'menu'         => '<path d="M4 7h16M4 12h16M4 17h16"/>',
			'close'        => '<path d="M6 6l12 12M18 6L6 18"/>',
			'plus'         => '<path d="M12 5v14M5 12h14"/>',
			'minus'        => '<path d="M5 12h14"/>',
			'sun'          => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5 5l1.4 1.4M17.6 17.6L19 19M19 5l-1.4 1.4M6.4 17.6L5 19"/>',
			'moon'         => '<path d="M20 14.2A8.2 8.2 0 0 1 9.8 4a8.3 8.3 0 1 0 10.2 10.2z"/>',
			'play'         => '<path d="M8 5.5v13l11-6.5-11-6.5z"/>',
			'check'        => '<path d="M4 12.5l5 5L20 6.5"/>',
			'arrow-up'     => '<path d="M12 20V4M6 10l6-6 6 6"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}

		return sprintf(
			'<svg class="moon-icon %1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
			esc_attr( 'moon-icon--' . $name . ( $class ? ' ' . $class : '' ) ),
			(int) $size,
			$paths[ $name ]
		);
	}

	/**
	 * Truncate rich text for teasers while keeping formatting.
	 */
	public static function excerpt( string $text, int $words = 26 ): string {
		return wp_trim_words( wp_strip_all_tags( $text ), $words, '…' );
	}

	/**
	 * Sanitised inline SVG string for echo contexts.
	 *
	 * @param string $svg Raw SVG markup produced by icon().
	 */
	public static function svg( string $svg ): string {
		return wp_kses(
			$svg,
			array(
				'svg'      => array(
					'class'        => true,
					'width'        => true,
					'height'       => true,
					'viewbox'      => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
					'stroke-linecap' => true,
					'stroke-linejoin' => true,
					'aria-hidden'  => true,
					'focusable'    => true,
				),
				'path'     => array( 'd' => true ),
				'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true ),
				'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ),
				'polyline' => array( 'points' => true ),
				'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
			)
		);
	}
}
