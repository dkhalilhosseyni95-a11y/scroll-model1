<?php
/**
 * Homepage section builder.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_registry = function_exists( 'moon_homepage_registry' ) ? moon_homepage_registry() : array();
$moon_stored   = get_option( 'moon_sections', array() );
$moon_stored   = is_array( $moon_stored ) ? $moon_stored : array();
$moon_rows     = array();
$moon_seen     = array();

foreach ( $moon_stored as $row ) {
	$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';

	if ( ! $id || ! isset( $moon_registry[ $id ] ) || isset( $moon_seen[ $id ] ) ) {
		continue;
	}

	$moon_seen[ $id ] = true;
	$moon_rows[]      = array(
		'id'      => $id,
		'enabled' => empty( $row['enabled'] ) ? 0 : 1,
	);
}

foreach ( array_keys( $moon_registry ) as $id ) {
	if ( ! isset( $moon_seen[ $id ] ) ) {
		$moon_rows[] = array(
			'id'      => $id,
			'enabled' => 1,
		);
	}
}
?>
<div class="moon-builder-card">
	<h2><?php esc_html_e( 'بخش‌های صفحه اصلی', 'moon-core' ); ?></h2>
	<p class="moon-field__desc"><?php esc_html_e( 'هر بخش را می‌توانید فعال یا غیرفعال کنید و با دکمه‌های بالا/پایین ترتیب نمایش را تغییر دهید. محتوای هر بخش در تب‌های همین صفحه است.', 'moon-core' ); ?></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-builder-form">
		<input type="hidden" name="action" value="moon_save_sections">
		<?php wp_nonce_field( 'moon_save_sections', 'moon_sections_nonce' ); ?>

		<div class="moon-builder" data-moon-builder>
			<?php foreach ( $moon_rows as $index => $row ) : ?>
				<?php $definition = isset( $moon_registry[ $row['id'] ] ) ? $moon_registry[ $row['id'] ] : null; ?>
				<?php if ( ! $definition ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<div class="moon-builder__row" data-moon-row>
					<span class="moon-builder__grip" aria-hidden="true">☰</span>
					<input type="hidden" name="sections[<?php echo esc_attr( (string) $index ); ?>][id]" value="<?php echo esc_attr( $row['id'] ); ?>">
					<div class="moon-builder__text">
						<strong><?php echo esc_html( $definition['label'] ); ?></strong>
						<span><?php echo esc_html( $definition['desc'] ); ?></span>
					</div>
					<label class="moon-toggle moon-builder__toggle">
						<input type="checkbox" name="sections[<?php echo esc_attr( (string) $index ); ?>][enabled]" value="1" <?php checked( $row['enabled'] ); ?>>
						<span><?php esc_html_e( 'فعال', 'moon-core' ); ?></span>
					</label>
					<div class="moon-builder__moves">
						<button type="button" class="button" data-moon-move="up" aria-label="<?php esc_attr_e( 'انتقال به بالا', 'moon-core' ); ?>">↑</button>
						<button type="button" class="button" data-moon-move="down" aria-label="<?php esc_attr_e( 'انتقال به پایین', 'moon-core' ); ?>">↓</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="moon-builder__actions">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره ترتیب بخش‌ها', 'moon-core' ); ?></button>
			<span class="moon-field__desc"><?php esc_html_e( 'بخش‌های غیرفعال در سایت نمایش داده نمی‌شوند.', 'moon-core' ); ?></span>
		</p>
	</form>
</div>
