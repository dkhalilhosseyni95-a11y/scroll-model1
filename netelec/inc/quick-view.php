<?php
/**
 * Quick view modal endpoint.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX: return the quick view markup for a product.
 */
function netelec_ajax_quick_view() {
	netelec_ajax_verify();

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product || 'publish' !== get_post_status( $product_id ) ) {
		netelec_ajax_fail( 'محصول یافت نشد.' );
	}

	$post = get_post( $product_id );
	setup_postdata( $post );
	$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	ob_start();
	?>
	<div class="qv">
		<div class="qv__media">
			<?php
			if ( $product->get_image_id() ) {
				echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_single', false, array( 'alt' => $product->get_name() ) );
			} else {
				echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>

		<div class="qv__body">
			<div class="pdp-meta">
				<?php if ( $product->get_sku() ) : ?>
					<span class="pdp-meta__item">شناسه کالا: <strong><?php echo esc_html( $product->get_sku() ); ?></strong></span>
				<?php endif; ?>
				<span class="pdp-meta__item"><?php echo wp_kses_post( wc_get_product_category_list( $product_id ) ); ?></span>
			</div>

			<h2 class="qv__title"><?php echo esc_html( $product->get_name() ); ?></h2>

			<?php if ( $product->get_rating_count() ) : ?>
				<div class="qv__rating">
					<?php netelec_stars( (float) $product->get_average_rating() ); ?>
					<span>(<?php echo esc_html( number_format_i18n( $product->get_review_count() ) ); ?> نظر)</span>
				</div>
			<?php endif; ?>

			<div class="qv__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

			<div class="qv__stock">
				<?php if ( $product->is_in_stock() ) : ?>
					<span class="pc-stock pc-stock--in">موجود در انبار</span>
				<?php else : ?>
					<span class="pc-stock pc-stock--out">ناموجود</span>
				<?php endif; ?>
			</div>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="qv__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
			<?php endif; ?>

			<div class="qv__actions">
				<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
					<a
						href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
						data-quantity="1"
						class="button add_to_cart_button ajax_add_to_cart btn btn--primary"
						data-product_id="<?php echo esc_attr( $product_id ); ?>"
						data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
						rel="nofollow"
					><?php netelec_icon( 'cart', 'nc-icon--sm' ); ?> افزودن به سبد خرید</a>
				<?php endif; ?>
				<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">مشاهده کامل محصول</a>
			</div>
		</div>
	</div>
	<?php
	$html = ob_get_clean();

	wp_reset_postdata();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_netelec_quick_view', 'netelec_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_netelec_quick_view', 'netelec_ajax_quick_view' );
