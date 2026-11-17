<?php
/**
 * Quick cart preview, opened from the header bag icon.
 *
 * Markup is WooCommerce's own mini cart so quantities, the remove links and the
 * totals keep working; the drawer body is registered as an AJAX fragment in
 * inc/integrations/woocommerce.php and refreshed after every add-to-cart.
 *
 * @package Moon_Theme
 */

if ( ! moon_is_woo() ) {
	return;
}
?>
<aside
	class="moon-cart-drawer"
	id="moon-cart"
	data-moon-panel="cart"
	aria-hidden="true"
	aria-label="<?php esc_attr_e( 'پیش‌نمایش سبد خرید', 'moon-theme' ); ?>"
>
	<div class="moon-cart-drawer__head">
		<div>
			<p class="moon-eyebrow"><span dir="ltr">Your bag</span></p>
			<p class="moon-cart-drawer__title"><?php esc_html_e( 'سبد خرید', 'moon-theme' ); ?></p>
		</div>
		<button type="button" class="moon-iconbtn" data-moon-close aria-label="<?php esc_attr_e( 'بستن', 'moon-theme' ); ?>">
			<?php moon_the_icon( 'x', 18 ); ?>
		</button>
	</div>

	<div class="moon-cart-drawer__body" data-moon-cart-body>
		<?php woocommerce_mini_cart(); ?>
	</div>
</aside>
