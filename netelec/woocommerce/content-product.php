<?php
/**
 * Custom product card for the shop loop.
 *
 * @package Netelec
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$netelec_categories = wc_get_product_category_list( $product->get_id(), ', ' );
?>
<li <?php wc_product_class( 'product-card', $product ); ?>>
	<div class="pc-media">
		<?php netelec_product_badges( $product ); ?>

		<a class="pc-media__link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
			<?php
			if ( $product->get_image_id() ) {
				echo wp_get_attachment_image(
					$product->get_image_id(),
					'woocommerce_thumbnail',
					false,
					array(
						'class'   => 'pc-media__img',
						'alt'     => $product->get_name(),
						'loading' => 'lazy',
					)
				);
			} else {
				echo wc_placeholder_img( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</a>

		<?php netelec_card_actions( $product ); ?>

		<?php if ( ! $product->is_in_stock() ) : ?>
			<span class="pc-outofstock">ناموجود</span>
		<?php endif; ?>
	</div>

	<div class="pc-body">
		<?php if ( netelec_opt( 'show_card_category' ) && $netelec_categories ) : ?>
			<div class="pc-cat"><?php echo wp_kses_post( $netelec_categories ); ?></div>
		<?php endif; ?>

		<h2 class="pc-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

		<?php if ( netelec_opt( 'show_rating' ) ) : ?>
			<div class="pc-rating">
				<?php if ( $product->get_rating_count() ) : ?>
					<?php netelec_stars( (float) $product->get_average_rating() ); ?>
					<span class="pc-rating__count">(<?php echo esc_html( number_format_i18n( $product->get_review_count() ) ); ?> نظر)</span>
				<?php else : ?>
					<span class="pc-rating__empty">بدون امتیاز</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="pc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

		<?php netelec_card_stock( $product ); ?>
	</div>

	<div class="pc-foot">
		<?php woocommerce_template_loop_add_to_cart(); ?>
	</div>
</li>
