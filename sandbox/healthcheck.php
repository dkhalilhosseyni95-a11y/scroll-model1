<?php
/**
 * Container healthcheck for the WordPress service.
 *
 * Probes the database, the PHP handler through Apache and the Moon Core mount.
 * A brand-new database (no wp_options yet) still counts as healthy so the
 * one-shot installer, which depends on this service, can run.
 *
 * @package MoonCore\Sandbox
 */

$failures = array();
$notes    = array();

$host = getenv( 'WORDPRESS_DB_HOST' ) ? getenv( 'WORDPRESS_DB_HOST' ) : 'db:3306';
$user = getenv( 'WORDPRESS_DB_USER' );
$pass = getenv( 'WORDPRESS_DB_PASSWORD' );
$name = getenv( 'WORDPRESS_DB_NAME' );

$parts   = explode( ':', $host );
$db_host = $parts[0];
$db_port = isset( $parts[1] ) ? (int) $parts[1] : 3306;

mysqli_report( MYSQLI_REPORT_OFF );

$db = mysqli_connect( $db_host, $user, $pass, $name, $db_port );

if ( ! $db ) {
	$failures[] = 'database: ' . mysqli_connect_error();
} else {
	$result = mysqli_query( $db, "SHOW TABLES LIKE 'wp_options'" );
	$table  = $result ? mysqli_fetch_row( $result ) : array();
	if ( ! $table ) {
		$notes[] = 'database reachable, WordPress not installed yet';
	} else {
		$notes[] = 'database ok';
	}
	mysqli_close( $db );
}

// Any real page proves Apache + PHP are serving (installer, login or front page).
$context = stream_context_create(
	array(
		'http' => array(
			'follow_location' => 1,
			'max_redirects'   => 5,
			'timeout'         => 8,
			'ignore_errors'   => true,
			'user_agent'      => 'moon-healthcheck',
		),
	)
);
$page = @file_get_contents( 'http://127.0.0.1/', false, $context );
if ( false === $page || strlen( $page ) < 200 ) {
	$failures[] = 'php/apache: no HTML response';
} else {
	$notes[] = 'php ok';
}

if ( ! file_exists( '/var/www/html/wp-content/plugins/moon-core/moon-core.php' ) ) {
	$failures[] = 'plugin: moon-core not mounted';
}

if ( ! file_exists( '/var/www/html/wp-config.php' ) ) {
	$failures[] = 'wordpress: wp-config.php missing';
}

if ( $failures ) {
	echo 'FAIL ' . implode( ' | ', $failures ) . PHP_EOL;
	exit( 1 );
}

echo 'OK ' . implode( ' | ', $notes ) . PHP_EOL;
exit( 0 );
