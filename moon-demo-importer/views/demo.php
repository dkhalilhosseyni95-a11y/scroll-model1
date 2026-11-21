<?php
/**
 * Demo importer screen: demo card, step selection, progress, report, rollback.
 *
 * @package Moon_Demo_Importer
 */

defined( 'ABSPATH' ) || exit;

$moon_labels  = Moon_Demo_Importer::step_labels();
$moon_options = (array) get_option( 'moon_options', array() );
$moon_history = Moon_Demo_Importer::history();
$moon_report  = get_option( 'moon_demo_last_report', array() );

$moon_counts = array(
	'posts'    => (int) wp_count_posts( 'post' )->publish,
	'pages'    => (int) wp_count_posts( 'page' )->publish,
	'products' => post_type_exists( 'product' ) ? (int) wp_count_posts( 'product' )->publish : 0,
);

$moon_has_content = ( $moon_counts['products'] > 0 ) || ( $moon_counts['posts'] > 1 );
?>
<div class="wrap moon-admin" dir="rtl">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'درون‌ریز محتوای دمو', 'moon-theme' ); ?>
	</h1>

	<div class="moon-demo">
		<div class="moon-demo__card">
			<div class="moon-demo__thumb" aria-hidden="true">
				<?php if ( defined( 'MOON_THEME_URI' ) ) : ?>
					<img src="<?php echo esc_url( MOON_THEME_URI . '/assets/images/hero.jpg' ); ?>" alt="" loading="lazy">
				<?php endif; ?>
			</div>
			<div class="moon-demo__body">
				<h2><?php esc_html_e( 'MOON Fashion Demo', 'moon-theme' ); ?></h2>
				<p>
					<?php esc_html_e( 'یک فروشگاه پوشاک کامل: صفحه‌های پایه، دسته‌بندی‌ها، محصولات ساده و متغیر، رگال و استودیو، تصاویر، منو و تنظیمات صفحه اصلی.', 'moon-theme' ); ?>
				</p>
				<ul class="moon-demo__facts">
					<li><?php echo esc_html( sprintf( __( 'صفحه‌ها: %s', 'moon-theme' ), number_format_i18n( $moon_counts['pages'] ) ) ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'محصولات: %s', 'moon-theme' ), number_format_i18n( $moon_counts['products'] ) ) ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'نوشته‌ها: %s', 'moon-theme' ), number_format_i18n( $moon_counts['posts'] ) ) ); ?></li>
				</ul>
			</div>
		</div>

		<?php if ( $moon_has_content ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<?php esc_html_e( 'این سایت از قبل محتوا دارد. مواردی که با همان نشانی وجود داشته باشند نادیده گرفته می‌شوند و هیچ محتوایی حذف یا بازنویسی نمی‌شود. با این حال، گزینه‌های مربوط به تنظیمات را فقط اگر لازم است انتخاب کنید.', 'moon-theme' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<form id="moon-demo-form" class="moon-demo__form">
			<h2><?php esc_html_e( 'چه بخش‌هایی نصب شود؟', 'moon-theme' ); ?></h2>

			<div class="moon-demo__steps">
				<?php foreach ( $moon_labels as $moon_step => $moon_label ) : ?>
					<label class="moon-check">
						<input type="checkbox" name="steps[]" value="<?php echo esc_attr( $moon_step ); ?>" <?php checked( 'products' !== $moon_step && 'store' !== $moon_step, true ); ?>>
						<span><?php echo esc_html( $moon_label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>

			<p class="moon-field__desc">
				<?php esc_html_e( 'پیش از شروع، یک نسخه پشتیبان از تنظیمات فعلی ذخیره می‌شود تا در صورت نیاز بتوانید همه‌چیز را بازگردانید.', 'moon-theme' ); ?>
			</p>

			<p>
				<button type="button" class="button button-primary button-hero" data-moon-demo-start><?php esc_html_e( 'شروع نصب', 'moon-theme' ); ?></button>
				<span class="moon-demo__status" data-moon-demo-status role="status" aria-live="polite"></span>
			</p>
		</form>

		<div class="moon-demo__progress" data-moon-demo-progress hidden>
			<h2><?php esc_html_e( 'گزارش پیشرفت', 'moon-theme' ); ?></h2>
			<ol class="moon-demo__log" data-moon-demo-log></ol>
		</div>

		<?php if ( is_array( $moon_history ) ) : ?>
			<section class="moon-demo__history">
				<h2><?php esc_html_e( 'تاریخچه نصب', 'moon-theme' ); ?></h2>
				<p>
					<?php
					echo esc_html( sprintf(
						/* translators: 1: date, 2: number of created posts, 3: status. */
						__( 'آخرین اجرا: %1$s — %2$s مورد ایجاد شد — وضعیت: %3$s', 'moon-theme' ),
						date_i18n( 'Y/m/d H:i', (int) $moon_history['time'] ),
						number_format_i18n( count( (array) $moon_history['created']['posts'] ) ),
						! empty( $moon_history['complete'] ) ? __( 'کامل', 'moon-theme' ) : __( 'ناقص', 'moon-theme' )
					) );
					?>
				</p>

				<button type="button" class="button button-link-delete" data-moon-demo-rollback data-moon-confirm="<?php esc_attr_e( 'محتوای ایجادشده حذف و تنظیمات پیش از نصب بازگردد؟', 'moon-theme' ); ?>">
					<?php esc_html_e( 'بازگردانی آخرین نصب', 'moon-theme' ); ?>
				</button>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $moon_report['messages'] ) || ! empty( $moon_report['errors'] ) ) : ?>
			<section class="moon-demo__report">
				<h2><?php esc_html_e( 'گزارش آخرین اجرا', 'moon-theme' ); ?></h2>

				<?php if ( ! empty( $moon_report['errors'] ) ) : ?>
					<ul class="moon-demo__list is-bad">
						<?php foreach ( $moon_report['errors'] as $moon_error ) : ?>
							<li><?php echo esc_html( $moon_error ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( ! empty( $moon_report['messages'] ) ) : ?>
					<ul class="moon-demo__list">
						<?php foreach ( $moon_report['messages'] as $moon_message ) : ?>
							<li><?php echo esc_html( $moon_message ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</div>
