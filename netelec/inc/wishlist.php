<?php
/**
 * Wishlist: cookie for guests, user meta for logged-in customers.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read the raw wishlist ids.
 *
 * @return int[]
 */
function netelec_wishlist_ids() {
	if ( is_user_logged_in() ) {
		$ids = get_user_meta( get_current_user_id(), 'netelec_wishlist', true );
		return array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
	}

	$cookie = isset( $_COOKIE['netelec_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['netelec_wishlist'] ) ) : '';
	return array_values( array_filter( array_map( 'absint', explode( ',', $cookie ) ) ) );
}

/**
 * Wishlist count.
 *
 * @return int
 */
function netelec_wishlist_count() {
	return count( netelec_wishlist_ids() );
}

/**
 * Persist wishlist ids for the current visitor.
 *
 * @param int[] $ids Product ids.
 */
function netelec_wishlist_save( $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), 'netelec_wishlist', $ids );
		return;
	}

	$value = implode( ',', $ids );
	$path  = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	setcookie( 'netelec_wishlist', $value, time() + ( 30 * DAY_IN_SECONDS ), $path, COOKIE_DOMAIN, is_ssl(), false );
	$_COOKIE['netelec_wishlist'] = $value;
}

/**
 * AJAX: toggle a product in the wishlist.
 */
function netelec_ajax_wishlist_toggle() {
	netelec_ajax_verify();

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product ) {
		netelec_ajax_fail( 'محصول یافت نشد.' );
	}

	$ids    = netelec_wishlist_ids();
	$exists = in_array( $product_id, $ids, true );

	if ( $exists ) {
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
	} else {
		$ids[] = $product_id;
	}

	netelec_wishlist_save( $ids );

	wp_send_json_success(
		array(
			'count'   => count( $ids ),
			'ids'     => $ids,
			'in_list' => ! $exists,
		)
	);
}
add_action( 'wp_ajax_netelec_wishlist_toggle', 'netelec_ajax_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_netelec_wishlist_toggle', 'netelec_ajax_wishlist_toggle' );

/**
 * Merge the guest wishlist into the customer account on login.
 *
 * @param string $user_login Login.
 */
function netelec_wishlist_merge_on_login( $user_login ) {
	$user = get_user_by( 'login', $user_login );
	if ( ! $user || empty( $_COOKIE['netelec_wishlist'] ) ) {
		return;
	}

	$guest = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['netelec_wishlist'] ) ) ) ) );
	if ( ! $guest ) {
		return;
	}

	$saved = (array) get_user_meta( $user->ID, 'netelec_wishlist', true );
	update_user_meta( $user->ID, 'netelec_wishlist', array_values( array_unique( array_merge( $saved, $guest ) ) ) );
}
add_action( 'wp_login', 'netelec_wishlist_merge_on_login' );

/**
 * Render a product grid from a list of ids (wishlist / compare / recent).
 *
 * @param int[]  $ids   Product ids.
 * @param string $empty Empty state message.
 */
function netelec_render_products_by_ids( $ids, $empty = 'هنوز محصولی اضافه نکرده‌اید.' ) {
	$ids = array_filter( array_map( 'absint', (array) $ids ) );

	if ( ! $ids || ! function_exists( 'wc_get_template_part' ) ) {
		echo '<div class="shop-empty">';
		netelec_icon( 'heart' );
		echo '<h2>' . esc_html( $empty ) . '</h2>';
		printf( '<a class="btn btn--primary" href="%s">مشاهده محصولات</a>', esc_url( wc_get_page_permalink( 'shop' ) ) );
		echo '</div>';
		return;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $ids ),
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return;
	}

	wc_set_loop_prop( 'columns', netelec_loop_columns() );
	woocommerce_product_loop_start();
	while ( $query->have_posts() ) :
		$query->the_post();
		wc_get_template_part( 'content', 'product' );
	endwhile;
	woocommerce_product_loop_end();
	wp_reset_postdata();
	wc_reset_loop();
}
