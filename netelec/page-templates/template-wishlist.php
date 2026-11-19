<?php
/**
 * Template Name: علاقه‌مندی‌ها
 *
 * @package Netelec
 */

get_header();

$netelec_ids = netelec_wishlist_ids();
?>
<?php netelec_page_header( get_the_title(), 'محصولاتی که برای بررسی ذخیره کرده‌اید' ); ?>

<div class="container wishlist-page">
	<div class="wishlist-page__bar">
		<p><?php echo esc_html( number_format_i18n( count( $netelec_ids ) ) ); ?> محصول در لیست علاقه‌مندی‌ها</p>
		<?php if ( $netelec_ids && function_exists( 'wc_get_page_permalink' ) ) : ?>
			<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">ادامه خرید</a>
		<?php endif; ?>
	</div>

	<?php netelec_render_products_by_ids( $netelec_ids, 'لیست علاقه‌مندی‌های شما خالی است.' ); ?>
</div>

<?php get_footer(); ?>
