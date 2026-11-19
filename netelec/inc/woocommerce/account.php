<?php
/**
 * My account enhancements.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quick cards on the account dashboard (real customer data only).
 */
function netelec_account_dashboard_cards() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$customer_id = get_current_user_id();
	$orders      = wc_get_customer_order_count( $customer_id );
	$downloads   = count( wc_get_customer_available_downloads( $customer_id ) );
	$wishlist    = netelec_wishlist_count();
	$cards       = array(
		array( 'package', number_format_i18n( $orders ), 'سفارش‌ها', wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ),
		array( 'download', number_format_i18n( $downloads ), 'دانلودها', wc_get_endpoint_url( 'downloads', '', wc_get_page_permalink( 'myaccount' ) ) ),
		array( 'heart', number_format_i18n( $wishlist ), 'علاقه‌مندی‌ها', netelec_page_url( 'wishlist' ) ),
		array( 'pin', '', 'آدرس‌ها', wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ) ) ),
	);
	?>
	<div class="account-cards">
		<?php foreach ( $cards as $card ) : ?>
			<a class="account-card" href="<?php echo esc_url( $card[3] ); ?>">
				<span class="account-card__icon"><?php netelec_icon( $card[0] ); ?></span>
				<span class="account-card__text">
					<?php if ( $card[1] ) : ?>
						<strong><?php echo esc_html( $card[1] ); ?></strong>
					<?php endif; ?>
					<small><?php echo esc_html( $card[2] ); ?></small>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}
add_action( 'woocommerce_account_dashboard', 'netelec_account_dashboard_cards', 5 );

/**
 * Hide the default "From your account dashboard…" paragraph, the cards above
 * already explain the account area.
 *
 * @param string $text Dashboard text.
 * @return string
 */
function netelec_account_dashboard_text( $text ) {
	return '';
}
add_filter( 'woocommerce_account_dashboard_text', 'netelec_account_dashboard_text' );

/**
 * Logout link inside the account navigation gets a distinct style class.
 *
 * @param array $items Menu items.
 * @return array
 */
function netelec_account_menu_items( $items ) {
	if ( isset( $items['customer-logout'] ) ) {
		$items['customer-logout'] = 'خروج از حساب';
	}
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'netelec_account_menu_items' );
