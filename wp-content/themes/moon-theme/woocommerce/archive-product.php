<?php
/**
 * Product archive template.
 *
 * The grid is refreshed by AJAX (assets/js/shop.js) using the theme's own filter
 * panel, so colour/size/price filters never reload the page while the URL stays
 * shareable.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 * Prints the .moon-shop wrapper and the MOON shop header.
 */
do_action( 'woocommerce_before_main_content' );

$moon_sort = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'menu_order'; // phpcs:ignore WordPress.Security.NonceVerification
$moon_sorts = array(
	'menu_order' => __( 'پیشنهاد MOON', 'moon-theme' ),
	'popularity' => __( 'محبوب‌ترین', 'moon-theme' ),
	'rating'     => __( 'بیشترین امتیاز', 'moon-theme' ),
	'date'       => __( 'جدیدترین', 'moon-theme' ),
	'price'      => __( 'ارزان‌ترین', 'moon-theme' ),
	'price-desc' => __( 'گران‌ترین', 'moon-theme' ),
);
$moon_total = (int) wc_get_loop_prop( 'total' );
?>
<?php get_template_part( 'template-parts/shop/category-tabs' ); ?>
<?php get_template_part( 'template-parts/shop/subcategories' ); ?>
<div class="moon-shop__layout">
	<?php if ( class_exists( 'Moon_Shop' ) ) : ?>
		<aside class="moon-shop__filters" id="moon-shop-filters" aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'moon-theme' ); ?>">
			<?php Moon_Shop::filters_ui(); ?>
		</aside>
		<div class="moon-shop__backdrop" data-moon-filters-close hidden></div>
	<?php endif; ?>

	<div class="moon-shop__main">
		<div class="moon-shop__toolbar">
			<?php if ( class_exists( 'Moon_Shop' ) ) : ?>
				<button type="button" class="moon-shop__filter-btn" data-moon-filters-open aria-controls="moon-shop-filters">
					<?php moon_the_icon( 'menu', 16 ); ?>
					<span><?php esc_html_e( 'فیلترها', 'moon-theme' ); ?></span>
					<?php if ( Moon_Shop::has_active_filters() ) : ?>
						<span class="moon-shop__filter-dot" aria-hidden="true"></span>
					<?php endif; ?>
				</button>
			<?php endif; ?>

			<?php if ( ! class_exists( 'Moon_Settings' ) || Moon_Settings::get( 'shop_count', '1' ) ) : ?>
				<span class="moon-shop__count" data-moon-count>
					<?php
					echo esc_html( sprintf(
						/* translators: %s: number of products. */
						__( 'نمایش %s محصول', 'moon-theme' ),
						moon_fa_digits( number_format_i18n( $moon_total ) )
					) );
					?>
				</span>
			<?php endif; ?>

			<?php if ( ! class_exists( 'Moon_Settings' ) || Moon_Settings::get( 'shop_view_toggle', '1' ) ) : ?>
				<div class="moon-shop__views" role="group" aria-label="<?php esc_attr_e( 'حالت نمایش محصولات', 'moon-theme' ); ?>">
					<button type="button" class="moon-shop__view" data-moon-view="grid" aria-pressed="true" aria-label="<?php esc_attr_e( 'نمایش شبکه‌ای', 'moon-theme' ); ?>">
						<?php moon_the_icon( 'menu', 15 ); ?>
					</button>
					<button type="button" class="moon-shop__view" data-moon-view="list" aria-pressed="false" aria-label="<?php esc_attr_e( 'نمایش لیستی', 'moon-theme' ); ?>">
						<?php moon_the_icon( 'menu', 15, 'moon-icon--list' ); ?>
					</button>
				</div>
			<?php endif; ?>

			<label class="moon-shop__sort">
				<span class="screen-reader-text"><?php esc_html_e( 'ترتیب نمایش', 'moon-theme' ); ?></span>
				<select data-moon-sort>
					<?php foreach ( $moon_sorts as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $moon_sort, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>

		<div class="moon-shop__grid" data-moon-grid>
			<?php if ( woocommerce_product_loop() ) : ?>
				<?php
				woocommerce_product_loop_start();

				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}

				woocommerce_product_loop_end();
				?>
			<?php else : ?>
				<div class="moon-shop__empty">
					<p><?php esc_html_e( 'محصولی با این فیلترها پیدا نشد.', 'moon-theme' ); ?></p>
					<button type="button" class="moon-btn moon-btn--dark" data-moon-filter-reset><?php esc_html_e( 'پاک کردن فیلترها', 'moon-theme' ); ?></button>
				</div>
			<?php endif; ?>
		</div>

		<div class="moon-shop__pager" data-moon-pager>
			<?php
			$moon_pages = (int) $GLOBALS['wp_query']->max_num_pages;
			$moon_page  = max( 1, (int) get_query_var( 'paged' ) );
			$moon_more  = class_exists( 'Moon_Settings' ) && 'more' === Moon_Settings::get( 'shop_pagination', 'pages' );

			if ( $moon_more && $moon_page < $moon_pages ) {
				printf(
					'<div class="moon-loadmore"><button type="button" class="moon-btn moon-btn--dark" data-moon-loadmore data-next="%1$s">%2$s</button></div>',
					esc_url( get_pagenum_link( $moon_page + 1 ) ),
					esc_html__( 'محصولات بیشتر', 'moon-theme' )
				);
			} elseif ( class_exists( 'Moon_Shop' ) ) {
				echo Moon_Shop::pagination( $moon_pages, $moon_page ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				do_action( 'woocommerce_after_shop_loop' );
			}
			?>
		</div>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
