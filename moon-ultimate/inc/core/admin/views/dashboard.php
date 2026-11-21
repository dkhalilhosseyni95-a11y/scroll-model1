<?php
/**
 * Panel dashboard: real store numbers, chart, status and shortcuts.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_stats = Moon_Stats::all();
$moon_shop  = admin_url( 'edit.php?post_type=product' );
?>
<div class="wrap moon-admin moon-dash" dir="rtl">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'داشبورد', 'moon-theme' ); ?>
		<span class="moon-dash__version">v<?php echo esc_html( MOON_THEME_VERSION ); ?></span>
	</h1>

	<?php Moon_Admin::notices(); ?>

	<?php if ( ! empty( $moon_stats['warnings'] ) ) : ?>
		<div class="moon-dash__warnings">
			<h2><?php esc_html_e( 'نیاز به بررسی', 'moon-theme' ); ?></h2>
			<ul>
				<?php foreach ( $moon_stats['warnings'] as $moon_warning ) : ?>
					<li><?php echo esc_html( $moon_warning ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="moon-cards">
		<?php
		$moon_cards = array(
			array(
				'label' => __( 'فروش کل', 'moon-theme' ),
				'value' => Moon_Stats::money( $moon_stats['sales']['total'] ),
				'note'  => __( 'همه سفارش‌های پرداخت‌شده', 'moon-theme' ),
				'icon'  => 'star',
			),
			array(
				'label' => __( 'فروش امروز', 'moon-theme' ),
				'value' => Moon_Stats::money( $moon_stats['sales']['today'] ),
				'note'  => gmdate( 'Y/m/d' ),
				'icon'  => 'clock',
			),
			array(
				'label' => __( 'فروش این ماه', 'moon-theme' ),
				'value' => Moon_Stats::money( $moon_stats['sales']['month'] ),
				'note'  => date_i18n( 'F Y' ),
				'icon'  => 'gem',
			),
			array(
				'label' => __( 'سفارش‌ها', 'moon-theme' ),
				'value' => number_format_i18n( $moon_stats['orders']['total'] ),
				'note'  => sprintf( __( '%s در انتظار پردازش', 'moon-theme' ), number_format_i18n( $moon_stats['orders']['processing'] ) ),
				'icon'  => 'shopping-bag',
			),
			array(
				'label' => __( 'محصولات', 'moon-theme' ),
				'value' => number_format_i18n( $moon_stats['products']['published'] ),
				'note'  => sprintf( __( '%s تخفیف‌دار', 'moon-theme' ), number_format_i18n( $moon_stats['products']['on_sale'] ) ),
				'icon'  => 'menu',
			),
			array(
				'label' => __( 'ناموجود', 'moon-theme' ),
				'value' => number_format_i18n( $moon_stats['products']['out'] ),
				'note'  => __( 'محصولات بدون موجودی', 'moon-theme' ),
				'icon'  => 'x',
			),
			array(
				'label' => __( 'سفارش‌های در انتظار', 'moon-theme' ),
				'value' => number_format_i18n( $moon_stats['orders']['pending'] ),
				'note'  => __( 'پرداخت‌نشده', 'moon-theme' ),
				'icon'  => 'clock',
			),
			array(
				'label' => __( 'مشتریان', 'moon-theme' ),
				'value' => number_format_i18n( $moon_stats['customers']['total'] ),
				'note'  => isset( $moon_stats['customers']['by_role']['customer'] ) ? sprintf( __( '%s مشتری', 'moon-theme' ), number_format_i18n( $moon_stats['customers']['by_role']['customer'] ) ) : '',
				'icon'  => 'user',
			),
		);

		foreach ( $moon_cards as $moon_card ) :
			?>
			<div class="moon-card-stat">
				<span class="moon-card-stat__icon"><?php moon_the_icon( $moon_card['icon'], 18 ); ?></span>
				<span class="moon-card-stat__label"><?php echo esc_html( $moon_card['label'] ); ?></span>
				<strong class="moon-card-stat__value"><?php echo esc_html( $moon_card['value'] ); ?></strong>
				<?php if ( $moon_card['note'] ) : ?><span class="moon-card-stat__note"><?php echo esc_html( $moon_card['note'] ); ?></span><?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="moon-dash__grid">
		<section class="moon-box moon-box--chart">
			<h2><?php esc_html_e( 'فروش ۱۴ روز گذشته', 'moon-theme' ); ?></h2>
			<div class="moon-chart" role="img" aria-label="<?php esc_attr_e( 'نمودار فروش دو هفته اخیر', 'moon-theme' ); ?>">
				<?php foreach ( $moon_stats['chart']['days'] as $moon_day => $moon_total ) : ?>
					<?php $moon_height = max( 2, ( $moon_total / $moon_stats['chart']['max'] ) * 100 ); ?>
					<span class="moon-chart__bar" style="height:<?php echo esc_attr( (string) round( $moon_height, 1 ) ); ?>%" title="<?php echo esc_attr( $moon_day . ' — ' . Moon_Stats::money( $moon_total ) ); ?>">
						<span class="moon-chart__tip"><?php echo esc_html( Moon_Stats::money( $moon_total ) ); ?></span>
					</span>
				<?php endforeach; ?>
			</div>
			<div class="moon-chart__axis">
				<span><?php echo esc_html( gmdate( 'm/d', strtotime( '-13 days' ) ) ); ?></span>
				<span><?php esc_html_e( 'امروز', 'moon-theme' ); ?></span>
			</div>
		</section>

		<section class="moon-box">
			<h2><?php esc_html_e( 'محصولات پرفروش', 'moon-theme' ); ?></h2>
			<?php if ( $moon_stats['top'] ) : ?>
				<ul class="moon-list">
					<?php foreach ( $moon_stats['top'] as $moon_product ) : ?>
						<li>
							<span class="moon-list__thumb"><?php echo $moon_product['thumb']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="moon-list__main">
								<a href="<?php echo esc_url( (string) $moon_product['url'] ); ?>"><?php echo esc_html( $moon_product['name'] ); ?></a>
								<small><?php echo wp_kses_post( $moon_product['price'] ); ?></small>
							</span>
							<span class="moon-list__meta"><?php echo esc_html( sprintf( __( '%s فروش', 'moon-theme' ), number_format_i18n( $moon_product['sales'] ) ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="moon-empty"><?php esc_html_e( 'پس از ثبت اولین سفارش، پرفروش‌ها اینجا نمایش داده می‌شوند.', 'moon-theme' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="moon-box">
			<h2><?php esc_html_e( 'آخرین سفارش‌ها', 'moon-theme' ); ?></h2>
			<?php if ( $moon_stats['latest'] ) : ?>
				<table class="widefat striped moon-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'سفارش', 'moon-theme' ); ?></th>
							<th><?php esc_html_e( 'مشتری', 'moon-theme' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'moon-theme' ); ?></th>
							<th><?php esc_html_e( 'مبلغ', 'moon-theme' ); ?></th>
							<th><?php esc_html_e( 'تاریخ', 'moon-theme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $moon_stats['latest'] as $moon_order ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $moon_order['url'] ); ?>">#<?php echo esc_html( $moon_order['number'] ); ?></a></td>
								<td><?php echo esc_html( $moon_order['name'] ? $moon_order['name'] : '—' ); ?></td>
								<td><?php echo esc_html( $moon_order['status'] ); ?></td>
								<td><?php echo wp_kses_post( $moon_order['total'] ); ?></td>
								<td><?php echo esc_html( $moon_order['date'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="moon-empty"><?php esc_html_e( 'هنوز سفارشی ثبت نشده است.', 'moon-theme' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="moon-box">
			<h2><?php esc_html_e( 'وضعیت سیستم', 'moon-theme' ); ?></h2>
			<ul class="moon-status">
				<?php foreach ( $moon_stats['status'] as $moon_status ) : ?>
					<li>
						<span class="moon-status__dot<?php echo $moon_status['ok'] ? ' is-ok' : ' is-warn'; ?>" aria-hidden="true"></span>
						<span class="moon-status__label"><?php echo esc_html( $moon_status['label'] ); ?></span>
						<span class="moon-status__value" dir="auto"><?php echo esc_html( $moon_status['value'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	</div>

	<section class="moon-box moon-box--links">
		<h2><?php esc_html_e( 'دسترسی سریع', 'moon-theme' ); ?></h2>
		<div class="moon-shortcuts">
			<a class="moon-shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=moon-homepage' ) ); ?>"><?php moon_the_icon( 'home', 18 ); ?><span><?php esc_html_e( 'صفحه‌ساز', 'moon-theme' ); ?></span></a>
			<a class="moon-shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=moon-design' ) ); ?>"><?php moon_the_icon( 'gem', 18 ); ?><span><?php esc_html_e( 'رنگ و فونت', 'moon-theme' ); ?></span></a>
			<a class="moon-shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=moon-header' ) ); ?>"><?php moon_the_icon( 'menu', 18 ); ?><span><?php esc_html_e( 'هدر و مگامنو', 'moon-theme' ); ?></span></a>
			<a class="moon-shortcut" href="<?php echo esc_url( $moon_shop ); ?>"><?php moon_the_icon( 'shopping-bag', 18 ); ?><span><?php esc_html_e( 'محصولات', 'moon-theme' ); ?></span></a>
			<a class="moon-shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders' ) ); ?>"><?php moon_the_icon( 'truck', 18 ); ?><span><?php esc_html_e( 'سفارش‌ها', 'moon-theme' ); ?></span></a>
			<a class="moon-shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=moon-tools' ) ); ?>"><?php moon_the_icon( 'shield-check', 18 ); ?><span><?php esc_html_e( 'پشتیبان‌گیری', 'moon-theme' ); ?></span></a>
			<?php if ( class_exists( 'Moon_Demo_Importer' ) ) : ?>
				<a class="moon-shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=moon-demo' ) ); ?>"><?php moon_the_icon( 'rotate-ccw', 18 ); ?><span><?php esc_html_e( 'درون‌ریز دمو', 'moon-theme' ); ?></span></a>
			<?php endif; ?>
			<a class="moon-shortcut" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php moon_the_icon( 'eye', 18 ); ?><span><?php esc_html_e( 'مشاهده سایت', 'moon-theme' ); ?></span></a>
		</div>
	</section>
</div>
