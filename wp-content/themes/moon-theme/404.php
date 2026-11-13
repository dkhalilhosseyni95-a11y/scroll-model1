<?php
/**
 * 404.
 *
 * @package Moon_Theme
 */

get_header();
?>
<div class="moon-404">
	<div>
		<p class="moon-404__code" dir="ltr">404</p>
		<h1 class="moon-404__title"><?php esc_html_e( 'این صفحه پیدا نشد', 'moon-theme' ); ?></h1>
		<p class="moon-404__text"><?php esc_html_e( 'ممکن است نشانی تغییر کرده باشد. از فروشگاه شروع کنید.', 'moon-theme' ); ?></p>
		<div class="moon-404__actions">
			<a class="moon-btn moon-btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'moon-theme' ); ?></a>
			<a class="moon-btn moon-btn--outline" href="<?php echo esc_url( moon_shop_url() ); ?>"><?php esc_html_e( 'فروشگاه', 'moon-theme' ); ?></a>
		</div>
		<div class="moon-blog-layout" style="padding:2.5rem 0 0;max-width:28rem;margin-inline:auto;">
			<?php get_search_form(); ?>
		</div>
	</div>
</div>
<?php
get_footer();
