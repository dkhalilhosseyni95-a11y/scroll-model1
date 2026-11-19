<?php
/**
 * Shop filters (price, attributes, availability) built on standard WooCommerce
 * query hooks — no extra plugin required.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apply the sidebar filters to the main product query.
 *
 * @param WP_Query $q Query.
 */
function netelec_apply_product_filters( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only catalog filters.
	$min_price = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : '';
	$max_price = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : '';

	$meta_query = (array) $q->get( 'meta_query' );

	if ( '' !== $min_price || '' !== $max_price ) {
		$range = array( '' !== $min_price ? (float) $min_price : 0, '' !== $max_price ? (float) $max_price : 999999999 );
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $range,
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		);
	}

	if ( ! empty( $_GET['in_stock'] ) ) {
		$meta_query[] = array(
			'key'   => '_stock_status',
			'value' => 'instock',
		);
	}

	if ( $meta_query ) {
		$q->set( 'meta_query', $meta_query );
	}

	$tax_query = (array) $q->get( 'tax_query' );

	foreach ( $_GET as $key => $value ) {
		if ( 0 !== strpos( $key, 'filter_' ) ) {
			continue;
		}

		$taxonomy = sanitize_key( substr( $key, 7 ) );
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$raw   = wp_unslash( $value );
		$slugs = is_array( $raw ) ? array_map( 'sanitize_title', $raw ) : array_map( 'sanitize_title', explode( ',', (string) $raw ) );
		$terms = array_filter( $slugs );
		if ( ! $terms ) {
			continue;
		}

		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $terms,
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	if ( $tax_query ) {
		$q->set( 'tax_query', $tax_query );
	}

	if ( ! empty( $_GET['on_sale'] ) && function_exists( 'wc_get_product_ids_on_sale' ) ) {
		$on_sale = wc_get_product_ids_on_sale();
		$q->set( 'post__in', $on_sale ? $on_sale : array( 0 ) );
	}
	// phpcs:enable
}
add_action( 'woocommerce_product_query', 'netelec_apply_product_filters' );

/**
 * Current filter values.
 *
 * @return array
 */
function netelec_current_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	return array(
		'min_price' => isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : '',
		'max_price' => isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : '',
		'in_stock'  => ! empty( $_GET['in_stock'] ),
		'on_sale'   => ! empty( $_GET['on_sale'] ),
	);
	// phpcs:enable
}

/**
 * URL of the current archive with one filter removed.
 *
 * @param string $remove Key to remove.
 * @return string
 */
function netelec_filter_url( $remove = '' ) {
	$base = is_product_taxonomy() ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' );
	if ( is_wp_error( $base ) ) {
		$base = wc_get_page_permalink( 'shop' );
	}

	$args = array();
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	foreach ( $_GET as $key => $value ) {
		if ( $key === $remove || 'paged' === $key ) {
			continue;
		}
		if ( is_array( $value ) ) {
			$args[ sanitize_key( $key ) ] = array_map( 'sanitize_text_field', wp_unslash( $value ) );
		} elseif ( is_scalar( $value ) ) {
			$args[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}
	// phpcs:enable

	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Active filter chips shown above the product grid.
 */
function netelec_active_filters() {
	$filters = netelec_current_filters();
	$chips   = array();

	if ( '' !== $filters['min_price'] || '' !== $filters['max_price'] ) {
		$label = 'قیمت: ';
		$label .= '' !== $filters['min_price'] ? wc_price( (float) $filters['min_price'] ) : '۰';
		$label .= ' تا ';
		$label .= '' !== $filters['max_price'] ? wc_price( (float) $filters['max_price'] ) : 'بی‌نهایت';
		$chips[] = array(
			'label' => $label,
			'url'   => netelec_filter_url( 'min_price' ),
			'also'  => 'max_price',
		);
	}

	if ( $filters['in_stock'] ) {
		$chips[] = array( 'label' => 'فقط کالاهای موجود', 'url' => netelec_filter_url( 'in_stock' ) );
	}

	if ( $filters['on_sale'] ) {
		$chips[] = array( 'label' => 'فقط تخفیف‌دارها', 'url' => netelec_filter_url( 'on_sale' ) );
	}

	// Attribute chips.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	foreach ( $_GET as $key => $value ) {
		if ( 0 !== strpos( $key, 'filter_' ) ) {
			continue;
		}
		$taxonomy = sanitize_key( substr( $key, 7 ) );
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$raw   = wp_unslash( $value );
		$slugs = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', sanitize_title( $slug ), $taxonomy );
			if ( $term ) {
				$chips[] = array(
					'label' => $term->name,
					'url'   => netelec_filter_url( $key ),
				);
			}
		}
	}
	// phpcs:enable

	if ( ! $chips ) {
		return;
	}

	echo '<div class="active-filters">';
	echo '<span class="active-filters__label">فیلترهای فعال:</span>';
	foreach ( $chips as $chip ) {
		printf(
			'<a class="active-filters__chip" href="%1$s">%2$s %3$s</a>',
			esc_url( $chip['url'] ),
			esc_html( $chip['label'] ),
			netelec_icon( 'close', 'nc-icon--xs' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
	printf( '<a class="active-filters__clear" href="%s">حذف همه</a>', esc_url( wc_get_page_permalink( 'shop' ) ) );
	echo '</div>';
}
