<?php
/**
 * Local development configuration for the NETELEC sandbox.
 * Not shipped inside the theme; production sites keep their own wp-config.php.
 */

// The Base44 preview proxy terminates TLS and forwards the request over plain
// HTTP, keeping the original scheme only in Cloudflare's CF-Visitor header.
$netelec_forwarded_proto = isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ? strtolower( (string) strtok( $_SERVER['HTTP_X_FORWARDED_PROTO'], ',' ) ) : '';
$netelec_cf_scheme       = isset( $_SERVER['HTTP_CF_VISITOR'] ) ? json_decode( (string) $_SERVER['HTTP_CF_VISITOR'], true ) : null;
$netelec_is_https        = ( 'https' === $netelec_forwarded_proto )
	|| ( is_array( $netelec_cf_scheme ) && isset( $netelec_cf_scheme['scheme'] ) && 'https' === $netelec_cf_scheme['scheme'] )
	|| ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] );

if ( $netelec_is_https ) {
	$_SERVER['HTTPS'] = 'on';
}

// Inside the sandbox the Host header carries the internal sandbox domain. The
// browser reaches the same port through the public preview suffix, so rewrite
// the host before WordPress builds any URL (assets, forms, redirects).
$netelec_host    = ! empty( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : 'localhost:3000';
$netelec_public  = getenv( 'BASE44_PUBLIC_HOST_SUFFIX' );
$netelec_sandbox = getenv( 'BASE44_SANDBOX_HOST_DOMAIN' );

if ( $netelec_public && $netelec_sandbox && substr( $netelec_host, -strlen( $netelec_sandbox ) ) === $netelec_sandbox ) {
	// "3000-<sandbox domain>" -> "3000-<public suffix>".
	if ( preg_match( '/^(\d+)-/', $netelec_host, $netelec_port_match ) ) {
		$netelec_host = $netelec_port_match[1] . '-' . $netelec_public;
	}
}

$netelec_scheme = $netelec_is_https ? 'https' : 'http';

define( 'WP_HOME', $netelec_scheme . '://' . $netelec_host );
define( 'WP_SITEURL', $netelec_scheme . '://' . $netelec_host );

define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'wordpress' );
define( 'DB_PASSWORD', 'netelec_local_db' );
define( 'DB_HOST', 'db:3306' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY', 'netelec-local-dev-auth-key-not-for-production' );
define( 'SECURE_AUTH_KEY', 'netelec-local-dev-secure-auth-key' );
define( 'LOGGED_IN_KEY', 'netelec-local-dev-logged-in-key' );
define( 'NONCE_KEY', 'netelec-local-dev-nonce-key' );
define( 'AUTH_SALT', 'netelec-local-dev-auth-salt' );
define( 'SECURE_AUTH_SALT', 'netelec-local-dev-secure-auth-salt' );
define( 'LOGGED_IN_SALT', 'netelec-local-dev-logged-in-salt' );
define( 'NONCE_SALT', 'netelec-local-dev-nonce-salt' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', '/tmp/wp-debug.log' );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'FS_METHOD', 'direct' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
