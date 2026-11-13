<?php
/**
 * Product archive template.
 *
 * @package Moon_Theme
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 * Prints the .moon-shop wrapper and the MOON shop header.
 */
do_action( 'woocommerce_before_main_content' );

$moon_has_filters = is_active_sidebar( 'shop-sidebar' );
?>
<div class="moon-shop__layout <?php echo $moon_has_filters ? 'has-filters' : 'no-filters'; ?>">
	<?php if ( $moon_has_filters ) : ?>
		<aside class="moon-shop__sidebar" aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'moon-theme' ); ?>">
			<?php dynamic_sidebar( 'shop-sidebar' ); ?>
		</aside>
	<?php endif; ?>

	<div class="moon-shop__main">
		<?php if ( woocommerce_product_loop() ) : ?>

			<div class="moon-shop__toolbar">
				<?php
				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
				?>
			</div>

			<?php
			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();
					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
			}

			woocommerce_product_loop_end();

			/**
			 * Hook: woocommerce_after_shop_loop.
			 *
			 * @hooked woocommerce_pagination - 10
			 */
			do_action( 'woocommerce_after_shop_loop' );
			?>
		<?php else : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php endif; ?>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
