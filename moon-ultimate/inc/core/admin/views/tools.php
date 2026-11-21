<?php
/**
 * Tools: backup, export, import, reset, and a short system report.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_backup = get_option( 'moon_settings_backup' );
$moon_stats  = Moon_Stats::all( true );
?>
<div class="wrap moon-admin" dir="rtl">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'ابزارها و پشتیبان‌گیری', 'moon-theme' ); ?>
	</h1>

	<?php Moon_Admin::notices(); ?>

	<div class="moon-dash__grid">
		<section class="moon-box">
			<h2><?php esc_html_e( 'خروجی تنظیمات', 'moon-theme' ); ?></h2>
			<p><?php esc_html_e( 'یک فایل JSON از همه تنظیمات، بخش‌های صفحه اصلی و ستون‌های فوتر بگیرید. برای انتقال سایت یا بازگردانی امن به کار می‌رود.', 'moon-theme' ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'moon_tools', 'moon_action' => 'export' ), admin_url( 'admin-post.php' ) ), 'moon_tools' ) ); ?>"><?php esc_html_e( 'دانلود فایل تنظیمات', 'moon-theme' ); ?></a>
			</p>
		</section>

		<section class="moon-box">
			<h2><?php esc_html_e( 'وارد کردن تنظیمات', 'moon-theme' ); ?></h2>
			<p><?php esc_html_e( 'فایل خروجی MOON را انتخاب کنید. مقادیر با همان اعتبارسنجی پنل ذخیره می‌شوند و محتوای نامعتبر رد می‌شود.', 'moon-theme' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="moon_tools">
				<input type="hidden" name="moon_action" value="import">
				<?php wp_nonce_field( 'moon_tools' ); ?>
				<input type="file" name="moon_import" accept="application/json,.json" required>
				<button type="submit" class="button"><?php esc_html_e( 'وارد کردن', 'moon-theme' ); ?></button>
			</form>
		</section>

		<section class="moon-box">
			<h2><?php esc_html_e( 'نسخه پشتیبان داخلی', 'moon-theme' ); ?></h2>
			<p><?php esc_html_e( 'قبل از تغییرات بزرگ، یک نسخه از تنظیمات را در دیتابیس ذخیره کنید تا در صورت نیاز بازگردانی شود.', 'moon-theme' ); ?></p>

			<?php if ( is_array( $moon_backup ) && ! empty( $moon_backup['time'] ) ) : ?>
				<p class="moon-field__desc">
					<?php echo esc_html( sprintf( __( 'آخرین نسخه: %s', 'moon-theme' ), date_i18n( 'Y/m/d H:i', (int) $moon_backup['time'] ) ) ); ?>
				</p>
			<?php endif; ?>

			<p>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'moon_tools', 'moon_action' => 'backup' ), admin_url( 'admin-post.php' ) ), 'moon_tools' ) ); ?>"><?php esc_html_e( 'ساخت نسخه پشتیبان', 'moon-theme' ); ?></a>

				<?php if ( is_array( $moon_backup ) && ! empty( $moon_backup['data'] ) ) : ?>
					<a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'moon_tools', 'moon_action' => 'restore' ), admin_url( 'admin-post.php' ) ), 'moon_tools' ) ); ?>" data-moon-confirm="<?php esc_attr_e( 'تنظیمات فعلی با نسخه پشتیبان جایگزین شود؟', 'moon-theme' ); ?>"><?php esc_html_e( 'بازگردانی نسخه پشتیبان', 'moon-theme' ); ?></a>
				<?php endif; ?>
			</p>
		</section>

		<section class="moon-box">
			<h2><?php esc_html_e( 'بازگردانی کامل', 'moon-theme' ); ?></h2>
			<p><?php esc_html_e( 'همه تنظیمات طراحی و فروشگاه به مقادیر پیش‌فرض قالب برمی‌گردد. محصولات، سفارش‌ها و مشتریان دست‌نخورده می‌مانند.', 'moon-theme' ); ?></p>
			<p>
				<a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'moon_tools', 'moon_action' => 'reset', 'moon_page_key' => 'all' ), admin_url( 'admin-post.php' ) ), 'moon_tools' ) ); ?>" data-moon-confirm="<?php esc_attr_e( 'همه تنظیمات به پیش‌فرض بازگردند؟', 'moon-theme' ); ?>"><?php esc_html_e( 'بازگردانی همه تنظیمات', 'moon-theme' ); ?></a>
			</p>
		</section>
	</div>

	<section class="moon-box">
		<h2><?php esc_html_e( 'گزارش وضعیت', 'moon-theme' ); ?></h2>
		<table class="widefat striped moon-table">
			<tbody>
				<?php foreach ( $moon_stats['status'] as $moon_status ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $moon_status['label'] ); ?></th>
						<td dir="auto"><?php echo esc_html( $moon_status['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'محصولات منتشرشده', 'moon-theme' ); ?></th>
					<td><?php echo esc_html( number_format_i18n( $moon_stats['products']['published'] ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'سفارش‌ها', 'moon-theme' ); ?></th>
					<td><?php echo esc_html( number_format_i18n( $moon_stats['orders']['total'] ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</section>
</div>
