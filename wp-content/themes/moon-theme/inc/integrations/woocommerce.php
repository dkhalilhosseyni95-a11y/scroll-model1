<?php
/**
 * WooCommerce integration.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare WooCommerce support.
 */
function moon_woocommerce_setup() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1200,
		'product_grid'          => array(
			'default_rows'    => 3,
			'min_rows'        => 1,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'moon_woocommerce_setup' );

if ( ! moon_is_woo() ) {
	return;
}

/**
 * Theme wrappers around WooCommerce screens.
 */
function moon_woo_wrappers() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

	add_action( 'woocommerce_before_main_content', 'moon_woo_open', 5 );
	add_action( 'woocommerce_after_main_content', 'moon_woo_close', 50 );
}
add_action( 'init', 'moon_woo_wrappers' );

/**
 * Open the shop content wrapper.
 */
function moon_woo_open() {
	echo '<div class="moon-shop"><div class="moon-container">';
}

/**
 * Close the shop content wrapper.
 */
function moon_woo_close() {
	echo '</div></div>';
}

/**
 * Shop page header replaces the default page title.
 */
function moon_woo_shop_header() {
	if ( ! is_shop() && ! is_product_taxonomy() ) {
		return;
	}

	$title = woocommerce_page_title( false );

	if ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->description ) && $term->description ) {
			$title .= '|' . $term->description;
		}
	}

	$parts = explode( '|', $title, 2 );

	echo '<header class="moon-shop__header">';
	echo '<div class="moon-breadcrumb-wrap">';
	woocommerce_breadcrumb( array(
		'delimiter' => '<span aria-hidden="true">/</span>',
		'wrap_before' => '<nav class="moon-breadcrumb" aria-label="' . esc_attr__( 'مسیر صفحه', 'moon-theme' ) . '">',
		'wrap_after'  => '</nav>',
	) );
	echo '</div>';
	echo '<h1 class="moon-shop__title">' . esc_html( $parts[0] ) . '</h1>';

	if ( ! empty( $parts[1] ) ) {
		echo '<p class="moon-shop__desc">' . esc_html( wp_strip_all_tags( $parts[1] ) ) . '</p>';
	}

	echo '</header>';
}
add_action( 'woocommerce_before_main_content', 'moon_woo_shop_header', 5 );

/**
 * Hide WooCommerce's own page title output.
 *
 * @return bool
 */
function moon_woo_hide_title() {
	return false;
}
add_filter( 'woocommerce_show_page_title', 'moon_woo_hide_title' );

/**
 * Product columns.
 *
 * @return int
 */
function moon_woo_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'moon_woo_columns', 20 );

/**
 * Products per page.
 *
 * @return int
 */
function moon_woo_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'moon_woo_per_page', 20 );

/**
 * Related products: four columns, four items.
 *
 * @param array $args Related product arguments.
 * @return array
 */
function moon_woo_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'moon_woo_related_args' );

/**
 * Refresh the header cart counter after AJAX add-to-cart.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function moon_woo_cart_fragments( $fragments ) {
	$count = moon_cart_count();

	$fragments['.moon-cart-count'] = sprintf(
		'<span class="moon-cart-count" data-count="%1$d">%2$s</span>',
		$count,
		$count > 0 ? esc_html( moon_fa_digits( (string) $count ) ) : ''
	);

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'moon_woo_cart_fragments' );

/**
 * Keep the header quick-cart drawer in sync with AJAX add-to-cart requests.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function moon_woo_drawer_fragments( $fragments ) {
	if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
		return $fragments;
	}

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$fragments['div.moon-cart-drawer__body'] = '<div class="moon-cart-drawer__body" data-moon-cart-body>' . $mini_cart . '</div>';

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'moon_woo_drawer_fragments' );

/**
 * Persian digits for WooCommerce price output.
 *
 * @param string $html Price HTML.
 * @return string
 */
function moon_woo_price_digits( $html ) {
	return moon_fa_digits( $html );
}
add_filter( 'wc_price', 'moon_woo_price_digits' );

/**
 * Persian digits for stock counts.
 *
 * @param string $qty Formatted quantity.
 * @return string
 */
function moon_woo_stock_digits( $qty ) {
	return moon_fa_digits( $qty );
}
add_filter( 'woocommerce_format_stock_quantity', 'moon_woo_stock_digits' );

/**
 * Persian digits for the "2 × price" quantity line in the mini cart and drawer.
 *
 * @param string $quantity_html Quantity line.
 * @return string
 */
function moon_woo_mini_cart_quantity_digits( $quantity_html ) {
	return moon_fa_digits( $quantity_html );
}
add_filter( 'woocommerce_widget_cart_item_quantity', 'moon_woo_mini_cart_quantity_digits' );

/**
 * Product data used by the homepage carousel.
 *
 * @param string $source recent|featured|sale.
 * @param int    $limit  Number of products.
 * @return array
 */
function moon_woo_carousel_products( $source = 'recent', $limit = 8 ) {
	$args = array(
		'limit'   => max( 1, (int) $limit ),
		'status'  => 'publish',
		'orderby' => 'date',
		'order'   => 'DESC',
	);

	if ( 'featured' === $source ) {
		$args['featured'] = true;
	} elseif ( 'sale' === $source ) {
		$args['include'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
	}

	$products = wc_get_products( $args );
	$items    = array();

	foreach ( $products as $product ) {
		$items[] = moon_woo_carousel_item( $product );
	}

	return $items;
}

/**
 * Normalise a WC product for the carousel template.
 *
 * @param WC_Product $product Product object.
 * @return array
 */
function moon_woo_carousel_item( $product ) {
	$image_id  = $product->get_image_id();
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'moon-card' ) : wc_placeholder_img_src( 'moon-card' );
	$tag       = '';

	if ( $product->is_on_sale() ) {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();

		if ( $regular > 0 && $sale > 0 ) {
			$tag = '−' . moon_fa_digits( (string) round( ( ( $regular - $sale ) / $regular ) * 100 ) ) . '٪';
		} else {
			$tag = __( 'تخفیف', 'moon-theme' );
		}
	} elseif ( $product->is_featured() ) {
		$tag = __( 'ویژه', 'moon-theme' );
	}

	return array(
		'id'       => $product->get_id(),
		'name'     => $product->get_name(),
		'en'       => $product->get_slug(),
		'price'    => $product->get_price_html(),
		'image'    => $image_url,
		'url'      => get_permalink( $product->get_id() ),
		'tag'      => $tag,
		'cover'    => true,
		'add_url'  => $product->is_type( 'simple' ) && $product->is_in_stock() ? esc_url( $product->add_to_cart_url() ) : '',
		'add_id'   => $product->is_type( 'simple' ) && $product->is_in_stock() ? $product->get_id() : 0,
		'sizes'    => moon_woo_product_sizes( $product ),
		'excerpt'  => wp_strip_all_tags( $product->get_short_description() ),
	);
}

/**
 * Available size terms for a product (used in the quick view).
 *
 * @param WC_Product $product Product.
 * @return array
 */
function moon_woo_product_sizes( $product ) {
	$sizes = array();

	if ( $product instanceof WC_Product_Variable ) {
		$attributes = $product->get_variation_attributes();

		foreach ( array( 'pa_size', 'size' ) as $key ) {
			if ( ! empty( $attributes[ $key ] ) ) {
				$sizes = $attributes[ $key ];
				break;
			}
		}

		// Variation attributes are term slugs; show the term names instead.
		$sizes = array_map( function ( $size ) {
			$term = get_term_by( 'slug', $size, 'pa_size' );

			return $term && ! is_wp_error( $term ) ? $term->name : $size;
		}, $sizes );
	}

	return array_slice( array_map( 'strval', (array) $sizes ), 0, 6 );
}

/**
 * Add the sale badge markup used by the MOON card.
 */
function moon_woo_before_shop_loop_item_title() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}
	?>
	<div class="moon-card__badges">
		<?php if ( $product->is_on_sale() ) : ?>
			<span class="moon-card__badge"><?php esc_html_e( 'تخفیف', 'moon-theme' ); ?></span>
		<?php elseif ( $product->is_featured() ) : ?>
			<span class="moon-card__badge"><?php esc_html_e( 'ویژه', 'moon-theme' ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Single product: add a compact trust row under the add-to-cart form.
 */
function moon_woo_single_trust() {
	$items = array(
		array( 'truck', __( 'ارسال سریع', 'moon-theme' ), __( 'ارسال به سراسر ایران', 'moon-theme' ) ),
		array( 'rotate-ccw', __( 'بازگشت آسان', 'moon-theme' ), __( 'تا ۷ روز پس از دریافت', 'moon-theme' ) ),
		array( 'shield-check', __( 'پرداخت امن', 'moon-theme' ), __( 'درگاه رمزنگاری‌شده', 'moon-theme' ) ),
	);

	echo '<ul class="moon-trust">';

	foreach ( $items as $item ) {
		echo '<li class="moon-trust__item">';
		echo moon_icon( $item[0], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span><strong>' . esc_html( $item[1] ) . '</strong><em>' . esc_html( $item[2] ) . '</em></span>';
		echo '</li>';
	}

	echo '</ul>';
}
add_action( 'woocommerce_single_product_summary', 'moon_woo_single_trust', 45 );
