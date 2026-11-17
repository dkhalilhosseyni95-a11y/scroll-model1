<?php
/**
 * Admin panel: menu, pages, assets, save handlers and tools.
 *
 * The panel is schema driven: every page below renders the tabs and fields that
 * `Moon_Settings::schema()` declares, so a new option needs no new markup. The
 * three builder screens (homepage, footer, elements) have dedicated views.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * The MOON admin panel.
 */
class Moon_Admin {

	/**
	 * Capability required for every panel action.
	 */
	const CAP = 'manage_options';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_moon_tools', array( __CLASS__, 'handle_tools' ) );
	}

	/**
	 * Panel pages: slug => [label, view].
	 *
	 * @return array
	 */
	public static function pages() {
		$pages = array(
			'moon-theme'       => array( __( 'داشبورد', 'moon-theme' ), 'dashboard' ),
			'moon-homepage'    => array( __( 'صفحه اصلی', 'moon-theme' ), 'homepage' ),
			'moon-design'      => array( __( 'ظاهر و طراحی', 'moon-theme' ), 'design' ),
			'moon-header'      => array( __( 'هدر', 'moon-theme' ), 'header' ),
			'moon-footer'      => array( __( 'فوتر', 'moon-theme' ), 'footer' ),
			'moon-shop'        => array( __( 'فروشگاه', 'moon-theme' ), 'shop' ),
			'moon-account-panel' => array( __( 'حساب کاربری', 'moon-theme' ), 'account' ),
			'moon-mobile'      => array( __( 'موبایل', 'moon-theme' ), 'mobile' ),
			'moon-performance' => array( __( 'عملکرد', 'moon-theme' ), 'performance' ),
			'moon-seo'         => array( __( 'سئو و کد سفارشی', 'moon-theme' ), 'seo' ),
			'moon-tools'       => array( __( 'ابزارها', 'moon-theme' ), 'tools' ),
			'moon-subscribers' => array( __( 'خبرنامه', 'moon-theme' ), 'subscribers' ),
		);

		return apply_filters( 'moon_admin_pages', $pages );
	}

	/**
	 * Register the admin menu.
	 */
	public static function menu() {
		$pages = self::pages();
		$first = true;

		foreach ( $pages as $slug => $page ) {
			$callback = array( __CLASS__, 'render_' . $page[1] );

			if ( $first ) {
				add_menu_page(
					__( 'MOON', 'moon-theme' ),
					__( 'MOON', 'moon-theme' ),
					self::CAP,
					$slug,
					$callback,
					'dashicons-star-filled',
					58
				);

				$first = false;
				continue;
			}

			add_submenu_page( 'moon-theme', $page[0], $page[0], self::CAP, $slug, $callback );
		}

	}

	/**
	 * Panel styles and scripts.
	 *
	 * @param string $hook Current page hook.
	 */
	public static function assets( $hook ) {
		$screen   = get_current_screen();
		$is_panel = 0 === strpos( (string) $hook, 'moon-' ) || false !== strpos( (string) $hook, 'moon-' );

		if ( ! $is_panel && ( ! $screen || ! in_array( $screen->post_type, array( 'moon_rack', 'moon_outfit' ), true ) ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_style( 'moon-core-admin', MOON_THEME_URI . '/assets/admin/core-admin.css', array( 'wp-color-picker' ), MOON_THEME_VERSION );
		wp_enqueue_script( 'moon-core-admin', MOON_THEME_URI . '/assets/admin/core-admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), MOON_THEME_VERSION, true );
	}

	/* ----------------------------------------------------------------------
	 * Views
	 * -------------------------------------------------------------------- */

	/**
	 * Dashboard.
	 */
	public static function render_dashboard() {
		self::guard();
		include MOON_THEME_CORE_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Homepage builder.
	 */
	public static function render_homepage() {
		self::guard();
		include MOON_THEME_CORE_DIR . 'admin/views/builder.php';
		self::render_settings_page( 'homepage' );
	}

	/**
	 * Design settings.
	 */
	public static function render_design() {
		self::guard();
		self::render_settings_page( 'design' );
	}

	/**
	 * Header settings.
	 */
	public static function render_header() {
		self::guard();
		self::render_settings_page( 'header' );
	}

	/**
	 * Footer builder + content.
	 */
	public static function render_footer() {
		self::guard();
		include MOON_THEME_CORE_DIR . 'admin/views/footer.php';
	}

	/**
	 * Shop settings.
	 */
	public static function render_shop() {
		self::guard();
		self::render_settings_page( 'shop' );
	}

	/**
	 * Account settings.
	 */
	public static function render_account() {
		self::guard();
		self::render_settings_page( 'account' );
	}

	/**
	 * Mobile settings.
	 */
	public static function render_mobile() {
		self::guard();
		self::render_settings_page( 'mobile' );
	}

	/**
	 * Performance settings.
	 */
	public static function render_performance() {
		self::guard();
		self::render_settings_page( 'performance' );
	}

	/**
	 * SEO and custom code.
	 */
	public static function render_seo() {
		self::guard();
		self::render_settings_page( 'seo' );
	}

	/**
	 * Tools: export, import, reset, status.
	 */
	public static function render_tools() {
		self::guard();
		include MOON_THEME_CORE_DIR . 'admin/views/tools.php';
	}

	/**
	 * Newsletter subscribers.
	 */
	public static function render_subscribers() {
		self::guard();
		include MOON_THEME_CORE_DIR . 'admin/views/subscribers.php';
	}

	/**
	 * Shared settings page renderer.
	 *
	 * Inputs live inside tab panels but submit through one hidden form using
	 * the HTML5 `form` attribute, so a builder form on the same screen never
	 * nests inside the options form.
	 *
	 * @param string $page Page key in the schema.
	 */
	public static function render_settings_page( $page ) {
		$schema = Moon_Settings::schema();

		if ( ! isset( $schema[ $page ] ) ) {
			return;
		}

		$definition = $schema[ $page ];
		$options    = Moon_Settings::all();
		$first_tab  = key( $definition['tabs'] );
		?>
		<div class="wrap moon-admin" dir="rtl">
			<h1 class="moon-admin__title">
				<span class="moon-admin__mark">MOON</span>
				<?php echo esc_html( $definition['label'] ); ?>
			</h1>

			<?php self::notices(); ?>

			<form id="moon-options-form" method="post" action="options.php">
				<?php settings_fields( 'moon_options_group' ); ?>
				<input type="hidden" name="moon_options[moon_page]" value="<?php echo esc_attr( $page ); ?>">
			</form>

			<nav class="nav-tab-wrapper moon-admin__tabs" aria-label="<?php esc_attr_e( 'بخش‌های تنظیمات', 'moon-theme' ); ?>">
				<?php foreach ( $definition['tabs'] as $tab_key => $tab ) : ?>
					<a href="#moon-tab-<?php echo esc_attr( $tab_key ); ?>" class="nav-tab<?php echo $tab_key === $first_tab ? ' nav-tab-active' : ''; ?>" data-moon-tab><?php echo esc_html( $tab['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<?php foreach ( $definition['tabs'] as $tab_key => $tab ) : ?>
				<section class="moon-tab-panel<?php echo $tab_key === $first_tab ? ' is-active' : ''; ?>" id="moon-tab-<?php echo esc_attr( $tab_key ); ?>">
					<?php if ( ! empty( $tab['desc'] ) ) : ?>
						<p class="moon-panel-desc"><?php echo esc_html( $tab['desc'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $tab['fields'] ) ) : ?>
						<div class="moon-fields">
							<?php foreach ( $tab['fields'] as $field ) : ?>
								<?php
								$key   = $field['key'];
								$value = array_key_exists( $key, $options ) ? $options[ $key ] : $field['default'];
								self::field_row( $field, $value );
								?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>

			<div class="moon-admin__save">
				<button type="submit" form="moon-options-form" class="button button-primary button-hero"><?php esc_html_e( 'ذخیره تنظیمات', 'moon-theme' ); ?></button>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'مشاهده سایت', 'moon-theme' ); ?></a>
				<a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'moon_tools', 'moon_action' => 'reset', 'moon_page_key' => $page ), admin_url( 'admin-post.php' ) ), 'moon_tools' ) ); ?>" data-moon-confirm="<?php esc_attr_e( 'تنظیمات این صفحه به مقادیر پیش‌فرض بازگردد؟', 'moon-theme' ); ?>"><?php esc_html_e( 'بازگردانی این بخش', 'moon-theme' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single field row.
	 *
	 * @param array $field Field definition.
	 * @param mixed $value Current value.
	 */
	public static function field_row( $field, $value ) {
		$key  = $field['key'];
		$name = 'moon_options[' . $key . ']';
		$id   = 'moon_field_' . $key;
		$type = $field['type'];
		$desc = isset( $field['desc'] ) ? $field['desc'] : '';
		$form = ' form="moon-options-form"';
		$ltr  = ! empty( $field['ltr'] );
		?>
		<div class="moon-field moon-field--<?php echo esc_attr( $type ); ?>">
			<label class="moon-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<div class="moon-field__control">
				<?php if ( 'textarea' === $type ) : ?>
					<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4" class="large-text"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
				<?php elseif ( 'code' === $type ) : ?>
					<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="8" class="large-text code moon-ltr" dir="ltr" spellcheck="false"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
				<?php elseif ( 'checkbox' === $type ) : ?>
					<label class="moon-toggle">
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php checked( ! empty( $value ) ); ?>>
						<span><?php esc_html_e( 'فعال', 'moon-theme' ); ?></span>
					</label>
				<?php elseif ( 'checkboxes' === $type ) : ?>
					<?php $selected = array_filter( explode( ',', (string) $value ) ); ?>
					<div class="moon-checkgroup">
						<?php foreach ( (array) $field['options'] as $option_value => $option_label ) : ?>
							<label class="moon-checkbox">
								<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( (string) $option_value ); ?>" <?php checked( in_array( (string) $option_value, $selected, true ) ); ?><?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<span><?php echo esc_html( $option_label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				<?php elseif ( 'sortable' === $type ) : ?>
					<?php
					$rows  = array();
					$parts = explode( ',', (string) $value );

					foreach ( $parts as $chunk ) {
						$pair = explode( ':', $chunk );

						if ( isset( $field['options'][ $pair[0] ] ) ) {
							$rows[ $pair[0] ] = ! empty( $pair[1] );
						}
					}

					foreach ( (array) $field['options'] as $option_value => $option_label ) {
						if ( ! isset( $rows[ $option_value ] ) ) {
							$rows[ $option_value ] = false;
						}
					}
					?>
					<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" data-moon-sortable-input<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<ul class="moon-sortable" data-moon-sortable>
						<?php foreach ( $rows as $option_value => $enabled ) : ?>
							<li class="moon-sortable__row" data-key="<?php echo esc_attr( (string) $option_value ); ?>">
								<span class="moon-sortable__grip" aria-hidden="true">☰</span>
								<label class="moon-toggle">
									<input type="checkbox" data-moon-sortable-toggle <?php checked( $enabled ); ?>>
									<span><?php echo esc_html( $field['options'][ $option_value ] ); ?></span>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( 'select' === $type ) : ?>
					<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php foreach ( (array) $field['options'] as $option_value => $option_label ) : ?>
							<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php elseif ( 'color' === $type ) : ?>
					<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="moon-color" dir="ltr"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php elseif ( 'media' === $type ) : ?>
					<div class="moon-media" data-moon-media data-type="image">
						<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) (int) $value ); ?>" data-moon-media-input<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<div class="moon-media__preview">
							<?php if ( (int) $value ) : ?>
								<?php echo wp_get_attachment_image( (int) $value, 'medium' ); ?>
							<?php endif; ?>
						</div>
						<button type="button" class="button" data-moon-media-select><?php esc_html_e( 'انتخاب تصویر', 'moon-theme' ); ?></button>
						<button type="button" class="button-link moon-media__remove" data-moon-media-remove <?php echo (int) $value ? '' : 'hidden'; ?>><?php esc_html_e( 'حذف', 'moon-theme' ); ?></button>
					</div>
				<?php elseif ( 'media_url' === $type ) : ?>
					<div class="moon-media" data-moon-media data-type="file" data-url-input>
						<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text moon-ltr" dir="ltr" data-moon-media-url<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<button type="button" class="button" data-moon-media-select><?php esc_html_e( 'انتخاب فایل', 'moon-theme' ); ?></button>
					</div>
				<?php elseif ( 'number' === $type ) : ?>
					<input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" min="<?php echo esc_attr( (string) ( isset( $field['min'] ) ? $field['min'] : 0 ) ); ?>" max="<?php echo esc_attr( (string) ( isset( $field['max'] ) ? $field['max'] : 999999999 ) ); ?>" class="small-text"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php elseif ( 'url' === $type ) : ?>
					<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text moon-ltr" dir="ltr" placeholder="https://"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php else : ?>
					<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text"<?php echo $ltr ? ' dir="ltr"' : ''; ?><?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php endif; ?>

				<?php if ( $desc ) : ?>
					<p class="moon-field__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render admin notices queued by handlers.
	 */
	public static function notices() {
		$notice = isset( $_GET['moon_notice'] ) ? sanitize_key( wp_unslash( $_GET['moon_notice'] ) ) : '';

		if ( ! $notice ) {
			return;
		}

		$messages = array(
			'saved'         => __( 'تنظیمات ذخیره شد.', 'moon-theme' ),
			'order'         => __( 'ترتیب ذخیره شد.', 'moon-theme' ),
			'added'         => __( 'بخش جدید اضافه شد. برای نمایش در سایت، تغییرات را منتشر کنید.', 'moon-theme' ),
			'duplicated'    => __( 'بخش کپی شد.', 'moon-theme' ),
			'deleted'       => __( 'بخش حذف شد.', 'moon-theme' ),
			'published'     => __( 'تغییرات صفحه اصلی منتشر شد.', 'moon-theme' ),
			'discarded'     => __( 'پیش‌نویس کنار گذاشته شد.', 'moon-theme' ),
			'reset'         => __( 'صفحه اصلی به حالت پیش‌فرض بازگشت.', 'moon-theme' ),
			'footer-saved'  => __( 'ستون‌های فوتر ذخیره شد.', 'moon-theme' ),
			'footer-reset'  => __( 'ستون‌های فوتر به حالت پیش‌فرض بازگشت.', 'moon-theme' ),
			'exported'      => __( 'فایل تنظیمات ساخته شد.', 'moon-theme' ),
			'imported'      => __( 'تنظیمات با موفقیت وارد شد.', 'moon-theme' ),
			'import-failed' => __( 'فایل تنظیمات معتبر نبود؛ چیزی تغییر نکرد.', 'moon-theme' ),
			'backup'        => __( 'نسخه پشتیبان از تنظیمات ذخیره شد.', 'moon-theme' ),
			'restored'      => __( 'تنظیمات از نسخه پشتیبان بازگردانده شد.', 'moon-theme' ),
			'reset-all'     => __( 'همه تنظیمات به مقادیر پیش‌فرض بازگشتند.', 'moon-theme' ),
			'failed'        => __( 'عملیات انجام نشد؛ دسترسی یا nonce معتبر نبود.', 'moon-theme' ),
		);

		$class = in_array( $notice, array( 'failed', 'import-failed' ), true ) ? 'notice-error' : 'notice-success';

		printf(
			'<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $class ),
			esc_html( isset( $messages[ $notice ] ) ? $messages[ $notice ] : '' )
		);
	}

	/* ----------------------------------------------------------------------
	 * Tools
	 * -------------------------------------------------------------------- */

	/**
	 * Export, import, backup, restore and reset.
	 */
	public static function handle_tools() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
		}

		check_admin_referer( 'moon_tools' );

		$action = isset( $_REQUEST['moon_action'] ) ? sanitize_key( wp_unslash( $_REQUEST['moon_action'] ) ) : '';
		$page   = isset( $_REQUEST['moon_page_key'] ) ? sanitize_key( wp_unslash( $_REQUEST['moon_page_key'] ) ) : 'all';

		switch ( $action ) {
			case 'export':
				$json = wp_json_encode( Moon_Settings::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

				nocache_headers();
				header( 'Content-Type: application/json; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename="moon-settings-' . gmdate( 'Ymd-His' ) . '.json"' );
				echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
				exit;

			case 'backup':
				update_option( 'moon_settings_backup', array(
					'time' => time(),
					'data' => Moon_Settings::export(),
				), false );

				self::redirect( 'moon-tools', 'backup' );
				break;

			case 'restore':
				$backup = get_option( 'moon_settings_backup' );

				if ( is_array( $backup ) && ! empty( $backup['data'] ) ) {
					Moon_Settings::import( wp_json_encode( $backup['data'] ) );
					self::redirect( 'moon-tools', 'restored' );
				}

				self::redirect( 'moon-tools', 'failed' );
				break;

			case 'import':
				$file = isset( $_FILES['moon_import'] ) ? $_FILES['moon_import'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

				if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
					self::redirect( 'moon-tools', 'import-failed' );
				}

				$raw    = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local upload.
				$result = Moon_Settings::import( (string) $raw );

				self::redirect( 'moon-tools', is_wp_error( $result ) ? 'import-failed' : 'imported' );
				break;

			case 'reset':
				Moon_Settings::reset( $page );
				self::redirect( self::slug_for( $page ), 'all' === $page ? 'reset-all' : 'saved' );
				break;
		}

		self::redirect( 'moon-tools', 'failed' );
	}

	/**
	 * Panel slug that shows one settings page.
	 *
	 * @param string $schema_key Page key in the schema.
	 * @return string
	 */
	public static function slug_for( $schema_key ) {
		foreach ( self::pages() as $slug => $page ) {
			if ( $page[1] === $schema_key ) {
				return $slug;
			}
		}

		return 'moon-theme';
	}

	/**
	 * Redirect back to a panel page with a notice.
	 *
	 * @param string $page   Page slug.
	 * @param string $notice Notice key.
	 */
	private static function redirect( $page, $notice ) {
		wp_safe_redirect( add_query_arg( 'moon_notice', $notice, moon_core_admin_url( $page ) ) );
		exit;
	}

	/**
	 * Capability guard.
	 */
	public static function guard() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
		}
	}
}
