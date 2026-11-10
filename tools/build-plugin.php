<?php
/**
 * Builds the installable packages in dist/.
 *
 *   php tools/build-plugin.php
 *
 * Produces:
 *   dist/moon-core.zip                    — the plugin (upload in WordPress → Plugins)
 *   dist/moon-elementor-templates.zip     — home / header / footer templates for Elementor
 *
 * @package MoonCore\Tools
 */

$root = dirname( __DIR__ );
$dist = $root . '/dist';

if ( ! is_dir( $dist ) ) {
	mkdir( $dist, 0775, true );
}

/**
 * Add a directory tree to a zip archive.
 *
 * @param ZipArchive $zip    Archive.
 * @param string     $source Absolute source directory.
 * @param string     $prefix Path inside the archive.
 */
function moon_add_tree( ZipArchive $zip, $source, $prefix ) {
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	$skip = array( '.DS_Store', 'Thumbs.db', '.gitkeep' );

	foreach ( $iterator as $item ) {
		$name = str_replace( '\\', '/', substr( $item->getPathname(), strlen( $source ) + 1 ) );

		if ( in_array( basename( $name ), $skip, true ) ) {
			continue;
		}

		$target = $prefix . '/' . $name;

		if ( $item->isDir() ) {
			$zip->addEmptyDir( $target );
		} else {
			$zip->addFile( $item->getPathname(), $target );
		}
	}
}

/* ---------- plugin ---------- */
$plugin_zip = $dist . '/moon-core.zip';
if ( file_exists( $plugin_zip ) ) {
	unlink( $plugin_zip );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $plugin_zip, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Cannot create {$plugin_zip}\n" );
	exit( 1 );
}

moon_add_tree( $zip, $root . '/plugins/moon-core', 'moon-core' );
$zip->close();

/* ---------- Elementor templates ---------- */
$templates = array( 'home.json', 'header.json', 'footer.json' );
$template_zip = $dist . '/moon-elementor-templates.zip';

if ( file_exists( $template_zip ) ) {
	unlink( $template_zip );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $template_zip, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Cannot create {$template_zip}\n" );
	exit( 1 );
}

$added = 0;
foreach ( $templates as $file ) {
	$path = $root . '/templates/elementor/' . $file;
	if ( file_exists( $path ) ) {
		$zip->addFile( $path, $file );
		$added++;
	}
}
$zip->close();

printf(
	"dist/moon-core.zip (%d KB)\ndist/moon-elementor-templates.zip (%d templates)\n",
	(int) round( filesize( $plugin_zip ) / 1024 ),
	$added
);
