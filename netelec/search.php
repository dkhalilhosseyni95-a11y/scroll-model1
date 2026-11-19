<?php
/**
 * Search results. Product searches render the shop grid.
 *
 * @package Netelec
 */

get_header();

$netelec_is_product_search = 'product' === get_query_var( 'post_type' );

if ( $netelec_is_product_search && function_exists( 'wc_get_template_part' ) ) {
	$netelec_show_sidebar = netelec_opt( 'show_sidebar_filters' );
	?>
	<?php netelec_page_header( 'نتایج جستجو برای «' . get_search_query() . '»', sprintf( '%s محصول پیدا شد', number_format_i18n( $GLOBALS['wp_query']->found_posts ) ) ); ?>

	<div class="container shop-layout<?php echo $netelec_show_sidebar ? ' shop-layout--sidebar' : ''; ?>">
		<?php if ( $netelec_show_sidebar ) : ?>
			<aside class="shop-sidebar" id="shop-filters" aria-label="فیلتر محصولات">
				<button class="shop-sidebar__close" type="button" aria-label="بستن فیلترها"><?php netelec_icon( 'close' ); ?></button>
				<?php get_template_part( 'template-parts/shop/filters' ); ?>
			</aside>
		<?php endif; ?>

		<div class="shop-main">
			<?php if ( woocommerce_product_loop() ) : ?>
				<?php netelec_shop_toolbar(); ?>
				<?php woocommerce_product_loop_start(); ?>
				<?php
				while ( have_posts() ) {
					the_post();
					wc_get_template_part( 'content', 'product' );
				}
				?>
				<?php woocommerce_product_loop_end(); ?>
				<?php woocommerce_pagination(); ?>
			<?php else : ?>
				<div class="shop-empty">
					<?php netelec_icon( 'search' ); ?>
					<h2>محصولی پیدا نشد</h2>
					<p>عبارت دیگری را جستجو کنید یا همه محصولات را ببینید.</p>
					<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">همه محصولات</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	get_footer();
	return;
}
?>

<?php netelec_page_header( 'نتایج جستجو برای «' . get_search_query() . '»' ); ?>

<div class="container blog-layout<?php echo netelec_opt( 'blog_sidebar' ) ? ' blog-layout--sidebar' : ''; ?>">
	<div class="blog-main">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid" style="--nc-cols:<?php echo esc_attr( max( 1, (int) netelec_opt( 'blog_columns' ) ) ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content-card' );
				endwhile;
				?>
			</div>
			<?php netelec_pagination(); ?>
		<?php else : ?>
			<div class="shop-empty">
				<?php netelec_icon( 'search' ); ?>
				<h2>نتیجه‌ای پیدا نشد</h2>
				<p>عبارت دیگری را امتحان کنید.</p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
