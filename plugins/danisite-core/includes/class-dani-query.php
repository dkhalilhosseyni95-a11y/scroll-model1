<?php
/**
 * Data access for services, projects, team, testimonials, FAQ and posts.
 *
 * Every getter returns the live WordPress content when it exists and a designed
 * demo set otherwise, so widgets never render empty before the importer runs.
 *
 * @package DaniSite_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Content queries with demo fallbacks.
 */
class Dani_Query {

	/**
	 * Services.
	 *
	 * @param int $limit Maximum items.
	 * @return array
	 */
	public static function services( $limit = 6 ) {
		$items = self::collect(
			array(
				'post_type'      => 'dani_service',
				'posts_per_page' => (int) $limit,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
			),
			function ( $post ) {
				return array(
					'id'       => $post->ID,
					'title'    => get_the_title( $post ),
					'desc'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
					'url'      => get_permalink( $post ),
					'icon'     => get_post_meta( $post->ID, '_dani_icon', true ),
					'price'    => get_post_meta( $post->ID, '_dani_price', true ),
					'features' => array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $post->ID, '_dani_features', true ) ) ) ),
					'thumb'    => get_the_post_thumbnail_url( $post, 'dani-card' ),
					'demo'     => false,
				);
			}
		);

		if ( ! empty( $items ) ) {
			return $items;
		}

		return array(
			array(
				'title'    => 'طراحی رابط و تجربه کاربری',
				'desc'     => 'طراحی رابط‌هایی که بدون توضیح فهمیده می‌شوند؛ از وایرفریم و جریان کاربر تا دیزاین سیستم.',
				'url'      => home_url( '/services/' ),
				'icon'     => 'palette',
				'price'    => '',
				'features' => array(),
				'thumb'    => '',
				'demo'     => true,
			),
			array(
				'title'    => 'طراحی و توسعه وردپرس',
				'desc'     => 'سایت‌های سریع، امن و قابل مدیریت؛ بدون نیاز به کدنویسی برای به‌روزرسانی محتوا.',
				'url'      => home_url( '/services/' ),
				'icon'     => 'code',
				'price'    => '',
				'features' => array(),
				'thumb'    => '',
				'demo'     => true,
			),
			array(
				'title'    => 'توسعه فرانت‌اند',
				'desc'     => 'پیاده‌سازی دقیق طراحی با کد تمیز، انیمیشن‌های سبک و عملکرد بالا در همه دستگاه‌ها.',
				'url'      => home_url( '/services/' ),
				'icon'     => 'layers',
				'price'    => '',
				'features' => array(),
				'thumb'    => '',
				'demo'     => true,
			),
			array(
				'title'    => 'هویت دیجیتال برند',
				'desc'     => 'یک زبان بصری یکپارچه در وب، شبکه‌های اجتماعی و تمام نقاط تماس دیجیتال برند.',
				'url'      => home_url( '/services/' ),
				'icon'     => 'sparkles',
				'price'    => '',
				'features' => array(),
				'thumb'    => '',
				'demo'     => true,
			),
			array(
				'title'    => 'بهینه‌سازی سرعت و سئو',
				'desc'     => 'Core Web Vitals، ساختار فنی سئو و رشد ارگانیک پایدار برای بلندمدت.',
				'url'      => home_url( '/services/' ),
				'icon'     => 'gauge',
				'price'    => '',
				'features' => array(),
				'thumb'    => '',
				'demo'     => true,
			),
		);
	}

	/**
	 * Portfolio projects.
	 *
	 * @param int    $limit      Maximum items.
	 * @param string $category   Optional term slug.
	 * @param bool   $featured   Only items flagged for the homepage.
	 * @return array
	 */
	public static function projects( $limit = 6, $category = '', $featured = false ) {
		$args = array(
			'post_type'      => 'dani_project',
			'posts_per_page' => (int) $limit,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
		);

		if ( $category ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'dani_project_cat',
					'field'    => 'slug',
					'terms'    => sanitize_title( $category ),
				),
			);
		}

		if ( $featured ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_dani_featured',
					'value'   => '1',
					'compare' => '=',
				),
			);
		}

		$items = self::collect(
			$args,
			function ( $post ) {
				$terms = get_the_terms( $post, 'dani_project_cat' );
				return array(
					'id'       => $post->ID,
					'title'    => get_the_title( $post ),
					'desc'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
					'url'      => get_permalink( $post ),
					'client'   => get_post_meta( $post->ID, '_dani_client', true ),
					'year'     => get_post_meta( $post->ID, '_dani_year', true ),
					'scope'    => get_post_meta( $post->ID, '_dani_scope', true ),
					'ratio'    => get_post_meta( $post->ID, '_dani_ratio', true ),
					'category' => ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : '',
					'slug'     => ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '',
					'thumb'    => get_the_post_thumbnail_url( $post, 'dani-card' ),
					'demo'     => false,
				);
			}
		);

		if ( ! empty( $items ) ) {
			return $items;
		}

		return self::demo_projects();
	}

	/**
	 * Demo projects transcribed from the Lovable homepage.
	 *
	 * @return array
	 */
	public static function demo_projects() {
		$base = DANI_CORE_URL . 'assets/img/';

		return array(
			array(
				'title'    => 'فروشگاه اینترنتی کفش',
				'desc'     => 'ویترینی مینیمال که محصول را در مرکز توجه نگه می‌دارد.',
				'category' => 'فروشگاهی · نمونه نمایشی',
				'slug'     => 'shop',
				'url'      => home_url( '/portfolio/' ),
				'thumb'    => $base . 'work-shoes.jpg',
				'ratio'    => '16-10',
				'scope'    => 'طراحی رابط، توسعه وردپرس',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۴',
				'demo'     => true,
			),
			array(
				'title'    => 'فروشگاه موبایل و لوازم دیجیتال',
				'desc'     => 'تجربه‌ای سریع برای مقایسه و خرید.',
				'category' => 'فروشگاهی · نمونه نمایشی',
				'slug'     => 'shop',
				'url'      => home_url( '/portfolio/' ),
				'thumb'    => $base . 'work-mobile.jpg',
				'ratio'    => '4-5',
				'scope'    => 'طراحی رابط، فروشگاه',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۴',
				'demo'     => true,
			),
			array(
				'title'    => 'فروشگاه محصولات آرایشی',
				'desc'     => 'زبان بصری لطیف با تمرکز بر حس اعتماد.',
				'category' => 'فروشگاهی · نمونه نمایشی',
				'slug'     => 'shop',
				'url'      => home_url( '/portfolio/' ),
				'thumb'    => $base . 'work-cosmetics.jpg',
				'ratio'    => '4-5',
				'scope'    => 'هویت دیجیتال، طراحی رابط',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۳',
				'demo'     => true,
			),
			array(
				'title'    => 'وب‌سایت خدمات زیبایی',
				'desc'     => 'رزرو آسان و فضایی آرام و دعوت‌کننده.',
				'category' => 'خدماتی · نمونه نمایشی',
				'slug'     => 'service',
				'url'      => home_url( '/portfolio/' ),
				'thumb'    => $base . 'work-beauty.jpg',
				'ratio'    => '4-3',
				'scope'    => 'طراحی رابط، رزرو آنلاین',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۳',
				'demo'     => true,
			),
			array(
				'title'    => 'وب‌سایت شرکتی',
				'desc'     => 'هویتی مطمئن و ساختاری روشن برای معرفی برند.',
				'category' => 'شرکتی · نمونه نمایشی',
				'slug'     => 'corporate',
				'url'      => home_url( '/portfolio/' ),
				'thumb'    => $base . 'work-corporate.jpg',
				'ratio'    => '16-9',
				'scope'    => 'طراحی رابط، توسعه',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۲',
				'demo'     => true,
			),
		);
	}

	/**
	 * Portfolio categories.
	 *
	 * @return array
	 */
	public static function project_categories() {
		$terms = get_terms( array(
			'taxonomy'   => 'dani_project_cat',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array(
				array( 'slug' => 'shop', 'name' => 'فروشگاهی' ),
				array( 'slug' => 'service', 'name' => 'خدماتی' ),
				array( 'slug' => 'corporate', 'name' => 'شرکتی' ),
			);
		}

		$out = array();
		foreach ( $terms as $term ) {
			$out[] = array( 'slug' => $term->slug, 'name' => $term->name );
		}

		return $out;
	}

	/**
	 * Team members.
	 *
	 * @param int $limit Maximum items.
	 * @return array
	 */
	public static function team( $limit = 6 ) {
		$items = self::collect(
			array(
				'post_type'      => 'dani_team',
				'posts_per_page' => (int) $limit,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
			),
			function ( $post ) {
				return array(
					'id'        => $post->ID,
					'title'     => get_the_title( $post ),
					'role'      => get_post_meta( $post->ID, '_dani_role', true ),
					'desc'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
					'thumb'     => get_the_post_thumbnail_url( $post, 'dani-portrait' ),
					'url'       => get_permalink( $post ),
					'instagram' => get_post_meta( $post->ID, '_dani_instagram', true ),
					'linkedin'  => get_post_meta( $post->ID, '_dani_linkedin', true ),
					'x'         => get_post_meta( $post->ID, '_dani_x', true ),
					'demo'      => false,
				);
			}
		);

		if ( ! empty( $items ) ) {
			return $items;
		}

		return array(
			array(
				'title' => 'دانیال حسینی',
				'role'  => 'بنیان‌گذار و مدیر خلاقیت',
				'desc'  => 'طراح و توسعه‌دهنده‌ای که طراحی را نقطه تلاقی استراتژی، زیبایی و فناوری می‌داند.',
				'thumb' => DANI_CORE_URL . 'assets/img/studio.jpg',
				'url'   => home_url( '/about/' ),
				'instagram' => 'https://instagram.com',
				'linkedin'  => '',
				'x'         => '',
				'demo'  => true,
			),
		);
	}

	/**
	 * Testimonials.
	 *
	 * @param int $limit Maximum items.
	 * @return array
	 */
	public static function quotes( $limit = 5 ) {
		$items = self::collect(
			array(
				'post_type'      => 'dani_quote',
				'posts_per_page' => (int) $limit,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
			),
			function ( $post ) {
				return array(
					'id'      => $post->ID,
					'text'    => wp_strip_all_tags( $post->post_content ),
					'author'  => get_post_meta( $post->ID, '_dani_author', true ),
					'role'    => get_post_meta( $post->ID, '_dani_role', true ),
					'company' => get_post_meta( $post->ID, '_dani_company', true ),
					'rating'  => (int) get_post_meta( $post->ID, '_dani_rating', true ),
					'thumb'   => get_the_post_thumbnail_url( $post, 'thumbnail' ),
					'demo'    => false,
				);
			}
		);

		if ( ! empty( $items ) ) {
			return $items;
		}

		return array(
			array(
				'text'   => 'این بخش برای نمایش نظر واقعی یکی از مشتریان رزرو شده است. پس از تکمیل پروژه‌ها، متن تأییدشده جایگزین می‌شود.',
				'author' => 'نام مشتری',
				'role'   => 'نمونه نمایشی',
				'rating' => 0,
				'demo'   => true,
			),
			array(
				'text'   => 'نمونه متن نظر مشتری: توضیحی کوتاه درباره تجربه همکاری، کیفیت طراحی و نتیجه نهایی پروژه.',
				'author' => 'نام مشتری',
				'role'   => 'نمونه نمایشی',
				'rating' => 0,
				'demo'   => true,
			),
			array(
				'text'   => 'جای خالی برای بازخورد مشتری. این محتوا صرفاً نمایشی است و نظر هیچ شخص واقعی نیست.',
				'author' => 'نام مشتری',
				'role'   => 'نمونه نمایشی',
				'rating' => 0,
				'demo'   => true,
			),
		);
	}

	/**
	 * FAQ entries.
	 *
	 * @param int $limit Maximum items.
	 * @return array
	 */
	public static function faqs( $limit = 8 ) {
		$items = self::collect(
			array(
				'post_type'      => 'dani_faq',
				'posts_per_page' => (int) $limit,
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
			),
			function ( $post ) {
				return array(
					'id'      => $post->ID,
					'question' => get_the_title( $post ),
					'answer'   => wp_strip_all_tags( $post->post_content ),
					'group'    => get_post_meta( $post->ID, '_dani_group', true ),
					'demo'     => false,
				);
			}
		);

		if ( ! empty( $items ) ) {
			return $items;
		}

		return array(
			array(
				'question' => 'یک پروژه وب‌سایت معمولاً چقدر زمان می‌برد؟',
				'answer'   => 'مدت زمان به دامنه پروژه بستگی دارد؛ یک سایت شرکتی معمولاً ۳ تا ۵ هفته و یک فروشگاه کامل ۶ تا ۱۰ هفته زمان می‌برد. در جلسه اول، زمان‌بندی دقیق و مرحله‌به‌مرحله مشخص می‌شود.',
			),
			array(
				'question' => 'هزینه طراحی سایت چگونه محاسبه می‌شود؟',
				'answer'   => 'هزینه بر اساس تعداد صفحات، پیچیدگی رابط، امکانات فنی و نیاز به تولید محتوا برآورد می‌شود. پیش از شروع، پیشنهاد قیمت شفاف و بدون هزینه پنهان ارائه می‌کنیم.',
			),
			array(
				'question' => 'آیا سایت را می‌توانم خودم مدیریت کنم؟',
				'answer'   => 'بله. تمام صفحات با Elementor ساخته می‌شوند و محتوای تکرارشونده (خدمات، نمونه‌کارها، تیم، نظرات و مقالات) از پنل وردپرس قابل مدیریت است؛ برای شروع، یک جلسه آموزش هم برگزار می‌شود.',
			),
			array(
				'question' => 'پشتیبانی بعد از تحویل شامل چه مواردی است؟',
				'answer'   => 'سه ماه پشتیبانی رایگان شامل رفع خطا، به‌روزرسانی امنیتی و راهنمایی محتوایی است. پس از آن، بسته‌های پشتیبانی ماهانه و سالانه اختیاری ارائه می‌شود.',
			),
			array(
				'question' => 'سایت برای موبایل و سرعت بهینه خواهد بود؟',
				'answer'   => 'همه پروژه‌ها واکنش‌گرا، سبک و مطابق Core Web Vitals ساخته می‌شوند؛ تصاویر بهینه، فونت‌های سبک و بارگذاری هوشمند اسکریپت‌ها بخشی از استاندارد کار است.',
			),
			array(
				'question' => 'برای شروع همکاری چه چیزی لازم است؟',
				'answer'   => 'یک گفت‌وگوی کوتاه درباره اهداف، مخاطب و زمان‌بندی کافی است. اگر لوگو یا هویت بصری دارید آن را بفرستید؛ اگر نه، در همان مسیر با هم می‌سازیمش.',
			),
		);
	}

	/**
	 * Blog posts.
	 *
	 * @param int    $limit    Maximum items.
	 * @param string $category Optional category slug.
	 * @return array
	 */
	public static function posts( $limit = 3, $category = '' ) {
		$args = array(
			'post_type'           => 'post',
			'posts_per_page'      => (int) $limit,
			'ignore_sticky_posts' => true,
		);

		if ( $category ) {
			$args['category_name'] = sanitize_title( $category );
		}

		return self::collect(
			$args,
			function ( $post ) {
				$cats = get_the_category( $post->ID );
				return array(
					'id'      => $post->ID,
					'title'   => get_the_title( $post ),
					'excerpt' => wp_strip_all_tags( get_the_excerpt( $post ) ),
					'url'     => get_permalink( $post ),
					'date'    => dani_fa_number( get_the_date( 'j F Y', $post ) ),
					'thumb'   => get_the_post_thumbnail_url( $post, 'dani-card' ),
					'cat'     => ! empty( $cats ) ? $cats[0]->name : '',
					'cat_url' => ! empty( $cats ) ? get_category_link( $cats[0] ) : '',
				);
			}
		);
	}

	/**
	 * Homepage workflow steps, transcribed from the Lovable "PROCESS" section.
	 *
	 * @return array
	 */
	public static function steps() {
		return array(
			array( 'n' => '۰۱', 'title' => 'گفت‌وگو و شناخت', 'desc' => 'شناخت کسب‌وکار، مخاطب و هدف واقعی پروژه در یک جلسه صریح.' ),
			array( 'n' => '۰۲', 'title' => 'استراتژی و ساختار', 'desc' => 'تعیین مسیر محتوا، معماری اطلاعات و اولویت‌های تجربه کاربری.' ),
			array( 'n' => '۰۳', 'title' => 'طراحی تجربه', 'desc' => 'طراحی رابط در فیگما با تمرکز بر وضوح، جریان کاربر و هویت برند.' ),
			array( 'n' => '۰۴', 'title' => 'پیاده‌سازی', 'desc' => 'توسعه دقیق طراحی با کدی تمیز، واکنش‌گرا و قابل مدیریت.' ),
			array( 'n' => '۰۵', 'title' => 'تست و بهینه‌سازی', 'desc' => 'آزمون دستگاه‌ها، سرعت، دسترس‌پذیری و اصلاح جزئیات.' ),
			array( 'n' => '۰۶', 'title' => 'رونمایی و همراهی', 'desc' => 'انتشار، آموزش تیم و پشتیبانی برای رشد پایدار.' ),
		);
	}

	/**
	 * Default statistics block.
	 *
	 * @return array
	 */
	public static function stats() {
		return array(
			array( 'value' => '۴۰+', 'label' => 'پروژه تحویل‌شده' ),
			array( 'value' => '۸', 'label' => 'سال تجربه' ),
			array( 'value' => '۹۸٪', 'label' => 'رضایت مشتریان' ),
			array( 'value' => '۳', 'label' => 'ماه پشتیبانی رایگان' ),
		);
	}

	/**
	 * Run a WP_Query and map results.
	 *
	 * @param array    $args  Query args.
	 * @param callable $mapper Row mapper.
	 * @return array
	 */
	private static function collect( $args, $mapper ) {
		$args = array_merge( array( 'no_found_rows' => true, 'post_status' => 'publish' ), $args );
		$query = new WP_Query( $args );
		$items = array();

		foreach ( $query->posts as $post ) {
			$items[] = call_user_func( $mapper, $post );
		}

		wp_reset_postdata();

		return $items;
	}
}
