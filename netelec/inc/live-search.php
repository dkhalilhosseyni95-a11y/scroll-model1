<?php
/**
 * Live product search endpoint.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX: search products for the header dropdown.
 */
function netelec_ajax_live_search() {
	netelec_ajax_verify();

	$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

	if ( mb_strlen( $term ) < 2 ) {
		wp_send_json_success( array( 'items' => array(), 'total' => 0 ) );
	}

	if ( ! netelec_rate_limit( 'live_search', 120, MINUTE_IN_SECONDS ) ) {
		netelec_ajax_fail( 'تعداد درخواست‌ها زیاد است. کمی بعد تلاش کنید.', 429 );
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			's'                   => $term,
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'exclude-from-search',
					'operator' => 'NOT IN',
				),
			),
		)
	);

	$items = array();

	foreach ( $query->posts as $post ) {
		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			continue;
		}

		$thumb = $product->get_image_id()
			? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_gallery_thumbnail' )
			: wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );

		$items[] = array(
			'id'    => $post->ID,
			'title' => wp_strip_all_tags( get_the_title( $post ) ),
			'url'   => get_permalink( $post ),
			'price' => wp_strip_all_tags( $product->get_price_html() ),
			'thumb' => $thumb,
		);
	}

	wp_send_json_success(
		array(
			'items'    => $items,
			'total'    => (int) $query->found_posts,
			'more_url' => add_query_arg(
				array(
					's'         => $term,
					'post_type' => 'product',
				),
				home_url( '/' )
			),
		)
	);
}
add_action( 'wp_ajax_netelec_live_search', 'netelec_ajax_live_search' );
add_action( 'wp_ajax_nopriv_netelec_live_search', 'netelec_ajax_live_search' );
