<?php
/**
 * Best selling products.
 *
 * @package Netelec
 */

if ( ! function_exists( 'netelec_render_products_section' ) ) {
	return;
}

netelec_render_products_section(
	netelec_opt( 'bestsellers_title' ),
	array(
		'posts_per_page' => (int) netelec_opt( 'bestsellers_count' ),
		'meta_key'       => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	),
	function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : ''
);
