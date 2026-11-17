<?php
/**
 * Builds the two shippable ZIP packages from the source in this repository.
 *
 * Runs inside the `wordpress:cli-php8.3` image (PHP with the zip extension),
 * because the sandbox host has no `zip` binary of its own.
 *
 * Usage: bash tools/build-packages.sh
 */

$root = dirname( __DIR__ );
$dist = $root . '/dist';

if ( ! is_dir( $dist ) && ! mkdir( $dist, 0777, true ) && ! is_dir( $dist ) ) {
	fwrite( STDERR, "cannot create dist/\n" );
	exit( 1 );
}

$packages = array(
	'moon-theme-final.zip'         => array( $root . '/wp-content/themes/moon-theme', 'moon-theme' ),
	'moon-demo-importer-final.zip' => array( $root . '/wp-content/plugins/moon-demo-importer', 'moon-demo-importer' ),
);

$skip = array( '.DS_Store', 'node_modules', '.git', '.idea', '.vscode' );

$failed = false;

foreach ( $packages as $zip_name => $package ) {
	list( $dir, $prefix ) = $package;

	$zip_path = $dist . '/' . $zip_name;
	if ( file_exists( $zip_path ) ) {
		unlink( $zip_path );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_path, ZipArchive::CREATE ) ) {
		fwrite( STDERR, "cannot create {$zip_name}\n" );
		$failed = true;
		continue;
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
	);

	$count = 0;
	foreach ( $files as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}

		$relative = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dir ) + 1 ) );
		$parts    = explode( '/', $relative );

		if ( array_intersect( $parts, $skip ) || substr( $relative, -4 ) === '.zip' ) {
			continue;
		}

		$zip->addFile( $file->getPathname(), $prefix . '/' . $relative );
		$count++;
	}

	$zip->close();

	printf( "%s — %d files, %s KB\n", $zip_name, $count, number_format( filesize( $zip_path ) / 1024 ) );
}

exit( $failed ? 1 : 0 );
