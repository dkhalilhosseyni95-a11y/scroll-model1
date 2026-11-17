<?php
/**
 * Footer builder: columns with their own type, plus the footer content settings.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_columns = Moon_Settings::footer_columns();
$moon_types   = Moon_Settings::footer_column_types();
$moon_menus   = wp_get_nav_menus();
?>
<div class="wrap moon-admin" dir="rtl">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'سازنده فوتر', 'moon-theme' ); ?>
	</h1>

	<?php Moon_Admin::notices(); ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-moon-footer-form>
		<input type="hidden" name="action" value="moon_save_footer">
		<?php wp_nonce_field( 'moon_save_footer' ); ?>

		<div class="moon-builder" data-moon-columns>
			<?php foreach ( $moon_columns as $moon_index => $moon_column ) : ?>
				<div class="moon-builder__row moon-builder__row--column" data-moon-column>
					<div class="moon-builder__head">
						<span class="moon-builder__grip" aria-hidden="true">☰</span>
						<strong class="moon-builder__title"><?php echo esc_html( $moon_column['title'] ? $moon_column['title'] : __( 'ستون بدون عنوان', 'moon-theme' ) ); ?></strong>
						<button type="button" class="button button-link-delete" data-moon-column-remove><?php esc_html_e( 'حذف ستون', 'moon-theme' ); ?></button>
					</div>

					<div class="moon-fields moon-fields--compact">
						<div class="moon-field moon-field--text">
							<label class="moon-field__label"><?php esc_html_e( 'عنوان ستون', 'moon-theme' ); ?></label>
							<div class="moon-field__control">
								<input type="text" name="columns[<?php echo esc_attr( (string) $moon_index ); ?>][title]" value="<?php echo esc_attr( $moon_column['title'] ); ?>" class="regular-text">
							</div>
						</div>

						<div class="moon-field moon-field--select">
							<label class="moon-field__label"><?php esc_html_e( 'نوع محتوا', 'moon-theme' ); ?></label>
							<div class="moon-field__control">
								<select name="columns[<?php echo esc_attr( (string) $moon_index ); ?>][type]" data-moon-column-type>
									<?php foreach ( $moon_types as $moon_type_key => $moon_type_label ) : ?>
										<option value="<?php echo esc_attr( $moon_type_key ); ?>" <?php selected( $moon_column['type'], $moon_type_key ); ?>><?php echo esc_html( $moon_type_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="moon-field moon-field--select" data-moon-column-menu <?php echo 'menu' === $moon_column['type'] ? '' : 'hidden'; ?>>
							<label class="moon-field__label"><?php esc_html_e( 'منو', 'moon-theme' ); ?></label>
							<div class="moon-field__control">
								<select name="columns[<?php echo esc_attr( (string) $moon_index ); ?>][menu_id]">
									<option value="0"><?php esc_html_e( '— انتخاب منو —', 'moon-theme' ); ?></option>
									<?php foreach ( $moon_menus as $moon_menu ) : ?>
										<option value="<?php echo esc_attr( (string) $moon_menu->term_id ); ?>" <?php selected( (int) ( isset( $moon_column['menu_id'] ) ? $moon_column['menu_id'] : 0 ), (int) $moon_menu->term_id ); ?>><?php echo esc_html( $moon_menu->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="moon-field moon-field--textarea" data-moon-column-content <?php echo in_array( $moon_column['type'], array( 'links', 'text' ), true ) ? '' : 'hidden'; ?>>
							<label class="moon-field__label"><?php esc_html_e( 'محتوا', 'moon-theme' ); ?></label>
							<div class="moon-field__control">
								<textarea name="columns[<?php echo esc_attr( (string) $moon_index ); ?>][content]" rows="4"><?php echo esc_textarea( $moon_column['content'] ); ?></textarea>
								<p class="moon-field__desc"><?php esc_html_e( 'در حالت «لینک‌های دستی» هر خط: عنوان|نشانی', 'moon-theme' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<template data-moon-column-template>
			<div class="moon-builder__row moon-builder__row--column" data-moon-column>
				<div class="moon-builder__head">
					<span class="moon-builder__grip" aria-hidden="true">☰</span>
					<strong class="moon-builder__title"><?php esc_html_e( 'ستون جدید', 'moon-theme' ); ?></strong>
					<button type="button" class="button button-link-delete" data-moon-column-remove><?php esc_html_e( 'حذف ستون', 'moon-theme' ); ?></button>
				</div>
				<div class="moon-fields moon-fields--compact">
					<div class="moon-field moon-field--text">
						<label class="moon-field__label"><?php esc_html_e( 'عنوان ستون', 'moon-theme' ); ?></label>
						<div class="moon-field__control">
							<input type="text" name="columns[__i__][title]" value="" class="regular-text">
						</div>
					</div>
					<div class="moon-field moon-field--select">
						<label class="moon-field__label"><?php esc_html_e( 'نوع محتوا', 'moon-theme' ); ?></label>
						<div class="moon-field__control">
							<select name="columns[__i__][type]" data-moon-column-type>
								<?php foreach ( $moon_types as $moon_type_key => $moon_type_label ) : ?>
									<option value="<?php echo esc_attr( $moon_type_key ); ?>"><?php echo esc_html( $moon_type_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="moon-field moon-field--select" data-moon-column-menu hidden>
						<label class="moon-field__label"><?php esc_html_e( 'منو', 'moon-theme' ); ?></label>
						<div class="moon-field__control">
							<select name="columns[__i__][menu_id]">
								<option value="0"><?php esc_html_e( '— انتخاب منو —', 'moon-theme' ); ?></option>
								<?php foreach ( $moon_menus as $moon_menu ) : ?>
									<option value="<?php echo esc_attr( (string) $moon_menu->term_id ); ?>"><?php echo esc_html( $moon_menu->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="moon-field moon-field--textarea" data-moon-column-content>
						<label class="moon-field__label"><?php esc_html_e( 'محتوا', 'moon-theme' ); ?></label>
						<div class="moon-field__control">
							<textarea name="columns[__i__][content]" rows="4"></textarea>
						</div>
					</div>
				</div>
			</div>
		</template>

		<p class="moon-builder__add">
			<button type="button" class="button" data-moon-column-add><?php esc_html_e( 'افزودن ستون', 'moon-theme' ); ?></button>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره فوتر', 'moon-theme' ); ?></button>
			<button type="submit" class="button button-link-delete" name="footer_reset" value="1" data-moon-confirm="<?php esc_attr_e( 'ستون‌ها به حالت پیش‌فرض بازگردند؟', 'moon-theme' ); ?>"><?php esc_html_e( 'بازگردانی پیش‌فرض', 'moon-theme' ); ?></button>
		</p>
	</form>
</div>

<?php Moon_Admin::render_settings_page( 'footer' ); ?>
