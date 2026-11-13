<?php
/**
 * Theme settings: schema, sanitising and registration.
 *
 * Every field in the panel maps to a real setting consumed by the theme; there
 * are no display-only options.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings definition.
 */
class Moon_Settings {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the option with the Settings API.
	 */
	public static function register() {
		register_setting( 'moon_options_group', 'moon_options', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			'default'           => array(),
		) );
	}

	/**
	 * Panel pages, tabs and fields.
	 *
	 * @return array
	 */
	public static function schema() {
		$icon_options = array(
			'gem'          => 'Gem — کیفیت',
			'shield-check' => 'Shield — امنیت',
			'truck'        => 'Truck — ارسال',
			'headphones'   => 'Headphones — پشتیبانی',
			'star'         => 'Star — ستاره',
			'clock'        => 'Clock — زمان',
			'map-pin'      => 'Map pin — آدرس',
			'mail'         => 'Mail — ایمیل',
			'phone'        => 'Phone — تلفن',
			'rotate-ccw'   => 'Rotate — بازگشت',
		);

		return array(
			'homepage' => array(
				'label' => __( 'صفحه اصلی', 'moon-core' ),
				'tabs'  => array(
					'builder'  => array(
						'label'   => __( 'ترتیب بخش‌ها', 'moon-core' ),
						'builder' => true,
					),
					'hero'     => array(
						'label'  => __( 'هیرو', 'moon-core' ),
						'fields' => array(
							self::field( 'hero_eyebrow', 'برچسب بالای عنوان', 'text', array( 'ltr' => true ) ),
							self::field( 'hero_title_1', 'عنوان · خط اول' ),
							self::field( 'hero_title_2', 'عنوان · خط دوم' ),
							self::field( 'hero_desc', 'توضیح', 'textarea' ),
							self::field( 'hero_btn1_text', 'دکمه اول · متن' ),
							self::field( 'hero_btn1_url', 'دکمه اول · لینک', 'url', array( 'ltr' => true ) ),
							self::field( 'hero_btn2_text', 'دکمه دوم · متن' ),
							self::field( 'hero_btn2_url', 'دکمه دوم · لینک', 'url', array( 'ltr' => true ) ),
							self::field( 'hero_image_id', 'تصویر هیرو', 'media' ),
						),
					),
					'cats'     => array(
						'label'  => __( 'دسته‌بندی‌ها', 'moon-core' ),
						'fields' => array(
							self::field( 'cat1_en', 'کارت اول · عنوان انگلیسی', 'text', array( 'ltr' => true ) ),
							self::field( 'cat1_label', 'کارت اول · نام فارسی' ),
							self::field( 'cat1_note', 'کارت اول · زیرنویس' ),
							self::field( 'cat1_url', 'کارت اول · لینک', 'url', array( 'ltr' => true ) ),
							self::field( 'cat1_image_id', 'کارت اول · تصویر', 'media' ),
							self::field( 'cat2_en', 'کارت دوم · عنوان انگلیسی', 'text', array( 'ltr' => true ) ),
							self::field( 'cat2_label', 'کارت دوم · نام فارسی' ),
							self::field( 'cat2_note', 'کارت دوم · زیرنویس' ),
							self::field( 'cat2_url', 'کارت دوم · لینک', 'url', array( 'ltr' => true ) ),
							self::field( 'cat2_image_id', 'کارت دوم · تصویر', 'media' ),
						),
					),
					'rack'     => array(
						'label'  => __( 'رگال', 'moon-core' ),
						'fields' => array(
							self::field( 'rack_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'rack_title', 'عنوان' ),
							self::field( 'rack_desc', 'توضیح', 'textarea' ),
							self::field( 'rack_note', 'یادداشت قیمت' ),
						),
					),
					'studio'   => array(
						'label'  => __( 'استودیو', 'moon-core' ),
						'fields' => array(
							self::field( 'studio_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'studio_title', 'عنوان' ),
							self::field( 'studio_cta', 'متن دکمه' ),
						),
					),
					'products' => array(
						'label'  => __( 'محصولات', 'moon-core' ),
						'fields' => array(
							self::field( 'products_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'products_title', 'عنوان' ),
							self::field( 'products_note', 'یادداشت زیر کاروسل' ),
							self::field( 'products_source', 'منبع محصولات', 'select', array(
								'options' => array(
									'recent'   => 'جدیدترین محصولات',
									'featured' => 'محصولات ویژه',
									'sale'     => 'محصولات تخفیف‌دار',
								),
							) ),
							self::field( 'products_limit', 'تعداد محصولات', 'number', array( 'min' => 2, 'max' => 20 ) ),
						),
					),
					'campaign' => array(
						'label'  => __( 'کمپین', 'moon-core' ),
						'fields' => array(
							self::field( 'campaign_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'campaign_title', 'عنوان', 'text', array( 'ltr' => true ) ),
							self::field( 'campaign_text', 'توضیح', 'textarea' ),
							self::field( 'campaign_btn_text', 'دکمه · متن' ),
							self::field( 'campaign_btn_url', 'دکمه · لینک', 'url', array( 'ltr' => true ) ),
							self::field( 'campaign_image_id', 'تصویر کمپین', 'media' ),
						),
					),
					'story'    => array(
						'label'  => __( 'داستان برند', 'moon-core' ),
						'fields' => array(
							self::field( 'story_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'story_title', 'عنوان · خط اول' ),
							self::field( 'story_title_2', 'عنوان · خط دوم' ),
							self::field( 'story_text', 'متن', 'textarea' ),
							self::field( 'story_image_id', 'تصویر', 'media' ),
							self::field( 'story_year', 'سال تأسیس', 'text', array( 'ltr' => true ) ),
							self::field( 'story_year_note', 'زیرنویس سال' ),
							self::field( 'benefit1_title', 'مزیت ۱ · عنوان' ),
							self::field( 'benefit1_desc', 'مزیت ۱ · توضیح' ),
							self::field( 'benefit1_icon', 'مزیت ۱ · آیکون', 'select', array( 'options' => $icon_options ) ),
							self::field( 'benefit2_title', 'مزیت ۲ · عنوان' ),
							self::field( 'benefit2_desc', 'مزیت ۲ · توضیح' ),
							self::field( 'benefit2_icon', 'مزیت ۲ · آیکون', 'select', array( 'options' => $icon_options ) ),
							self::field( 'benefit3_title', 'مزیت ۳ · عنوان' ),
							self::field( 'benefit3_desc', 'مزیت ۳ · توضیح' ),
							self::field( 'benefit3_icon', 'مزیت ۳ · آیکون', 'select', array( 'options' => $icon_options ) ),
							self::field( 'benefit4_title', 'مزیت ۴ · عنوان' ),
							self::field( 'benefit4_desc', 'مزیت ۴ · توضیح' ),
							self::field( 'benefit4_icon', 'مزیت ۴ · آیکون', 'select', array( 'options' => $icon_options ) ),
						),
					),
					'chapters' => array(
						'label'  => __( 'داستان اسکرولی', 'moon-core' ),
						'fields' => array(
							self::field( 'scene1_eyebrow', 'صحنه ۱ · برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'scene1_text', 'صحنه ۱ · متن' ),
							self::field( 'scene1_image_id', 'صحنه ۱ · تصویر', 'media' ),
							self::field( 'scene2_eyebrow', 'صحنه ۲ · برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'scene2_text', 'صحنه ۲ · متن' ),
							self::field( 'scene2_image_id', 'صحنه ۲ · تصویر', 'media' ),
							self::field( 'scene3_eyebrow', 'صحنه ۳ · برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'scene3_text', 'صحنه ۳ · متن' ),
							self::field( 'scene3_image_id', 'صحنه ۳ · تصویر', 'media' ),
						),
					),
					'voices'   => array(
						'label'  => __( 'نظرات', 'moon-core' ),
						'fields' => array(
							self::field( 'quote1_text', 'نظر ۱ · متن', 'textarea' ),
							self::field( 'quote1_name', 'نظر ۱ · نام' ),
							self::field( 'quote1_city', 'نظر ۱ · شهر' ),
							self::field( 'quote2_text', 'نظر ۲ · متن', 'textarea' ),
							self::field( 'quote2_name', 'نظر ۲ · نام' ),
							self::field( 'quote2_city', 'نظر ۲ · شهر' ),
							self::field( 'quote3_text', 'نظر ۳ · متن', 'textarea' ),
							self::field( 'quote3_name', 'نظر ۳ · نام' ),
							self::field( 'quote3_city', 'نظر ۳ · شهر' ),
						),
					),
					'newsletter' => array(
						'label'  => __( 'خبرنامه', 'moon-core' ),
						'fields' => array(
							self::field( 'news_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
							self::field( 'news_title', 'عنوان' ),
							self::field( 'news_note', 'پیام پس از عضویت' ),
						),
					),
					'mobile'   => array(
						'label'  => __( 'موبایل', 'moon-core' ),
						'fields' => array(
							self::field( 'hero_mobile_min_height', 'حداقل ارتفاع هیرو در موبایل (px)', 'number', array( 'min' => 420, 'max' => 900, 'desc' => 'برای صفحه‌های کوتاه موبایل مفید است.' ) ),
							self::field( 'mobile_hide_scrollstory', 'پنهان کردن داستان اسکرولی در موبایل', 'checkbox' ),
						),
					),
				),
			),
			'brand'    => array(
				'label' => __( 'برند و ظاهر', 'moon-core' ),
				'tabs'  => array(
					'identity' => array(
						'label'  => __( 'هویت', 'moon-core' ),
						'fields' => array(
							self::field( 'brand_name', 'نام برند', 'text', array( 'desc' => 'اگر خالی بماند از عنوان سایت استفاده می‌شود.' ) ),
							self::field( 'brand_tagline', 'شعار', 'text', array( 'desc' => 'اگر خالی بماند از توضیح سایت استفاده می‌شود.' ) ),
							self::field( 'logo_id', 'لوگو', 'media', array( 'desc' => 'اگر خالی بماند، لوگوی سفارشیِ وردپرس و سپس نام متنی نمایش داده می‌شود.' ) ),
							self::field( 'accent', 'رنگ برند', 'color', array( 'desc' => 'رنگ تأکید دکمه‌ها و جزئیات. خالی = رنگ پیش‌فرض کالکشن.' ) ),
							self::field( 'font_display', 'فونت عنوان‌های نمایشی', 'select', array(
								'options' => array(
									'serif' => 'Cormorant Garamond (پیش‌فرض)',
									'sans'  => 'Vazirmatn',
								),
							) ),
							self::field( 'type_scale', 'مقیاس تایپوگرافی', 'select', array(
								'options' => array(
									'94'  => 'فشرده',
									'100' => 'استاندارد',
									'106' => 'بزرگ‌تر',
								),
							) ),
						),
					),
				),
			),
			'chrome'   => array(
				'label' => __( 'هدر و فوتر', 'moon-core' ),
				'tabs'  => array(
					'header' => array(
						'label'  => __( 'هدر', 'moon-core' ),
						'fields' => array(
							self::field( 'header_sticky', 'هدر چسبیده به بالای صفحه', 'checkbox' ),
							self::field( 'header_search', 'آیکون جستجو', 'checkbox' ),
							self::field( 'header_account', 'آیکون حساب کاربری', 'checkbox', array( 'desc' => 'فقط با ووکامرس فعال نمایش داده می‌شود.' ) ),
							self::field( 'header_wishlist', 'آیکون علاقه‌مندی‌ها', 'checkbox' ),
							self::field( 'header_theme_toggle', 'کلید حالت روشن/تاریک', 'checkbox' ),
						),
					),
					'footer' => array(
						'label'  => __( 'فوتر', 'moon-core' ),
						'fields' => array(
							self::field( 'footer_about', 'متن معرفی', 'textarea' ),
							self::field( 'footer_instagram', 'اینستاگرام', 'url', array( 'ltr' => true ) ),
							self::field( 'footer_telegram', 'تلگرام', 'url', array( 'ltr' => true ) ),
							self::field( 'footer_col1', 'ستون ۱ · لینک‌ها', 'textarea', array( 'desc' => 'هر خط: عنوان|نشانی' ) ),
							self::field( 'footer_col2', 'ستون ۲ · لینک‌ها', 'textarea', array( 'desc' => 'هر خط: عنوان|نشانی' ) ),
							self::field( 'footer_col3', 'ستون ۳ · لینک‌ها', 'textarea', array( 'desc' => 'هر خط: عنوان|نشانی' ) ),
							self::field( 'footer_copyright', 'کپی‌رایت' ),
							self::field( 'footer_cities', 'شهرها', 'text', array( 'ltr' => true ) ),
						),
					),
				),
			),
		);
	}

	/**
	 * Field definition helper.
	 *
	 * @param string $key   Option key.
	 * @param string $label Label.
	 * @param string $type  Control type.
	 * @param array  $extra Extra attributes.
	 * @return array
	 */
	private static function field( $key, $label, $type = 'text', $extra = array() ) {
		return array_merge( array(
			'key'   => $key,
			'label' => $label,
			'type'  => $type,
			'desc'  => '',
		), $extra );
	}

	/**
	 * Flat key => field map.
	 *
	 * @return array
	 */
	public static function fields() {
		$fields = array();

		foreach ( self::schema() as $page ) {
			foreach ( $page['tabs'] as $tab ) {
				if ( empty( $tab['fields'] ) ) {
					continue;
				}

				foreach ( $tab['fields'] as $field ) {
					$fields[ $field['key'] ] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Defaults from the theme (or the stored option when the theme is inactive).
	 *
	 * @return array
	 */
	public static function defaults() {
		if ( function_exists( 'moon_option_defaults' ) ) {
			return moon_option_defaults();
		}

		$stored = get_option( 'moon_options', array() );

		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * Sanitise every submitted field.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$stored = get_option( 'moon_options', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$input  = is_array( $input ) ? $input : array();
		$output = $stored;

		foreach ( self::fields() as $key => $field ) {
			$type = $field['type'];

			if ( 'checkbox' === $type ) {
				$output[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
				continue;
			}

			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			$value = $input[ $key ];

			switch ( $type ) {
				case 'textarea':
					$output[ $key ] = sanitize_textarea_field( $value );
					break;
				case 'url':
					$output[ $key ] = esc_url_raw( $value );
					break;
				case 'color':
					$color          = sanitize_hex_color( $value );
					$output[ $key ] = $color ? $color : '';
					break;
				case 'media':
				case 'number':
					$output[ $key ] = absint( $value );
					break;
				case 'select':
					$options        = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
					$output[ $key ] = in_array( (string) $value, array_map( 'strval', $options ), true ) ? (string) $value : '';
					break;
				default:
					$output[ $key ] = sanitize_text_field( $value );
			}
		}

		return $output;
	}
}
