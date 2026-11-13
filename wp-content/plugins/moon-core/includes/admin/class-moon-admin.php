<?php
/**
 * Admin panel: menu, pages, save handlers.
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
		add_action( 'admin_post_moon_save_sections', array( __CLASS__, 'save_sections' ) );
		add_action( 'admin_post_moon_demo_import', array( __CLASS__, 'demo_import' ) );
	}

	/**
	 * Register the admin menu.
	 */
	public static function menu() {
		add_menu_page(
			__( 'MOON', 'moon-core' ),
			__( 'MOON', 'moon-core' ),
			self::CAP,
			'moon-theme',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-star-filled',
			58
		);

		add_submenu_page( 'moon-theme', __( 'داشبورد', 'moon-core' ), __( 'داشبورد', 'moon-core' ), self::CAP, 'moon-theme', array( __CLASS__, 'render_dashboard' ) );
		add_submenu_page( 'moon-theme', __( 'صفحه اصلی', 'moon-core' ), __( 'صفحه اصلی', 'moon-core' ), self::CAP, 'moon-homepage', array( __CLASS__, 'render_homepage' ) );
		add_submenu_page( 'moon-theme', __( 'برند و ظاهر', 'moon-core' ), __( 'برند و ظاهر', 'moon-core' ), self::CAP, 'moon-brand', array( __CLASS__, 'render_brand' ) );
		add_submenu_page( 'moon-theme', __( 'هدر و فوتر', 'moon-core' ), __( 'هدر و فوتر', 'moon-core' ), self::CAP, 'moon-chrome', array( __CLASS__, 'render_chrome' ) );
		add_submenu_page( 'moon-theme', __( 'نصب محتوا', 'moon-core' ), __( 'نصب محتوا', 'moon-core' ), self::CAP, 'moon-demo', array( __CLASS__, 'render_demo' ) );
		add_submenu_page( 'moon-theme', __( 'خبرنامه', 'moon-core' ), __( 'خبرنامه', 'moon-core' ), self::CAP, 'moon-subscribers', array( __CLASS__, 'render_subscribers' ) );
	}

	/**
	 * Panel styles and scripts.
	 *
	 * @param string $hook Current page hook.
	 */
	public static function assets( $hook ) {
		$screen = get_current_screen();
		$is_panel = 0 === strpos( (string) $hook, 'moon-theme' ) || false !== strpos( (string) $hook, 'moon-' );

		if ( ! $is_panel && ( ! $screen || ! in_array( $screen->post_type, array( 'moon_rack', 'moon_outfit' ), true ) ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_style( 'moon-core-admin', MOON_CORE_URL . 'assets/css/admin.css', array(), MOON_CORE_VERSION );
		wp_enqueue_script( 'moon-core-admin', MOON_CORE_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), MOON_CORE_VERSION, true );
	}

	/* ----------------------------------------------------------------------
	 * Views
	 * -------------------------------------------------------------------- */

	/**
	 * Dashboard page.
	 */
	public static function render_dashboard() {
		self::guard();
		include MOON_CORE_DIR . 'includes/admin/views/dashboard.php';
	}

	/**
	 * Homepage settings page.
	 */
	public static function render_homepage() {
		self::guard();
		self::render_settings_page( 'homepage' );
	}

	/**
	 * Brand page.
	 */
	public static function render_brand() {
		self::guard();
		self::render_settings_page( 'brand' );
	}

	/**
	 * Header and footer page.
	 */
	public static function render_chrome() {
		self::guard();
		self::render_settings_page( 'chrome' );
	}

	/**
	 * Demo importer page.
	 */
	public static function render_demo() {
		self::guard();
		include MOON_CORE_DIR . 'includes/admin/views/demo.php';
	}

	/**
	 * Newsletter subscribers.
	 */
	public static function render_subscribers() {
		self::guard();
		include MOON_CORE_DIR . 'includes/admin/views/subscribers.php';
	}

	/**
	 * Shared settings page renderer.
	 *
	 * The settings inputs live in tab panels but submit through a single
	 * hidden form element using the HTML5 `form` attribute, so the homepage
	 * builder can keep its own separate form.
	 *
	 * @param string $page Page key in the schema.
	 */
	private static function render_settings_page( $page ) {
		$schema = Moon_Settings::schema();

		if ( ! isset( $schema[ $page ] ) ) {
			return;
		}

		$definition = $schema[ $page ];
		$options    = get_option( 'moon_options', array() );
		$options    = is_array( $options ) ? $options : array();
		$defaults   = Moon_Settings::defaults();
		$first_tab  = key( $definition['tabs'] );
		?>
		<div class="wrap moon-admin">
			<h1 class="moon-admin__title">
				<span class="moon-admin__mark">MOON</span>
				<?php echo esc_html( $definition['label'] ); ?>
			</h1>

			<?php self::notices(); ?>

			<form id="moon-options-form" method="post" action="options.php">
				<?php settings_fields( 'moon_options_group' ); ?>
			</form>

			<nav class="nav-tab-wrapper moon-admin__tabs" aria-label="<?php esc_attr_e( 'بخش‌های تنظیمات', 'moon-core' ); ?>">
				<?php foreach ( $definition['tabs'] as $tab_key => $tab ) : ?>
					<a href="#moon-tab-<?php echo esc_attr( $tab_key ); ?>" class="nav-tab<?php echo $tab_key === $first_tab ? ' nav-tab-active' : ''; ?>" data-moon-tab><?php echo esc_html( $tab['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<?php foreach ( $definition['tabs'] as $tab_key => $tab ) : ?>
				<section class="moon-tab-panel<?php echo $tab_key === $first_tab ? ' is-active' : ''; ?>" id="moon-tab-<?php echo esc_attr( $tab_key ); ?>">
					<?php if ( ! empty( $tab['builder'] ) ) : ?>
						<?php include MOON_CORE_DIR . 'includes/admin/views/builder.php'; ?>
					<?php endif; ?>

					<?php if ( ! empty( $tab['fields'] ) ) : ?>
						<div class="moon-fields">
							<?php foreach ( $tab['fields'] as $field ) : ?>
								<?php
								$key   = $field['key'];
								$value = array_key_exists( $key, $options ) ? $options[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
								self::field_row( $field, $value );
								?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>

			<div class="moon-admin__save">
				<button type="submit" form="moon-options-form" class="button button-primary button-hero"><?php esc_html_e( 'ذخیره تنظیمات', 'moon-core' ); ?></button>
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
		$key   = $field['key'];
		$name  = 'moon_options[' . $key . ']';
		$id    = 'moon_field_' . $key;
		$type  = $field['type'];
		$desc  = isset( $field['desc'] ) ? $field['desc'] : '';
		$form  = ' form="moon-options-form"';
		$ltr   = ! empty( $field['ltr'] );
		?>
		<div class="moon-field moon-field--<?php echo esc_attr( $type ); ?>">
			<label class="moon-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<div class="moon-field__control">
				<?php if ( 'textarea' === $type ) : ?>
					<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4" class="large-text"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
				<?php elseif ( 'checkbox' === $type ) : ?>
					<label class="moon-toggle">
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php checked( ! empty( $value ) ); ?>>
						<span><?php esc_html_e( 'فعال', 'moon-core' ); ?></span>
					</label>
				<?php elseif ( 'select' === $type ) : ?>
					<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php foreach ( (array) $field['options'] as $option_value => $option_label ) : ?>
							<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php elseif ( 'color' === $type ) : ?>
					<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="moon-color" data-default-color="" dir="ltr"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php elseif ( 'media' === $type ) : ?>
					<div class="moon-media" data-moon-media>
						<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) (int) $value ); ?>" data-moon-media-input<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<div class="moon-media__preview">
							<?php if ( (int) $value ) : ?>
								<?php echo wp_get_attachment_image( (int) $value, 'medium' ); ?>
							<?php endif; ?>
						</div>
						<button type="button" class="button" data-moon-media-select><?php esc_html_e( 'انتخاب تصویر', 'moon-core' ); ?></button>
						<button type="button" class="button-link moon-media__remove" data-moon-media-remove <?php echo (int) $value ? '' : 'hidden'; ?>><?php esc_html_e( 'حذف', 'moon-core' ); ?></button>
					</div>
				<?php elseif ( 'number' === $type ) : ?>
					<input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" min="<?php echo esc_attr( (string) ( isset( $field['min'] ) ? $field['min'] : 0 ) ); ?>" max="<?php echo esc_attr( (string) ( isset( $field['max'] ) ? $field['max'] : 9999 ) ); ?>" class="small-text"<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			'saved'   => __( 'تنظیمات ذخیره شد.', 'moon-core' ),
			'order'   => __( 'ترتیب بخش‌های صفحه اصلی ذخیره شد.', 'moon-core' ),
			'imported' => __( 'محتوای اولیه نصب شد.', 'moon-core' ),
			'failed'  => __( 'عملیات انجام نشد؛ دسترسی یا nonce معتبر نبود.', 'moon-core' ),
		);

		$class = in_array( $notice, array( 'failed' ), true ) ? 'notice-error' : 'notice-success';

		printf(
			'<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $class ),
			esc_html( isset( $messages[ $notice ] ) ? $messages[ $notice ] : '' )
		);
	}

	/* ----------------------------------------------------------------------
	 * Handlers
	 * -------------------------------------------------------------------- */

	/**
	 * Persist the homepage section order.
	 */
	public static function save_sections() {
		self::guard();

		if ( ! isset( $_POST['moon_sections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moon_sections_nonce'] ) ), 'moon_save_sections' ) ) {
			wp_safe_redirect( add_query_arg( 'moon_notice', 'failed', moon_core_admin_url( 'moon-homepage' ) ) );
			exit;
		}

		$registry = function_exists( 'moon_homepage_registry' ) ? moon_homepage_registry() : array();
		$rows     = isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ? wp_unslash( $_POST['sections'] ) : array();
		$clean    = array();
		$seen     = array();

		foreach ( $rows as $row ) {
			$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';

			if ( ! $id || ! isset( $registry[ $id ] ) || isset( $seen[ $id ] ) ) {
				continue;
			}

			$seen[ $id ] = true;
			$clean[]     = array(
				'id'      => $id,
				'enabled' => empty( $row['enabled'] ) ? 0 : 1,
			);
		}

		// Keep sections the request did not include (e.g. added by an update).
		foreach ( array_keys( $registry ) as $id ) {
			if ( ! isset( $seen[ $id ] ) ) {
				$clean[] = array(
					'id'      => $id,
					'enabled' => 1,
				);
			}
		}

		update_option( 'moon_sections', $clean );

		wp_safe_redirect( add_query_arg( 'moon_notice', 'order', moon_core_admin_url( 'moon-homepage' ) ) );
		exit;
	}

	/**
	 * Run the demo importer from the panel.
	 */
	public static function demo_import() {
		self::guard();

		if ( ! isset( $_POST['moon_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moon_demo_nonce'] ) ), 'moon_demo_import' ) ) {
			wp_safe_redirect( add_query_arg( 'moon_notice', 'failed', moon_core_admin_url( 'moon-demo' ) ) );
			exit;
		}

		$options = array(
			'products'   => ! empty( $_POST['import_products'] ),
			'media'      => ! empty( $_POST['import_media'] ),
			'pages'      => ! empty( $_POST['import_pages'] ),
			'rack'       => ! empty( $_POST['import_rack'] ),
			'taxonomies' => ! empty( $_POST['import_taxonomies'] ),
		);

		$report = Moon_Demo_Importer::run( $options );

		set_transient( 'moon_demo_report_' . get_current_user_id(), $report, 5 * MINUTE_IN_SECONDS );

		wp_safe_redirect( add_query_arg( 'moon_notice', 'imported', moon_core_admin_url( 'moon-demo' ) ) );
		exit;
	}

	/**
	 * Capability guard.
	 */
	private static function guard() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-core' ), 403 );
		}
	}
}
