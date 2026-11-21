<?php
/**
 * Mobile bottom navigation (hidden on desktop).
 *
 * Items and their order come from the MOON panel (Mobile → ناوبری پایین).
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Moon_Settings' ) ) {
	if ( ! Moon_Settings::get( 'bottom_nav', '1' ) ) {
		return;
	}

	$moon_items = array_filter( explode( ',', (string) Moon_Settings::get( 'bottom_nav_items', 'shop,search,compare,account,cart' ) ) );
} else {
	$moon_items = array( 'shop', 'search', 'compare', 'account', 'cart' );
}

if ( empty( $moon_items ) ) {
	return;
}

$moon_count  = moon_cart_count();
$moon_is_shop = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
?>
<nav class="moon-bottomnav" aria-label="<?php esc_attr_e( 'ناوبری سریع', 'moon-theme' ); ?>">
	<?php foreach ( $moon_items as $moon_item ) : ?>
		<?php if ( 'home' === $moon_item ) : ?>
			<a class="moon-bottomnav__item<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php moon_the_icon( 'home', 20 ); ?><span><?php esc_html_e( 'خانه', 'moon-theme' ); ?></span>
			</a>
		<?php elseif ( 'shop' === $moon_item && moon_is_woo() ) : ?>
			<a class="moon-bottomnav__item<?php echo $moon_is_shop ? ' is-active' : ''; ?>" href="<?php echo esc_url( moon_shop_url() ); ?>">
				<?php moon_the_icon( 'store', 20 ); ?><span><?php esc_html_e( 'فروشگاه', 'moon-theme' ); ?></span>
			</a>
		<?php elseif ( 'compare' === $moon_item && class_exists( 'Moon_Compare' ) ) : ?>
			<a class="moon-bottomnav__item<?php echo is_page( 'compare' ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( Moon_Compare::page_url() ); ?>">
				<?php moon_the_icon( 'compare', 20 ); ?><span><?php esc_html_e( 'مقایسه', 'moon-theme' ); ?></span>
			</a>
		<?php elseif ( 'search' === $moon_item ) : ?>
			<button type="button" class="moon-bottomnav__item" data-moon-open="search" aria-expanded="false">
				<?php moon_the_icon( 'search', 20 ); ?><span><?php esc_html_e( 'جستجو', 'moon-theme' ); ?></span>
			</button>
		<?php elseif ( 'wishlist' === $moon_item ) : ?>
			<button type="button" class="moon-bottomnav__item" data-moon-open="wishlist" aria-expanded="false">
				<?php moon_the_icon( 'heart', 20 ); ?><span><?php esc_html_e( 'علاقه‌مندی', 'moon-theme' ); ?></span>
			</button>
		<?php elseif ( 'cart' === $moon_item && moon_is_woo() ) : ?>
			<a class="moon-bottomnav__item moon-cart-toggle" href="<?php echo esc_url( moon_cart_url() ); ?>" data-moon-open="cart" aria-expanded="false">
				<?php moon_the_icon( 'shopping-bag', 20 ); ?>
				<span class="moon-cart-count<?php echo $moon_count > 0 ? ' has-count' : ''; ?>" data-count="<?php echo esc_attr( (string) $moon_count ); ?>"><?php echo $moon_count > 0 ? esc_html( moon_fa_digits( (string) $moon_count ) ) : ''; ?></span>
				<span><?php esc_html_e( 'سبد', 'moon-theme' ); ?></span>
			</a>
		<?php elseif ( 'account' === $moon_item && moon_is_woo() ) : ?>
			<a class="moon-bottomnav__item<?php echo function_exists( 'is_account_page' ) && is_account_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( moon_account_url() ); ?>">
				<?php moon_the_icon( 'user', 20 ); ?><span><?php echo is_user_logged_in() ? esc_html__( 'حساب', 'moon-theme' ) : esc_html__( 'ورود', 'moon-theme' ); ?></span>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
</nav>
