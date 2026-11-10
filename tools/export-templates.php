<?php
/**
 * Exports the Elementor documents built by Moon_Core_Demo as importable JSON.
 *
 * Run inside the WordPress container once the stack is up:
 *   docker compose -f docker-compose.base44.yml run --rm -T \
 *     -v "$PWD/tools:/opt/tools:ro" -v "$PWD/templates/elementor:/opt/export" \
 *     cli eval-file /opt/tools/export-templates.php
 *
 * @package MoonCore\Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$moon_export_dir = '/opt/export';

if ( ! is_dir( $moon_export_dir ) ) {
	WP_CLI::error( 'Export directory /opt/export is not mounted.' );
}

$moon_documents = array(
	'home'   => array(
		'type'  => 'page',
		'title' => 'MOON — صفحه اصلی',
		'file'  => 'home.json',
		'data'  => Moon_Core_Demo::home_content(),
	),
	'header' => array(
		'type'  => 'header',
		'title' => 'MOON — Header',
		'file'  => 'header.json',
		'data'  => Moon_Core_Demo::header_content(),
	),
	'footer' => array(
		'type'  => 'footer',
		'title' => 'MOON — Footer',
		'file'  => 'footer.json',
		'data'  => Moon_Core_Demo::footer_content(),
	),
);

foreach ( $moon_documents as $moon_key => $moon_document ) {
	$moon_payload = array(
		'version'       => '0.4',
		'title'         => $moon_document['title'],
		'type'          => $moon_document['type'],
		'content'       => $moon_document['data'],
		'page_settings' => array(),
		'metadata'      => array(
			'template_type' => $moon_document['type'],
		),
	);

	$moon_path = trailingslashit( $moon_export_dir ) . $moon_document['file'];
	$moon_json = wp_json_encode( $moon_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

	if ( false === file_put_contents( $moon_path, $moon_json ) ) {
		WP_CLI::warning( 'Could not write ' . $moon_path );
		continue;
	}

	WP_CLI::log( 'wrote ' . $moon_document['file'] . ' (' . size_format( strlen( $moon_json ) ) . ')' );
}

WP_CLI::success( 'Elementor templates exported.' );
