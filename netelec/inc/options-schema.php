<?php
/**
 * Settings schema and defaults for the NETELEC theme panel.
 *
 * One option row (`netelec_settings`) stores every theme setting. The schema
 * below drives the admin UI, sanitization and the generated CSS variables, so
 * a new field only has to be declared once.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for every setting.
 *
 * @return array
 */
function netelec_defaults() {
	return array(
		// General / design.
		'logo_id'           => 0,
		'logo_width'        => 168,
		'brand_name'        => 'NETELEC',
		'brand_tagline'     => 'تجهیزات تخصصی شبکه',
		'primary_color'     => '#2563EB',
		'dark_color'        => '#0F172A',
		'text_color'        => '#172033',
		'muted_color'       => '#64748B',
		'bg_color'          => '#F7F9FC',
		'card_color'        => '#FFFFFF',
		'border_color'      => '#E7ECF3',
		'success_color'     => '#16A34A',
		'sale_color'        => '#DC2626',
		'button_text_color' => '#FFFFFF',
		'header_bg'         => '#FFFFFF',
		'footer_bg'         => '#0F172A',
		'radius'            => 16,
		'container'         => 1280,
		'shadow'            => 'soft',
		'font_stack'        => 'vazirmatn',

		// Header.
		'show_topbar'    => 1,
		'topbar_text'    => 'ارسال سریع به سراسر ایران · ضمانت اصالت کالا · مشاوره تخصصی خرید',
		'phone'          => '۰۲۱-۰۰۰۰۰۰۰۰',
		'show_search'    => 1,
		'show_account'   => 1,
		'show_wishlist'  => 1,
		'show_cart'      => 1,
		'sticky_header'  => 1,
		'header_height'  => 78,
		'header_note'    => 'پشتیبانی خرید:',
		'mobile_columns' => 2,
		'mobile_hide_topbar' => 1,

		// Homepage.
		'home_order'         => 'hero,benefits,categories,bestsellers,sale,new_arrivals,promo,blog',
		'home_enabled'       => array( 'hero', 'benefits', 'categories', 'bestsellers', 'sale', 'new_arrivals', 'promo', 'blog' ),
		'hero_badge'         => 'تجهیزات تخصصی شبکه و الکترونیک',
		'hero_title'         => 'زیرساخت شبکه‌ای مطمئن، از کابل تا سوئیچ',
		'hero_desc'          => 'کابل شبکه، سوکت و کیستون، تجهیزات پسیو و اکتیو را با ضمانت اصالت و مشاوره تخصصی انتخاب کنید.',
		'hero_btn_text'      => 'مشاهده محصولات',
		'hero_btn_url'       => '/shop/',
		'hero_btn2_text'     => 'مشاوره خرید',
		'hero_btn2_url'      => '/contact/',
		'hero_image_id'      => 0,
		'hero_points'        => "ضمانت اصالت کالا\nارسال سریع و مطمئن\nپشتیبانی تخصصی",
		'categories_title'   => 'خرید بر اساس دسته‌بندی',
		'categories_count'   => 6,
		'bestsellers_title'  => 'پرفروش‌ترین محصولات',
		'bestsellers_count'  => 4,
		'sale_title'         => 'محصولات تخفیف‌دار',
		'sale_count'         => 4,
		'new_title'          => 'جدیدترین محصولات',
		'new_count'          => 4,
		'blog_title'         => 'مقالات و راهنمای شبکه',
		'blog_count'         => 3,
		'benefit_1_title'    => 'ارسال سریع',
		'benefit_1_desc'     => 'ارسال به سراسر ایران',
		'benefit_2_title'    => 'ضمانت اصالت',
		'benefit_2_desc'     => 'تأمین از منابع معتبر',
		'benefit_3_title'    => 'پشتیبانی تخصصی',
		'benefit_3_desc'     => 'مشاوره پیش و پس از خرید',
		'benefit_4_title'    => 'پرداخت امن',
		'benefit_4_desc'     => 'از طریق درگاه بانکی',
		'promo_badge'        => 'پیشنهاد ویژه',
		'promo_title'        => 'تجهیزات شبکه را حرفه‌ای انتخاب کنید',
		'promo_text'         => 'در انتخاب کابل، سوکت و تجهیزات اکتیو شک نکنید؛ کارشناسان NETELEC در کنار شما هستند.',
		'promo_btn_text'     => 'دریافت مشاوره',
		'promo_btn_url'      => '/contact/',
		'promo_image_id'     => 0,

		// Products.
		'shop_columns'         => 4,
		'shop_per_page'        => 12,
		'show_sale_badge'      => 1,
		'show_new_badge'       => 1,
		'show_stock'           => 1,
		'show_rating'          => 1,
		'show_card_category'   => 1,
		'quick_view'           => 1,
		'wishlist_enabled'     => 1,
		'compare_enabled'      => 1,
		'show_sidebar_filters' => 1,
		'show_related'         => 1,
		'related_count'        => 4,
		'show_upsells'         => 1,
		'show_recently_viewed' => 1,
		'single_sticky_bar'    => 1,

		// Blog.
		'blog_columns'     => 3,
		'blog_show_meta'   => 1,
		'blog_show_excerpt' => 1,
		'blog_show_author' => 1,
		'blog_show_readtime' => 1,
		'blog_sidebar'     => 0,
		'blog_related'     => 1,

		// Footer.
		'footer_about'     => 'NETELEC مرجع تخصصی تأمین تجهیزات شبکه و الکترونیک؛ از کابل و سوکت تا تجهیزات پسیو و اکتیو، برای راه‌اندازی شبکه‌ای پایدار.',
		'footer_phone'     => '۰۲۱-۰۰۰۰۰۰۰۰',
		'footer_email'     => 'info@example.com',
		'footer_address'   => 'تهران، خیابان ...',
		'footer_hours'     => 'شنبه تا پنجشنبه، ۹ تا ۱۸',
		'social_instagram' => '',
		'social_telegram'  => '',
		'social_whatsapp'  => '',
		'social_linkedin'  => '',
		'footer_badges'    => "ضمانت اصالت کالا\nارسال سریع\nپشتیبانی تخصصی\nپرداخت امن",
		'copyright'        => 'تمامی حقوق مادی و معنوی این وب‌سایت متعلق به NETELEC است.',

		// Mobile.
		'mobile_sticky_cart'      => 1,
		'mobile_floating_buttons' => 1,

		// SMS / OTP.
		'otp_enabled'        => 0,
		'sms_provider'       => 'none',
		'sms_api_key'        => '',
		'sms_username'       => '',
		'sms_password'       => '',
		'sms_sender'         => '',
		'sms_pattern'        => '',
		'otp_length'         => 5,
		'otp_expiry'         => 3,
		'otp_resend_delay'   => 60,
		'otp_max_attempts'   => 5,
		'otp_hourly_limit'   => 10,
	);
}

/**
 * Field definition helper.
 *
 * @param string $type    Field type.
 * @param string $label   Label.
 * @param mixed  $default Default value.
 * @param array  $extra   Extra arguments.
 * @return array
 */
function netelec_field( $type, $label, $default = '', $extra = array() ) {
	return array_merge(
		array(
			'type'    => $type,
			'label'   => $label,
			'default' => $default,
		),
		$extra
	);
}

/**
 * Full settings schema: tab => sections => fields.
 *
 * @return array
 */
function netelec_options_schema() {
	$d = netelec_defaults();

	return array(
		'general' => array(
			'label'    => 'عمومی و ظاهر',
			'icon'     => 'dashicons-admin-generic',
			'sections' => array(
				'branding' => array(
					'title'  => 'برند و لوگو',
					'desc'   => 'لوگوی سایت از «تنظیمات > سفارشی‌سازی > هویت سایت» هم قابل تنظیم است؛ اولویت با لوگوی همین پنل است.',
					'fields' => array(
						'logo_id'        => netelec_field( 'media', 'لوگو', $d['logo_id'], array( 'desc' => 'تصویر PNG یا SVG با پس‌زمینه شفاف، ارتفاع پیشنهادی ۶۴ پیکسل.' ) ),
						'logo_width'     => netelec_field( 'number', 'عرض لوگو (پیکسل)', $d['logo_width'], array( 'min' => 80, 'max' => 320 ) ),
						'brand_name'     => netelec_field( 'text', 'نام برند', $d['brand_name'] ),
						'brand_tagline'  => netelec_field( 'text', 'شعار کوتاه برند', $d['brand_tagline'] ),
					),
				),
				'colors'   => array(
					'title'  => 'رنگ‌ها',
					'desc'   => 'این رنگ‌ها به‌صورت متغیرهای CSS روی کل سایت اعمال می‌شوند.',
					'fields' => array(
						'primary_color'     => netelec_field( 'color', 'رنگ اصلی', $d['primary_color'] ),
						'dark_color'        => netelec_field( 'color', 'رنگ تیره (تاکید)', $d['dark_color'] ),
						'text_color'        => netelec_field( 'color', 'رنگ متن اصلی', $d['text_color'] ),
						'muted_color'       => netelec_field( 'color', 'رنگ متن ثانویه', $d['muted_color'] ),
						'bg_color'          => netelec_field( 'color', 'پس‌زمینه سایت', $d['bg_color'] ),
						'card_color'        => netelec_field( 'color', 'پس‌زمینه کارت‌ها', $d['card_color'] ),
						'border_color'      => netelec_field( 'color', 'رنگ حاشیه‌ها', $d['border_color'] ),
						'success_color'     => netelec_field( 'color', 'رنگ موفقیت / موجودی', $d['success_color'] ),
						'sale_color'        => netelec_field( 'color', 'رنگ تخفیف', $d['sale_color'] ),
						'button_text_color' => netelec_field( 'color', 'رنگ متن دکمه‌ها', $d['button_text_color'] ),
						'header_bg'         => netelec_field( 'color', 'پس‌زمینه هدر', $d['header_bg'] ),
						'footer_bg'         => netelec_field( 'color', 'پس‌زمینه فوتر', $d['footer_bg'] ),
					),
				),
				'layout'   => array(
					'title'  => 'چیدمان',
					'fields' => array(
						'container'   => netelec_field( 'number', 'عرض محتوای سایت (پیکسل)', $d['container'], array( 'min' => 960, 'max' => 1600 ) ),
						'radius'      => netelec_field( 'number', 'گردی گوشه‌ها (پیکسل)', $d['radius'], array( 'min' => 0, 'max' => 32 ) ),
						'shadow'      => netelec_field( 'select', 'شدت سایه‌ها', $d['shadow'], array( 'options' => array( 'none' => 'بدون سایه', 'soft' => 'ملایم', 'medium' => 'متوسط', 'strong' => 'قوی' ) ) ),
						'font_stack'  => netelec_field( 'select', 'فونت سایت', $d['font_stack'], array( 'options' => array( 'vazirmatn' => 'وزیرمتن (پیشنهادی)', 'system' => 'فونت سیستم' ) ) ),
					),
				),
			),
		),
		'header' => array(
			'label'    => 'هدر',
			'icon'     => 'dashicons-align-center',
			'sections' => array(
				'header_main' => array(
					'title'  => 'نوار بالایی و هدر',
					'fields' => array(
						'show_topbar'   => netelec_field( 'checkbox', 'نمایش نوار اطلاعیه بالای هدر', $d['show_topbar'] ),
						'topbar_text'   => netelec_field( 'text', 'متن نوار بالایی', $d['topbar_text'] ),
						'phone'         => netelec_field( 'text', 'شماره تماس هدر', $d['phone'] ),
						'header_note'   => netelec_field( 'text', 'برچسب کنار شماره تماس', $d['header_note'] ),
						'header_height' => netelec_field( 'number', 'ارتفاع هدر (پیکسل)', $d['header_height'], array( 'min' => 56, 'max' => 140 ) ),
						'sticky_header' => netelec_field( 'checkbox', 'هدر چسبان هنگام اسکرول', $d['sticky_header'] ),
					),
				),
				'header_tools' => array(
					'title'  => 'ابزارهای هدر',
					'fields' => array(
						'show_search'   => netelec_field( 'checkbox', 'نمایش جستجوی زنده محصولات', $d['show_search'] ),
						'show_account'  => netelec_field( 'checkbox', 'نمایش ورود / حساب کاربری', $d['show_account'] ),
						'show_wishlist' => netelec_field( 'checkbox', 'نمایش علاقه‌مندی‌ها', $d['show_wishlist'] ),
						'show_cart'     => netelec_field( 'checkbox', 'نمایش سبد خرید با تعداد و مبلغ', $d['show_cart'] ),
					),
				),
			),
		),
		'home' => array(
			'label'    => 'صفحه اصلی',
			'icon'     => 'dashicons-admin-home',
			'sections' => array(
				'sections' => array(
					'title'  => 'سکشن‌ها و ترتیب نمایش',
					'desc'   => 'با کشیدن هر ردیف ترتیب را تغییر دهید و با تیک، نمایش آن سکشن را روشن یا خاموش کنید.',
					'fields' => array(
						'home_sections' => netelec_field(
							'sections',
							'سکشن‌های صفحه اصلی',
							'',
							array(
								'options' => array(
									'hero'         => 'بنر اصلی',
									'benefits'     => 'مزایای فروشگاه',
									'categories'   => 'دسته‌بندی‌های منتخب',
									'bestsellers'  => 'پرفروش‌ترین محصولات',
									'sale'         => 'محصولات تخفیف‌دار',
									'new_arrivals' => 'جدیدترین محصولات',
									'promo'        => 'بنر تبلیغاتی',
									'blog'         => 'مقالات منتخب',
								),
							)
						),
					),
				),
				'hero' => array(
					'title'  => 'بنر اصلی',
					'fields' => array(
						'hero_badge'     => netelec_field( 'text', 'برچسب بالای عنوان', $d['hero_badge'] ),
						'hero_title'     => netelec_field( 'text', 'عنوان اصلی', $d['hero_title'] ),
						'hero_desc'      => netelec_field( 'textarea', 'توضیحات', $d['hero_desc'] ),
						'hero_btn_text'  => netelec_field( 'text', 'متن دکمه اصلی', $d['hero_btn_text'] ),
						'hero_btn_url'   => netelec_field( 'text', 'لینک دکمه اصلی', $d['hero_btn_url'] ),
						'hero_btn2_text' => netelec_field( 'text', 'متن دکمه دوم', $d['hero_btn2_text'] ),
						'hero_btn2_url'  => netelec_field( 'text', 'لینک دکمه دوم', $d['hero_btn2_url'] ),
						'hero_image_id'  => netelec_field( 'media', 'تصویر بنر', $d['hero_image_id'], array( 'desc' => 'اگر خالی بماند، تصویر گرافیکی پیش‌فرض قالب نمایش داده می‌شود.' ) ),
						'hero_points'    => netelec_field( 'textarea', 'مزیت‌های بنر (هر خط یک مورد)', $d['hero_points'] ),
					),
				),
				'home_products' => array(
					'title'  => 'عنوان و تعداد بخش‌ها',
					'fields' => array(
						'categories_title'  => netelec_field( 'text', 'عنوان دسته‌بندی‌ها', $d['categories_title'] ),
						'categories_count'  => netelec_field( 'number', 'تعداد دسته‌بندی', $d['categories_count'], array( 'min' => 3, 'max' => 12 ) ),
						'bestsellers_title' => netelec_field( 'text', 'عنوان پرفروش‌ها', $d['bestsellers_title'] ),
						'bestsellers_count' => netelec_field( 'number', 'تعداد پرفروش‌ها', $d['bestsellers_count'], array( 'min' => 2, 'max' => 12 ) ),
						'sale_title'        => netelec_field( 'text', 'عنوان تخفیف‌ها', $d['sale_title'] ),
						'sale_count'        => netelec_field( 'number', 'تعداد تخفیف‌ها', $d['sale_count'], array( 'min' => 2, 'max' => 12 ) ),
						'new_title'         => netelec_field( 'text', 'عنوان جدیدترین‌ها', $d['new_title'] ),
						'new_count'         => netelec_field( 'number', 'تعداد جدیدترین‌ها', $d['new_count'], array( 'min' => 2, 'max' => 12 ) ),
						'blog_title'        => netelec_field( 'text', 'عنوان مقالات', $d['blog_title'] ),
						'blog_count'        => netelec_field( 'number', 'تعداد مقالات', $d['blog_count'], array( 'min' => 1, 'max' => 6 ) ),
					),
				),
				'benefits' => array(
					'title'  => 'مزایای فروشگاه',
					'desc'   => 'چهار مزیت که زیر بنر اصلی نمایش داده می‌شود. عنوان خالی، آن مورد را حذف می‌کند.',
					'fields' => array(
						'benefit_1_title' => netelec_field( 'text', 'مزیت ۱ — عنوان', $d['benefit_1_title'] ),
						'benefit_1_desc'  => netelec_field( 'text', 'مزیت ۱ — توضیح', $d['benefit_1_desc'] ),
						'benefit_2_title' => netelec_field( 'text', 'مزیت ۲ — عنوان', $d['benefit_2_title'] ),
						'benefit_2_desc'  => netelec_field( 'text', 'مزیت ۲ — توضیح', $d['benefit_2_desc'] ),
						'benefit_3_title' => netelec_field( 'text', 'مزیت ۳ — عنوان', $d['benefit_3_title'] ),
						'benefit_3_desc'  => netelec_field( 'text', 'مزیت ۳ — توضیح', $d['benefit_3_desc'] ),
						'benefit_4_title' => netelec_field( 'text', 'مزیت ۴ — عنوان', $d['benefit_4_title'] ),
						'benefit_4_desc'  => netelec_field( 'text', 'مزیت ۴ — توضیح', $d['benefit_4_desc'] ),
					),
				),
				'promo' => array(
					'title'  => 'بنر تبلیغاتی',
					'fields' => array(
						'promo_badge'    => netelec_field( 'text', 'برچسب بنر', $d['promo_badge'] ),
						'promo_title'    => netelec_field( 'text', 'عنوان بنر', $d['promo_title'] ),
						'promo_text'     => netelec_field( 'textarea', 'متن بنر', $d['promo_text'] ),
						'promo_btn_text' => netelec_field( 'text', 'متن دکمه', $d['promo_btn_text'] ),
						'promo_btn_url'  => netelec_field( 'text', 'لینک دکمه', $d['promo_btn_url'] ),
						'promo_image_id' => netelec_field( 'media', 'تصویر بنر', $d['promo_image_id'] ),
					),
				),
			),
		),
		'products' => array(
			'label'    => 'فروشگاه',
			'icon'     => 'dashicons-cart',
			'sections' => array(
				'grid' => array(
					'title'  => 'شبکه محصولات',
					'fields' => array(
						'shop_columns'         => netelec_field( 'select', 'تعداد ستون در دسکتاپ', $d['shop_columns'], array( 'options' => array( '3' => '۳ ستون', '4' => '۴ ستون', '5' => '۵ ستون' ) ) ),
						'shop_per_page'        => netelec_field( 'number', 'تعداد محصول در هر صفحه', $d['shop_per_page'], array( 'min' => 4, 'max' => 48 ) ),
						'show_sidebar_filters' => netelec_field( 'checkbox', 'نمایش ستون فیلترها در فروشگاه', $d['show_sidebar_filters'] ),
					),
				),
				'card' => array(
					'title'  => 'کارت محصول',
					'fields' => array(
						'show_sale_badge'    => netelec_field( 'checkbox', 'نشان تخفیف', $d['show_sale_badge'] ),
						'show_new_badge'     => netelec_field( 'checkbox', 'نشان محصول جدید', $d['show_new_badge'] ),
						'show_stock'         => netelec_field( 'checkbox', 'وضعیت موجودی', $d['show_stock'] ),
						'show_rating'        => netelec_field( 'checkbox', 'امتیاز و تعداد نظرات', $d['show_rating'] ),
						'show_card_category' => netelec_field( 'checkbox', 'دسته‌بندی محصول روی کارت', $d['show_card_category'] ),
						'quick_view'         => netelec_field( 'checkbox', 'مشاهده سریع محصول', $d['quick_view'] ),
						'wishlist_enabled'   => netelec_field( 'checkbox', 'دکمه علاقه‌مندی‌ها', $d['wishlist_enabled'] ),
						'compare_enabled'    => netelec_field( 'checkbox', 'دکمه مقایسه محصولات', $d['compare_enabled'] ),
					),
				),
				'single' => array(
					'title'  => 'صفحه محصول',
					'fields' => array(
						'show_related'         => netelec_field( 'checkbox', 'نمایش محصولات مرتبط', $d['show_related'] ),
						'related_count'        => netelec_field( 'number', 'تعداد محصولات مرتبط', $d['related_count'], array( 'min' => 2, 'max' => 8 ) ),
						'show_upsells'         => netelec_field( 'checkbox', 'نمایش محصولات مکمل (Upsell)', $d['show_upsells'] ),
						'show_recently_viewed' => netelec_field( 'checkbox', 'نمایش محصولات اخیراً مشاهده‌شده', $d['show_recently_viewed'] ),
						'single_sticky_bar'    => netelec_field( 'checkbox', 'نوار خرید چسبان در موبایل', $d['single_sticky_bar'] ),
					),
				),
			),
		),
		'blog' => array(
			'label'    => 'وبلاگ',
			'icon'     => 'dashicons-admin-post',
			'sections' => array(
				'blog_main' => array(
					'title'  => 'فهرست و کارت مقاله',
					'fields' => array(
						'blog_columns'       => netelec_field( 'select', 'تعداد ستون', $d['blog_columns'], array( 'options' => array( '1' => '۱ ستون', '2' => '۲ ستون', '3' => '۳ ستون' ) ) ),
						'blog_show_meta'     => netelec_field( 'checkbox', 'نمایش دسته‌بندی و تاریخ', $d['blog_show_meta'] ),
						'blog_show_author'   => netelec_field( 'checkbox', 'نمایش نویسنده', $d['blog_show_author'] ),
						'blog_show_readtime' => netelec_field( 'checkbox', 'زمان تقریبی مطالعه', $d['blog_show_readtime'] ),
						'blog_show_excerpt'  => netelec_field( 'checkbox', 'نمایش خلاصه مقاله', $d['blog_show_excerpt'] ),
						'blog_sidebar'       => netelec_field( 'checkbox', 'نمایش ستون کناری', $d['blog_sidebar'] ),
						'blog_related'       => netelec_field( 'checkbox', 'نمایش مقالات مرتبط', $d['blog_related'] ),
					),
				),
			),
		),
		'footer' => array(
			'label'    => 'فوتر',
			'icon'     => 'dashicons-editor-insertmore',
			'sections' => array(
				'footer_main' => array(
					'title'  => 'محتوای فوتر',
					'fields' => array(
						'footer_about'   => netelec_field( 'textarea', 'توضیح کوتاه برند', $d['footer_about'] ),
						'footer_phone'   => netelec_field( 'text', 'شماره تماس', $d['footer_phone'] ),
						'footer_email'   => netelec_field( 'text', 'ایمیل', $d['footer_email'] ),
						'footer_address' => netelec_field( 'text', 'آدرس', $d['footer_address'] ),
						'footer_hours'   => netelec_field( 'text', 'ساعات پاسخ‌گویی', $d['footer_hours'] ),
						'footer_badges'  => netelec_field( 'textarea', 'نمادهای اعتماد (هر خط یک مورد)', $d['footer_badges'] ),
						'copyright'      => netelec_field( 'text', 'متن کپی‌رایت', $d['copyright'] ),
					),
				),
				'footer_social' => array(
					'title'  => 'شبکه‌های اجتماعی',
					'desc'   => 'آدرس کامل صفحه را وارد کنید؛ فیلد خالی نمایش داده نمی‌شود.',
					'fields' => array(
						'social_instagram' => netelec_field( 'url', 'اینستاگرام', $d['social_instagram'] ),
						'social_telegram'  => netelec_field( 'url', 'تلگرام', $d['social_telegram'] ),
						'social_whatsapp'  => netelec_field( 'url', 'واتساپ', $d['social_whatsapp'] ),
						'social_linkedin'  => netelec_field( 'url', 'لینکدین', $d['social_linkedin'] ),
					),
				),
			),
		),
		'mobile' => array(
			'label'    => 'موبایل',
			'icon'     => 'dashicons-smartphone',
			'sections' => array(
				'mobile_main' => array(
					'title'  => 'تنظیمات موبایل',
					'fields' => array(
						'mobile_columns'          => netelec_field( 'select', 'تعداد ستون محصولات در موبایل', $d['mobile_columns'], array( 'options' => array( '1' => '۱ ستون', '2' => '۲ ستون' ) ) ),
						'mobile_hide_topbar'      => netelec_field( 'checkbox', 'مخفی کردن نوار بالایی در موبایل', $d['mobile_hide_topbar'] ),
						'mobile_sticky_cart'      => netelec_field( 'checkbox', 'نوار خرید چسبان در صفحه محصول', $d['mobile_sticky_cart'] ),
						'mobile_floating_buttons' => netelec_field( 'checkbox', 'دکمه‌های شناور (بازگشت به بالا و واتساپ)', $d['mobile_floating_buttons'] ),
					),
				),
			),
		),
		'sms' => array(
			'label'    => 'ورود پیامکی',
			'icon'     => 'dashicons-smartphone',
			'sections' => array(
				'sms_general' => array(
					'title'  => 'اتصال سرویس پیامک',
					'desc'   => 'کلید و اطلاعات سرویس فقط در دیتابیس وردپرس ذخیره می‌شود و هرگز در سمت کاربر بارگذاری نمی‌شود.',
					'fields' => array(
						'otp_enabled'  => netelec_field( 'checkbox', 'فعال‌سازی ورود و ثبت‌نام با موبایل', $d['otp_enabled'], array( 'desc' => 'تا زمانی که سرویس پیامک تنظیم و تست نشود، این گزینه را روشن نکنید.' ) ),
						'sms_provider' => netelec_field( 'select', 'سرویس‌دهنده', $d['sms_provider'], array( 'options' => netelec_sms_provider_choices() ) ),
						'sms_api_key'  => netelec_field( 'password', 'API Key', $d['sms_api_key'] ),
						'sms_username' => netelec_field( 'text', 'نام کاربری (در صورت نیاز)', $d['sms_username'] ),
						'sms_password' => netelec_field( 'password', 'رمز عبور (در صورت نیاز)', $d['sms_password'] ),
						'sms_sender'   => netelec_field( 'text', 'شماره خط ارسال', $d['sms_sender'] ),
						'sms_pattern'  => netelec_field( 'text', 'کد الگوی پیامک (Pattern)', $d['sms_pattern'], array( 'desc' => 'در صورت خالی بودن، پیام متنی معمولی ارسال می‌شود.' ) ),
					),
				),
				'sms_security' => array(
					'title'  => 'تنظیمات امنیتی کد',
					'fields' => array(
						'otp_length'       => netelec_field( 'number', 'طول کد', $d['otp_length'], array( 'min' => 4, 'max' => 8 ) ),
						'otp_expiry'       => netelec_field( 'number', 'اعتبار کد (دقیقه)', $d['otp_expiry'], array( 'min' => 1, 'max' => 15 ) ),
						'otp_resend_delay' => netelec_field( 'number', 'فاصله ارسال مجدد (ثانیه)', $d['otp_resend_delay'], array( 'min' => 30, 'max' => 300 ) ),
						'otp_max_attempts' => netelec_field( 'number', 'حداکثر تلاش برای هر کد', $d['otp_max_attempts'], array( 'min' => 3, 'max' => 10 ) ),
						'otp_hourly_limit' => netelec_field( 'number', 'حداکثر درخواست در ساعت (هر شماره/IP)', $d['otp_hourly_limit'], array( 'min' => 3, 'max' => 30 ) ),
					),
				),
			),
		),
	);
}
