<?php
/**
 * Newsletter subscribers.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_subscribers = get_option( 'moon_subscribers', array() );
$moon_subscribers = is_array( $moon_subscribers ) ? $moon_subscribers : array();
?>
<div class="wrap moon-admin">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'خبرنامه', 'moon-core' ); ?>
	</h1>

	<?php \Moon_Admin::notices(); ?>

	<section class="moon-panel">
		<h2>
			<?php
			printf(
				esc_html__( '%s عضو', 'moon-core' ),
				esc_html( moon_fa_digits( (string) count( $moon_subscribers ) ) )
			);
			?>
		</h2>

		<?php if ( empty( $moon_subscribers ) ) : ?>
			<p class="moon-field__desc"><?php esc_html_e( 'هنوز کسی در خبرنامه ثبت‌نام نکرده است. فرم عضویت در فوتر سایت قرار دارد.', 'moon-core' ); ?></p>
		<?php else : ?>
			<table class="widefat striped moon-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ایمیل', 'moon-core' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'moon-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_reverse( $moon_subscribers ) as $moon_subscriber ) : ?>
						<tr>
							<td dir="ltr"><?php echo esc_html( isset( $moon_subscriber['email'] ) ? $moon_subscriber['email'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $moon_subscriber['date'] ) ? $moon_subscriber['date'] : '' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</section>
</div>
