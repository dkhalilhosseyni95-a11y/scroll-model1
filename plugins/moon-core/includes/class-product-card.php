<?php
/**
 * Product card markup shared by the grid, carousel and related-products widgets.
 *
 * Every value comes from WooCommerce objects; nothing is hardcoded. The add to
 * cart button is rendered by WooCommerce itself so AJAX, stock and variation
 * behaviour stay native.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Product_Card {

	/**
	 * Render one product card.
	 *
	 * @param \WC_Product         $moon_product Product object.
	 * @param array<string,mixed> $args         Rendering options.
	 */
	public static function render( \WC_Product $moon_product, array $args = array() ): void {
		$args = wp_parse_args(
			$args,
			array(
				'show_quick_view' => true,
				'show_category'   => true,
				'show_rating'     => false,
				'image_size'      => 'moon-product',
			)
		);

		$permalink = get_permalink( $moon_product->get_id() );
		$title     = $moon_product->get_name();
		$is_sale   = $moon_product->is_on_sale();
		$in_stock  = $moon_product->is_in_stock();

		$category = '';
		$terms    = wc_get_product_category_list( $moon_product->get_id(), '||' );
		if ( $terms ) {
			$parts    = explode( '||', wp_strip_all_tags( $terms ) );
			$category = trim( $parts[0] );
		}

		echo '<article class="moon-product-card' . ( $is_sale ? ' is-on-sale' : '' ) . ( $in_stock ? '' : ' is-sold-out' ) . '" data-moon-reveal-item>';
		echo '<div class="moon-product-card__media">';

		printf(
			'<a class="moon-product-card__link" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $permalink ),
			esc_attr( $title ),
			wp_kses_post( $moon_product->get_image( $args['image_size'], array( 'loading' => 'lazy' ) ) )
		);

		$secondary = self::secondary_image( $moon_product, $args['image_size'] );

		echo '<div class="moon-product-card__badges">';
		if ( ! $in_stock ) {
			echo '<span class="moon-badge moon-badge--muted">' . esc_html__( 'Sold out', 'moon-core' ) . '</span>';
		} elseif ( $is_sale ) {
			echo '<span class="moon-badge">' . esc_html__( 'Sale', 'moon-core' ) . '</span>';
		}
		echo '</div>';

		if ( $secondary ) {
			echo '<div class="moon-product-card__secondary" aria-hidden="true">' . wp_kses_post( $secondary ) . '</div>';
		}

		echo '<div class="moon-product-card__actions">';

		if ( $args['show_quick_view'] && Settings::get( 'quick_view' ) === 'yes' ) {
			printf(
				'<button type="button" class="moon-product-card__action moon-quick-view-trigger" data-moon-quick-view="%1$d" aria-label="%2$s">%3$s<span>%4$s</span></button>',
				(int) $moon_product->get_id(),
				esc_attr( sprintf( /* translators: %s: product name */ __( 'Quick view: %s', 'moon-core' ), $title ) ),
				Helpers::svg( Helpers::icon( 'plus', 16 ) ),
				esc_html__( 'Quick view', 'moon-core' )
			);
		}

		// Official WooCommerce add-to-cart button (AJAX aware, stock aware).
		global $product;
		$moon_previous = $product instanceof \WC_Product ? $product : null;
		$product       = $moon_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- required by WooCommerce template functions.
		woocommerce_template_loop_add_to_cart();
		$product = $moon_previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored.

		echo '</div></div>';

		echo '<div class="moon-product-card__body">';
		if ( $args['show_category'] && '' !== $category ) {
			echo '<p class="moon-product-card__cat">' . esc_html( $category ) . '</p>';
		}
		printf(
			'<h3 class="moon-product-card__title"><a href="%1$s">%2$s</a></h3>',
			esc_url( $permalink ),
			esc_html( $title )
		);

		echo '<div class="moon-product-card__price">' . wp_kses_post( $moon_product->get_price_html() ) . '</div>';

		if ( $args['show_rating'] && $moon_product->get_average_rating() > 0 ) {
			printf(
				'<p class="moon-product-card__rating">%1$s <span>%2$s</span></p>',
				wc_get_rating_html( (float) $moon_product->get_average_rating() ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce markup.
				esc_html( sprintf( /* translators: %s: review count */ _n( '%s review', '%s reviews', (int) $moon_product->get_review_count(), 'moon-core' ), number_format_i18n( (int) $moon_product->get_review_count() ) ) )
			);
		}

		echo '</div></article>';
	}

	/**
	 * First gallery image used for the hover crossfade.
	 */
	private static function secondary_image( \WC_Product $moon_product, string $size ): string {
		$gallery = $moon_product->get_gallery_image_ids();

		if ( empty( $gallery ) ) {
			return '';
		}

		$html = wp_get_attachment_image( (int) $gallery[0], $size, false, array( 'loading' => 'lazy' ) );

		return $html ?: '';
	}
}
