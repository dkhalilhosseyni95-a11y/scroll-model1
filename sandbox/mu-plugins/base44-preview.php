<?php
/**
 * Plugin Name: Base44 Preview URL
 * Description: Makes WordPress answer on the Base44 preview origin. Only active when
 *              BASE44_PREVIEW_MODE is exactly "1"; unset means normal behaviour.
 *
 * The sandbox id rotates, so the origin is never hardcoded — it is read from
 * MOON_PREVIEW_ORIGIN, which compose builds from BASE44_PUBLIC_HOST_SUFFIX.
 *
 * @package MoonCore\Sandbox
 */

defined( 'ABSPATH' ) || exit;

if ( '1' !== getenv( 'BASE44_PREVIEW_MODE' ) ) {
	return;
}

$moon_preview_origin = getenv( 'MOON_PREVIEW_ORIGIN' );

if ( ! $moon_preview_origin ) {
	return;
}

$moon_preview_origin = untrailingslashit( $moon_preview_origin );
$moon_preview_host   = wp_parse_url( $moon_preview_origin, PHP_URL_HOST );
$moon_preview_scheme = wp_parse_url( $moon_preview_origin, PHP_URL_SCHEME );

// Keep every generated URL on the public origin.
add_filter(
	'option_home',
	static function () use ( $moon_preview_origin ) {
		return $moon_preview_origin;
	}
);
add_filter(
	'option_siteurl',
	static function () use ( $moon_preview_origin ) {
		return $moon_preview_origin;
	}
);

// The proxy forwards an internal Host header; without this rewrite WordPress
// canonical-redirects forever (ERR_TOO_MANY_REDIRECTS).
if ( $moon_preview_host ) {
	$_SERVER['HTTP_HOST']   = $moon_preview_host;
	$_SERVER['SERVER_NAME'] = $moon_preview_host;
}

if ( 'https' === $moon_preview_scheme ) {
	$_SERVER['HTTPS']       = 'on';
	$_SERVER['SERVER_PORT'] = '443';
} else {
	unset( $_SERVER['HTTPS'] );
	$_SERVER['SERVER_PORT'] = '80';
}

// Trust the proxy's forwarded protocol for is_ssl() decisions.
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

// Elementor's dev assets and HMR are gated by Origin; allow the preview origin.
add_filter(
	'http_origin',
	static function ( $origin ) use ( $moon_preview_origin ) {
		return $moon_preview_origin;
	}
);
