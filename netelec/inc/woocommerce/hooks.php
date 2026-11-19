<?php
/**
 * WooCommerce hooks: single product, shop layout, account.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace the default shop toolbar with the theme's own.
 */
function netelec_shop_hooks() {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
}
add_action( 'init', 'netelec_shop_hooks' );

/**
 * Keep "min – max" price ranges in logical order in the RTL layout.
 *
 * @param string $price    Formatted range.
 * @param mixed  $from     Min price.
 * @param mixed  $to       Max price.
 * @param string $currency Currency.
 * @return string
 */
function netelec_price_range_direction( $price, $from = '', $to = '', $currency = '' ) {
	return '<span class="nc-price-range" dir="ltr">' . $price . '</span>';
}
add_filter( 'woocommerce_format_price_range', 'netelec_price_range_direction', 10, 4 );

/**
 * Extra trust list under the add-to-cart button.
 */
function netelec_single_trust() {
	$items = array(
		array( 'shield', 'ضمانت اصالت کالا' ),
		array( 'truck', 'ارسال سریع و بسته‌بندی ایمن' ),
		array( 'refresh', 'امکان بازگشت کالا طبق شرایط فروشگاه' ),
		array( 'card', 'پرداخت امن از طریق درگاه بانکی' ),
	);

	echo '<ul class="pdp-trust">';
	foreach ( $items as $item ) {
		echo '<li>' . netelec_icon( $item[0], 'nc-icon--sm' ) . esc_html( $item[1] ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup is static.
	}
	echo '</ul>';
}
add_action( 'woocommerce_single_product_summary', 'netelec_single_trust', 35 );

/**
 * Anchor used by the sticky buy bar.
 */
function netelec_buy_anchor() {
	echo '<span id="pdp-buy" class="pdp-anchor"></span>';
}
add_action( 'woocommerce_single_product_summary', 'netelec_buy_anchor', 29 );

/**
 * Show the product SKU / category line above the title.
 */
function netelec_single_meta_top() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	echo '<div class="pdp-meta">';
	if ( $product->get_sku() ) {
		echo '<span class="pdp-meta__item">شناسه کالا: <strong>' . esc_html( $product->get_sku() ) . '</strong></span>';
	}
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		echo '<span class="pdp-meta__item">' . wp_kses_post( wc_get_product_category_list( $product->get_id() ) ) . '</span>';
	}
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'netelec_single_meta_top', 4 );

/**
 * Remove related products / upsells when disabled in the panel.
 */
function netelec_single_sections() {
	if ( ! netelec_opt( 'show_related' ) ) {
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}
	if ( ! netelec_opt( 'show_upsells' ) ) {
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	}
}
add_action( 'wp', 'netelec_single_sections' );

/**
 * Recently viewed products on the product page.
 */
function netelec_single_recently_viewed() {
	if ( ! netelec_opt( 'show_recently_viewed' ) ) {
		return;
	}
	get_template_part( 'template-parts/shop/recently-viewed' );
}
add_action( 'woocommerce_after_single_product_summary', 'netelec_single_recently_viewed', 25 );

/**
 * Sticky add-to-cart bar for mobile.
 */
function netelec_sticky_add_to_cart() {
	if ( ! is_product() || ! netelec_opt( 'single_sticky_bar' ) ) {
		return;
	}

	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	?>
	<div class="pdp-sticky" data-pdp-sticky>
		<div class="pdp-sticky__info">
			<strong><?php echo esc_html( $product->get_name() ); ?></strong>
			<span><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</div>
		<a class="btn btn--primary" href="#pdp-buy">
			<?php netelec_icon( 'cart', 'nc-icon--sm' ); ?>
			<?php echo $product->is_type( 'variable' ) ? 'انتخاب گزینه‌ها' : 'افزودن به سبد'; ?>
		</a>
	</div>
	<?php
}
add_action( 'wp_footer', 'netelec_sticky_add_to_cart', 5 );

/**
 * Shop toolbar: result count, ordering and active filters.
 */
function netelec_shop_toolbar() {
	?>
	<div class="shop-toolbar">
		<div class="shop-toolbar__count">
			<?php woocommerce_result_count(); ?>
		</div>
		<div class="shop-toolbar__sort">
			<?php woocommerce_catalog_ordering(); ?>
		</div>
	</div>
	<?php
	netelec_active_filters();
}

/**
 * Cart page: add a "continue shopping" link.
 */
function netelec_cart_continue() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}
	$shop = wc_get_page_permalink( 'shop' );
	printf(
		'<a class="btn btn--ghost btn--sm cart-continue" href="%s">%s ادامه خرید</a>',
		esc_url( $shop ),
		netelec_icon( 'arrow-left', 'nc-icon--sm' )
	);
}
add_action( 'woocommerce_before_cart', 'netelec_cart_continue' );

/**
 * Checkout: add a trust line above the form.
 */
function netelec_checkout_trust() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	echo '<div class="checkout-trust">';
	echo netelec_icon( 'lock', 'nc-icon--sm' ) . '<span>پرداخت امن؛ اطلاعات شما روی درگاه بانکی رمزنگاری می‌شود.</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
}
add_action( 'woocommerce_before_checkout_form', 'netelec_checkout_trust', 5 );

/**
 * Account page: greeting above the navigation.
 */
function netelec_account_greeting() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$user = wp_get_current_user();
	echo '<div class="account-hello">';
	echo '<span class="account-hello__avatar">' . esc_html( mb_substr( $user->display_name, 0, 1 ) ) . '</span>';
	echo '<div><strong>' . esc_html( $user->display_name ) . '</strong><small>خوش آمدید</small></div>';
	echo '</div>';
}
add_action( 'woocommerce_before_account_navigation', 'netelec_account_greeting' );

/**
 * Empty cart message gets a shop link.
 *
 * @param string $message Message.
 * @return string
 */
function netelec_empty_cart_message( $message ) {
	$shop = wc_get_page_permalink( 'shop' );
	return $message . '<p><a class="btn btn--primary" href="' . esc_url( $shop ) . '">مشاهده محصولات</a></p>';
}
add_filter( 'wc_empty_cart_message', 'netelec_empty_cart_message' );

/**
 * Wrap the single product summary sections in our layout classes.
 */
function netelec_before_single_product_summary() {
	echo '<div class="pdp-layout"><div class="pdp-gallery">';
}
add_action( 'woocommerce_before_single_product_summary', 'netelec_before_single_product_summary', 1 );

/**
 * Close the gallery column and open the summary column.
 */
function netelec_after_single_product_images() {
	echo '</div><div class="pdp-summary">';
}
add_action( 'woocommerce_before_single_product_summary', 'netelec_after_single_product_images', 21 );

/**
 * Close the product layout columns.
 */
function netelec_close_product_layout() {
	echo '</div></div>';
}
add_action( 'woocommerce_after_single_product_summary', 'netelec_close_product_layout', 1 );
