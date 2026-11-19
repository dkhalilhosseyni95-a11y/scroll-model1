<?php
/**
 * WooCommerce theme integration.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Products per row in the shop loop.
 *
 * @return int
 */
function netelec_loop_columns() {
	return max( 1, (int) netelec_opt( 'shop_columns' ) );
}
add_filter( 'loop_shop_columns', 'netelec_loop_columns', 20 );

/**
 * Products per page.
 *
 * @return int
 */
function netelec_products_per_page() {
	return max( 4, (int) netelec_opt( 'shop_per_page' ) );
}
add_filter( 'loop_shop_per_page', 'netelec_products_per_page', 20 );

/**
 * Related products count.
 *
 * @param array $args Related args.
 * @return array
 */
function netelec_related_args( $args ) {
	$args['posts_per_page'] = (int) netelec_opt( 'related_count' );
	$args['columns']        = netelec_loop_columns();
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'netelec_related_args' );

/**
 * Keep the cart widget in sync after AJAX add-to-cart.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function netelec_cart_fragments_filter( $fragments ) {
	ob_start();
	?>
	<span class="icon-btn__badge cart-count"><?php echo esc_html( netelec_cart_count() ); ?></span>
	<?php
	$fragments['.cart-count'] = ob_get_clean();

	ob_start();
	?>
	<span class="cart-total"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
	<?php
	$fragments['.cart-total'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'netelec_cart_fragments_filter' );

/**
 * Percentage of discount for a product (0 when not on sale).
 *
 * @param WC_Product $product Product.
 * @return int
 */
function netelec_discount_percent( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}

	$percentages = array();

	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );
			if ( ! $variation || ! $variation->is_on_sale() ) {
				continue;
			}
			$regular = (float) $variation->get_regular_price();
			$sale    = (float) $variation->get_sale_price();
			if ( $regular > 0 && $sale > 0 ) {
				$percentages[] = ( ( $regular - $sale ) / $regular ) * 100;
			}
		}
	} else {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $regular > 0 && $sale > 0 ) {
			$percentages[] = ( ( $regular - $sale ) / $regular ) * 100;
		}
	}

	if ( ! $percentages ) {
		return 0;
	}

	return (int) round( max( $percentages ) );
}

/**
 * Is the product new (published within the last 45 days)?
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function netelec_is_new_product( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	$created = $product->get_date_created();
	if ( ! $created ) {
		return false;
	}
	return ( time() - $created->getTimestamp() ) < ( 45 * DAY_IN_SECONDS );
}

/**
 * Render a product section (used on the homepage).
 *
 * @param string $title   Section title.
 * @param array  $args    WP_Query args.
 * @param string $link    "View all" link.
 * @param int    $columns Columns.
 */
function netelec_render_products_section( $title, $args, $link = '', $columns = 4 ) {
	if ( ! function_exists( 'wc_get_template_part' ) ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 4,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
		)
	);

	// Never show products hidden from the catalog.
	$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => 'exclude-from-catalog',
		'operator' => 'NOT IN',
	);

	unset( $args['fields'] );

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return;
	}

	wc_set_loop_prop( 'columns', $columns );
	wc_set_loop_prop( 'name', 'netelec-home' );

	// Product sections can render on any page, so make sure the cart scripts
	// are available in the footer.
	wp_enqueue_script( 'wc-add-to-cart' );
	wp_enqueue_script( 'wc-cart-fragments' );
	?>
	<section class="section section--products">
		<div class="container">
			<?php netelec_section_head( $title, $link ); ?>
			<?php woocommerce_product_loop_start(); ?>
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			endwhile;
			?>
			<?php woocommerce_product_loop_end(); ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	wc_reset_loop();
}

/**
 * Product badges (sale / new / out of stock).
 *
 * @param WC_Product $product Product.
 */
function netelec_product_badges( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	echo '<div class="pc-badges">';

	if ( netelec_opt( 'show_sale_badge' ) && $product->is_on_sale() ) {
		$percent = netelec_discount_percent( $product );
		printf(
			'<span class="pc-badge pc-badge--sale">%s</span>',
			esc_html( $percent ? '٪' . number_format_i18n( $percent ) . ' تخفیف' : 'تخفیف' )
		);
	}

	if ( netelec_opt( 'show_new_badge' ) && netelec_is_new_product( $product ) ) {
		echo '<span class="pc-badge pc-badge--new">جدید</span>';
	}

	echo '</div>';
}

/**
 * Card action buttons (wishlist, compare, quick view).
 *
 * @param WC_Product $product Product.
 */
function netelec_card_actions( $product ) {
	$id           = $product->get_id();
	$in_wishlist  = in_array( $id, netelec_wishlist_ids(), true );
	$in_compare   = in_array( $id, netelec_compare_ids(), true );
	?>
	<div class="pc-actions">
		<?php if ( netelec_opt( 'wishlist_enabled' ) ) : ?>
			<button type="button" class="pc-action<?php echo $in_wishlist ? ' is-active' : ''; ?>" data-wishlist-toggle="<?php echo esc_attr( $id ); ?>" aria-label="افزودن به علاقه‌مندی‌ها" title="علاقه‌مندی‌ها">
				<?php netelec_icon( 'heart' ); ?>
			</button>
		<?php endif; ?>

		<?php if ( netelec_opt( 'compare_enabled' ) ) : ?>
			<button type="button" class="pc-action<?php echo $in_compare ? ' is-active' : ''; ?>" data-compare-toggle="<?php echo esc_attr( $id ); ?>" aria-label="افزودن به مقایسه" title="مقایسه">
				<?php netelec_icon( 'compare' ); ?>
			</button>
		<?php endif; ?>

		<?php if ( netelec_opt( 'quick_view' ) ) : ?>
			<button type="button" class="pc-action" data-quick-view="<?php echo esc_attr( $id ); ?>" aria-label="مشاهده سریع" title="مشاهده سریع">
				<?php netelec_icon( 'eye' ); ?>
			</button>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Stock line on cards.
 *
 * @param WC_Product $product Product.
 */
function netelec_card_stock( $product ) {
	if ( ! netelec_opt( 'show_stock' ) ) {
		return;
	}

	if ( ! $product->is_in_stock() ) {
		echo '<span class="pc-stock pc-stock--out">ناموجود</span>';
		return;
	}

	$quantity = $product->get_stock_quantity();
	if ( $product->managing_stock() && null !== $quantity && $quantity > 0 && $quantity <= 5 ) {
		printf( '<span class="pc-stock pc-stock--low">تنها %s عدد در انبار</span>', esc_html( number_format_i18n( $quantity ) ) );
		return;
	}

	echo '<span class="pc-stock pc-stock--in">موجود در انبار</span>';
}
