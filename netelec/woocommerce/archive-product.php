<?php
/**
 * Shop archive template.
 *
 * @package Netelec
 */

defined( 'ABSPATH' ) || exit;

get_header();

$netelec_show_sidebar = netelec_opt( 'show_sidebar_filters' ) && ( is_shop() || is_product_taxonomy() );
$netelec_title        = woocommerce_page_title( false );
$netelec_desc         = '';

if ( is_product_taxonomy() ) {
	$netelec_desc = term_description();
}
?>
<div class="page-hero page-hero--shop">
	<div class="container">
		<?php netelec_breadcrumb(); ?>
		<h1 class="page-hero__title"><?php echo esc_html( $netelec_title ? $netelec_title : 'فروشگاه' ); ?></h1>
		<?php if ( $netelec_desc ) : ?>
			<div class="page-hero__sub"><?php echo wp_kses_post( $netelec_desc ); ?></div>
		<?php endif; ?>
	</div>
</div>

<div class="container shop-layout<?php echo $netelec_show_sidebar ? ' shop-layout--sidebar' : ''; ?>">
	<?php if ( $netelec_show_sidebar ) : ?>
		<aside class="shop-sidebar" id="shop-filters" aria-label="فیلتر محصولات">
			<button class="shop-sidebar__close" type="button" aria-label="بستن فیلترها"><?php netelec_icon( 'close' ); ?></button>
			<?php get_template_part( 'template-parts/shop/filters' ); ?>
			<?php if ( is_active_sidebar( 'shop-sidebar' ) ) : ?>
				<?php dynamic_sidebar( 'shop-sidebar' ); ?>
			<?php endif; ?>
		</aside>
	<?php endif; ?>

	<div class="shop-main">
		<?php if ( $netelec_show_sidebar ) : ?>
			<button class="shop-filter-toggle btn btn--ghost btn--sm" type="button" aria-controls="shop-filters" aria-expanded="false">
				<?php netelec_icon( 'filter', 'nc-icon--sm' ); ?>
				فیلتر محصولات
			</button>
		<?php endif; ?>

		<?php if ( woocommerce_product_loop() ) : ?>
			<?php netelec_shop_toolbar(); ?>

			<?php woocommerce_product_loop_start(); ?>

			<?php
			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
			}
			?>

			<?php woocommerce_product_loop_end(); ?>

			<?php woocommerce_pagination(); ?>
		<?php else : ?>
			<div class="shop-empty">
				<?php netelec_icon( 'package' ); ?>
				<h2>محصولی یافت نشد</h2>
				<p>فیلترها را تغییر دهید یا همه محصولات را ببینید.</p>
				<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">همه محصولات</a>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
