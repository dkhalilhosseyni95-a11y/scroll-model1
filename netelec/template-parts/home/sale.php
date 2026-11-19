<?php
/**
 * On-sale products.
 *
 * @package Netelec
 */

if ( ! function_exists( 'netelec_render_products_section' ) || ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
	return;
}

$netelec_on_sale = wc_get_product_ids_on_sale();
if ( ! $netelec_on_sale ) {
	return;
}

netelec_render_products_section(
	netelec_opt( 'sale_title' ),
	array(
		'posts_per_page' => (int) netelec_opt( 'sale_count' ),
		'post__in'       => $netelec_on_sale,
		'orderby'        => 'date',
	),
	function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : ''
);
