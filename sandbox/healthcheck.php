<?php
/**
 * Container healthcheck: the database answers and the site serves a page.
 *
 * Read-only. It never writes to the database or the filesystem.
 *
 * @package Moon_Child
 */

$moon_db = @new mysqli(
	(string) getenv( 'WORDPRESS_DB_HOST' ),
	(string) getenv( 'WORDPRESS_DB_USER' ),
	(string) getenv( 'WORDPRESS_DB_PASSWORD' ),
	(string) getenv( 'WORDPRESS_DB_NAME' )
);

if ( $moon_db->connect_errno ) {
	fwrite( STDERR, "database: {$moon_db->connect_error}\n" );
	exit( 1 );
}
$moon_db->close();

$moon_context = stream_context_create(
	array(
		'http' => array(
			'timeout'         => 5,
			'ignore_errors'   => true,
			'follow_location' => 0,
		),
	)
);

$moon_body = @file_get_contents( 'http://127.0.0.1/', false, $moon_context );

if ( false === $moon_body ) {
	fwrite( STDERR, "web: no response from localhost\n" );
	exit( 1 );
}

$moon_status = 0;
if ( isset( $http_response_header[0] ) && preg_match( '#\s(\d{3})\s#', $http_response_header[0], $moon_match ) ) {
	$moon_status = (int) $moon_match[1];
}

if ( $moon_status >= 500 || 0 === $moon_status ) {
	fwrite( STDERR, "web: status {$moon_status}\n" );
	exit( 1 );
}

echo "ok\n";
exit( 0 );
