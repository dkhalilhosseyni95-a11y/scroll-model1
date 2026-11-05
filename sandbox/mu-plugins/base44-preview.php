<?php
/**
 * Base44 preview host resolution (development sandbox only).
 *
 * WordPress builds absolute URLs from the stored site URL. Inside the sandbox
 * the app is served through a proxy on a rotating preview host, so the URLs
 * must follow the public preview origin instead of the value written at
 * install time. This file is mounted into wp-content/mu-plugins and does
 * nothing at all unless BASE44_PREVIEW_MODE is exactly "1".
 *
 * Enabled:  home/site URLs are forced to https://<preview origin> and the
 *           request is treated as HTTPS so redirects and cookies stay on the
 *           origin the browser is actually using.
 * Unset / any other value: WordPress behaves exactly as configured in the
 *           database (production behaviour).
 *
 * @package Base44_Sandbox
 */

defined( 'ABSPATH' ) || exit;

if ( '1' !== getenv( 'BASE44_PREVIEW_MODE' ) ) {
	return;
}

$base44_preview_origin = getenv( 'DANI_PREVIEW_ORIGIN' );

if ( ! $base44_preview_origin ) {
	return;
}

$base44_preview_origin = untrailingslashit( $base44_preview_origin );

// The proxy forwards the request with the internal sandbox Host header while
// WordPress is configured with the public preview URL. Without this, core's
// canonical redirects keep sending the browser to a host that redirects back.
// Only in preview mode; production keeps the real request host.
$base44_preview_host = wp_parse_url( $base44_preview_origin, PHP_URL_HOST );
if ( $base44_preview_host ) {
	$base44_preview_port       = wp_parse_url( $base44_preview_origin, PHP_URL_PORT );
	$_SERVER['HTTP_HOST']      = $base44_preview_host . ( $base44_preview_port ? ':' . $base44_preview_port : '' );
	$_SERVER['SERVER_NAME']    = $base44_preview_host;
}

// The proxy terminates TLS; tell WordPress the request is secure.
if ( 0 === strpos( $base44_preview_origin, 'https://' ) ) {
	$_SERVER['HTTPS']        = 'on';
	$_SERVER['SERVER_PORT']  = '443';
	$_SERVER['REQUEST_SCHEME'] = 'https';
}

add_filter(
	'option_home',
	function ( $value ) use ( $base44_preview_origin ) {
		return $base44_preview_origin;
	},
	99
);

add_filter(
	'option_siteurl',
	function ( $value ) use ( $base44_preview_origin ) {
		return $base44_preview_origin;
	},
	99
);

// Keep generated asset/upload URLs on the preview origin.
add_filter(
	'content_url',
	function ( $url ) use ( $base44_preview_origin ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		return $base44_preview_origin . $path;
	},
	99
);

/**
 * Show a small badge in the admin bar so it is obvious this is a sandbox.
 *
 * @param WP_Admin_Bar $bar Admin bar instance.
 * @return void
 */
function base44_preview_admin_notice( $bar ) {
	$bar->add_node(
		array(
			'id'    => 'base44-preview',
			'title' => 'حالت پیش‌نمایش Base44',
			'href'  => admin_url( 'admin.php?page=dani-importer' ),
		)
	);
}
add_action( 'admin_bar_menu', 'base44_preview_admin_notice', 100 );
