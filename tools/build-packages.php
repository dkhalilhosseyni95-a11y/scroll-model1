<?php
/**
 * Build the installable packages in dist/.
 *
 * Usage (from the repository root):
 *   php tools/build-packages.php
 *
 * In the Base44 sandbox, where PHP is not installed on the host:
 *   docker compose -f docker-compose.base44.yml run --rm -T --entrypoint php \
 *     -v "$PWD:/opt/repo" setup /opt/repo/tools/build-packages.php
 *
 * @package DaniSite
 */

$root = dirname( __DIR__ );
$dist = $root . '/dist';

$packages = array(
	'danisite-parent' => array(
		'path' => $root . '/themes/danisite-parent',
		'skip' => array(),
	),
	'danisite-child'  => array(
		'path' => $root . '/themes/danisite-child',
		'skip' => array(),
	),
	'danisite-core'   => array(
		'path' => $root . '/plugins/danisite-core',
		'skip' => array(),
	),
);

$skip_names = array( '.DS_Store', 'Thumbs.db', '.git', 'node_modules', '.dani-tmp' );

if ( ! is_dir( $dist ) && ! mkdir( $dist, 0755, true ) && ! is_dir( $dist ) ) {
	fwrite( STDERR, "cannot create {$dist}\n" );
	exit( 1 );
}

/**
 * Recursively add a directory to a zip under a given prefix.
 *
 * @param ZipArchive $zip    Archive.
 * @param string     $dir    Absolute source directory.
 * @param string     $prefix Path inside the archive.
 * @param array      $skip   File/directory names to ignore.
 * @return int Number of files added.
 */
function dani_zip_dir( ZipArchive $zip, $dir, $prefix, array $skip ) {
	$count    = 0;
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $iterator as $item ) {
		$name = $item->getFilename();

		if ( in_array( $name, $skip, true ) ) {
			continue;
		}

		$relative = str_replace( '\\', '/', substr( $item->getPathname(), strlen( $dir ) + 1 ) );
		$local    = $prefix . '/' . $relative;

		if ( $item->isDir() ) {
			$zip->addEmptyDir( $local );
			continue;
		}

		if ( $zip->addFile( $item->getPathname(), $local ) ) {
			++$count;
		}
	}

	return $count;
}

foreach ( $packages as $slug => $package ) {
	if ( ! is_dir( $package['path'] ) ) {
		fwrite( STDERR, "missing source: {$package['path']}\n" );
		exit( 1 );
	}

	$file = $dist . '/' . $slug . '.zip';

	if ( file_exists( $file ) ) {
		unlink( $file );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $file, ZipArchive::CREATE ) ) {
		fwrite( STDERR, "cannot create {$file}\n" );
		exit( 1 );
	}

	$count = dani_zip_dir( $zip, $package['path'], $slug, $skip_names );
	$zip->close();

	printf( "%-16s %3d files  %6.1f KB\n", basename( $file ), $count, filesize( $file ) / 1024 );
}

echo "done\n";
