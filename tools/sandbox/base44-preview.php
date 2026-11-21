<?php
/**
 * Plugin Name: Base44 preview origin
 * Description: Serves WordPress on the sandbox preview host. Sandbox only.
 *
 * The preview is a proxy in front of the container: the browser asks for
 * https://3000-<public suffix>, but the request that reaches PHP carries the
 * internal sandbox host in `Host`. WordPress would then print that internal
 * host in runtime URLs (AJAX endpoints, redirects), which the browser cannot
 * reach — add-to-cart and login silently fail.
 *
 * With BASE44_PREVIEW_MODE=1 the public origin is derived from
 * BASE44_PUBLIC_HOST_SUFFIX and used for WP_HOME/WP_SITEURL. With the flag
 * unset nothing changes.
 *
 * @package Moon_Sandbox
 */

if ( '1' !== getenv( 'BASE44_PREVIEW_MODE' ) ) {
	return;
}

$moon_suffix = (string) getenv( 'BASE44_PUBLIC_HOST_SUFFIX' );

if ( '' !== $moon_suffix && preg_match( '/^[A-Za-z0-9.\-]+$/', $moon_suffix ) ) {
	$moon_base = 'https://3000-' . $moon_suffix;

	// Behind the preview proxy every request is HTTPS; tell WordPress so it
	// issues secure cookies and same-origin admin URLs.
	$_SERVER['HTTPS']       = 'on';
	$_SERVER['SERVER_PORT'] = '443';

	if ( ! defined( 'WP_HOME' ) ) {
		define( 'WP_HOME', $moon_base );
	}

	if ( ! defined( 'WP_SITEURL' ) ) {
		define( 'WP_SITEURL', $moon_base );
	}

	/*
	 * The proxy forwards the internal sandbox host in `Host`, so WordPress
	 * believes the visitor asked for a different host than home_url() and
	 * redirects there — the browser then loops. Only the host comparison is
	 * dropped; path canonicalisation still runs.
	 */
	add_filter( 'redirect_canonical', function ( $redirect_url, $requested_url ) use ( $moon_base ) {
		$home_host = wp_parse_url( $moon_base, PHP_URL_HOST );
		$req_host  = wp_parse_url( $requested_url, PHP_URL_HOST );

		if ( $req_host && $home_host && strtolower( $req_host ) !== strtolower( $home_host ) ) {
			return false;
		}

		return $redirect_url;
	}, 10, 2 );

	return;
}

// No suffix (a local run): keep the request host so the site still works.
$moon_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';

if ( '' === $moon_host || ! preg_match( '/^[A-Za-z0-9.\-]+(:\d+)?$/', $moon_host ) ) {
	return;
}

$moon_local  = in_array( strtolower( explode( ':', $moon_host )[0] ), array( 'localhost', '127.0.0.1' ), true );
$moon_scheme = $moon_local ? 'http' : 'https';

if ( ! $moon_local ) {
	$_SERVER['HTTPS']       = 'on';
	$_SERVER['SERVER_PORT'] = '443';
}

if ( ! defined( 'WP_HOME' ) ) {
	define( 'WP_HOME', $moon_scheme . '://' . $moon_host );
}

if ( ! defined( 'WP_SITEURL' ) ) {
	define( 'WP_SITEURL', $moon_scheme . '://' . $moon_host );
}
