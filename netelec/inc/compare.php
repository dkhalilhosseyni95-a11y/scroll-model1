<?php
/**
 * Product comparison (max 4 products, cookie based).
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maximum number of compared products.
 *
 * @return int
 */
function netelec_compare_limit() {
	return 4;
}

/**
 * Compared product ids.
 *
 * @return int[]
 */
function netelec_compare_ids() {
	$cookie = isset( $_COOKIE['netelec_compare'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['netelec_compare'] ) ) : '';
	return array_values( array_filter( array_map( 'absint', explode( ',', $cookie ) ) ) );
}

/**
 * Compared product count.
 *
 * @return int
 */
function netelec_compare_count() {
	return count( netelec_compare_ids() );
}

/**
 * Persist compared ids.
 *
 * @param int[] $ids Product ids.
 */
function netelec_compare_save( $ids ) {
	$ids   = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ), 0, netelec_compare_limit() );
	$value = implode( ',', $ids );
	$path  = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	setcookie( 'netelec_compare', $value, time() + ( 7 * DAY_IN_SECONDS ), $path, COOKIE_DOMAIN, is_ssl(), false );
	$_COOKIE['netelec_compare'] = $value;
}

/**
 * AJAX: toggle a product in the compare list.
 */
function netelec_ajax_compare_toggle() {
	netelec_ajax_verify();

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product ) {
		netelec_ajax_fail( 'محصول یافت نشد.' );
	}

	$ids    = netelec_compare_ids();
	$exists = in_array( $product_id, $ids, true );

	if ( $exists ) {
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
	} else {
		if ( count( $ids ) >= netelec_compare_limit() ) {
			netelec_ajax_fail( 'حداکثر ۴ محصول را می‌توانید مقایسه کنید. ابتدا یکی را حذف کنید.' );
		}
		$ids[] = $product_id;
	}

	netelec_compare_save( $ids );

	wp_send_json_success(
		array(
			'count'   => count( $ids ),
			'ids'     => $ids,
			'in_list' => ! $exists,
		)
	);
}
add_action( 'wp_ajax_netelec_compare_toggle', 'netelec_ajax_compare_toggle' );
add_action( 'wp_ajax_nopriv_netelec_compare_toggle', 'netelec_ajax_compare_toggle' );

/**
 * Attribute rows used in the comparison table.
 *
 * @param WC_Product[] $products Products.
 * @return array
 */
function netelec_compare_attributes( $products ) {
	$rows = array();

	foreach ( $products as $product ) {
		foreach ( $product->get_attributes() as $attribute ) {
			$name = wc_attribute_label( $attribute->get_name() );
			if ( ! isset( $rows[ $name ] ) ) {
				$rows[ $name ] = array();
			}
		}
	}

	return $rows;
}
