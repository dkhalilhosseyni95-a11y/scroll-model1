<?php
/**
 * Live search: product (and journal) suggestions while typing.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run one visible-results query.
 *
 * @param string $term  Search term.
 * @param string $type  Post type.
 * @param int    $limit Max rows.
 * @return WP_Post[]
 */
function moon_live_search_query( $term, $type, $limit ) {
	if ( ! post_type_exists( $type ) ) {
		return array();
	}

	$query = new WP_Query( array(
		's'                   => $term,
		'post_type'           => $type,
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	$found = array();

	foreach ( $query->posts as $post ) {
		if ( 'product' === $type && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post->ID );

			if ( $product && ! $product->is_visible() ) {
				continue;
			}
		}

		$found[] = $post;
	}

	return $found;
}

/**
 * One result list.
 *
 * @param string    $label Heading.
 * @param WP_Post[] $posts Results.
 * @param string    $type  Post type.
 * @return string
 */
function moon_live_search_group( $label, $posts, $type ) {
	$html = '<div class="moon-ls__group"><p class="moon-ls__title">' . esc_html( $label ) . '</p><ul class="moon-ls__list">';

	foreach ( $posts as $post ) {
		$title = get_the_title( $post );
		$thumb = get_the_post_thumbnail( $post, 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
		$meta  = '';

		if ( 'product' === $type && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post->ID );

			if ( $product ) {
				$meta = $product->is_in_stock() ? wp_kses_post( $product->get_price_html() ) : '<span class="moon-ls__out">' . esc_html__( 'ناموجود', 'moon-theme' ) . '</span>';
			}
		}

		$html .= sprintf(
			'<li><a class="moon-ls__row" href="%1$s"><span class="moon-ls__thumb">%2$s</span><span class="moon-ls__text"><span class="moon-ls__name">%3$s</span><span class="moon-ls__meta">%4$s</span></span></a></li>',
			esc_url( get_permalink( $post ) ),
			$thumb, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-generated markup.
			esc_html( $title ),
			$meta // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above / WooCommerce price HTML.
		);
	}

	return $html . '</ul></div>';
}

/**
 * AJAX handler.
 */
function moon_live_search_ajax() {
	check_ajax_referer( 'moon_shop', 'nonce' );

	$term = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
	$term = mb_substr( trim( preg_replace( '/\s+/u', ' ', $term ) ), 0, 80, 'UTF-8' );

	if ( mb_strlen( $term, 'UTF-8' ) < 2 ) {
		wp_send_json_success( array( 'html' => '', 'count' => 0 ) );
	}

	$products = moon_live_search_query( $term, 'product', 6 );
	$posts    = moon_live_search_query( $term, 'post', 3 );
	$html     = '';

	if ( $products ) {
		$html .= moon_live_search_group( __( 'محصولات', 'moon-theme' ), $products, 'product' );
	}

	if ( $posts ) {
		$html .= moon_live_search_group( __( 'مقاله‌ها', 'moon-theme' ), $posts, 'post' );
	}

	$count = count( $products ) + count( $posts );

	if ( ! $count ) {
		$html = '<p class="moon-ls__empty">' . esc_html( sprintf( __( 'نتیجه‌ای برای «%s» پیدا نشد. کلمه دیگری را امتحان کنید.', 'moon-theme' ), $term ) ) . '</p>';
	}

	wp_send_json_success( array(
		'html'      => $html,
		'count'     => $count,
		'moreUrl'   => add_query_arg( array( 's' => $term, 'post_type' => 'product' ), home_url( '/' ) ),
		'moreLabel' => sprintf( __( 'دیدن همه نتایج برای «%s»', 'moon-theme' ), $term ),
	) );
}
add_action( 'wp_ajax_moon_live_search', 'moon_live_search_ajax' );
add_action( 'wp_ajax_nopriv_moon_live_search', 'moon_live_search_ajax' );
