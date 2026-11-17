<?php
/**
 * Homepage builder: section registry, renderer, draft/publish and sanitising.
 *
 * Every section is an *instance*: it has its own id, type, enabled flag and
 * options, so the same type can appear twice with different content. Rows are
 * stored in `moon_sections` (published) and `moon_sections_draft` (being
 * edited). Rows saved by older versions — `{id: 'hero', enabled: 1}` — are
 * migrated in place, so an existing site keeps its exact homepage.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Section registry and rendering.
 */
class Moon_Sections {

	/**
	 * Maximum instances the builder accepts.
	 */
	const MAX_ROWS = 30;

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_post_moon_builder', array( __CLASS__, 'handle_builder' ) );
	}

	/* ----------------------------------------------------------------------
	 * Registry
	 * -------------------------------------------------------------------- */

	/**
	 * Every available section type.
	 *
	 * Legacy types render the theme's original template and keep reading the
	 * content options they always used; only the shared layout options are
	 * per instance. New types carry their own content fields.
	 *
	 * @return array
	 */
	public static function registry() {
		$icons = array(
			'gem'          => 'Gem',
			'shield-check' => 'Shield',
			'truck'        => 'Truck',
			'headphones'   => 'Headphones',
			'star'         => 'Star',
			'clock'        => 'Clock',
			'map-pin'      => 'Map pin',
			'mail'         => 'Mail',
			'phone'        => 'Phone',
			'rotate-ccw'   => 'Rotate',
		);

		$registry = array(
			/* ----- Original design sections ------------------------------- */
			'hero'         => array(
				'label'  => 'هیرو',
				'desc'   => 'بخش تمام‌صفحه با تصویر و عنوان اصلی.',
				'legacy' => 'hero',
				'fields' => array(),
			),
			'categories'   => array(
				'label'  => 'دسته‌بندی‌ها',
				'desc'   => 'دو کارت کالکشن زنانه و مردانه.',
				'legacy' => 'categories',
				'fields' => array(),
			),
			'rack'         => array(
				'label'  => 'رگال تعاملی',
				'desc'   => 'لباس‌های آویزان روی رگال با پنل جزئیات.',
				'legacy' => 'rack',
				'fields' => array(),
			),
			'studio'       => array(
				'label'  => 'استودیو استایل',
				'desc'   => 'تغییر استایل مدل با تصاویر آماده.',
				'legacy' => 'studio',
				'fields' => array(),
			),
			'products'     => array(
				'label'  => 'محصولات',
				'desc'   => 'کاروسل افقی محصولات ووکامرس.',
				'legacy' => 'products',
				'fields' => array(),
			),
			'campaign'     => array(
				'label'  => 'کمپین',
				'desc'   => 'بخش تصویری تمام‌عرض با پارالاکس.',
				'legacy' => 'campaign',
				'fields' => array(),
			),
			'story'        => array(
				'label'  => 'داستان برند',
				'desc'   => 'متن برند، تصویر آتلیه و مزیت‌های خرید.',
				'legacy' => 'story',
				'fields' => array(),
			),
			'scrollstory'  => array(
				'label'  => 'داستان اسکرولی',
				'desc'   => 'سه صحنه متحرک وابسته به اسکرول.',
				'legacy' => 'scrollstory',
				'fields' => array(),
			),
			'testimonials' => array(
				'label'  => 'نظرات مشتریان',
				'desc'   => 'اسلایدشو خودکار نظرات.',
				'legacy' => 'testimonials',
				'fields' => array(),
			),

			/* ----- Builder sections --------------------------------------- */
			'slider'       => array(
				'label'  => 'اسلایدر تصاویر',
				'desc'   => 'تا سه اسلاید تصویری با عنوان و دکمه.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text', array( 'default' => '' ) ),
					moon_field( 'height', 'ارتفاع', 'select', array(
						'options' => array( 'md' => 'متوسط', 'lg' => 'بلند', 'full' => 'تمام‌صفحه' ),
						'default' => 'lg',
					) ),
					moon_field( 'slide1_image', 'اسلاید ۱ · تصویر', 'media' ),
					moon_field( 'slide1_title', 'اسلاید ۱ · عنوان', 'text', array( 'default' => '' ) ),
					moon_field( 'slide1_text', 'اسلاید ۱ · متن', 'text', array( 'default' => '' ) ),
					moon_field( 'slide1_url', 'اسلاید ۱ · لینک', 'url' ),
					moon_field( 'slide2_image', 'اسلاید ۲ · تصویر', 'media' ),
					moon_field( 'slide2_title', 'اسلاید ۲ · عنوان', 'text', array( 'default' => '' ) ),
					moon_field( 'slide2_text', 'اسلاید ۲ · متن', 'text', array( 'default' => '' ) ),
					moon_field( 'slide2_url', 'اسلاید ۲ · لینک', 'url' ),
					moon_field( 'slide3_image', 'اسلاید ۳ · تصویر', 'media' ),
					moon_field( 'slide3_title', 'اسلاید ۳ · عنوان', 'text', array( 'default' => '' ) ),
					moon_field( 'slide3_text', 'اسلاید ۳ · متن', 'text', array( 'default' => '' ) ),
					moon_field( 'slide3_url', 'اسلاید ۳ · لینک', 'url' ),
				),
			),
			'banner'       => array(
				'label'  => 'بنر تبلیغاتی',
				'desc'   => 'یک بنر تمام‌عرض با تصویر، متن و دکمه.',
				'fields' => array(
					moon_field( 'eyebrow', 'برچسب', 'text', array( 'ltr' => true ) ),
					moon_field( 'title', 'عنوان', 'text', array( 'default' => 'کالکشن جدید' ) ),
					moon_field( 'text', 'متن', 'textarea' ),
					moon_field( 'image', 'تصویر', 'media' ),
					moon_field( 'btn_text', 'دکمه · متن', 'text', array( 'default' => 'مشاهده' ) ),
					moon_field( 'btn_url', 'دکمه · لینک', 'url' ),
					moon_field( 'align', 'چیدمان متن', 'select', array(
						'options' => array( 'center' => 'وسط', 'start' => 'ابتدای تصویر' ),
						'default' => 'center',
					) ),
					moon_field( 'overlay', 'لایه تیره روی تصویر (٪)', 'number', array( 'min' => 0, 'max' => 90, 'default' => 45 ) ),
				),
			),
			'banners'      => array(
				'label'  => 'بنر چندستونه',
				'desc'   => 'تا سه بنر کنار هم برای کمپین‌ها.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text' ),
					moon_field( 'b1_image', 'بنر ۱ · تصویر', 'media' ),
					moon_field( 'b1_title', 'بنر ۱ · عنوان', 'text' ),
					moon_field( 'b1_url', 'بنر ۱ · لینک', 'url' ),
					moon_field( 'b2_image', 'بنر ۲ · تصویر', 'media' ),
					moon_field( 'b2_title', 'بنر ۲ · عنوان', 'text' ),
					moon_field( 'b2_url', 'بنر ۲ · لینک', 'url' ),
					moon_field( 'b3_image', 'بنر ۳ · تصویر', 'media' ),
					moon_field( 'b3_title', 'بنر ۳ · عنوان', 'text' ),
					moon_field( 'b3_url', 'بنر ۳ · لینک', 'url' ),
				),
			),
			'collections'  => array(
				'label'  => 'کالکشن‌ها',
				'desc'   => 'دسته‌بندی‌های ووکامرس به‌صورت کارت تصویری.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text', array( 'default' => 'کالکشن‌ها' ) ),
					moon_field( 'count', 'تعداد دسته', 'number', array( 'min' => 2, 'max' => 12, 'default' => 4 ) ),
					moon_field( 'parent', 'فقط زیرمجموعه‌های این دسته (شناسه)', 'number', array( 'min' => 0, 'max' => 999999, 'default' => 0 ) ),
				),
			),
			'features'     => array(
				'label'  => 'مزایای فروشگاه',
				'desc'   => 'چهار مزیت با آیکون، مناسب زیر هیرو.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text' ),
					moon_field( 'f1_icon', 'مزیت ۱ · آیکون', 'select', array( 'options' => $icons, 'default' => 'truck' ) ),
					moon_field( 'f1_title', 'مزیت ۱ · عنوان', 'text', array( 'default' => 'ارسال سریع' ) ),
					moon_field( 'f1_text', 'مزیت ۱ · توضیح', 'text', array( 'default' => 'ارسال رایگان بالای ۲ میلیون تومان' ) ),
					moon_field( 'f2_icon', 'مزیت ۲ · آیکون', 'select', array( 'options' => $icons, 'default' => 'shield-check' ) ),
					moon_field( 'f2_title', 'مزیت ۲ · عنوان', 'text', array( 'default' => 'اصالت کالا' ) ),
					moon_field( 'f2_text', 'مزیت ۲ · توضیح', 'text', array( 'default' => 'ضمانت اورجینال بودن' ) ),
					moon_field( 'f3_icon', 'مزیت ۳ · آیکون', 'select', array( 'options' => $icons, 'default' => 'rotate-ccw' ) ),
					moon_field( 'f3_title', 'مزیت ۳ · عنوان', 'text', array( 'default' => '۷ روز بازگشت' ) ),
					moon_field( 'f3_text', 'مزیت ۳ · توضیح', 'text', array( 'default' => 'بازگشت آسان کالا' ) ),
					moon_field( 'f4_icon', 'مزیت ۴ · آیکون', 'select', array( 'options' => $icons, 'default' => 'headphones' ) ),
					moon_field( 'f4_title', 'مزیت ۴ · عنوان', 'text', array( 'default' => 'پشتیبانی' ) ),
					moon_field( 'f4_text', 'مزیت ۴ · توضیح', 'text', array( 'default' => 'پاسخگویی در ساعات کاری' ) ),
				),
			),
			'brands'       => array(
				'label'  => 'برندها',
				'desc'   => 'ردیف لوگوی برندها.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text' ),
					moon_field( 'logo1', 'لوگو ۱', 'media' ),
					moon_field( 'logo2', 'لوگو ۲', 'media' ),
					moon_field( 'logo3', 'لوگو ۳', 'media' ),
					moon_field( 'logo4', 'لوگو ۴', 'media' ),
					moon_field( 'logo5', 'لوگو ۵', 'media' ),
					moon_field( 'logo6', 'لوگو ۶', 'media' ),
				),
			),
			'promo'        => array(
				'label'  => 'پیشنهاد ویژه',
				'desc'   => 'پیشنهاد با تصویر، دکمه و شمارش معکوس.',
				'fields' => array(
					moon_field( 'eyebrow', 'برچسب', 'text', array( 'ltr' => true, 'default' => 'Limited Offer' ) ),
					moon_field( 'title', 'عنوان', 'text', array( 'default' => 'پیشنهاد این هفته' ) ),
					moon_field( 'text', 'متن', 'textarea' ),
					moon_field( 'image', 'تصویر', 'media' ),
					moon_field( 'btn_text', 'دکمه · متن', 'text', array( 'default' => 'خرید' ) ),
					moon_field( 'btn_url', 'دکمه · لینک', 'url' ),
					moon_field( 'ends_at', 'پایان پیشنهاد (تاریخ)', 'text', array( 'ltr' => true, 'desc' => 'نمونه: 2026-12-31 23:59' ) ),
				),
			),
			'video'        => array(
				'label'  => 'ویدئوی تبلیغاتی',
				'desc'   => 'ویدئوی mp4 یا لینک یوتیوب/آپارات با پوستر.',
				'fields' => array(
					moon_field( 'title', 'عنوان', 'text' ),
					moon_field( 'text', 'متن', 'textarea' ),
					moon_field( 'video_url', 'لینک ویدئو', 'url', array( 'desc' => 'mp4 یا لینک embed.' ) ),
					moon_field( 'poster', 'تصویر پوستر', 'media' ),
				),
			),
			'gallery'      => array(
				'label'  => 'گالری تصاویر',
				'desc'   => 'شبکه تصاویر با لایت‌باکس.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text' ),
					moon_field( 'g1', 'تصویر ۱', 'media' ),
					moon_field( 'g2', 'تصویر ۲', 'media' ),
					moon_field( 'g3', 'تصویر ۳', 'media' ),
					moon_field( 'g4', 'تصویر ۴', 'media' ),
					moon_field( 'g5', 'تصویر ۵', 'media' ),
					moon_field( 'g6', 'تصویر ۶', 'media' ),
				),
			),
			'posts'        => array(
				'label'  => 'مقالات',
				'desc'   => 'آخرین نوشته‌های وبلاگ.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text', array( 'default' => 'مجله MOON' ) ),
					moon_field( 'count', 'تعداد مقاله', 'number', array( 'min' => 2, 'max' => 9, 'default' => 3 ) ),
				),
			),
			'faq'          => array(
				'label'  => 'سوالات متداول',
				'desc'   => 'آکاردئون سوال و جواب.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text', array( 'default' => 'سوالات متداول' ) ),
					moon_field( 'q1', 'سوال ۱', 'text', array( 'default' => 'ارسال چقدر طول می‌کشد؟' ) ),
					moon_field( 'a1', 'پاسخ ۱', 'textarea', array( 'default' => 'سفارش‌ها بین ۲ تا ۴ روز کاری ارسال می‌شوند.' ) ),
					moon_field( 'q2', 'سوال ۲', 'text', array( 'default' => 'امکان بازگشت کالا وجود دارد؟' ) ),
					moon_field( 'a2', 'پاسخ ۲', 'textarea', array( 'default' => 'تا ۷ روز پس از دریافت، در صورت سالم بودن کالا.' ) ),
					moon_field( 'q3', 'سوال ۳', 'text', array( 'default' => '' ) ),
					moon_field( 'a3', 'پاسخ ۳', 'textarea', array( 'default' => '' ) ),
					moon_field( 'q4', 'سوال ۴', 'text', array( 'default' => '' ) ),
					moon_field( 'a4', 'پاسخ ۴', 'textarea', array( 'default' => '' ) ),
				),
			),
			'counters'     => array(
				'label'  => 'شمارنده و آمار',
				'desc'   => 'سه عدد با برچسب.',
				'fields' => array(
					moon_field( 'title', 'عنوان بخش', 'text' ),
					moon_field( 'c1_value', 'عدد ۱', 'text', array( 'default' => '۱۲' ) ),
					moon_field( 'c1_label', 'برچسب ۱', 'text', array( 'default' => 'سال تجربه' ) ),
					moon_field( 'c2_value', 'عدد ۲', 'text', array( 'default' => '۸۵۰۰' ) ),
					moon_field( 'c2_label', 'برچسب ۲', 'text', array( 'default' => 'مشتری راضی' ) ),
					moon_field( 'c3_value', 'عدد ۳', 'text', array( 'default' => '۳۲۰' ) ),
					moon_field( 'c3_label', 'برچسب ۳', 'text', array( 'default' => 'محصول' ) ),
				),
			),
			'newsletter'   => array(
				'label'  => 'خبرنامه',
				'desc'   => 'فرم عضویت در خبرنامه.',
				'fields' => array(
					moon_field( 'eyebrow', 'برچسب', 'text', array( 'ltr' => true, 'default' => 'The Moon Letter' ) ),
					moon_field( 'title', 'عنوان', 'text', array( 'default' => 'اولین نفری باشید که کالکشن بعدی را می‌بیند.' ) ),
					moon_field( 'note', 'پیام پس از عضویت', 'text', array( 'default' => 'ممنون! به زودی خبرتان می‌کنیم.' ) ),
				),
			),
			'html'         => array(
				'label'  => 'HTML سفارشی',
				'desc'   => 'هر محتوای HTML؛ فقط برای مدیران.',
				'admin'  => true,
				'fields' => array(
					moon_field( 'content', 'محتوا', 'code', array( 'default' => '' ) ),
				),
			),
		);

		// Shared layout options every section gets.
		foreach ( $registry as $type => $definition ) {
			$registry[ $type ]['fields'][] = moon_field( 'space', 'فاصله اضافه', 'select', array(
				'options' => array( 'default' => 'پیش‌فرض', 'none' => 'بدون فاصله', 'sm' => 'کم', 'lg' => 'زیاد' ),
				'default' => 'default',
			) );
			$registry[ $type ]['fields'][] = moon_field( 'bg', 'پس‌زمینه بخش', 'color', array( 'default' => '' ) );
			$registry[ $type ]['fields'][] = moon_field( 'hide_mobile', 'پنهان در موبایل', 'checkbox', array( 'default' => 0 ) );
			$registry[ $type ]['fields'][] = moon_field( 'anchor', 'شناسه لنگر (id)', 'text', array( 'ltr' => true, 'default' => '' ) );
		}

		/**
		 * Filter available homepage sections.
		 *
		 * A site or child theme can register its own type without touching the
		 * theme files: add an entry with `fields` and a `render` callable.
		 *
		 * @param array $registry Types.
		 */
		return apply_filters( 'moon_homepage_sections', $registry );
	}

	/**
	 * Default published rows (the shipped homepage).
	 *
	 * @return array
	 */
	public static function default_rows() {
		$rows = array();

		foreach ( array( 'hero', 'categories', 'rack', 'studio', 'products', 'campaign', 'story', 'scrollstory', 'testimonials' ) as $type ) {
			$rows[] = array(
				'id'      => $type,
				'type'    => $type,
				'enabled' => 'testimonials' === $type ? 0 : 1,
				'options' => array(),
			);
		}

		return $rows;
	}

	/**
	 * Normalise a stored row list (legacy rows have no `type`/`options`).
	 *
	 * @param array $rows Rows.
	 * @return array
	 */
	public static function normalize_rows( $rows ) {
		$registry = self::registry();
		$clean    = array();
		$seen     = array();

		if ( ! is_array( $rows ) ) {
			return $clean;
		}

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$id   = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
			$type = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : $id;

			if ( ! $id ) {
				$id = $type;
			}

			if ( ! $type || ! isset( $registry[ $type ] ) || isset( $seen[ $id ] ) ) {
				continue;
			}

			if ( ! empty( $registry[ $type ]['admin'] ) && ! current_user_can( 'unfiltered_html' ) && ! is_admin() ) {
				continue;
			}

			$seen[ $id ] = true;
			$clean[]     = array(
				'id'      => $id,
				'type'    => $type,
				'enabled' => empty( $row['enabled'] ) ? 0 : 1,
				'options' => self::sanitize_options( $type, isset( $row['options'] ) ? $row['options'] : array() ),
			);
		}

		return $clean;
	}

	/**
	 * Sanitise one row list against the registry.
	 *
	 * @param array $rows Rows.
	 * @return array
	 */
	public static function sanitize_rows( $rows ) {
		return array_slice( self::normalize_rows( $rows ), 0, self::MAX_ROWS );
	}

	/**
	 * Sanitise the options of one section.
	 *
	 * @param string $type    Section type.
	 * @param mixed  $options Raw options.
	 * @return array
	 */
	public static function sanitize_options( $type, $options ) {
		$registry = self::registry();
		$options  = is_array( $options ) ? $options : array();
		$clean    = array();

		if ( ! isset( $registry[ $type ] ) ) {
			return $clean;
		}

		foreach ( $registry[ $type ]['fields'] as $field ) {
			$key   = $field['key'];
			$value = isset( $options[ $key ] ) ? $options[ $key ] : ( isset( $field['default'] ) ? $field['default'] : '' );

			$clean[ $key ] = Moon_Settings::sanitize_value( $field, $value );
		}

		return $clean;
	}

	/* ----------------------------------------------------------------------
	 * Published / draft rows
	 * -------------------------------------------------------------------- */

	/**
	 * Published rows (what the front page renders).
	 *
	 * @return array
	 */
	public static function published_rows() {
		$stored = get_option( 'moon_sections', array() );

		if ( ! is_array( $stored ) || empty( $stored ) ) {
			return self::default_rows();
		}

		$rows = self::normalize_rows( $stored );

		return $rows ? $rows : self::default_rows();
	}

	/**
	 * Draft rows (what the builder edits).
	 *
	 * @return array
	 */
	public static function draft_rows() {
		$draft = get_option( 'moon_sections_draft', array() );

		if ( is_array( $draft ) && ! empty( $draft ) ) {
			$rows = self::normalize_rows( $draft );

			if ( $rows ) {
				return $rows;
			}
		}

		return self::published_rows();
	}

	/**
	 * Whether a draft differs from the published homepage.
	 *
	 * @return bool
	 */
	public static function has_draft() {
		$draft     = get_option( 'moon_sections_draft', null );
		$published = get_option( 'moon_sections', null );

		if ( ! is_array( $draft ) || empty( $draft ) ) {
			return false;
		}

		return wp_json_encode( self::normalize_rows( $draft ) ) !== wp_json_encode( self::normalize_rows( $published ) );
	}

	/**
	 * Publish the draft.
	 */
	public static function publish() {
		$rows = self::draft_rows();

		update_option( 'moon_sections', $rows );
		delete_option( 'moon_sections_draft' );
	}

	/**
	 * Throw the draft away.
	 */
	public static function discard_draft() {
		delete_option( 'moon_sections_draft' );
	}

	/* ----------------------------------------------------------------------
	 * Builder actions
	 * -------------------------------------------------------------------- */

	/**
	 * Handle save / add / duplicate / delete / publish / reset.
	 */
	public static function handle_builder() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
		}

		check_admin_referer( 'moon_builder' );

		$action = isset( $_POST['builder_action'] ) ? sanitize_key( wp_unslash( $_POST['builder_action'] ) ) : 'save';
		$rows   = self::draft_rows();
		$notice = 'saved';

		switch ( $action ) {
			case 'add':
				$type = isset( $_POST['section_type'] ) ? sanitize_key( wp_unslash( $_POST['section_type'] ) ) : '';

				if ( $type && isset( self::registry()[ $type ] ) && count( $rows ) < self::MAX_ROWS ) {
					$rows[] = array(
						'id'      => $type . '_' . wp_generate_password( 5, false, false ),
						'type'    => $type,
						'enabled' => 1,
						'options' => self::sanitize_options( $type, array() ),
					);

					$notice = 'added';
				}
				break;

			case 'duplicate':
				$target = isset( $_POST['section_id'] ) ? sanitize_key( wp_unslash( $_POST['section_id'] ) ) : '';
				$next   = array();

				foreach ( $rows as $row ) {
					$next[] = $row;

					if ( $row['id'] === $target && count( $next ) < self::MAX_ROWS ) {
						$copy         = $row;
						$copy['id']   = $row['type'] . '_' . wp_generate_password( 5, false, false );
						$next[]       = $copy;
					}
				}

				$rows   = $next;
				$notice = 'duplicated';
				break;

			case 'delete':
				$target = isset( $_POST['section_id'] ) ? sanitize_key( wp_unslash( $_POST['section_id'] ) ) : '';

				$rows   = array_values( array_filter( $rows, function ( $row ) use ( $target ) {
					return $row['id'] !== $target;
				} ) );
				$notice = 'deleted';
				break;

			case 'publish':
				update_option( 'moon_sections_draft', self::normalize_rows( $rows ) );
				self::publish();

				wp_safe_redirect( add_query_arg( 'moon_notice', 'published', moon_core_admin_url( 'moon-homepage' ) ) );
				exit;

			case 'discard':
				self::discard_draft();

				wp_safe_redirect( add_query_arg( 'moon_notice', 'discarded', moon_core_admin_url( 'moon-homepage' ) ) );
				exit;

			case 'reset':
				delete_option( 'moon_sections' );
				delete_option( 'moon_sections_draft' );

				wp_safe_redirect( add_query_arg( 'moon_notice', 'reset', moon_core_admin_url( 'moon-homepage' ) ) );
				exit;

			default:
				$submitted = isset( $_POST['rows'] ) && is_array( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array();
				$ordered   = array();
				$by_id     = array();

				foreach ( $rows as $row ) {
					$by_id[ $row['id'] ] = $row;
				}

				foreach ( $submitted as $row ) {
					$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';

					if ( ! $id || ! isset( $by_id[ $id ] ) ) {
						continue;
					}

					$current              = $by_id[ $id ];
					$current['enabled']   = empty( $row['enabled'] ) ? 0 : 1;
					$current['options']   = self::sanitize_options( $current['type'], isset( $row['options'] ) ? $row['options'] : array() );
					$ordered[ $id ]       = $current;
					unset( $by_id[ $id ] );
				}

				// Rows the form did not include stay, switched off, so a partial
				// save never loses a section.
				foreach ( $by_id as $row ) {
					$row['enabled'] = 0;
					$ordered[ $row['id'] ] = $row;
				}

				$rows = array_values( $ordered );
				break;
		}

		update_option( 'moon_sections_draft', self::normalize_rows( $rows ) );

		wp_safe_redirect( add_query_arg( 'moon_notice', $notice, moon_core_admin_url( 'moon-homepage' ) ) );
		exit;
	}

	/* ----------------------------------------------------------------------
	 * Rendering
	 * -------------------------------------------------------------------- */

	/**
	 * Enabled rows for the front page (draft when an admin previews).
	 *
	 * @return array
	 */
	public static function render_rows() {
		$preview = isset( $_GET['moon_sections_preview'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification

		$rows = $preview ? self::draft_rows() : self::published_rows();

		return array_values( array_filter( $rows, function ( $row ) {
			return ! empty( $row['enabled'] );
		} ) );
	}

	/**
	 * Render one section instance.
	 *
	 * @param array $row Row.
	 */
	public static function render( $row ) {
		$registry = self::registry();
		$type     = $row['type'];

		if ( ! isset( $registry[ $type ] ) ) {
			return;
		}

		$definition = $registry[ $type ];
		$options    = isset( $row['options'] ) ? $row['options'] : array();

		// Legacy sections keep their original template and read their content
		// from the panel's content tabs; new types are rendered here.
		if ( ! empty( $definition['legacy'] ) ) {
			$file = MOON_THEME_DIR . '/template-parts/homepage/' . $definition['legacy'] . '.php';

			if ( file_exists( $file ) ) {
				$GLOBALS['moon_section'] = $row;
				get_template_part( 'template-parts/homepage/' . $definition['legacy'] );
			}

			return;
		}

		if ( ! empty( $definition['admin'] ) && ! current_user_can( 'unfiltered_html' ) ) {
			return;
		}

		$classes = array( 'moon-sec', 'moon-sec--' . sanitize_html_class( $type ) );

		if ( ! empty( $options['space'] ) && 'default' !== $options['space'] ) {
			$classes[] = 'moon-sec--space-' . sanitize_html_class( $options['space'] );
		}

		if ( ! empty( $options['hide_mobile'] ) ) {
			$classes[] = 'moon-sec--hide-mobile';
		}

		$style = '';

		if ( ! empty( $options['bg'] ) ) {
			$style = 'background:' . $options['bg'];
		}

		$anchor = ! empty( $options['anchor'] ) ? sanitize_title( $options['anchor'] ) : 'moon-sec-' . sanitize_title( $row['id'] );

		echo '<section id="' . esc_attr( $anchor ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';

		$method = 'render_' . $type;

		if ( method_exists( __CLASS__, $method ) ) {
			self::$method( $options );
		} elseif ( isset( $definition['render'] ) && is_callable( $definition['render'] ) ) {
			call_user_func( $definition['render'], $options, $row );
		}

		echo '</section>';
	}

	/**
	 * Section heading helper.
	 *
	 * @param string $title Title.
	 * @param string $eyebrow Eyebrow.
	 */
	private static function heading( $title, $eyebrow = '' ) {
		if ( '' === trim( (string) $title ) && '' === trim( (string) $eyebrow ) ) {
			return;
		}
		?>
		<div class="moon-container">
			<header class="moon-sec-head">
				<?php if ( $eyebrow ) : ?><span class="moon-eyebrow"><?php echo esc_html( $eyebrow ); ?></span><?php endif; ?>
				<?php if ( $title ) : ?><h2 class="moon-sec-head__title"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
			</header>
		</div>
		<?php
	}

	/**
	 * Image or bundled fallback by option key.
	 *
	 * @param array  $options Options.
	 * @param string $key     Media option key.
	 * @param string $alt     Alt text.
	 * @param string $size    Size.
	 * @return string
	 */
	private static function media( $options, $key, $alt = '', $size = 'large' ) {
		$id = isset( $options[ $key ] ) ? (int) $options[ $key ] : 0;

		if ( ! $id ) {
			return '';
		}

		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt, 'loading' => 'lazy' ) );
	}

	/* ---- Slider ---------------------------------------------------------- */

	/**
	 * Image slider.
	 *
	 * @param array $o Options.
	 */
	private static function render_slider( $o ) {
		$slides = array();

		foreach ( array( 1, 2, 3 ) as $index ) {
			$image = self::media( $o, 'slide' . $index . '_image', isset( $o[ 'slide' . $index . '_title' ] ) ? $o[ 'slide' . $index . '_title' ] : '', 'full' );

			if ( ! $image ) {
				continue;
			}

			$slides[] = array(
				'image' => $image,
				'title' => isset( $o[ 'slide' . $index . '_title' ] ) ? $o[ 'slide' . $index . '_title' ] : '',
				'text'  => isset( $o[ 'slide' . $index . '_text' ] ) ? $o[ 'slide' . $index . '_text' ] : '',
				'url'   => isset( $o[ 'slide' . $index . '_url' ] ) ? $o[ 'slide' . $index . '_url' ] : '',
			);
		}

		if ( empty( $slides ) ) {
			return;
		}

		$height = isset( $o['height'] ) ? $o['height'] : 'lg';
		?>
		<div class="moon-slider moon-slider--<?php echo esc_attr( $height ); ?>" data-moon-slider>
			<div class="moon-slider__track">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<figure class="moon-slider__slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-slide>
						<?php echo $slide['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image. ?>
						<figcaption class="moon-slider__caption">
							<?php if ( $slide['title'] ) : ?><h2><?php echo esc_html( $slide['title'] ); ?></h2><?php endif; ?>
							<?php if ( $slide['text'] ) : ?><p><?php echo esc_html( $slide['text'] ); ?></p><?php endif; ?>
							<?php if ( $slide['url'] ) : ?>
								<a class="moon-btn" href="<?php echo esc_url( $slide['url'] ); ?>"><?php esc_html_e( 'مشاهده', 'moon-theme' ); ?></a>
							<?php endif; ?>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $slides ) > 1 ) : ?>
				<div class="moon-slider__dots" role="tablist">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<button type="button" class="moon-slider__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-slide-to="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'اسلاید %s', 'moon-theme' ), number_format_i18n( $index + 1 ) ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( isset( $o['title'] ) && $o['title'] ) : ?>
				<h2 class="screen-reader-text"><?php echo esc_html( $o['title'] ); ?></h2>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ---- Banner ---------------------------------------------------------- */

	/**
	 * Full-width banner.
	 *
	 * @param array $o Options.
	 */
	private static function render_banner( $o ) {
		$image   = self::media( $o, 'image', isset( $o['title'] ) ? $o['title'] : '', 'full' );
		$overlay = isset( $o['overlay'] ) ? (int) $o['overlay'] : 45;
		$align   = isset( $o['align'] ) ? $o['align'] : 'center';
		?>
		<div class="moon-banner moon-banner--<?php echo esc_attr( $align ); ?>"<?php echo $image ? '' : ' data-empty="1"'; ?>>
			<?php if ( $image ) : ?>
				<div class="moon-banner__media"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<span class="moon-banner__scrim" style="opacity:<?php echo esc_attr( (string) ( max( 0, min( 90, $overlay ) ) / 100 ) ); ?>" aria-hidden="true"></span>
			<?php endif; ?>

			<div class="moon-banner__body">
				<?php if ( ! empty( $o['eyebrow'] ) ) : ?><span class="moon-eyebrow"><?php echo esc_html( $o['eyebrow'] ); ?></span><?php endif; ?>
				<?php if ( ! empty( $o['title'] ) ) : ?><h2 class="moon-banner__title"><?php echo esc_html( $o['title'] ); ?></h2><?php endif; ?>
				<?php if ( ! empty( $o['text'] ) ) : ?><p class="moon-banner__text"><?php echo esc_html( $o['text'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $o['btn_text'] ) && ! empty( $o['btn_url'] ) ) : ?>
					<a class="moon-btn moon-btn--light" href="<?php echo esc_url( $o['btn_url'] ); ?>"><?php echo esc_html( $o['btn_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Multi banner ---------------------------------------------------- */

	/**
	 * Two or three banners side by side.
	 *
	 * @param array $o Options.
	 */
	private static function render_banners( $o ) {
		$items = array();

		foreach ( array( 1, 2, 3 ) as $index ) {
			$image = self::media( $o, 'b' . $index . '_image', isset( $o[ 'b' . $index . '_title' ] ) ? $o[ 'b' . $index . '_title' ] : '', 'moon-card' );

			if ( ! $image ) {
				continue;
			}

			$items[] = array(
				'image' => $image,
				'title' => isset( $o[ 'b' . $index . '_title' ] ) ? $o[ 'b' . $index . '_title' ] : '',
				'url'   => isset( $o[ 'b' . $index . '_url' ] ) ? $o[ 'b' . $index . '_url' ] : '',
			);
		}

		if ( empty( $items ) ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-banners moon-banners--<?php echo esc_attr( (string) count( $items ) ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<a class="moon-banners__item" href="<?php echo esc_url( $item['url'] ? $item['url'] : '#' ); ?>">
						<?php echo $item['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php if ( $item['title'] ) : ?><span class="moon-banners__title"><?php echo esc_html( $item['title'] ); ?></span><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Collections ----------------------------------------------------- */

	/**
	 * Product category cards.
	 *
	 * @param array $o Options.
	 */
	private static function render_collections( $o ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return;
		}

		$count = isset( $o['count'] ) ? (int) $o['count'] : 4;
		$args  = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => max( 2, min( 12, $count ) ),
			'orderby'    => 'count',
			'order'      => 'DESC',
		);

		if ( ! empty( $o['parent'] ) ) {
			$args['parent'] = (int) $o['parent'];
		} else {
			$args['parent'] = 0;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-collections">
				<?php foreach ( $terms as $term ) : ?>
					<?php
					$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
					$link         = get_term_link( $term );
					?>
					<a class="moon-collections__item" href="<?php echo esc_url( is_wp_error( $link ) ? '#' : $link ); ?>">
						<?php if ( $thumbnail_id ) : ?>
							<?php echo wp_get_attachment_image( $thumbnail_id, 'moon-card', false, array( 'loading' => 'lazy', 'alt' => $term->name ) ); ?>
						<?php else : ?>
							<span class="moon-collections__ph" aria-hidden="true"><?php echo esc_html( mb_substr( $term->name, 0, 1 ) ); ?></span>
						<?php endif; ?>
						<span class="moon-collections__name"><?php echo esc_html( $term->name ); ?></span>
						<span class="moon-collections__count"><?php echo esc_html( sprintf( __( '%s محصول', 'moon-theme' ), number_format_i18n( $term->count ) ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Features -------------------------------------------------------- */

	/**
	 * Four shop benefits.
	 *
	 * @param array $o Options.
	 */
	private static function render_features( $o ) {
		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-features">
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<?php
					$icon  = isset( $o[ 'f' . $i . '_icon' ] ) ? $o[ 'f' . $i . '_icon' ] : '';
					$title = isset( $o[ 'f' . $i . '_title' ] ) ? $o[ 'f' . $i . '_title' ] : '';
					$text  = isset( $o[ 'f' . $i . '_text' ] ) ? $o[ 'f' . $i . '_text' ] : '';

					if ( ! $title && ! $text ) {
						continue;
					}
					?>
					<div class="moon-features__item">
						<?php if ( $icon ) : ?><span class="moon-features__icon"><?php moon_the_icon( $icon, 22 ); ?></span><?php endif; ?>
						<div>
							<?php if ( $title ) : ?><strong><?php echo esc_html( $title ); ?></strong><?php endif; ?>
							<?php if ( $text ) : ?><span><?php echo esc_html( $text ); ?></span><?php endif; ?>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Brands ---------------------------------------------------------- */

	/**
	 * Brand logos.
	 *
	 * @param array $o Options.
	 */
	private static function render_brands( $o ) {
		$logos = array();

		for ( $i = 1; $i <= 6; $i++ ) {
			$logo = self::media( $o, 'logo' . $i, '', 'medium' );

			if ( $logo ) {
				$logos[] = $logo;
			}
		}

		if ( empty( $logos ) ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-brands">
				<?php foreach ( $logos as $logo ) : ?>
					<div class="moon-brands__item"><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Promo ----------------------------------------------------------- */

	/**
	 * Offer with optional countdown.
	 *
	 * @param array $o Options.
	 */
	private static function render_promo( $o ) {
		$image = self::media( $o, 'image', isset( $o['title'] ) ? $o['title'] : '', 'moon-card' );
		$ends  = ! empty( $o['ends_at'] ) ? strtotime( $o['ends_at'] ) : 0;
		?>
		<div class="moon-container">
			<div class="moon-promo">
				<?php if ( $image ) : ?><div class="moon-promo__media"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
				<div class="moon-promo__body">
					<?php if ( ! empty( $o['eyebrow'] ) ) : ?><span class="moon-eyebrow"><?php echo esc_html( $o['eyebrow'] ); ?></span><?php endif; ?>
					<?php if ( ! empty( $o['title'] ) ) : ?><h2><?php echo esc_html( $o['title'] ); ?></h2><?php endif; ?>
					<?php if ( ! empty( $o['text'] ) ) : ?><p><?php echo esc_html( $o['text'] ); ?></p><?php endif; ?>

					<?php if ( $ends && $ends > time() ) : ?>
						<div class="moon-countdown" data-moon-countdown="<?php echo esc_attr( (string) $ends ); ?>" role="timer" aria-live="off">
							<span data-moon-cd="days">۰</span><small><?php esc_html_e( 'روز', 'moon-theme' ); ?></small>
							<span data-moon-cd="hours">۰</span><small><?php esc_html_e( 'ساعت', 'moon-theme' ); ?></small>
							<span data-moon-cd="minutes">۰</span><small><?php esc_html_e( 'دقیقه', 'moon-theme' ); ?></small>
							<span data-moon-cd="seconds">۰</span><small><?php esc_html_e( 'ثانیه', 'moon-theme' ); ?></small>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $o['btn_text'] ) && ! empty( $o['btn_url'] ) ) : ?>
						<a class="moon-btn moon-btn--dark" href="<?php echo esc_url( $o['btn_url'] ); ?>"><?php echo esc_html( $o['btn_text'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/* ---- Video ----------------------------------------------------------- */

	/**
	 * Video section.
	 *
	 * @param array $o Options.
	 */
	private static function render_video( $o ) {
		$url = isset( $o['video_url'] ) ? $o['video_url'] : '';

		if ( ! $url ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '', '' );
		$poster_id = isset( $o['poster'] ) ? (int) $o['poster'] : 0;
		$is_file   = (bool) preg_match( '/\.(mp4|webm|ogg)(\?|$)/i', $url );
		?>
		<div class="moon-container">
			<div class="moon-video">
				<?php if ( $is_file ) : ?>
					<video controls preload="none" playsinline<?php echo $poster_id ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $poster_id, 'full' ) ) . '"' : ''; ?>>
						<source src="<?php echo esc_url( $url ); ?>" type="video/mp4">
					</video>
				<?php else : ?>
					<iframe src="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( isset( $o['title'] ) ? $o['title'] : '' ); ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen></iframe>
				<?php endif; ?>
				<?php if ( ! empty( $o['text'] ) ) : ?><p class="moon-video__text"><?php echo esc_html( $o['text'] ); ?></p><?php endif; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Gallery --------------------------------------------------------- */

	/**
	 * Image gallery.
	 *
	 * @param array $o Options.
	 */
	private static function render_gallery( $o ) {
		$images = array();

		for ( $i = 1; $i <= 6; $i++ ) {
			$image = self::media( $o, 'g' . $i, '', 'moon-card' );

			if ( $image ) {
				$images[] = $image;
			}
		}

		if ( empty( $images ) ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-gallery-grid">
				<?php foreach ( $images as $image ) : ?>
					<figure class="moon-gallery-grid__item"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Posts ----------------------------------------------------------- */

	/**
	 * Latest posts.
	 *
	 * @param array $o Options.
	 */
	private static function render_posts( $o ) {
		$count = isset( $o['count'] ) ? (int) $o['count'] : 3;
		$query = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => max( 2, min( 9, $count ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '', '' );
		?>
		<div class="moon-container">
			<div class="moon-posts">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<article class="moon-posts__item">
						<a href="<?php the_permalink(); ?>" class="moon-posts__media">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'moon-card', array( 'loading' => 'lazy' ) ); ?>
							<?php endif; ?>
						</a>
						<div class="moon-posts__body">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		</div>
		<?php
		wp_reset_postdata();
	}

	/* ---- FAQ ------------------------------------------------------------- */

	/**
	 * FAQ accordion (native details/summary: keyboard accessible, no JS).
	 *
	 * @param array $o Options.
	 */
	private static function render_faq( $o ) {
		$items = array();

		for ( $i = 1; $i <= 4; $i++ ) {
			$q = isset( $o[ 'q' . $i ] ) ? $o[ 'q' . $i ] : '';
			$a = isset( $o[ 'a' . $i ] ) ? $o[ 'a' . $i ] : '';

			if ( $q ) {
				$items[] = array( 'q' => $q, 'a' => $a );
			}
		}

		if ( empty( $items ) ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-faq">
				<?php foreach ( $items as $item ) : ?>
					<details class="moon-faq__item">
						<summary><?php echo esc_html( $item['q'] ); ?></summary>
						<p><?php echo esc_html( $item['a'] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Counters -------------------------------------------------------- */

	/**
	 * Stat counters.
	 *
	 * @param array $o Options.
	 */
	private static function render_counters( $o ) {
		$items = array();

		for ( $i = 1; $i <= 3; $i++ ) {
			$value = isset( $o[ 'c' . $i . '_value' ] ) ? $o[ 'c' . $i . '_value' ] : '';
			$label = isset( $o[ 'c' . $i . '_label' ] ) ? $o[ 'c' . $i . '_label' ] : '';

			if ( '' !== $value || '' !== $label ) {
				$items[] = array( 'value' => $value, 'label' => $label );
			}
		}

		if ( empty( $items ) ) {
			return;
		}

		self::heading( isset( $o['title'] ) ? $o['title'] : '' );
		?>
		<div class="moon-container">
			<div class="moon-counters">
				<?php foreach ( $items as $item ) : ?>
					<div class="moon-counters__item">
						<strong><?php echo esc_html( $item['value'] ); ?></strong>
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/* ---- Newsletter ------------------------------------------------------ */

	/**
	 * Newsletter block (reuses the theme's subscribe form).
	 *
	 * @param array $o Options.
	 */
	private static function render_newsletter( $o ) {
		?>
		<div class="moon-container">
			<div class="moon-newsletter">
				<?php if ( ! empty( $o['eyebrow'] ) ) : ?><span class="moon-eyebrow"><?php echo esc_html( $o['eyebrow'] ); ?></span><?php endif; ?>
				<h2><?php echo esc_html( isset( $o['title'] ) ? $o['title'] : '' ); ?></h2>
				<?php
				get_template_part( 'template-parts/footer/newsletter', null, array(
					'note' => isset( $o['note'] ) ? $o['note'] : '',
				) );
				?>
			</div>
		</div>
		<?php
	}

	/* ---- Custom HTML ----------------------------------------------------- */

	/**
	 * Administrator-provided HTML.
	 *
	 * @param array $o Options.
	 */
	private static function render_html( $o ) {
		$content = isset( $o['content'] ) ? (string) $o['content'] : '';

		if ( '' === trim( $content ) ) {
			return;
		}
		?>
		<div class="moon-container moon-content">
			<?php echo do_shortcode( wp_kses_post( $content ) ); ?>
		</div>
		<?php
	}
}
