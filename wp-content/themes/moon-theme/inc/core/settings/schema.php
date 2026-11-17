<?php
/**
 * Panel schema: every page, tab and field of the MOON settings.
 *
 * Each field maps to a real setting consumed by the theme — see
 * `Moon_Tokens` (design tokens), `Moon_Header`, `Moon_Footer`,
 * `Moon_Shop`, `Moon_Woo_Extras`, `inc/seo.php`, `inc/enqueue.php` and
 * `inc/setup.php`. A field is only added here when something reads it.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definition helper.
 *
 * @param string $key     Option key.
 * @param string $label   Label.
 * @param string $type    Control type.
 * @param array  $extra   Extra attributes (default, desc, options, min, max, ltr, …).
 * @return array
 */
function moon_field( $key, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'     => $key,
			'label'   => $label,
			'type'    => $type,
			'desc'    => '',
			'default' => '',
		),
		$extra
	);
}

/**
 * The schema.
 *
 * @return array
 */
function moon_settings_schema() {
	$fonts = array(
		''         => 'پیش‌فرض قالب',
		'vazir'    => 'Vazirmatn (فارسی)',
		'cormorant' => 'Cormorant Garamond (لاتین)',
		'system'   => 'فونت سیستم',
		'custom'   => 'فونت سفارشی (بارگذاری‌شده)',
	);

	$shadow_options = array(
		'soft'   => 'نرم (پیش‌فرض)',
		'none'   => 'بدون سایه',
		'strong' => 'عمیق',
	);

	$card_styles = array(
		'flat'     => 'مسطح',
		'bordered' => 'کادردار با حاشیه',
		'elevated' => 'برجسته با سایه',
	);

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

	$schema = array(
		/* ------------------------------------------------------------------
		 * محتوای بخش‌های صفحه اصلی (همراه با صفحه‌ساز)
		 * ---------------------------------------------------------------- */
		'homepage' => array(
			'label' => __( 'صفحه اصلی', 'moon-theme' ),
			'tabs'  => array(
				'hero'       => array(
					'label'  => __( 'هیرو', 'moon-theme' ),
					'fields' => array(
						moon_field( 'hero_eyebrow', 'برچسب بالای عنوان', 'text', array( 'ltr' => true ) ),
						moon_field( 'hero_title_1', 'عنوان · خط اول' ),
						moon_field( 'hero_title_2', 'عنوان · خط دوم' ),
						moon_field( 'hero_desc', 'توضیح', 'textarea' ),
						moon_field( 'hero_btn1_text', 'دکمه اول · متن' ),
						moon_field( 'hero_btn1_url', 'دکمه اول · لینک', 'url', array( 'ltr' => true ) ),
						moon_field( 'hero_btn2_text', 'دکمه دوم · متن' ),
						moon_field( 'hero_btn2_url', 'دکمه دوم · لینک', 'url', array( 'ltr' => true ) ),
						moon_field( 'hero_image_id', 'تصویر هیرو', 'media' ),
					),
				),
				'cats'       => array(
					'label'  => __( 'دسته‌بندی‌ها', 'moon-theme' ),
					'fields' => array(
						moon_field( 'cat1_en', 'کارت اول · عنوان انگلیسی', 'text', array( 'ltr' => true ) ),
						moon_field( 'cat1_label', 'کارت اول · نام فارسی' ),
						moon_field( 'cat1_note', 'کارت اول · زیرنویس' ),
						moon_field( 'cat1_url', 'کارت اول · لینک', 'url', array( 'ltr' => true ) ),
						moon_field( 'cat1_image_id', 'کارت اول · تصویر', 'media' ),
						moon_field( 'cat2_en', 'کارت دوم · عنوان انگلیسی', 'text', array( 'ltr' => true ) ),
						moon_field( 'cat2_label', 'کارت دوم · نام فارسی' ),
						moon_field( 'cat2_note', 'کارت دوم · زیرنویس' ),
						moon_field( 'cat2_url', 'کارت دوم · لینک', 'url', array( 'ltr' => true ) ),
						moon_field( 'cat2_image_id', 'کارت دوم · تصویر', 'media' ),
					),
				),
				'rack'       => array(
					'label'  => __( 'رگال', 'moon-theme' ),
					'fields' => array(
						moon_field( 'rack_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'rack_title', 'عنوان' ),
						moon_field( 'rack_desc', 'توضیح', 'textarea' ),
						moon_field( 'rack_note', 'یادداشت قیمت' ),
					),
				),
				'studio'     => array(
					'label'  => __( 'استودیو', 'moon-theme' ),
					'fields' => array(
						moon_field( 'studio_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'studio_title', 'عنوان' ),
						moon_field( 'studio_cta', 'متن دکمه' ),
					),
				),
				'products'   => array(
					'label'  => __( 'محصولات', 'moon-theme' ),
					'fields' => array(
						moon_field( 'products_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'products_title', 'عنوان' ),
						moon_field( 'products_note', 'یادداشت زیر کاروسل' ),
						moon_field( 'products_source', 'منبع محصولات', 'select', array(
							'options' => array(
								'recent'   => 'جدیدترین محصولات',
								'featured' => 'محصولات ویژه',
								'sale'     => 'محصولات تخفیف‌دار',
							),
							'default' => 'recent',
						) ),
						moon_field( 'products_limit', 'تعداد محصولات', 'number', array( 'min' => 2, 'max' => 20, 'default' => 8 ) ),
					),
				),
				'campaign'   => array(
					'label'  => __( 'کمپین', 'moon-theme' ),
					'fields' => array(
						moon_field( 'campaign_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'campaign_title', 'عنوان', 'text', array( 'ltr' => true ) ),
						moon_field( 'campaign_text', 'توضیح', 'textarea' ),
						moon_field( 'campaign_btn_text', 'دکمه · متن' ),
						moon_field( 'campaign_btn_url', 'دکمه · لینک', 'url', array( 'ltr' => true ) ),
						moon_field( 'campaign_image_id', 'تصویر کمپین', 'media' ),
					),
				),
				'story'      => array(
					'label'  => __( 'داستان برند', 'moon-theme' ),
					'fields' => array(
						moon_field( 'story_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'story_title', 'عنوان · خط اول' ),
						moon_field( 'story_title_2', 'عنوان · خط دوم' ),
						moon_field( 'story_text', 'متن', 'textarea' ),
						moon_field( 'story_image_id', 'تصویر', 'media' ),
						moon_field( 'story_year', 'سال تأسیس', 'text', array( 'ltr' => true ) ),
						moon_field( 'story_year_note', 'زیرنویس سال' ),
						moon_field( 'benefit1_title', 'مزیت ۱ · عنوان' ),
						moon_field( 'benefit1_desc', 'مزیت ۱ · توضیح' ),
						moon_field( 'benefit1_icon', 'مزیت ۱ · آیکون', 'select', array( 'options' => $icon_options, 'default' => 'gem' ) ),
						moon_field( 'benefit2_title', 'مزیت ۲ · عنوان' ),
						moon_field( 'benefit2_desc', 'مزیت ۲ · توضیح' ),
						moon_field( 'benefit2_icon', 'مزیت ۲ · آیکون', 'select', array( 'options' => $icon_options, 'default' => 'shield-check' ) ),
						moon_field( 'benefit3_title', 'مزیت ۳ · عنوان' ),
						moon_field( 'benefit3_desc', 'مزیت ۳ · توضیح' ),
						moon_field( 'benefit3_icon', 'مزیت ۳ · آیکون', 'select', array( 'options' => $icon_options, 'default' => 'truck' ) ),
						moon_field( 'benefit4_title', 'مزیت ۴ · عنوان' ),
						moon_field( 'benefit4_desc', 'مزیت ۴ · توضیح' ),
						moon_field( 'benefit4_icon', 'مزیت ۴ · آیکون', 'select', array( 'options' => $icon_options, 'default' => 'headphones' ) ),
					),
				),
				'chapters'   => array(
					'label'  => __( 'داستان اسکرولی', 'moon-theme' ),
					'fields' => array(
						moon_field( 'scene1_eyebrow', 'صحنه ۱ · برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'scene1_text', 'صحنه ۱ · متن' ),
						moon_field( 'scene1_image_id', 'صحنه ۱ · تصویر', 'media' ),
						moon_field( 'scene2_eyebrow', 'صحنه ۲ · برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'scene2_text', 'صحنه ۲ · متن' ),
						moon_field( 'scene2_image_id', 'صحنه ۲ · تصویر', 'media' ),
						moon_field( 'scene3_eyebrow', 'صحنه ۳ · برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'scene3_text', 'صحنه ۳ · متن' ),
						moon_field( 'scene3_image_id', 'صحنه ۳ · تصویر', 'media' ),
					),
				),
				'voices'     => array(
					'label'  => __( 'نظرات', 'moon-theme' ),
					'fields' => array(
						moon_field( 'quote1_text', 'نظر ۱ · متن', 'textarea' ),
						moon_field( 'quote1_name', 'نظر ۱ · نام' ),
						moon_field( 'quote1_city', 'نظر ۱ · شهر' ),
						moon_field( 'quote2_text', 'نظر ۲ · متن', 'textarea' ),
						moon_field( 'quote2_name', 'نظر ۲ · نام' ),
						moon_field( 'quote2_city', 'نظر ۲ · شهر' ),
						moon_field( 'quote3_text', 'نظر ۳ · متن', 'textarea' ),
						moon_field( 'quote3_name', 'نظر ۳ · نام' ),
						moon_field( 'quote3_city', 'نظر ۳ · شهر' ),
					),
				),
				'newsletter' => array(
					'label'  => __( 'خبرنامه', 'moon-theme' ),
					'fields' => array(
						moon_field( 'news_eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
						moon_field( 'news_title', 'عنوان' ),
						moon_field( 'news_note', 'پیام پس از عضویت' ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * طراحی: رنگ‌ها، فونت، چیدمان، انیمیشن
		 * ---------------------------------------------------------------- */
		'design' => array(
			'label' => __( 'ظاهر و طراحی', 'moon-theme' ),
			'tabs'  => array(
				'colors'     => array(
					'label'  => __( 'رنگ‌ها', 'moon-theme' ),
					'desc'   => __( 'همه رنگ‌ها به‌صورت متغیر CSS اعمال می‌شوند؛ نیازی به ویرایش کد نیست.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'theme_mode', 'حالت رنگی پیش‌فرض', 'select', array(
							'options' => array(
								'dark'   => 'تاریک (پیش‌فرض)',
								'light'  => 'روشن',
								'system' => 'هماهنگ با سیستم‌عامل کاربر (تاریک یا روشن)',
							),
							'default' => 'dark',
							'desc'    => 'کاربر همیشه می‌تواند با کلید هدر بین روشن و تاریک جابه‌جا شود؛ این گزینه فقط حالت شروع را تعیین می‌کند.',
						) ),
						moon_field( 'accent', 'رنگ برند (تأکیدی)', 'color', array( 'default' => '#c2a15a', 'desc' => 'دکمه‌های اصلی، لینک‌های فعال و جزئیات.' ) ),
						moon_field( 'accent_fg', 'رنگ متن روی برند', 'color', array( 'default' => '#1a1a1a' ) ),
						moon_field( 'color_link', 'رنگ لینک‌ها', 'color', array( 'default' => '' ) ),
						moon_field( 'color_sale', 'رنگ تخفیف و برچسب فروش', 'color', array( 'default' => '#b23a3a' ) ),
						moon_field( 'color_success', 'رنگ موفقیت و موجودی', 'color', array( 'default' => '#4c6b52' ) ),
						moon_field( 'color_warning', 'رنگ هشدار', 'color', array( 'default' => '#c9a227' ) ),
						moon_field( 'color_error', 'رنگ خطا', 'color', array( 'default' => '#b23a3a' ) ),
						moon_field( 'color_border', 'رنگ حاشیه‌ها', 'color', array( 'default' => '' ) ),
						moon_field( 'button_bg', 'پس‌زمینه دکمه‌ها', 'color', array( 'default' => '' ) ),
						moon_field( 'button_fg', 'متن دکمه‌ها', 'color', array( 'default' => '' ) ),
						moon_field( 'theme_dark_bg', 'حالت تاریک · پس‌زمینه', 'color', array( 'default' => '#171717' ) ),
						moon_field( 'theme_dark_card', 'حالت تاریک · کارت و سطح', 'color', array( 'default' => '#232323' ) ),
						moon_field( 'theme_dark_text', 'حالت تاریک · متن', 'color', array( 'default' => '#f2eee7' ) ),
						moon_field( 'theme_light_bg', 'حالت روشن · پس‌زمینه', 'color', array( 'default' => '#f7f3ea' ) ),
						moon_field( 'theme_light_card', 'حالت روشن · کارت و سطح', 'color', array( 'default' => '#fffdf8' ) ),
						moon_field( 'theme_light_text', 'حالت روشن · متن', 'color', array( 'default' => '#1f1f1f' ) ),
					),
				),
				'typography' => array(
					'label'  => __( 'فونت و تایپوگرافی', 'moon-theme' ),
					'fields' => array(
						moon_field( 'font_body', 'فونت متن', 'select', array( 'options' => $fonts, 'default' => '' ) ),
						moon_field( 'font_heading', 'فونت تیترها', 'select', array( 'options' => $fonts, 'default' => '' ) ),
						moon_field( 'font_price', 'فونت قیمت', 'select', array(
							'options' => array(
								''      => 'همان فونت متن',
								'vazir' => 'Vazirmatn',
								'cormorant' => 'Cormorant Garamond',
							),
							'default' => '',
						) ),
						moon_field( 'font_custom_family', 'نام فونت سفارشی', 'text', array( 'ltr' => true, 'default' => '', 'desc' => 'نام خانواده فونتی که فایل آن را در فیلد بعدی می‌گذارید.' ) ),
						moon_field( 'font_custom_url', 'فایل فونت سفارشی (woff2)', 'media_url', array( 'default' => '', 'desc' => 'فقط یک فایل woff2. برای بهینه ماندن، فونت اضافی در همه صفحات بارگذاری نمی‌شود.' ) ),
						moon_field( 'body_weight', 'وزن متن', 'select', array(
							'options' => array( '300' => 'نازک (300)', '400' => 'معمولی (400)', '500' => 'متوسط (500)' ),
							'default' => '400',
						) ),
						moon_field( 'heading_weight', 'وزن تیترها', 'select', array(
							'options' => array( '300' => 'نازک (300)', '400' => 'معمولی (400)', '500' => 'متوسط (500)', '600' => 'نیمه‌ضخیم (600)', '700' => 'ضخیم (700)' ),
							'default' => '400',
						) ),
						moon_field( 'font_size', 'اندازه پایه متن (٪)', 'number', array( 'min' => 85, 'max' => 120, 'default' => 100 ) ),
						moon_field( 'line_height', 'فاصله خطوط', 'number', array( 'min' => 120, 'max' => 220, 'default' => 175, 'desc' => 'درصد؛ ۱۷۵ یعنی ۱٫۷۵.' ) ),
						moon_field( 'letter_spacing', 'فاصله حروف (px)', 'number', array( 'min' => 0, 'max' => 3, 'default' => 0, 'desc' => 'برای متن فارسی صفر بماند تا حروف به هم نچسبند.' ) ),
						moon_field( 'heading_scale', 'مقیاس تیترها (٪)', 'number', array( 'min' => 85, 'max' => 125, 'default' => 100 ) ),
						moon_field( 'mobile_scale', 'مقیاس متن موبایل (٪)', 'number', array( 'min' => 85, 'max' => 115, 'default' => 100 ) ),
					),
				),
				'layout'     => array(
					'label'  => __( 'چیدمان و استایل', 'moon-theme' ),
					'fields' => array(
						moon_field( 'container_width', 'عرض محتوای سایت (px)', 'number', array( 'min' => 1080, 'max' => 1680, 'default' => 1360 ) ),
						moon_field( 'section_gap', 'فاصله بین سکشن‌ها (px)', 'number', array( 'min' => 32, 'max' => 180, 'default' => 96 ) ),
						moon_field( 'radius', 'شعاع گوشه‌ها (px)', 'number', array( 'min' => 0, 'max' => 40, 'default' => 4 ) ),
						moon_field( 'radius_sm', 'شعاع گوشه‌های کوچک (px)', 'number', array( 'min' => 0, 'max' => 24, 'default' => 2 ) ),
						moon_field( 'border_width', 'ضخامت حاشیه (px)', 'number', array( 'min' => 0, 'max' => 3, 'default' => 1 ) ),
						moon_field( 'shadow', 'سایه', 'select', array( 'options' => $shadow_options, 'default' => 'soft' ) ),
						moon_field( 'button_height', 'ارتفاع دکمه‌ها (px)', 'number', array( 'min' => 36, 'max' => 64, 'default' => 48 ) ),
						moon_field( 'input_height', 'ارتفاع ورودی‌ها (px)', 'number', array( 'min' => 36, 'max' => 64, 'default' => 46 ) ),
						moon_field( 'card_style', 'شکل کارت‌ها', 'select', array( 'options' => $card_styles, 'default' => 'flat' ) ),
						moon_field( 'image_ratio', 'نسبت تصویر کارت محصول', 'select', array(
							'options' => array( 'portrait' => 'عمودی (۳:۴)', 'square' => 'مربع', 'landscape' => 'افقی (۴:۳)' ),
							'default' => 'portrait',
						) ),
					),
				),
				'motion'     => array(
					'label'  => __( 'انیمیشن', 'moon-theme' ),
					'desc'   => __( 'انیمیشن‌ها سبک و با prefers-reduced-motion سازگارند. برای فروشگاه‌های شلوغ می‌توانید همه را خاموش کنید.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'animations', 'انیمیشن‌های رابط', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'anim_reveal', 'نمایش تدریجی سکشن‌ها هنگام اسکرول', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'anim_hover', 'افکت‌های hover روی کارت‌ها', 'checkbox', array( 'default' => 1 ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * هدر
		 * ---------------------------------------------------------------- */
		'header' => array(
			'label' => __( 'هدر', 'moon-theme' ),
			'tabs'  => array(
				'layout'   => array(
					'label'  => __( 'چیدمان', 'moon-theme' ),
					'fields' => array(
						moon_field( 'header_layout', 'نوع هدر', 'select', array(
							'options' => array(
								'glass'    => 'شیشه‌ای معلق (پیش‌فرض)',
								'standard' => 'استاندارد تمام‌عرض',
								'minimal'  => 'مینیمال',
								'centered' => 'لوگو در مرکز',
							),
							'default' => 'glass',
						) ),
						moon_field( 'header_sticky', 'هدر چسبیده به بالای صفحه', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'header_hide_scroll', 'مخفی شدن هنگام اسکرول به پایین و بازگشت هنگام اسکرول به بالا', 'checkbox', array( 'default' => 0 ) ),
						moon_field( 'header_transparent', 'شفاف روی صفحه اصلی', 'checkbox', array( 'default' => 0 ) ),
						moon_field( 'header_height', 'ارتفاع هدر (px)', 'number', array( 'min' => 52, 'max' => 120, 'default' => 72 ) ),
						moon_field( 'header_bg', 'پس‌زمینه هدر', 'color', array( 'default' => '' ) ),
						moon_field( 'header_fg', 'رنگ متن هدر', 'color', array( 'default' => '' ) ),
						moon_field( 'header_cta_text', 'دکمه CTA · متن', 'text', array( 'default' => '' ) ),
						moon_field( 'header_cta_url', 'دکمه CTA · لینک', 'url', array( 'default' => '' ) ),
					),
				),
				'elements' => array(
					'label'  => __( 'عناصر و ترتیب', 'moon-theme' ),
					'desc'   => __( 'با کشیدن هر ردیف ترتیب نمایش را تغییر دهید؛ تیک هر ردیف آن عنصر را در هدر نشان می‌دهد.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'header_elements', 'عناصر هدر', 'sortable', array(
							'options' => array(
								'search'   => 'جستجو',
								'account'  => 'حساب کاربری',
								'wishlist' => 'علاقه‌مندی‌ها',
								'compare'  => 'مقایسه محصولات',
								'cart'     => 'سبد خرید',
								'theme'    => 'کلید حالت روشن/تاریک',
								'cta'      => 'دکمه CTA',
								'social'   => 'شبکه‌های اجتماعی',
							),
							'default'  => 'search:1,account:1,wishlist:1,cart:1,theme:1',
						) ),
					),
				),
				'topbar'   => array(
					'label'  => __( 'نوار اطلاع‌رسانی', 'moon-theme' ),
					'fields' => array(
						moon_field( 'topbar_enabled', 'نمایش نوار بالای هدر', 'checkbox', array( 'default' => 0 ) ),
						moon_field( 'topbar_text', 'متن نوار', 'text', array( 'default' => 'ارسال رایگان برای خرید بالای ۲ میلیون تومان' ) ),
						moon_field( 'topbar_url', 'لینک نوار', 'url', array( 'default' => '' ) ),
						moon_field( 'topbar_bg', 'پس‌زمینه نوار', 'color', array( 'default' => '' ) ),
						moon_field( 'topbar_fg', 'متن نوار', 'color', array( 'default' => '' ) ),
					),
				),
				'mega'     => array(
					'label'  => __( 'مگامنو', 'moon-theme' ),
					'desc'   => __( 'مگامنو از زیرمنوهای خودِ وردپرس ساخته می‌شود: در نمایش → فهرست‌ها، زیرمنوهای آیتم اصلی را بسازید و همان آیتم را مگامنو کنید. کارت پیشنهادی هر آیتم را هم در همان‌جا تعیین می‌کنید.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'mega_enabled', 'فعال بودن مگامنو', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'mega_columns', 'تعداد ستون دسته‌ها', 'number', array( 'min' => 2, 'max' => 6, 'default' => 4 ) ),
						moon_field( 'mega_width', 'عرض پنل مگامنو', 'select', array(
							'options' => array(
								'container' => 'هم‌عرض محتوای سایت',
								'full'      => 'تمام‌عرض صفحه',
							),
							'default' => 'container',
						) ),
						moon_field( 'mega_show_images', 'نمایش تصویر دسته‌ها در مگامنو', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'mega_promo_image', 'تصویر کارت پیشنهادی (شناسه تصویر)', 'number', array( 'min' => 0, 'default' => 0 ) ),
						moon_field( 'mega_promo_title', 'عنوان کارت پیشنهادی', 'text', array( 'default' => 'کالکشن جدید' ) ),
						moon_field( 'mega_promo_text', 'توضیح کوتاه کارت پیشنهادی', 'text', array( 'default' => '' ) ),
						moon_field( 'mega_promo_url', 'لینک کارت پیشنهادی', 'url', array( 'default' => '' ) ),
						moon_field( 'category_strip', 'نوار زیردسته‌ها در صفحه دسته‌بندی', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'category_strip_images', 'نمایش تصویر در نوار زیردسته‌ها', 'checkbox', array( 'default' => 1 ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * فوتر
		 * ---------------------------------------------------------------- */
		'footer' => array(
			'label' => __( 'فوتر', 'moon-theme' ),
			'tabs'  => array(
				'columns' => array(
					'label'  => __( 'ستون‌ها', 'moon-theme' ),
					'footer' => true,
				),
				'content' => array(
					'label'  => __( 'محتوا و تماس', 'moon-theme' ),
					'fields' => array(
						moon_field( 'footer_about', 'معرفی برند', 'textarea', array( 'default' => 'پوشاک لوکس زنانه و مردانه؛ ساخته‌شده برای زمان.' ) ),
						moon_field( 'footer_newsletter', 'فرم خبرنامه در فوتر', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'footer_copyright', 'کپی‌رایت', 'text', array( 'default' => '© ۲۰۲۶ MOON — تمامی حقوق محفوظ است.' ) ),
						moon_field( 'footer_cities', 'شهرها', 'text', array( 'ltr' => true, 'default' => '' ) ),
						moon_field( 'footer_payments', 'روش‌های پرداخت نمایش‌داده‌شده', 'checkboxes', array(
							'options' => array(
								'shetab'   => 'شتاب',
								'zarinpal' => 'زرین‌پال',
								'visa'     => 'Visa',
								'master'   => 'Mastercard',
								'cod'      => 'پرداخت در محل',
							),
							'default' => 'shetab,zarinpal',
						) ),
						moon_field( 'footer_mobile_cols', 'تعداد ستون در موبایل', 'select', array(
							'options' => array( '1' => 'یک ستون', '2' => 'دو ستون' ),
							'default' => '1',
						) ),
					),
				),
				'contact' => array(
					'label'  => __( 'تماس و شبکه‌های اجتماعی', 'moon-theme' ),
					'fields' => array(
						moon_field( 'contact_phone', 'تلفن تماس', 'text', array( 'ltr' => true, 'default' => '' ) ),
						moon_field( 'contact_email', 'ایمیل', 'text', array( 'ltr' => true, 'default' => '' ) ),
						moon_field( 'contact_whatsapp', 'واتس‌اپ (شماره)', 'text', array( 'ltr' => true, 'default' => '' ) ),
						moon_field( 'contact_hours', 'ساعات پاسخگویی', 'text', array( 'default' => '' ) ),
						moon_field( 'contact_address', 'نشانی', 'textarea', array( 'default' => '' ) ),
						moon_field( 'social_instagram', 'اینستاگرام', 'url', array( 'default' => '' ) ),
						moon_field( 'social_telegram', 'تلگرام', 'url', array( 'default' => '' ) ),
						moon_field( 'social_whatsapp', 'لینک واتس‌اپ', 'url', array( 'default' => '' ) ),
						moon_field( 'social_youtube', 'یوتیوب', 'url', array( 'default' => '' ) ),
						moon_field( 'social_pinterest', 'پینترست', 'url', array( 'default' => '' ) ),
						moon_field( 'social_x', 'ایکس (توییتر)', 'url', array( 'default' => '' ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * فروشگاه
		 * ---------------------------------------------------------------- */
		'shop' => array(
			'label' => __( 'فروشگاه', 'moon-theme' ),
			'tabs'  => array(
				'archive' => array(
					'label'  => __( 'صفحه فروشگاه', 'moon-theme' ),
					'fields' => array(
						moon_field( 'shop_columns', 'تعداد ستون', 'select', array(
							'options' => array( '2' => '۲ ستون', '3' => '۳ ستون', '4' => '۴ ستون', '5' => '۵ ستون' ),
							'default' => '4',
						) ),
						moon_field( 'shop_per_page', 'تعداد محصول در صفحه', 'number', array( 'min' => 4, 'max' => 48, 'default' => 12 ) ),
						moon_field( 'shop_view', 'نمایش پیش‌فرض', 'select', array(
							'options' => array( 'grid' => 'شبکه‌ای', 'list' => 'لیستی' ),
							'default' => 'grid',
						) ),
						moon_field( 'shop_view_toggle', 'کلید تغییر شبکه‌ای/لیستی برای کاربر', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'shop_filters', 'محل فیلترها', 'select', array(
							'options' => array( 'sidebar' => 'ستون کنار', 'drawer' => 'کشوی موبایل و دکمه بالای نتایج', 'none' => 'بدون فیلتر' ),
							'default' => 'sidebar',
						) ),
						moon_field( 'shop_pagination', 'صفحه‌بندی', 'select', array(
							'options' => array( 'pages' => 'شماره صفحه', 'more' => 'دکمه «بیشتر»' ),
							'default' => 'pages',
						) ),
						moon_field( 'shop_count', 'نمایش تعداد نتایج', 'checkbox', array( 'default' => 1 ) ),
					),
				),
				'card'    => array(
					'label'  => __( 'کارت محصول', 'moon-theme' ),
					'fields' => array(
						moon_field( 'card_style', 'طرح کارت', 'select', array(
							'options' => array(
								'default'  => 'استاندارد',
								'minimal'  => 'مینیمال',
								'modern'   => 'مدرن (تصویر بزرگ)',
								'detailed' => 'اطلاعات کامل',
							),
							'default' => 'default',
						) ),
						moon_field( 'card_hover_image', 'تصویر دوم هنگام hover', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_badges', 'برچسب تخفیف و جدید', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_rating', 'امتیاز محصول', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_swatches', 'رنگ‌بندی روی کارت', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_quickview', 'مشاهده سریع', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_wishlist', 'افزودن به علاقه‌مندی', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_compare', 'افزودن به مقایسه', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'card_stock', 'نمایش موجودی باقی‌مانده', 'checkbox', array( 'default' => 1 ) ),
					),
				),
				'product' => array(
					'label'  => __( 'صفحه محصول', 'moon-theme' ),
					'fields' => array(
						moon_field( 'product_gallery', 'چیدمان گالری', 'select', array(
							'options' => array(
								'thumbs-bottom' => 'تصاویر کوچک زیر تصویر',
								'thumbs-right'  => 'تصاویر کوچک سمت راست',
								'thumbs-left'   => 'تصاویر کوچک سمت چپ',
								'grid'          => 'گالری شبکه‌ای',
							),
							'default' => 'thumbs-bottom',
						) ),
						moon_field( 'product_zoom', 'زوم تصویر و لایت‌باکس', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'product_sticky', 'خلاصه چسبان هنگام اسکرول', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'product_trust', 'نوار اعتماد (ارسال، اصالت، بازگشت)', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'product_countdown', 'شمارش معکوس تخفیف', 'checkbox', array( 'default' => 0 ) ),
						moon_field( 'product_related', 'تعداد محصولات مرتبط', 'number', array( 'min' => 0, 'max' => 12, 'default' => 4 ) ),
						moon_field( 'product_recent', 'نمایش «اخیراً مشاهده‌شده»', 'checkbox', array( 'default' => 1 ) ),
					),
				),
				'cart'    => array(
					'label'  => __( 'سبد خرید و تسویه', 'moon-theme' ),
					'fields' => array(
						moon_field( 'cart_drawer', 'کشوی سبد خرید پس از افزودن', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'free_ship_bar', 'نوار پیشرفت ارسال رایگان', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'free_ship_threshold', 'حد ارسال رایگان (تومان)', 'number', array( 'min' => 0, 'max' => 100000000, 'default' => 2000000 ) ),
						moon_field( 'sticky_atc', 'دکمه خرید چسبان در موبایل', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'cart_crosssells', 'پیشنهاد محصولات مکمل در سبد', 'checkbox', array( 'default' => 1 ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * حساب کاربری
		 * ---------------------------------------------------------------- */
		'account' => array(
			'label' => __( 'حساب کاربری', 'moon-theme' ),
			'tabs'  => array(
				'login' => array(
					'label'  => __( 'ورود و ثبت‌نام', 'moon-theme' ),
					'desc'   => __( 'ثبت‌نام فقط پس از تأیید کد پیامکی شماره موبایل امکان‌پذیر است؛ این رفتار قابل خاموش‌کردن نیست.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'otp_login', 'نمایش ورود با کد پیامکی', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'otp_register', 'امکان ثبت‌نام با موبایل', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'account_notice', 'متن راهنمای بالای صفحه حساب', 'text', array( 'default' => '' ) ),
						moon_field( 'auth_shortcode', 'شورتکد ورود و ثبت‌نام (افزونه خارجی)', 'text', array(
							'ltr'     => true,
							'default' => '',
							'desc'    => 'اگر پنل پیامکی یا افزونه ورود/ثبت‌نام جداگانه دارید، شورتکد آن را اینجا بگذارید (نمونه: [digits_phone] یا [my-sms-login]). با پر شدن این فیلد، فرم داخلی قالب جای خود را به همان فرم می‌دهد و ورود پیامکی قالب خاموش می‌شود.',
						) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * موبایل
		 * ---------------------------------------------------------------- */
		'mobile' => array(
			'label' => __( 'موبایل', 'moon-theme' ),
			'tabs'  => array(
				'layout' => array(
					'label'  => __( 'چیدمان موبایل', 'moon-theme' ),
					'fields' => array(
						moon_field( 'bottom_nav', 'ناوبری پایین موبایل', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'bottom_nav_items', 'آیتم‌های ناوبری پایین', 'checkboxes', array(
							'options' => array(
								'home'    => 'خانه',
								'shop'    => 'فروشگاه',
								'search'  => 'جستجو',
								'wishlist' => 'علاقه‌مندی',
								'account' => 'حساب',
								'cart'    => 'سبد خرید',
							),
							'default' => 'home,shop,search,account,cart',
						) ),
						moon_field( 'hero_mobile_min_height', 'حداقل ارتفاع هیرو در موبایل (px)', 'number', array( 'min' => 380, 'max' => 900, 'default' => 640 ) ),
						moon_field( 'mobile_hide_scrollstory', 'پنهان کردن داستان اسکرولی در موبایل', 'checkbox', array( 'default' => 0 ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * عملکرد
		 * ---------------------------------------------------------------- */
		'performance' => array(
			'label' => __( 'عملکرد و سرعت', 'moon-theme' ),
			'tabs'  => array(
				'perf' => array(
					'label'  => __( 'بهینه‌سازی', 'moon-theme' ),
					'fields' => array(
						moon_field( 'perf_disable_emojis', 'حذف اسکریپت ایموجی وردپرس', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'perf_disable_embed', 'حذف اسکریپت embed وردپرس', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'perf_lazy_images', 'بارگذاری تنبل تصاویر', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'perf_preload_fonts', 'preload فونت اصلی', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'perf_disable_blocks_css', 'حذف CSS بلاک‌ها در صفحات غیرمحتوایی', 'checkbox', array( 'default' => 1 ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * سئو و کد سفارشی
		 * ---------------------------------------------------------------- */
		'seo' => array(
			'label' => __( 'سئو و کد سفارشی', 'moon-theme' ),
			'tabs'  => array(
				'seo'    => array(
					'label'  => __( 'سئو', 'moon-theme' ),
					'desc'   => __( 'اگر Rank Math یا Yoast فعال باشد، تولید متاتگ و Schema تکراری خودکار متوقف می‌شود.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'seo_meta', 'متاتگ‌های Open Graph و توضیحات', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'seo_schema', 'Schema (محصول، مقاله، سازمان، مسیر)', 'checkbox', array( 'default' => 1 ) ),
						moon_field( 'seo_archive_canonical', 'canonical برای صفحات آرشیو و فیلتر', 'checkbox', array( 'default' => 1 ) ),
					),
				),
				'custom' => array(
					'label'  => __( 'کد سفارشی', 'moon-theme' ),
					'desc'   => __( 'این کدها فقط برای مدیر نمایش داده و ذخیره می‌شوند؛ آن‌ها را از منبع مطمئن وارد کنید.', 'moon-theme' ),
					'fields' => array(
						moon_field( 'custom_css', 'CSS سفارشی', 'code', array( 'default' => '' ) ),
						moon_field( 'custom_head', 'کد در head', 'code', array( 'default' => '' ) ),
						moon_field( 'custom_footer', 'کد پیش از بسته شدن body', 'code', array( 'default' => '' ) ),
					),
				),
			),
		),
	);

	/**
	 * Filter the panel schema so a child theme or a site can add its own page.
	 *
	 * @param array $schema Schema.
	 */
	return apply_filters( 'moon_settings_schema', $schema );
}
