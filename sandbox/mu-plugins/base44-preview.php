<?php
/**
 * Base44 preview host resolution (development sandbox only).
 *
 * WordPress builds absolute URLs from the stored site URL. Inside the sandbox the
 * app is served through a proxy on a rotating preview host, so URLs must follow
 * the public preview origin instead of the value written at install time.
 *
 * Enabled:  home/site URLs are forced to the preview origin and the request is
 *           treated as HTTPS so redirects and cookies stay on the origin the
 *           browser actually uses.
 * Unset / any other value: WordPress behaves exactly as configured in the
 *           database (normal production behaviour).
 *
 * @package Moon_Child
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

// The proxy forwards the internal sandbox Host header while WordPress is
// configured with the public preview URL; without this rewrite core's canonical
// redirects point at a host that redirects back (ERR_TOO_MANY_REDIRECTS).
$moon_preview_host = wp_parse_url( $moon_preview_origin, PHP_URL_HOST );
if ( $moon_preview_host ) {
	$moon_preview_port      = wp_parse_url( $moon_preview_origin, PHP_URL_PORT );
	$_SERVER['HTTP_HOST']   = $moon_preview_host . ( $moon_preview_port ? ':' . $moon_preview_port : '' );
	$_SERVER['SERVER_NAME'] = $moon_preview_host;
}

if ( 0 === strpos( $moon_preview_origin, 'https://' ) ) {
	$_SERVER['HTTPS']        = 'on';
	$_SERVER['SERVER_PORT']  = '443';
	$_SERVER['REQUEST_SCHEME'] = 'https';
}

add_filter(
	'option_home',
	static function () use ( $moon_preview_origin ) {
		return $moon_preview_origin;
	},
	99
);

add_filter(
	'option_siteurl',
	static function () use ( $moon_preview_origin ) {
		return $moon_preview_origin;
	},
	99
);

add_filter(
	'content_url',
	static function ( $url ) use ( $moon_preview_origin ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		return $moon_preview_origin . $path;
	},
	99
);

add_action(
	'admin_bar_menu',
	static function ( $bar ) {
		$bar->add_node(
			array(
				'id'    => 'moon-sandbox',
				'title' => 'MOON sandbox',
				'href'  => admin_url( 'admin.php?page=moon-core' ),
			)
		);
	},
	100
);
