<?php
/**
 * Shared helpers.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a theme option, falling back to the theme's defaults.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function moon_core_opt( $key, $default = '' ) {
	if ( function_exists( 'moon_opt' ) ) {
		return moon_opt( $key, $default );
	}

	$options = get_option( 'moon_options', array() );

	return isset( $options[ $key ] ) && '' !== $options[ $key ] ? $options[ $key ] : $default;
}

/**
 * Attachment ID for the brand logo set in the panel.
 *
 * @return int
 */
function moon_core_logo_id() {
	return (int) moon_core_opt( 'logo_id', 0 );
}

/**
 * Print the panel logo when one is set.
 */
function moon_core_logo() {
	$id = moon_core_logo_id();

	if ( ! $id ) {
		return;
	}

	$image = wp_get_attachment_image( $id, 'full', false, array(
		'alt'   => get_bloginfo( 'name' ),
		'class' => 'moon-brand__img',
	) );

	echo $image ? $image : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Whether the MOON theme is the active theme.
 *
 * @return bool
 */
function moon_core_theme_active() {
	return true;
}

/**
 * URL of the main MOON panel page.
 *
 * @param string $page Page slug.
 * @return string
 */
function moon_core_admin_url( $page = 'moon-theme' ) {
	return admin_url( 'admin.php?page=' . $page );
}

/**
 * Imported content marker.
 *
 * @return array
 */
function moon_core_import_state() {
	$state = get_option( 'moon_core_imported', array() );

	return is_array( $state ) ? $state : array();
}
