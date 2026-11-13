<?php
/**
 * Plugin Name: MOON Sandbox Preview
 * Description: Sandbox-only helper: serves WordPress on whatever host the preview proxy uses. Loaded from docker/mu-plugins, never shipped in the theme or plugin packages.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite the stored local base URL to the host of the current request.
 *
 * The preview proxy forwards the public host in the Host header and changes it
 * whenever the sandbox is recreated, so any stored absolute URL would be stale.
 * WordPress builds some URLs from constants captured before plugins load, so the
 * rewrite is applied to every URL factory rather than only to home/siteurl.
 *
 * Active only when BASE44_PREVIEW_MODE=1; without the flag the theme behaves
 * exactly as it does in production.
 */
if ( '1' === getenv( 'BASE44_PREVIEW_MODE' ) ) {
	$moon_suffix = (string) getenv( 'BASE44_PUBLIC_HOST_SUFFIX' );
	$moon_host   = '';

	if ( $moon_suffix ) {
		// The browser talks to <port>-<suffix>; the request Host is the sandbox node.
		$moon_host = '3000-' . $moon_suffix;
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		$moon_host = wp_unslash( $_SERVER['HTTP_X_FORWARDED_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	} elseif ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
		$moon_host = wp_unslash( $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	$moon_host = preg_replace( '/[^a-zA-Z0-9\.\-:\[\]]/', '', (string) $moon_host );

	if ( $moon_host ) {
		$moon_https = $moon_suffix
			|| ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] )
			|| ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] );

		$moon_scheme = $moon_https ? 'https' : 'http';
		$moon_base   = $moon_scheme . '://' . $moon_host;
		$moon_local  = 'localhost:3000';

		// Present the request as the public host so WordPress does not redirect
		// between the proxy host and the canonical public host.
		$_SERVER['HTTP_HOST']       = $moon_host;
		$_SERVER['SERVER_NAME']     = $moon_host;

		if ( $moon_https ) {
			$_SERVER['HTTPS'] = 'on';
		}

		if ( ! defined( 'WP_HOME' ) ) {
			define( 'WP_HOME', $moon_base );
		}

		if ( ! defined( 'WP_SITEURL' ) ) {
			define( 'WP_SITEURL', $moon_base );
		}

		$moon_rewrite = function ( $value ) use ( $moon_base, $moon_local ) {
			if ( ! is_string( $value ) || '' === $value ) {
				return $value;
			}

			// core may upgrade the scheme before this filter runs.
			return str_replace(
				array( 'http://' . $moon_local, 'https://' . $moon_local ),
				$moon_base,
				$value
			);
		};

		foreach ( array(
			'home_url',
			'site_url',
			'content_url',
			'includes_url',
			'plugins_url',
			'theme_root_uri',
			'admin_url',
			'login_url',
			'logout_url',
			'network_home_url',
			'network_site_url',
			'rest_url',
			'wp_get_attachment_url',
			'style_loader_src',
			'script_loader_src',
		) as $moon_filter ) {
			add_filter( $moon_filter, $moon_rewrite, 99 );
		}

		// Uploads are generated from constants captured at boot.
		add_filter( 'upload_dir', function ( $dirs ) use ( $moon_rewrite ) {
			$dirs['baseurl'] = $moon_rewrite( $dirs['baseurl'] );
			$dirs['url']     = $moon_rewrite( $dirs['url'] );

			return $dirs;
		}, 99 );

	}
}
