<?php
/**
 * Newest products.
 *
 * @package Netelec
 */

if ( ! function_exists( 'netelec_render_products_section' ) ) {
	return;
}

netelec_render_products_section(
	netelec_opt( 'new_title' ),
	array(
		'posts_per_page' => (int) netelec_opt( 'new_count' ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	),
	function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : ''
);
