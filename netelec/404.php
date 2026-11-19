<?php
/**
 * 404 page.
 *
 * @package Netelec
 */

get_header();
?>
<div class="error-page">
	<div class="container error-page__inner">
		<div class="error-page__art" aria-hidden="true">
			<span class="error-page__code">۴۰۴</span>
			<?php netelec_icon( 'alert' ); ?>
		</div>
		<h1>صفحه‌ای که دنبالش بودید پیدا نشد</h1>
		<p>ممکن است آدرس تغییر کرده باشد یا محصول حذف شده باشد. می‌توانید از جستجو یا لینک‌های زیر استفاده کنید.</p>

		<div class="error-page__search">
			<?php get_search_form(); ?>
		</div>

		<div class="error-page__links">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">صفحه اصلی</a>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="btn btn--ghost" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">فروشگاه</a>
			<?php endif; ?>
			<a class="btn btn--ghost" href="<?php echo esc_url( netelec_page_url( 'contact' ) ); ?>">تماس با ما</a>
		</div>
	</div>
</div>

<?php if ( function_exists( 'netelec_render_products_section' ) ) : ?>
	<?php
	netelec_render_products_section(
		'محصولات پیشنهادی',
		array(
			'posts_per_page' => 4,
			'orderby'        => 'date',
		),
		function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : ''
	);
	?>
<?php endif; ?>

<?php get_footer(); ?>
