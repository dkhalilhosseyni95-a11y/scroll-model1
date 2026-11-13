<?php
/**
 * Panel dashboard.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_state       = moon_core_import_state();
$moon_counts      = wp_count_posts( 'moon_rack' );
$moon_outfits     = wp_count_posts( 'moon_outfit' );
$moon_products    = post_type_exists( 'product' ) ? wp_count_posts( 'product' ) : null;
$moon_subscribers = get_option( 'moon_subscribers', array() );
$moon_subscribers = is_array( $moon_subscribers ) ? count( $moon_subscribers ) : 0;
$moon_sections    = get_option( 'moon_sections', array() );
$moon_enabled     = 0;

if ( is_array( $moon_sections ) ) {
	foreach ( $moon_sections as $row ) {
		if ( ! empty( $row['enabled'] ) ) {
			$moon_enabled++;
		}
	}
}
?>
<div class="wrap moon-admin moon-dashboard">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'داشبورد', 'moon-core' ); ?>
	</h1>

	<?php \Moon_Admin::notices(); ?>

	<div class="moon-cards">
		<div class="moon-card-stat">
			<span class="moon-card-stat__label"><?php esc_html_e( 'بخش‌های فعال صفحه اصلی', 'moon-core' ); ?></span>
			<strong class="moon-card-stat__value"><?php echo esc_html( moon_fa_digits( (string) $moon_enabled ) ); ?></strong>
			<a href="<?php echo esc_url( moon_core_admin_url( 'moon-homepage' ) ); ?>"><?php esc_html_e( 'مدیریت صفحه اصلی', 'moon-core' ); ?></a>
		</div>
		<div class="moon-card-stat">
			<span class="moon-card-stat__label"><?php esc_html_e( 'اقلام رگال', 'moon-core' ); ?></span>
			<strong class="moon-card-stat__value"><?php echo esc_html( moon_fa_digits( (string) ( isset( $moon_counts->publish ) ? $moon_counts->publish : 0 ) ) ); ?></strong>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=moon_rack' ) ); ?>"><?php esc_html_e( 'مدیریت رگال', 'moon-core' ); ?></a>
		</div>
		<div class="moon-card-stat">
			<span class="moon-card-stat__label"><?php esc_html_e( 'استایل‌های استودیو', 'moon-core' ); ?></span>
			<strong class="moon-card-stat__value"><?php echo esc_html( moon_fa_digits( (string) ( isset( $moon_outfits->publish ) ? $moon_outfits->publish : 0 ) ) ); ?></strong>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=moon_outfit' ) ); ?>"><?php esc_html_e( 'مدیریت استودیو', 'moon-core' ); ?></a>
		</div>
		<div class="moon-card-stat">
			<span class="moon-card-stat__label"><?php esc_html_e( 'اعضای خبرنامه', 'moon-core' ); ?></span>
			<strong class="moon-card-stat__value"><?php echo esc_html( moon_fa_digits( (string) $moon_subscribers ) ); ?></strong>
			<a href="<?php echo esc_url( moon_core_admin_url( 'moon-subscribers' ) ); ?>"><?php esc_html_e( 'مشاهده فهرست', 'moon-core' ); ?></a>
		</div>
	</div>

	<div class="moon-panels">
		<section class="moon-panel">
			<h2><?php esc_html_e( 'وضعیت سیستم', 'moon-core' ); ?></h2>
			<ul class="moon-list">
				<li>
					<span><?php esc_html_e( 'قالب MOON', 'moon-core' ); ?></span>
					<strong class="<?php echo moon_core_theme_active() ? 'is-ok' : 'is-bad'; ?>"><?php echo moon_core_theme_active() ? esc_html__( 'فعال', 'moon-core' ) : esc_html__( 'غیرفعال', 'moon-core' ); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e( 'ووکامرس', 'moon-core' ); ?></span>
					<strong class="<?php echo class_exists( 'WooCommerce' ) ? 'is-ok' : 'is-bad'; ?>">
						<?php
						if ( class_exists( 'WooCommerce' ) ) {
							printf( esc_html__( 'فعال — %s محصول', 'moon-core' ), esc_html( moon_fa_digits( (string) ( $moon_products ? $moon_products->publish : 0 ) ) ) );
						} else {
							esc_html_e( 'غیرفعال', 'moon-core' );
						}
						?>
					</strong>
				</li>
				<li>
					<span><?php esc_html_e( 'صفحه اصلی', 'moon-core' ); ?></span>
					<strong><?php echo get_permalink( (int) get_option( 'page_on_front' ) ) ? '<a href="' . esc_url( get_permalink( (int) get_option( 'page_on_front' ) ) ) . '">' . esc_html__( 'نمایش', 'moon-core' ) . '</a>' : esc_html__( 'تنظیم نشده', 'moon-core' ); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e( 'آخرین نصب محتوا', 'moon-core' ); ?></span>
					<strong><?php echo ! empty( $moon_state['time'] ) ? esc_html( $moon_state['time'] ) : esc_html__( 'هنوز انجام نشده', 'moon-core' ); ?></strong>
				</li>
			</ul>
		</section>

		<section class="moon-panel">
			<h2><?php esc_html_e( 'دسترسی سریع', 'moon-core' ); ?></h2>
			<div class="moon-quick">
				<a class="button" href="<?php echo esc_url( moon_core_admin_url( 'moon-homepage' ) ); ?>"><?php esc_html_e( 'بخش‌های صفحه اصلی', 'moon-core' ); ?></a>
				<a class="button" href="<?php echo esc_url( moon_core_admin_url( 'moon-brand' ) ); ?>"><?php esc_html_e( 'رنگ و فونت برند', 'moon-core' ); ?></a>
				<a class="button" href="<?php echo esc_url( moon_core_admin_url( 'moon-chrome' ) ); ?>"><?php esc_html_e( 'هدر و فوتر', 'moon-core' ); ?></a>
				<a class="button" href="<?php echo esc_url( moon_core_admin_url( 'moon-demo' ) ); ?>"><?php esc_html_e( 'نصب محتوای اولیه', 'moon-core' ); ?></a>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'مشاهده سایت', 'moon-core' ); ?></a>
			</div>
			<p class="moon-field__desc"><?php esc_html_e( 'قالب MOON بدون وابستگی به صفحه‌سازها کار می‌کند؛ همه تنظیمات از همین پنل اعمال می‌شوند.', 'moon-core' ); ?></p>
		</section>
	</div>
</div>
