<?php
/**
 * My account navigation.
 *
 * @package Netelec
 */

defined( 'ABSPATH' ) || exit;
?>
<nav class="woocommerce-MyAccount-navigation" aria-label="منوی حساب کاربری">
	<?php do_action( 'woocommerce_before_account_navigation' ); ?>

	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>

		<?php if ( netelec_opt( 'wishlist_enabled' ) ) : ?>
			<li class="netelec-account-link netelec-account-link--wishlist">
				<a href="<?php echo esc_url( netelec_page_url( 'wishlist' ) ); ?>">علاقه‌مندی‌ها</a>
			</li>
		<?php endif; ?>

		<?php if ( netelec_opt( 'compare_enabled' ) ) : ?>
			<li class="netelec-account-link netelec-account-link--compare">
				<a href="<?php echo esc_url( netelec_page_url( 'compare' ) ); ?>">مقایسه محصولات</a>
			</li>
		<?php endif; ?>
	</ul>

	<?php do_action( 'woocommerce_after_account_navigation' ); ?>
</nav>
