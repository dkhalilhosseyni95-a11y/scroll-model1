<?php
/**
 * Homepage builder: order, enable/disable, add, duplicate, delete, per-section
 * settings, draft preview and publish.
 *
 * Rows live in a draft option until "انتشار" is pressed, so a half-finished
 * layout is never shown to shoppers. Administrators can preview the draft with
 * `?moon_sections_preview=1`.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

$moon_registry = Moon_Sections::registry();
$moon_rows     = Moon_Sections::draft_rows();
$moon_draft    = Moon_Sections::has_draft();

/**
 * Render one option control for a builder row.
 *
 * @param array  $field Field definition.
 * @param mixed  $value Current value.
 * @param string $name  Input name.
 */
$moon_option_field = function ( $field, $value, $name ) {
	$id   = 'moon_opt_' . md5( $name );
	$form = ' form="moon-builder-form"';
	?>
	<div class="moon-field moon-field--<?php echo esc_attr( $field['type'] ); ?>">
		<label class="moon-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
		<div class="moon-field__control">
			<?php if ( 'textarea' === $field['type'] ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
			<?php elseif ( 'code' === $field['type'] ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="6" class="code moon-ltr" dir="ltr"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
			<?php elseif ( 'checkbox' === $field['type'] ) : ?>
				<label class="moon-toggle">
					<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php checked( ! empty( $value ) ); ?>>
					<span><?php esc_html_e( 'فعال', 'moon-theme' ); ?></span>
				</label>
			<?php elseif ( 'select' === $field['type'] ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php foreach ( (array) $field['options'] as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ( 'color' === $field['type'] ) : ?>
				<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="moon-color" dir="ltr"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php elseif ( 'media' === $field['type'] ) : ?>
				<div class="moon-media" data-moon-media>
					<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) (int) $value ); ?>" data-moon-media-input<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<div class="moon-media__preview">
						<?php if ( (int) $value ) : ?>
							<?php echo wp_get_attachment_image( (int) $value, 'medium' ); ?>
						<?php endif; ?>
					</div>
					<button type="button" class="button" data-moon-media-select><?php esc_html_e( 'انتخاب تصویر', 'moon-theme' ); ?></button>
					<button type="button" class="button-link" data-moon-media-remove <?php echo (int) $value ? '' : 'hidden'; ?>><?php esc_html_e( 'حذف', 'moon-theme' ); ?></button>
				</div>
			<?php elseif ( 'number' === $field['type'] ) : ?>
				<input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="small-text" min="<?php echo esc_attr( (string) ( isset( $field['min'] ) ? $field['min'] : 0 ) ); ?>" max="<?php echo esc_attr( (string) ( isset( $field['max'] ) ? $field['max'] : 999999 ) ); ?>"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php else : ?>
				<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text"<?php echo ! empty( $field['ltr'] ) ? ' dir="ltr"' : ''; ?><?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php endif; ?>

			<?php if ( ! empty( $field['desc'] ) ) : ?>
				<p class="moon-field__desc"><?php echo esc_html( $field['desc'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
};
?>
<div class="wrap moon-admin" dir="rtl">
	<h1 class="moon-admin__title">
		<span class="moon-admin__mark">MOON</span>
		<?php esc_html_e( 'صفحه‌ساز صفحه اصلی', 'moon-theme' ); ?>
		<?php if ( $moon_draft ) : ?>
			<span class="moon-badge moon-badge--warn"><?php esc_html_e( 'پیش‌نویس منتشرنشده', 'moon-theme' ); ?></span>
		<?php endif; ?>
	</h1>

	<?php Moon_Admin::notices(); ?>

	<form id="moon-builder-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="moon_builder">
		<input type="hidden" name="builder_action" value="save">
		<?php wp_nonce_field( 'moon_builder' ); ?>
	</form>

	<div class="moon-builder-toolbar">
		<button type="submit" form="moon-builder-form" class="button button-primary"><?php esc_html_e( 'ذخیره پیش‌نویس', 'moon-theme' ); ?></button>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-inline-form">
			<input type="hidden" name="action" value="moon_builder">
			<input type="hidden" name="builder_action" value="publish">
			<?php wp_nonce_field( 'moon_builder' ); ?>
			<button type="submit" class="button button-hero"><?php esc_html_e( 'انتشار تغییرات', 'moon-theme' ); ?></button>
		</form>

		<a class="button" href="<?php echo esc_url( add_query_arg( 'moon_sections_preview', '1', home_url( '/' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'پیش‌نمایش پیش‌نویس', 'moon-theme' ); ?></a>

		<?php if ( $moon_draft ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-inline-form">
				<input type="hidden" name="action" value="moon_builder">
				<input type="hidden" name="builder_action" value="discard">
				<?php wp_nonce_field( 'moon_builder' ); ?>
				<button type="submit" class="button" data-moon-confirm="<?php esc_attr_e( 'پیش‌نویس کنار گذاشته شود؟', 'moon-theme' ); ?>"><?php esc_html_e( 'بازگردانی تغییرات', 'moon-theme' ); ?></button>
			</form>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-inline-form">
			<input type="hidden" name="action" value="moon_builder">
			<input type="hidden" name="builder_action" value="reset">
			<?php wp_nonce_field( 'moon_builder' ); ?>
			<button type="submit" class="button button-link-delete" data-moon-confirm="<?php esc_attr_e( 'صفحه اصلی به چیدمان پیش‌فرض بازگردد؟', 'moon-theme' ); ?>"><?php esc_html_e( 'بازگردانی پیش‌فرض', 'moon-theme' ); ?></button>
		</form>
	</div>

	<div class="moon-builder" data-moon-builder>
		<?php foreach ( $moon_rows as $moon_index => $moon_row ) : ?>
			<?php
			$moon_type = $moon_row['type'];
			$moon_def  = isset( $moon_registry[ $moon_type ] ) ? $moon_registry[ $moon_type ] : null;

			if ( ! $moon_def ) {
				continue;
			}

			$moon_prefix = 'rows[' . $moon_index . ']';
			?>
			<div class="moon-builder__row" data-moon-row data-type="<?php echo esc_attr( $moon_type ); ?>">
				<div class="moon-builder__head">
					<span class="moon-builder__grip" aria-hidden="true">☰</span>

					<input type="hidden" name="<?php echo esc_attr( $moon_prefix ); ?>[id]" value="<?php echo esc_attr( $moon_row['id'] ); ?>" form="moon-builder-form">

					<div class="moon-builder__text">
						<strong><?php echo esc_html( $moon_def['label'] ); ?></strong>
						<span><?php echo esc_html( $moon_def['desc'] ); ?></span>
					</div>

					<label class="moon-toggle moon-builder__toggle">
						<input type="checkbox" name="<?php echo esc_attr( $moon_prefix ); ?>[enabled]" value="1" form="moon-builder-form" <?php checked( ! empty( $moon_row['enabled'] ) ); ?>>
						<span><?php esc_html_e( 'نمایش', 'moon-theme' ); ?></span>
					</label>

					<button type="button" class="button" data-moon-toggle-settings aria-expanded="false"><?php esc_html_e( 'تنظیمات', 'moon-theme' ); ?></button>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-inline-form">
						<input type="hidden" name="action" value="moon_builder">
						<input type="hidden" name="builder_action" value="duplicate">
						<input type="hidden" name="section_id" value="<?php echo esc_attr( $moon_row['id'] ); ?>">
						<?php wp_nonce_field( 'moon_builder' ); ?>
						<button type="submit" class="button" aria-label="<?php esc_attr_e( 'کپی بخش', 'moon-theme' ); ?>">⧉</button>
					</form>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-inline-form">
						<input type="hidden" name="action" value="moon_builder">
						<input type="hidden" name="builder_action" value="delete">
						<input type="hidden" name="section_id" value="<?php echo esc_attr( $moon_row['id'] ); ?>">
						<?php wp_nonce_field( 'moon_builder' ); ?>
						<button type="submit" class="button button-link-delete" data-moon-confirm="<?php esc_attr_e( 'این بخش حذف شود؟', 'moon-theme' ); ?>" aria-label="<?php esc_attr_e( 'حذف بخش', 'moon-theme' ); ?>">✕</button>
					</form>
				</div>

				<div class="moon-builder__settings" data-moon-row-settings hidden>
					<?php if ( empty( $moon_def['fields'] ) ) : ?>
						<p class="moon-field__desc"><?php esc_html_e( 'محتوای این بخش در تب‌های «صفحه اصلی» تنظیم می‌شود؛ اینجا فقط چیدمان آن را تغییر می‌دهید.', 'moon-theme' ); ?></p>
					<?php endif; ?>

					<div class="moon-fields moon-fields--compact">
						<?php foreach ( $moon_def['fields'] as $moon_field_def ) : ?>
							<?php
							$moon_key   = $moon_field_def['key'];
							$moon_value = isset( $moon_row['options'][ $moon_key ] ) ? $moon_row['options'][ $moon_key ] : ( isset( $moon_field_def['default'] ) ? $moon_field_def['default'] : '' );
							$moon_option_field( $moon_field_def, $moon_value, $moon_prefix . '[options][' . $moon_key . ']' );
							?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="moon-builder__add">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="moon-inline-form">
			<input type="hidden" name="action" value="moon_builder">
			<input type="hidden" name="builder_action" value="add">
			<?php wp_nonce_field( 'moon_builder' ); ?>

			<label class="screen-reader-text" for="moon-section-type"><?php esc_html_e( 'نوع بخش جدید', 'moon-theme' ); ?></label>
			<select id="moon-section-type" name="section_type">
				<?php foreach ( $moon_registry as $moon_key => $moon_definition ) : ?>
					<option value="<?php echo esc_attr( $moon_key ); ?>"><?php echo esc_html( $moon_definition['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'افزودن بخش', 'moon-theme' ); ?></button>
		</form>

		<p class="moon-field__desc">
			<?php esc_html_e( 'بخش‌های فعلی طرح MOON (هیرو، رگال، استودیو، داستان اسکرولی…) محتوای خود را از تب‌های «صفحه اصلی» می‌خوانند و مانند قبل کار می‌کنند. تغییرات تا زمان انتشار فقط در پیش‌نمایش دیده می‌شود.', 'moon-theme' ); ?>
		</p>
	</div>
</div>
