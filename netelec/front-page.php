<?php
/**
 * Front page: renders the sections enabled and ordered in the theme panel.
 *
 * @package Netelec
 */

get_header();

$netelec_order   = array_filter( explode( ',', (string) netelec_opt( 'home_order' ) ) );
$netelec_enabled = (array) netelec_opt( 'home_enabled' );
$netelec_map     = array(
	'new_arrivals' => 'new-arrivals',
);

foreach ( $netelec_order as $netelec_section ) {
	if ( ! in_array( $netelec_section, $netelec_enabled, true ) ) {
		continue;
	}

	$netelec_template = isset( $netelec_map[ $netelec_section ] ) ? $netelec_map[ $netelec_section ] : $netelec_section;
	get_template_part( 'template-parts/home/' . $netelec_template );
}

get_footer();
