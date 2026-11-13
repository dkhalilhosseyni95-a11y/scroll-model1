<?php
/**
 * Demo content importer.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_report = get_transient( 'moon_demo_report_' . get_current_user_id() );

if ( $moon_report ) {
	delete_transient( 'moon_demo_report_' . get_current_user_id() );
}
?>
<div class="wrap moon-admin">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'نصب محتوای اولیه', 'moon-core' ); ?>
	</h1>

	<?php \Moon_Admin::notices(); ?>

	<div class="moon-panels">
		<section class="moon-panel">
			<h2><?php esc_html_e( 'چه چیزی نصب می‌شود؟', 'moon-core' ); ?></h2>
			<p class="moon-field__desc">
				<?php esc_html_e( 'این نصب‌کننده محتوای نسخه نمایشی قالب (صفحه‌ها، منو، تصاویر، دسته‌بندی‌ها، رگال و استودیو) را ایجاد می‌کند. هیچ محتوای موجودی حذف یا بازنویسی نمی‌شود: صفحه‌ها و محصولاتی که از قبل با همان نشانی وجود دارند نادیده گرفته می‌شوند و هر اجرا فقط موارد جدید را اضافه می‌کند.', 'moon-core' ); ?>
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-demo-form">
				<input type="hidden" name="action" value="moon_demo_import">
				<?php wp_nonce_field( 'moon_demo_import', 'moon_demo_nonce' ); ?>

				<label class="moon-check"><input type="checkbox" name="import_pages" value="1" checked> <?php esc_html_e( 'صفحه‌های پایه (خانه، داستان ما، راهنمای سایز، ارسال و بازگشت، سوالات متداول، تماس، حریم خصوصی)', 'moon-core' ); ?></label>
				<label class="moon-check"><input type="checkbox" name="import_media" value="1" checked> <?php esc_html_e( 'تصاویر و رسانه‌ها', 'moon-core' ); ?></label>
				<label class="moon-check"><input type="checkbox" name="import_taxonomies" value="1" checked> <?php esc_html_e( 'دسته‌بندی محصولات (زنانه، مردانه، اکسسوری)', 'moon-core' ); ?></label>
				<label class="moon-check"><input type="checkbox" name="import_rack" value="1" checked> <?php esc_html_e( 'اقلام رگال و استایل‌های استودیو', 'moon-core' ); ?></label>
				<label class="moon-check"><input type="checkbox" name="import_products" value="1"> <?php esc_html_e( 'محصولات نمایشی ووکامرس (فقط اگر خودتان تأیید کنید)', 'moon-core' ); ?></label>

				<p class="moon-field__desc">
					<?php esc_html_e( 'محصولات نمایشی با قیمت‌های فرضی ساخته می‌شوند تا فروشگاه قابل بررسی باشد؛ پیش از راه‌اندازی واقعی آن‌ها را حذف یا ویرایش کنید.', 'moon-core' ); ?>
				</p>

				<p>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'نصب محتوای اولیه', 'moon-core' ); ?></button>
				</p>
			</form>
		</section>

		<?php if ( is_array( $moon_report ) ) : ?>
			<section class="moon-panel">
				<h2><?php esc_html_e( 'گزارش آخرین نصب', 'moon-core' ); ?></h2>

				<?php if ( ! empty( $moon_report['messages'] ) ) : ?>
					<ul class="moon-list">
						<?php foreach ( $moon_report['messages'] as $moon_message ) : ?>
							<li><span class="is-ok">✓</span> <?php echo esc_html( $moon_message ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( ! empty( $moon_report['skipped'] ) ) : ?>
					<h3><?php esc_html_e( 'نادیده گرفته شد (از قبل موجود بود)', 'moon-core' ); ?></h3>
					<ul class="moon-list">
						<?php foreach ( $moon_report['skipped'] as $moon_skip ) : ?>
							<li><span>•</span> <?php echo esc_html( $moon_skip ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( ! empty( $moon_report['errors'] ) ) : ?>
					<h3><?php esc_html_e( 'خطاها', 'moon-core' ); ?></h3>
					<ul class="moon-list">
						<?php foreach ( $moon_report['errors'] as $moon_error ) : ?>
							<li><span class="is-bad">!</span> <?php echo esc_html( $moon_error ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</div>
