<?php
/**
 * Settings page for Moon Core.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and reads the single `moon_core_settings` option.
 */
class Moon_Core_Settings {

	/**
	 * Option name.
	 */
	const OPTION = 'moon_core_settings';

	/**
	 * Admin page slug.
	 */
	const PAGE = 'moon-core';

	/**
	 * Singleton instance.
	 *
	 * @var Moon_Core_Settings|null
	 */
	private static $instance = null;

	/**
	 * Cached settings.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Moon_Core_Settings
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Register the settings page.
	 */
	public function add_menu() {
		add_menu_page(
			__( 'MOON', 'moon-core' ),
			__( 'MOON', 'moon-core' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' ),
			'dashicons-star-filled',
			58
		);
	}

	/**
	 * Register the setting with a sanitize callback.
	 */
	public function register() {
		register_setting(
			'moon_core_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Admin assets for the media picker.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function admin_assets( $hook ) {
		if ( 'toplevel_page_' . self::PAGE !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'moon-core-admin',
			MOON_CORE_URL . 'assets/js/moon-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			MOON_CORE_VERSION,
			true
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			// Brand.
			'brand_name'        => 'MOON',
			'brand_tagline'     => __( 'پوشاک لوکس، ساخته‌شده برای شب‌های ماندگار', 'moon-core' ),
			'logo_id'           => 0,
			'logo_dark_id'      => 0,

			// Palette.
			'color_ink'         => '#11110F',
			'color_charcoal'    => '#1B1A18',
			'color_cream'       => '#F3EFE6',
			'color_white'       => '#FAF9F6',
			'color_gold'        => '#B99A62',
			'color_gray'        => '#8D8981',

			// Dark palette.
			'dark_bg'           => '#11110F',
			'dark_surface'      => '#1B1A18',
			'dark_text'         => '#F3EFE6',
			'dark_muted'        => '#A39E94',
			'dark_gold'         => '#C9AC77',

			// Typography.
			'font_fa'           => 'Vazirmatn',
			'font_en_display'   => 'Jost',
			'font_en_text'      => 'Jost',
			'font_size_base'    => 16,

			// Header.
			'header_source'     => 'moon',
			'header_sticky'     => 1,
			'header_shrink'     => 1,
			'header_menu'       => '',
			'header_show_search' => 1,
			'header_show_account' => 1,
			'header_show_wishlist' => 1,
			'header_show_cart'  => 1,
			'header_show_toggle' => 1,
			'header_cta_text'   => __( 'کالکشن جدید', 'moon-core' ),
			'header_cta_url'    => '#',

			// Dark mode.
			'dark_default'      => 'system',
			'dark_toggle_label_light' => __( 'حالت شب', 'moon-core' ),
			'dark_toggle_label_dark'  => __( 'حالت روز', 'moon-core' ),

			// Animations.
			'animations'        => 1,
			'anim_duration'     => 700,

			// Hero.
			'hero_enabled'           => 1,
			'hero_video'        => '',
			'hero_poster_id'    => 0,
			'hero_disable_mobile_video' => 1,

			// Features.
			'enable_filters'    => 1,
			'enable_wishlist'   => 1,
			'enable_load_more'  => 1,
			'enable_size_guide' => 1,
			'enable_badges'     => 1,
			'enable_back_to_top' => 1,

			// Content snippets.
			'size_guide_html'   => '',
			'benefits_note'     => '',
			'footer_note'       => __( '© MOON — تمام حقوق محفوظ است.', 'moon-core' ),
		);
	}

	/**
	 * All settings merged over defaults.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null === self::$cache ) {
			$stored      = get_option( self::OPTION, array() );
			$stored      = is_array( $stored ) ? $stored : array();
			self::$cache = wp_parse_args( $stored, self::defaults() );
		}

		return self::$cache;
	}

	/**
	 * Read one setting.
	 *
	 * @param string $key     Setting name.
	 * @param mixed  $default Fallback when the key is absent.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$all = self::all();

		if ( array_key_exists( $key, $all ) && '' !== $all[ $key ] && null !== $all[ $key ] ) {
			return $all[ $key ];
		}

		if ( null !== $default ) {
			return $default;
		}

		$defaults = self::defaults();

		return array_key_exists( $key, $defaults ) ? $defaults[ $key ] : '';
	}

	/**
	 * Field definitions grouped by tab.
	 *
	 * @return array
	 */
	public function fields() {
		$fields = array(
			'brand' => array(
				'title'  => __( 'برند', 'moon-core' ),
				'fields' => array(
					'brand_name'    => array(
						'label' => __( 'نام برند', 'moon-core' ),
						'type'  => 'text',
					),
					'brand_tagline' => array(
						'label' => __( 'شعار برند', 'moon-core' ),
						'type'  => 'text',
					),
					'logo_id'       => array(
						'label' => __( 'لوگو (حالت روز)', 'moon-core' ),
						'type'  => 'media',
					),
					'logo_dark_id'  => array(
						'label' => __( 'لوگو (حالت شب)', 'moon-core' ),
						'type'  => 'media',
					),
				),
			),
			'colors' => array(
				'title'  => __( 'رنگ‌ها', 'moon-core' ),
				'fields' => array(
					'color_ink'      => array(
						'label' => __( 'مشکی عمیق', 'moon-core' ),
						'type'  => 'color',
					),
					'color_charcoal' => array(
						'label' => __( 'زغالی', 'moon-core' ),
						'type'  => 'color',
					),
					'color_cream'    => array(
						'label' => __( 'کرم روشن', 'moon-core' ),
						'type'  => 'color',
					),
					'color_white'    => array(
						'label' => __( 'سفید گرم', 'moon-core' ),
						'type'  => 'color',
					),
					'color_gold'     => array(
						'label' => __( 'طلایی مات', 'moon-core' ),
						'type'  => 'color',
					),
					'color_gray'     => array(
						'label' => __( 'خاکستری خنثی', 'moon-core' ),
						'type'  => 'color',
					),
				),
			),
			'dark' => array(
				'title'  => __( 'حالت شب', 'moon-core' ),
				'fields' => array(
					'dark_bg'      => array(
						'label' => __( 'پس‌زمینه', 'moon-core' ),
						'type'  => 'color',
					),
					'dark_surface' => array(
						'label' => __( 'سطح کارت‌ها', 'moon-core' ),
						'type'  => 'color',
					),
					'dark_text'    => array(
						'label' => __( 'متن', 'moon-core' ),
						'type'  => 'color',
					),
					'dark_muted'   => array(
						'label' => __( 'متن کم‌رنگ', 'moon-core' ),
						'type'  => 'color',
					),
					'dark_gold'    => array(
						'label' => __( 'طلایی حالت شب', 'moon-core' ),
						'type'  => 'color',
					),
					'dark_default' => array(
						'label'   => __( 'تم پیش‌فرض بازدیدکننده', 'moon-core' ),
						'type'    => 'select',
						'options' => array(
							'light'  => __( 'روشن', 'moon-core' ),
							'dark'   => __( 'تاریک', 'moon-core' ),
							'system' => __( 'هماهنگ با سیستم', 'moon-core' ),
						),
					),
					'dark_toggle_label_light' => array(
						'label' => __( 'متن دکمه در حالت روز', 'moon-core' ),
						'type'  => 'text',
					),
					'dark_toggle_label_dark'  => array(
						'label' => __( 'متن دکمه در حالت شب', 'moon-core' ),
						'type'  => 'text',
					),
				),
			),
			'typography' => array(
				'title'  => __( 'تایپوگرافی', 'moon-core' ),
				'fields' => array(
					'font_fa'         => array(
						'label' => __( 'فونت فارسی', 'moon-core' ),
						'type'  => 'text',
					),
					'font_en_display' => array(
						'label' => __( 'فونت نمایشی انگلیسی', 'moon-core' ),
						'type'  => 'text',
					),
					'font_en_text'    => array(
						'label' => __( 'فونت متنی انگلیسی', 'moon-core' ),
						'type'  => 'text',
					),
					'font_size_base'  => array(
						'label' => __( 'اندازه پایه (px)', 'moon-core' ),
						'type'  => 'number',
						'min'   => 12,
						'max'   => 22,
					),
				),
			),
			'header' => array(
				'title'  => __( 'هدر', 'moon-core' ),
				'fields' => array(
					'header_source'        => array(
						'label'   => __( 'منبع هدر', 'moon-core' ),
						'type'    => 'select',
						'options' => array(
							'moon'  => __( 'هدر شیشه‌ای MOON', 'moon-core' ),
							'theme' => __( 'هدر قالب / المنتور پرو', 'moon-core' ),
						),
						'desc'    => __( 'اگر قالب هدر را در Theme Builder المنتور پرو می‌سازید، «هدر قالب» را انتخاب کنید.', 'moon-core' ),
					),
					'header_sticky'        => array(
						'label' => __( 'چسبیدن هنگام اسکرول', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_shrink'        => array(
						'label' => __( 'جمع‌شدن هنگام اسکرول', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_menu'          => array(
						'label' => __( 'شناسه منوی وردپرس', 'moon-core' ),
						'type'  => 'text',
						'desc'  => __( 'خالی بگذارید تا منوی «دسته‌بندی‌های محصول» ساخته شود.', 'moon-core' ),
					),
					'header_show_search'   => array(
						'label' => __( 'آیکون جست‌وجو', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_show_account'  => array(
						'label' => __( 'حساب کاربری', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_show_wishlist' => array(
						'label' => __( 'علاقه‌مندی‌ها', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_show_cart'     => array(
						'label' => __( 'سبد خرید', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_show_toggle'   => array(
						'label' => __( 'دکمه روشن/تاریک', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'header_cta_text'      => array(
						'label' => __( 'متن دکمه هدر', 'moon-core' ),
						'type'  => 'text',
					),
					'header_cta_url'       => array(
						'label' => __( 'لینک دکمه هدر', 'moon-core' ),
						'type'  => 'url',
					),
				),
			),
			'motion' => array(
				'title'  => __( 'انیمیشن', 'moon-core' ),
				'fields' => array(
					'animations'    => array(
						'label' => __( 'فعال‌سازی انیمیشن‌ها', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'anim_duration' => array(
						'label' => __( 'مدت انیمیشن (میلی‌ثانیه)', 'moon-core' ),
						'type'  => 'number',
						'min'   => 200,
						'max'   => 1600,
					),
				),
			),
			'hero' => array(
				'title'  => __( 'هیرو', 'moon-core' ),
				'fields' => array(
					'hero_enabled'              => array(
						'label' => __( 'فعال‌سازی هیرو پیش‌فرض', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'hero_video'                => array(
						'label' => __( 'آدرس ویدئو MP4', 'moon-core' ),
						'type'  => 'url',
						'desc'  => __( 'مثال: آدرس فایل در کتابخانه رسانه یا CDN.', 'moon-core' ),
					),
					'hero_poster_id'            => array(
						'label' => __( 'تصویر Poster', 'moon-core' ),
						'type'  => 'media',
					),
					'hero_disable_mobile_video' => array(
						'label' => __( 'غیرفعال کردن ویدئو در موبایل', 'moon-core' ),
						'type'  => 'checkbox',
					),
				),
			),
			'features' => array(
				'title'  => __( 'قابلیت‌ها', 'moon-core' ),
				'fields' => array(
					'enable_filters'     => array(
						'label' => __( 'فیلترهای فروشگاه', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'enable_wishlist'    => array(
						'label' => __( 'علاقه‌مندی‌ها (در صورت نبود YITH)', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'enable_load_more'   => array(
						'label' => __( 'بارگذاری بیشتر به‌جای صفحه‌بندی', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'enable_size_guide'  => array(
						'label' => __( 'تب راهنمای سایز', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'enable_badges'      => array(
						'label' => __( 'نشان تخفیف و «جدید»', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'enable_back_to_top' => array(
						'label' => __( 'دکمه بازگشت به بالا', 'moon-core' ),
						'type'  => 'checkbox',
					),
					'size_guide_html'    => array(
						'label' => __( 'متن راهنمای سایز (HTML مجاز)', 'moon-core' ),
						'type'  => 'textarea',
					),
					'footer_note'        => array(
						'label' => __( 'متن پایین فوتر', 'moon-core' ),
						'type'  => 'text',
					),
				),
			),
		);

		return apply_filters( 'moon_core_settings_fields', $fields );
	}

	/**
	 * Sanitize the submitted option.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$defaults = self::defaults();
		$input    = is_array( $input ) ? $input : array();
		$clean    = array();

		foreach ( $this->fields() as $group ) {
			foreach ( $group['fields'] as $key => $field ) {
				$raw = isset( $input[ $key ] ) ? $input[ $key ] : null;

				switch ( $field['type'] ) {
					case 'checkbox':
						$clean[ $key ] = ( '1' === (string) $raw || 1 === $raw || true === $raw ) ? 1 : 0;
						break;
					case 'color':
						$color         = sanitize_hex_color( (string) $raw );
						$clean[ $key ] = $color ? $color : $defaults[ $key ];
						break;
					case 'media':
						$clean[ $key ] = absint( $raw );
						break;
					case 'number':
						$number = absint( $raw );
						if ( isset( $field['min'] ) ) {
							$number = max( $number, (int) $field['min'] );
						}
						if ( isset( $field['max'] ) ) {
							$number = min( $number, (int) $field['max'] );
						}
						$clean[ $key ] = $number ? $number : (int) $defaults[ $key ];
						break;
					case 'url':
						$clean[ $key ] = esc_url_raw( (string) $raw );
						break;
					case 'textarea':
						$clean[ $key ] = wp_kses_post( (string) $raw );
						break;
					case 'select':
						$allowed       = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
						$clean[ $key ] = in_array( (string) $raw, $allowed, true ) ? (string) $raw : $defaults[ $key ];
						break;
					default:
						$clean[ $key ] = sanitize_text_field( (string) $raw );
						break;
				}
			}
		}

		self::$cache = null;

		return wp_parse_args( $clean, $defaults );
	}

	/**
	 * Render the settings screen.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs = $this->fields();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab navigation.
		$current = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'brand';
		if ( ! isset( $tabs[ $current ] ) ) {
			$current = 'brand';
		}

		$values = self::all();
		?>
		<div class="wrap moon-admin">
			<h1><?php esc_html_e( 'تنظیمات MOON', 'moon-core' ); ?></h1>
			<p class="description"><?php esc_html_e( 'این مقادیر به‌صورت CSS Variables و تنظیمات پیش‌فرض ویجت‌های المنتور اعمال می‌شوند.', 'moon-core' ); ?></p>

			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $slug => $tab ) : ?>
					<a class="nav-tab <?php echo $slug === $current ? 'nav-tab-active' : ''; ?>"
						href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE . '&tab=' . $slug ) ); ?>">
						<?php echo esc_html( $tab['title'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="options.php">
				<?php settings_fields( 'moon_core_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( $tabs[ $current ]['fields'] as $key => $field ) : ?>
						<tr>
							<th scope="row"><label for="moon-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php $this->render_field( $key, $field, $values ); ?>
								<?php if ( ! empty( $field['desc'] ) ) : ?>
									<p class="description"><?php echo esc_html( $field['desc'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a single field control.
	 *
	 * @param string $key    Setting key.
	 * @param array  $field  Field definition.
	 * @param array  $values Current values.
	 */
	private function render_field( $key, $field, $values ) {
		$name  = self::OPTION . '[' . $key . ']';
		$id    = 'moon-' . $key;
		$value = isset( $values[ $key ] ) ? $values[ $key ] : '';

		switch ( $field['type'] ) {
			case 'checkbox':
				printf(
					'<input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( 1, (int) $value, false )
				);
				break;

			case 'color':
				printf(
					'<input type="text" class="moon-color-field" id="%1$s" name="%2$s" value="%3$s" data-default-color="%4$s" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $value )
				);
				break;

			case 'media':
				printf(
					'<div class="moon-media-field"><input type="hidden" id="%1$s" name="%2$s" value="%3$d" />' .
					'<button type="button" class="button moon-media-select">%4$s</button> ' .
					'<button type="button" class="button-link moon-media-clear">%5$s</button>' .
					'<div class="moon-media-preview">%6$s</div></div>',
					esc_attr( $id ),
					esc_attr( $name ),
					(int) $value,
					esc_html__( 'انتخاب تصویر', 'moon-core' ),
					esc_html__( 'حذف', 'moon-core' ),
					(int) $value ? wp_get_attachment_image( (int) $value, 'thumbnail' ) : ''
				);
				break;

			case 'number':
				printf(
					'<input type="number" id="%1$s" name="%2$s" value="%3$d" min="%4$d" max="%5$d" class="small-text" />',
					esc_attr( $id ),
					esc_attr( $name ),
					(int) $value,
					isset( $field['min'] ) ? (int) $field['min'] : 0,
					isset( $field['max'] ) ? (int) $field['max'] : 999
				);
				break;

			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%2$s" rows="6" class="large-text code">%3$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( $value )
				);
				break;

			case 'select':
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $field['options'] as $option_value => $label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $option_value ),
						selected( (string) $value, (string) $option_value, false ),
						esc_html( $label )
					);
				}
				echo '</select>';
				break;

			case 'url':
				printf(
					'<input type="url" id="%1$s" name="%2$s" value="%3$s" class="regular-text" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;

			default:
				printf(
					'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;
		}
	}
}
