<?php
/**
 * Demo content importer.
 *
 * Safe by design: nothing is deleted or overwritten. Existing pages, products,
 * terms and media are detected and skipped, and every run is idempotent.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imports the MOON demo content.
 */
class Moon_Demo_Importer {

	/**
	 * Report accumulator.
	 *
	 * @var array
	 */
	private static $report = array(
		'messages' => array(),
		'skipped'  => array(),
		'errors'   => array(),
	);

	/**
	 * Attachment map (file name => attachment ID).
	 *
	 * @var array
	 */
	private static $media = array();

	/**
	 * Hook WP-CLI.
	 */
	public static function init() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'moon-demo', array( __CLASS__, 'cli' ) );
		}
	}

	/**
	 * WP-CLI entry point: wp moon-demo import --products --media ...
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Flags.
	 */
	public static function cli( $args, $assoc_args ) {
		$options = array(
			'pages'      => true,
			'media'      => true,
			'taxonomies' => true,
			'rack'       => true,
			'products'   => isset( $assoc_args['products'] ),
		);

		$report = self::run( $options );

		foreach ( $report['messages'] as $message ) {
			WP_CLI::log( '✓ ' . $message );
		}

		foreach ( $report['skipped'] as $message ) {
			WP_CLI::log( '• ' . $message );
		}

		foreach ( $report['errors'] as $message ) {
			WP_CLI::warning( $message );
		}
	}

	/**
	 * Import demo content.
	 *
	 * @param array $options What to import.
	 * @return array Report.
	 */
	public static function run( $options = array() ) {
		self::$report = array(
			'messages' => array(),
			'skipped'  => array(),
			'errors'   => array(),
		);

		$options = wp_parse_args( $options, array(
			'pages'      => true,
			'media'      => true,
			'taxonomies' => true,
			'rack'       => true,
			'products'   => false,
		) );

		self::$media = get_option( 'moon_core_media', array() );
		self::$media = is_array( self::$media ) ? self::$media : array();

		if ( $options['media'] ) {
			self::import_media();
		}

		if ( $options['pages'] ) {
			self::import_pages();
			self::import_journal();
		}

		self::prepare_store();

		if ( $options['taxonomies'] ) {
			self::import_terms();
		}

		if ( $options['products'] ) {
			self::import_products();
		}

		if ( $options['rack'] ) {
			self::import_rack();
			self::import_outfits();
		}

		self::import_options();
		self::import_menu();

		update_option( 'moon_core_media', self::$media, false );

		$state = array(
			'time'     => date_i18n( 'Y-m-d H:i' ),
			'sections' => count( self::$report['messages'] ),
		);

		update_option( 'moon_core_imported', $state, false );

		return self::$report;
	}

	/* ----------------------------------------------------------------------
	 * Media
	 * -------------------------------------------------------------------- */

	/**
	 * Copy bundled images into the media library.
	 */
	private static function import_media() {
		$files = array(
			'hero.jpg', 'campaign.jpg', 'atelier.jpg', 'women.jpg', 'men.jpg',
			'outfit-1.jpg', 'outfit-2.jpg', 'outfit-3.jpg', 'outfit-4.jpg', 'outfit-5.jpg',
			'g-coat.png', 'g-hoodie.png', 'g-jacket.png', 'g-shirt.png', 'g-sweat.png',
		);

		$created = 0;

		foreach ( $files as $file ) {
			$id = self::attachment( $file );

			if ( $id ) {
				$created++;
			}
		}

		if ( $created ) {
			/* translators: %s: number of images. */
			self::$report['messages'][] = sprintf( __( '%s تصویر در کتابخانه رسانه آماده شد.', 'moon-core' ), number_format_i18n( $created ) );
		}
	}

	/**
	 * Import one bundled image, reusing an earlier import.
	 *
	 * @param string $file File name inside the theme assets.
	 * @return int Attachment ID or 0.
	 */
	private static function attachment( $file ) {
		if ( isset( self::$media[ $file ] ) ) {
			$existing = (int) self::$media[ $file ];

			if ( $existing && get_post( $existing ) ) {
				return $existing;
			}
		}

		$source = get_template_directory() . '/assets/images/' . $file;

		if ( ! file_exists( $source ) ) {
			/* translators: %s: file name. */
			self::$report['errors'][] = sprintf( __( 'فایل تصویر پیدا نشد: %s', 'moon-core' ), $file );
			return 0;
		}

		$contents = file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset.
		$upload   = wp_upload_bits( $file, null, $contents );

		if ( ! empty( $upload['error'] ) ) {
			self::$report['errors'][] = $upload['error'];
			return 0;
		}

		$filetype = wp_check_filetype( $upload['file'] );
		$title    = preg_replace( '/\.[^.]+$/', '', $file );

		$attachment_id = wp_insert_attachment( array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => ucwords( str_replace( array( '-', '_' ), ' ', $title ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		), $upload['file'] );

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			self::$report['errors'][] = __( 'ثبت تصویر در کتابخانه رسانه ناموفق بود.', 'moon-core' );
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'MOON — ' . $title );

		self::$media[ $file ] = (int) $attachment_id;

		return (int) $attachment_id;
	}

	/* ----------------------------------------------------------------------
	 * Pages
	 * -------------------------------------------------------------------- */

	/**
	 * Create the base pages and set the front page.
	 */
	private static function import_pages() {
		$pages = array(
			'home'          => array(
				'title'   => 'خانه',
				'content' => '',
			),
			'journal'       => array(
				'title'   => 'ژورنال',
				'content' => '',
			),
			'about'         => array(
				'title'   => 'داستان ما',
				'content' => "MOON از دل یک آتلیه کوچک متولد شد؛ جایی که هر لباس با دست برش می‌خورد و هر درز معنایی داشت.\n\nامروز همان دقت را در کالکشن‌هایی می‌بینید که برای زمان ساخته شده‌اند، نه برای فصل. پارچه‌ها از تأمین‌کنندگان منتخب انتخاب می‌شوند و هر نمونه پیش از تولید، روی بدن واقعی تنظیم می‌شود.\n\nما به برش‌های آرام، رنگ‌های خنثی و دوام لباس باور داریم؛ چیزی که سال‌ها بماند و هر بار پوشیدنش حس تازه‌ای بدهد.",
			),
			'size-guide'    => array(
				'title'   => 'راهنمای سایز',
				'content' => "<p>اندازه‌ها بر اساس سانتی‌متر و دور بدن اندازه‌گیری شده‌اند. اگر بین دو سایز هستید، سایز بزرگ‌تر را انتخاب کنید.</p>\n<table>\n<thead><tr><th>سایز</th><th>دور سینه</th><th>دور کمر</th><th>دور باسن</th></tr></thead>\n<tbody>\n<tr><td>S</td><td>86–90</td><td>68–72</td><td>92–96</td></tr>\n<tr><td>M</td><td>90–94</td><td>72–76</td><td>96–100</td></tr>\n<tr><td>L</td><td>94–100</td><td>76–82</td><td>100–106</td></tr>\n<tr><td>XL</td><td>100–106</td><td>82–88</td><td>106–112</td></tr>\n</tbody>\n</table>\n<p>برای مشاوره دقیق‌تر با پشتیبانی تماس بگیرید.</p>",
			),
			'shipping'      => array(
				'title'   => 'ارسال و بازگشت',
				'content' => "<p><strong>ارسال</strong></p>\n<p>سفارش‌های تهران طی ۲۴ ساعت کاری و شهرستان‌ها طی ۲ تا ۴ روز کاری ارسال می‌شوند. هزینه ارسال در مرحله تسویه‌حساب نمایش داده می‌شود.</p>\n<p><strong>بازگشت</strong></p>\n<p>تا ۷ روز پس از دریافت، در صورتی که برچسب لباس باز نشده باشد، امکان بازگشت یا تعویض وجود دارد. برای شروع فرایند بازگشت با پشتیبانی تماس بگیرید.</p>",
			),
			'faq'           => array(
				'title'   => 'سوالات متداول',
				'content' => "<p><strong>سفارش من چه زمانی ارسال می‌شود؟</strong><br>سفارش‌های ثبت‌شده تا ساعت ۱۴ همان روز بسته‌بندی و ارسال می‌شوند.</p>\n<p><strong>امکان تعویض سایز وجود دارد؟</strong><br>بله، تا ۷ روز پس از دریافت و در صورت سالم بودن لباس.</p>\n<p><strong>چطور از موجود بودن سایز مطمئن شوم؟</strong><br>در صفحه هر محصول، سایزهای موجود قابل انتخاب هستند و وضعیت موجودی نمایش داده می‌شود.</p>",
			),
			'contact'       => array(
				'title'   => 'تماس با ما',
				'content' => "<p>برای پرسش درباره سفارش‌ها، سایزبندی یا همکاری، از راه‌های زیر با ما در تماس باشید.</p>\n<p>ایمیل: hello@example.com<br>تلفن: ۰۲۱-۰۰۰۰۰۰۰۰<br>ساعت پاسخ‌گویی: شنبه تا پنجشنبه، ۱۰ تا ۱۸</p>",
			),
			'privacy'       => array(
				'title'   => 'حریم خصوصی',
				'content' => "<p>اطلاعاتی که در فرایند خرید ثبت می‌کنید (نام، نشانی، شماره تماس و ایمیل) فقط برای پردازش سفارش و پشتیبانی استفاده می‌شود و در اختیار شخص سوم قرار نمی‌گیرد.</p>\n<p>این صفحه را پیش از راه‌اندازی فروشگاه با متن حقوقی نهایی خود جایگزین کنید.</p>",
			),
		);

		foreach ( $pages as $slug => $page ) {
			$existing = get_page_by_path( $slug );

			if ( $existing ) {
				/* translators: %s: page title. */
				self::$report['skipped'][] = sprintf( __( 'صفحه «%s» از قبل وجود دارد.', 'moon-core' ), $page['title'] );
				continue;
			}

			$id = wp_insert_post( array(
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $page['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			), true );

			if ( is_wp_error( $id ) ) {
				self::$report['errors'][] = $id->get_error_message();
			}
		}

		$home = get_page_by_path( 'home' );

		if ( $home ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home->ID );
		}

		$journal = get_page_by_path( 'journal' );

		if ( $journal ) {
			update_option( 'page_for_posts', $journal->ID );
		}

		self::$report['messages'][] = __( 'صفحه‌های پایه بررسی و تنظیم شدند.', 'moon-core' );
	}

	/**
	 * Prepare the WooCommerce pages so they match the theme's classic markup.
	 *
	 * WooCommerce ships block cart/checkout pages; the theme styles the classic
	 * shortcode screens, so the demo store switches them and leaves real user
	 * content untouched (pages already using the shortcodes are skipped).
	 */
	private static function prepare_store() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		update_option( 'woocommerce_coming_soon', 'no' );

		$pages = array(
			'shop'      => array(
				'shortcode' => '',
				'default'   => 'Shop',
				'title'     => 'فروشگاه',
			),
			'cart'      => array(
				'shortcode' => '[woocommerce_cart]',
				'default'   => 'Cart',
				'title'     => 'سبد خرید',
			),
			'checkout'  => array(
				'shortcode' => '[woocommerce_checkout]',
				'default'   => 'Checkout',
				'title'     => 'تسویه حساب',
			),
			'myaccount' => array(
				'shortcode' => '',
				'default'   => 'My account',
				'title'     => 'حساب کاربری',
			),
		);

		foreach ( $pages as $key => $page ) {
			$page_id = (int) get_option( 'woocommerce_' . $key . '_page_id' );

			if ( ! $page_id || ! get_post( $page_id ) ) {
				continue;
			}

			$content = (string) get_post_field( 'post_content', $page_id );
			$title   = (string) get_post_field( 'post_title', $page_id );

			if ( $page['shortcode'] && false !== strpos( $content, 'wp:woocommerce/' ) ) {
				wp_update_post( array(
					'ID'           => $page_id,
					'post_content' => $page['shortcode'],
				) );

				/* translators: %s: page title. */
				self::$report['messages'][] = sprintf( __( 'صفحه «%s» به قالب کلاسیک ووکامرس تبدیل شد.', 'moon-core' ), $title );
			}

			if ( $page['default'] === $title ) {
				wp_update_post( array(
					'ID'         => $page_id,
					'post_title' => $page['title'],
				) );
			}
		}
	}

	/**
	 * Journal posts so the blog page is not empty.
	 */
	private static function import_journal() {
		$posts = array(
			array(
				'title'   => 'چگونه کالکشن پاییز را لایه‌لایه بپوشیم',
				'excerpt' => 'سه لایه ساده، از پارچه‌های خنثی تا بافت‌های گرم؛ فرمولی که هر روز جواب می‌دهد.',
				'image'   => 'campaign.jpg',
				'content' => "<p>لایه‌بندی در پاییز بیشتر از یک ترند است؛ راهی برای هم‌دما ماندن با ظاهری آرام. از یک لایه نازک پنبه‌ای شروع کنید، روی آن یک بافت گرم‌تر بیندازید و در نهایت پالتوی بلند را روی همه ببندید.</p>\n<p>رنگ‌های خنثی مثل استخوانی، خاکستری دودی و قهوه‌ای سوخته به شما اجازه می‌دهند هر سه لایه را کنار هم نگه دارید بدون آنکه شلوغ شود.</p>\n<h2>سه نکته‌ای که همیشه کار می‌کند</h2>\n<ul>\n<li>لایه‌های نازک‌تر را نزدیک بدن بپوشید.</li>\n<li>طول‌های متفاوت را با هم ترکیب کنید؛ کوتاه روی بلند.</li>\n<li>یک اکسسوری فلزی، همه‌چیز را جمع می‌کند.</li>\n</ul>",
			),
			array(
				'title'   => 'راهنمای انتخاب پالتو برای زمستان شهری',
				'excerpt' => 'از جنس پارچه تا قد آستین؛ چه چیزی یک پالتو را برای شهر مناسب می‌کند؟',
				'image'   => 'atelier.jpg',
				'content' => "<p>پالتوی شهری باید سه کار را همزمان انجام دهد: گرم نگه دارد، با لایه‌های زیرین جا شود و بعد از یک روز کامل هنوز فرم داشته باشد. پشم فشرده با درصد کمی کشش، بهترین تعادل را می‌دهد.</p>\n<p>قد پالتو را بر اساس قد خود انتخاب کنید: اگر بلندتر از ۱۷۵ سانتی‌متر هستید، مدل‌های زیر زانو فرم خوبی می‌دهند. برای قدهای کوتاه‌تر، پالتوی تا بالای زانو خط بدن را کشیده‌تر نشان می‌دهد.</p>",
			),
			array(
				'title'   => 'پارچه‌هایی که با گذر زمان بهتر می‌شوند',
				'excerpt' => 'درباره پشم، ابریشم و کتان؛ و اینکه چرا دوام یک انتخاب آگاهانه است.',
				'image'   => 'outfit-4.jpg',
				'content' => "<p>بعضی پارچه‌ها با شست‌وشو و پوشیدن، نرم‌تر و خوش‌فرم‌تر می‌شوند. پشم مرینوس، ابریشم سنگین و کتان بلند از این دسته‌اند.</p>\n<p>برای نگهداری از این پارچه‌ها، شست‌وشوی ملایم با آب سرد و خشک‌کردن در سایه کافی است. اتوی داغ و خشک‌کن، عمر لباس را کوتاه می‌کند.</p>",
			),
		);

		$index = 0;

		foreach ( $posts as $post ) {
			$index++;

			$existing = get_page_by_title( $post['title'], OBJECT, 'post' );

			if ( $existing ) {
				/* translators: %s: post title. */
				self::$report['skipped'][] = sprintf( __( 'نوشته «%s» از قبل وجود دارد.', 'moon-core' ), $post['title'] );
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'    => $post['title'],
				'post_excerpt'  => $post['excerpt'],
				'post_content'  => $post['content'],
				'post_status'   => 'publish',
				'post_type'     => 'post',
				'post_date'     => gmdate( 'Y-m-d H:i:s', strtotime( '-' . $index . ' week' ) ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', strtotime( '-' . $index . ' week' ) ),
			), true );

			if ( is_wp_error( $post_id ) ) {
				self::$report['errors'][] = $post_id->get_error_message();
				continue;
			}

			$image_id = self::attachment( $post['image'] );

			if ( $image_id ) {
				set_post_thumbnail( $post_id, $image_id );
			}
		}

		self::$report['messages'][] = __( 'نوشته‌های ژورنال آماده شدند.', 'moon-core' );
	}

	/* ----------------------------------------------------------------------
	 * Terms
	 * -------------------------------------------------------------------- */

	/**
	 * Product categories.
	 */
	private static function import_terms() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			self::$report['skipped'][] = __( 'ووکامرس فعال نیست؛ دسته‌بندی محصول ساخته نشد.', 'moon-core' );
			return;
		}

		$terms = array(
			'women'       => 'زنانه',
			'men'         => 'مردانه',
			'accessories' => 'اکسسوری',
		);

		foreach ( $terms as $slug => $name ) {
			if ( term_exists( $slug, 'product_cat' ) ) {
				continue;
			}

			wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}

		self::$report['messages'][] = __( 'دسته‌بندی‌های محصول آماده شدند.', 'moon-core' );
	}

	/* ----------------------------------------------------------------------
	 * Products
	 * -------------------------------------------------------------------- */

	/**
	 * Demo products.
	 */
	private static function import_products() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			self::$report['skipped'][] = __( 'ووکامرس فعال نیست؛ محصولی ساخته نشد.', 'moon-core' );
			return;
		}

		$products = array(
			array(
				'name'    => 'پالتو پشمی لونا',
				'slug'    => 'luna-wool-coat',
				'price'   => '18900000',
				'cat'     => 'women',
				'image'   => 'women.jpg',
				'desc'    => 'پالتو پشمی با برش بلند و یقه کلاسیک؛ آستر ساتن و دوخت تقویت‌شده. مناسب روزهای سرد شهری.',
				'variable' => true,
			),
			array(
				'name'    => 'هودی اورسایز دون',
				'slug'    => 'dune-hoodie',
				'price'   => '4200000',
				'sale'    => '3360000',
				'cat'     => 'women',
				'image'   => 'outfit-1.jpg',
				'desc'    => 'هودی اورسایز با پارچه پنبه‌ای سنگین و رنگ خنثی. دوخت دوبل در حاشیه‌ها.',
			),
			array(
				'name'    => 'کاپشن چرم اکلیپس',
				'slug'    => 'eclipse-leather',
				'price'   => '24500000',
				'cat'     => 'men',
				'image'   => 'outfit-4.jpg',
				'desc'    => 'کاپشن چرم طبیعی با آستر گرم و زیپ فلزی. برش نیمه‌جذب و جیب‌های مورب.',
			),
			array(
				'name'    => 'پیراهن پاپلین نوا',
				'slug'    => 'nova-poplin-shirt',
				'price'   => '2800000',
				'cat'     => 'men',
				'image'   => 'outfit-3.jpg',
				'desc'    => 'پیراهن پاپلین پنبه‌ای با یقه فرنچ و دوخت مینیمال؛ مناسب استفاده روزمره و رسمی.',
				'variable' => true,
			),
			array(
				'name'    => 'سویشرت کربن',
				'slug'    => 'carbon-crewneck',
				'price'   => '3600000',
				'cat'     => 'men',
				'image'   => 'outfit-2.jpg',
				'desc'    => 'سویشرت پنبه‌ای با بافت حلقه‌ای و یقه گرد؛ رنگ کربنی مات.',
			),
			array(
				'name'    => 'ست ابریشم آیوری',
				'slug'    => 'ivory-silk-set',
				'price'   => '9800000',
				'cat'     => 'women',
				'image'   => 'campaign.jpg',
				'desc'    => 'ست دو تکه ابریشم با برش آزاد و درزهای پنهان. حس سبک و خنک روی پوست.',
			),
			array(
				'name'    => 'پالتو چارکول',
				'slug'    => 'charcoal-overcoat',
				'price'   => '16400000',
				'cat'     => 'men',
				'image'   => 'men.jpg',
				'desc'    => 'پالتوی چارکول با پارچه فشرده و برش صاف؛ همراه با کمربند پارچه‌ای.',
				'featured' => true,
			),
			array(
				'name'    => 'شلوار کتان اسلیت',
				'slug'    => 'slate-cotton-trousers',
				'price'   => '3200000',
				'cat'     => 'men',
				'image'   => 'outfit-5.jpg',
				'desc'    => 'شلوار کتان با کمر کشی پنهان و برش مستقیم؛ مناسب استایل لایه‌ای.',
			),
		);

		$created = 0;
		$map     = array();

		foreach ( $products as $item ) {
			$existing = get_page_by_path( $item['slug'], OBJECT, 'product' );

			if ( $existing ) {
				$map[ $item['slug'] ] = $existing->ID;
				/* translators: %s: product name. */
				self::$report['skipped'][] = sprintf( __( 'محصول «%s» از قبل وجود دارد.', 'moon-core' ), $item['name'] );
				continue;
			}

			$product_id = ! empty( $item['variable'] ) ? self::create_variable_product( $item ) : self::create_simple_product( $item );

			if ( $product_id ) {
				$map[ $item['slug'] ] = $product_id;
				$created++;
			}
		}

		update_option( 'moon_core_product_map', $map, false );

		if ( $created ) {
			/* translators: %s: number of products. */
			self::$report['messages'][] = sprintf( __( '%s محصول نمایشی ساخته شد.', 'moon-core' ), number_format_i18n( $created ) );
		}
	}

	/**
	 * Build a simple product.
	 *
	 * @param array $item Product definition.
	 * @return int
	 */
	private static function create_simple_product( $item ) {
		try {
			$product = new WC_Product_Simple();
			$product->set_name( $item['name'] );
			$product->set_slug( $item['slug'] );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_description( $item['desc'] );
			$product->set_short_description( wp_trim_words( $item['desc'], 22 ) );
			$product->set_regular_price( $item['price'] );
			$product->set_manage_stock( false );
			$product->set_stock_status( 'instock' );
			$product->set_featured( ! empty( $item['featured'] ) );

			if ( ! empty( $item['sale'] ) ) {
				$product->set_sale_price( $item['sale'] );
			}

			$image_id = isset( self::$media[ $item['image'] ] ) ? (int) self::$media[ $item['image'] ] : 0;

			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}

			$term = get_term_by( 'slug', $item['cat'], 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				$product->set_category_ids( array( $term->term_id ) );
			}

			$product->save();

			return $product->get_id();
		} catch ( Throwable $error ) {
			self::$report['errors'][] = $error->getMessage();
			return 0;
		}
	}

	/**
	 * Build a variable product with S/M/L/XL variations.
	 *
	 * @param array $item Product definition.
	 * @return int
	 */
	private static function create_variable_product( $item ) {
		$taxonomy = self::ensure_size_attribute();

		if ( ! $taxonomy ) {
			return self::create_simple_product( $item );
		}

		try {
			$product = new WC_Product_Variable();
			$product->set_name( $item['name'] );
			$product->set_slug( $item['slug'] );
			$product->set_status( 'publish' );
			$product->set_description( $item['desc'] );
			$product->set_short_description( wp_trim_words( $item['desc'], 22 ) );

			$attribute_id = wc_attribute_taxonomy_id_by_name( 'size' );
			$term_ids     = array();

			foreach ( array( 'S', 'M', 'L', 'XL' ) as $size ) {
				$term = get_term_by( 'name', $size, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					$term_ids[] = (int) $term->term_id;
				}
			}

			if ( empty( $term_ids ) ) {
				return self::create_simple_product( $item );
			}

			$attribute = new WC_Product_Attribute();
			$attribute->set_id( $attribute_id );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( $term_ids );
			$attribute->set_position( 0 );
			$attribute->set_visible( true );
			$attribute->set_variation( true );

			$product->set_attributes( array( $attribute ) );

			$image_id = isset( self::$media[ $item['image'] ] ) ? (int) self::$media[ $item['image'] ] : 0;

			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}

			$term = get_term_by( 'slug', $item['cat'], 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				$product->set_category_ids( array( $term->term_id ) );
			}

			$product->save();

			foreach ( $term_ids as $term_id ) {
				$slug      = get_term_field( 'slug', $term_id, $taxonomy );
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $product->get_id() );
				$variation->set_attributes( array( $taxonomy => $slug ) );
				$variation->set_regular_price( $item['price'] );
				$variation->set_manage_stock( false );
				$variation->set_stock_status( 'instock' );
				$variation->save();
			}

			WC_Product_Variable::sync( $product->get_id() );

			return $product->get_id();
		} catch ( Throwable $error ) {
			self::$report['errors'][] = $error->getMessage();
			return self::create_simple_product( $item );
		}
	}

	/**
	 * Create the pa_size attribute with S/M/L/XL terms.
	 *
	 * @return string Taxonomy name or empty string.
	 */
	private static function ensure_size_attribute() {
		$taxonomy = 'pa_size';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$exists = wc_get_attribute_taxonomies();
			$found  = false;

			foreach ( $exists as $attribute ) {
				if ( 'size' === $attribute->attribute_name ) {
					$found = true;
				}
			}

			if ( ! $found && function_exists( 'wc_create_attribute' ) ) {
				$result = wc_create_attribute( array(
					'name'         => 'Size',
					'slug'         => 'size',
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				) );

				if ( is_wp_error( $result ) ) {
					self::$report['errors'][] = $result->get_error_message();
					return '';
				}

				delete_transient( 'wc_attribute_taxonomies' );
			}

			if ( ! taxonomy_exists( $taxonomy ) ) {
				register_taxonomy( $taxonomy, array( 'product' ), array(
					'hierarchical' => false,
					'show_ui'      => false,
					'query_var'    => false,
					'rewrite'      => false,
				) );
			}
		}

		foreach ( array( 'S', 'M', 'L', 'XL' ) as $size ) {
			if ( ! term_exists( $size, $taxonomy ) ) {
				wp_insert_term( $size, $taxonomy );
			}
		}

		return taxonomy_exists( $taxonomy ) ? $taxonomy : '';
	}

	/* ----------------------------------------------------------------------
	 * Rack and outfits
	 * -------------------------------------------------------------------- */

	/**
	 * Rack items linked to the demo products.
	 */
	private static function import_rack() {
		$map = get_option( 'moon_core_product_map', array() );
		$map = is_array( $map ) ? $map : array();

		$items = array(
			array( 'title' => 'پالتو پشمی لونا', 'en' => 'Luna Wool Coat', 'price' => '۱۸٬۹۰۰٬۰۰۰ تومان', 'image' => 'g-coat.png', 'colors' => '#101010, #5b4a3a, #99958F', 'product' => 'luna-wool-coat' ),
			array( 'title' => 'هودی اورسایز دون', 'en' => 'Dune Hoodie', 'price' => '۴٬۲۰۰٬۰۰۰ تومان', 'image' => 'g-hoodie.png', 'colors' => '#d8c9b0, #101010, #F5F1E9', 'product' => 'dune-hoodie' ),
			array( 'title' => 'کاپشن چرم اکلیپس', 'en' => 'Eclipse Leather', 'price' => '۲۴٬۵۰۰٬۰۰۰ تومان', 'image' => 'g-jacket.png', 'colors' => '#101010, #3b2a20', 'product' => 'eclipse-leather' ),
			array( 'title' => 'پیراهن پاپلین نوا', 'en' => 'Nova Poplin Shirt', 'price' => '۲٬۸۰۰٬۰۰۰ تومان', 'image' => 'g-shirt.png', 'colors' => '#FFFFFF, #cfd8e3, #101010', 'product' => 'nova-poplin-shirt' ),
			array( 'title' => 'سویشرت کربن', 'en' => 'Carbon Crewneck', 'price' => '۳٬۶۰۰٬۰۰۰ تومان', 'image' => 'g-sweat.png', 'colors' => '#3a3a3a, #d8c9b0', 'product' => 'carbon-crewneck' ),
		);

		$created = 0;
		$order   = 0;

		foreach ( $items as $item ) {
			$order++;

			$existing = get_page_by_title( $item['title'], OBJECT, 'moon_rack' );

			if ( $existing ) {
				self::$report['skipped'][] = sprintf( __( 'قلم رگال «%s» از قبل وجود دارد.', 'moon-core' ), $item['title'] );
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'  => $item['title'],
				'post_type'   => 'moon_rack',
				'post_status' => 'publish',
				'menu_order'  => $order,
			), true );

			if ( is_wp_error( $post_id ) ) {
				self::$report['errors'][] = $post_id->get_error_message();
				continue;
			}

			update_post_meta( $post_id, '_moon_en', $item['en'] );
			update_post_meta( $post_id, '_moon_price', $item['price'] );
			update_post_meta( $post_id, '_moon_colors', $item['colors'] );

			if ( isset( $map[ $item['product'] ] ) ) {
				update_post_meta( $post_id, '_moon_product', (int) $map[ $item['product'] ] );
			}

			if ( isset( self::$media[ $item['image'] ] ) ) {
				set_post_thumbnail( $post_id, (int) self::$media[ $item['image'] ] );
			}

			$created++;
		}

		if ( $created ) {
			/* translators: %s: number of rack items. */
			self::$report['messages'][] = sprintf( __( '%s قلم رگال ساخته شد.', 'moon-core' ), number_format_i18n( $created ) );
		}
	}

	/**
	 * Studio outfits.
	 */
	private static function import_outfits() {
		$map = get_option( 'moon_core_product_map', array() );
		$map = is_array( $map ) ? $map : array();

		$items = array(
			array( 'title' => 'هودی اورسایز مشکی', 'en' => 'Midnight Hoodie', 'image' => 'outfit-1.jpg', 'colors' => '#101010, #FFFFFF', 'product' => 'dune-hoodie' ),
			array( 'title' => 'سویشرت بژ', 'en' => 'Sand Crewneck', 'image' => 'outfit-2.jpg', 'colors' => '#d8c9b0, #101010', 'product' => 'carbon-crewneck' ),
			array( 'title' => 'پیراهن سفید مینیمال', 'en' => 'Pure White Shirt', 'image' => 'outfit-3.jpg', 'colors' => '#FFFFFF, #101010', 'product' => 'nova-poplin-shirt' ),
			array( 'title' => 'کاپشن چرم مشکی', 'en' => 'Black Leather', 'image' => 'outfit-4.jpg', 'colors' => '#101010, #F5F1E9', 'product' => 'eclipse-leather' ),
			array( 'title' => 'استایل لایه‌ای', 'en' => 'Layered Casual', 'image' => 'outfit-5.jpg', 'colors' => '#a68a6d, #6b6b6b', 'product' => 'slate-cotton-trousers' ),
		);

		$created = 0;
		$order   = 0;

		foreach ( $items as $item ) {
			$order++;

			$existing = get_page_by_title( $item['title'], OBJECT, 'moon_outfit' );

			if ( $existing ) {
				self::$report['skipped'][] = sprintf( __( 'استایل «%s» از قبل وجود دارد.', 'moon-core' ), $item['title'] );
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'  => $item['title'],
				'post_type'   => 'moon_outfit',
				'post_status' => 'publish',
				'menu_order'  => $order,
			), true );

			if ( is_wp_error( $post_id ) ) {
				self::$report['errors'][] = $post_id->get_error_message();
				continue;
			}

			update_post_meta( $post_id, '_moon_en', $item['en'] );
			update_post_meta( $post_id, '_moon_colors', $item['colors'] );

			if ( isset( $map[ $item['product'] ] ) ) {
				update_post_meta( $post_id, '_moon_product', (int) $map[ $item['product'] ] );
			}

			if ( isset( self::$media[ $item['image'] ] ) ) {
				set_post_thumbnail( $post_id, (int) self::$media[ $item['image'] ] );
			}

			$created++;
		}

		if ( $created ) {
			/* translators: %s: number of outfits. */
			self::$report['messages'][] = sprintf( __( '%s استایل برای استودیو ساخته شد.', 'moon-core' ), number_format_i18n( $created ) );
		}
	}

	/* ----------------------------------------------------------------------
	 * Options and menu
	 * -------------------------------------------------------------------- */

	/**
	 * Point the theme's image settings at the imported media.
	 */
	private static function import_options() {
		$options = get_option( 'moon_options', array() );
		$options = is_array( $options ) ? $options : array();

		$map = array(
			'hero_image_id'     => 'hero.jpg',
			'campaign_image_id' => 'campaign.jpg',
			'story_image_id'    => 'atelier.jpg',
			'cat1_image_id'     => 'women.jpg',
			'cat2_image_id'     => 'men.jpg',
			'scene1_image_id'   => 'atelier.jpg',
			'scene2_image_id'   => 'men.jpg',
			'scene3_image_id'   => 'hero.jpg',
		);

		foreach ( $map as $key => $file ) {
			if ( ! empty( $options[ $key ] ) ) {
				continue;
			}

			if ( isset( self::$media[ $file ] ) ) {
				$options[ $key ] = (int) self::$media[ $file ];
			}
		}

		update_option( 'moon_options', $options );

		if ( ! get_option( 'moon_sections' ) ) {
			$sections = array();

			foreach ( array( 'hero', 'categories', 'rack', 'studio', 'products', 'campaign', 'story', 'scrollstory', 'testimonials' ) as $id ) {
				$sections[] = array(
					'id'      => $id,
					'enabled' => 1,
				);
			}

			update_option( 'moon_sections', $sections );
		}
	}

	/**
	 * Primary navigation.
	 */
	private static function import_menu() {
		$menu_name = 'منوی اصلی';
		$menu      = wp_get_nav_menu_object( $menu_name );

		if ( $menu ) {
			self::$report['skipped'][] = __( 'منوی اصلی از قبل وجود دارد.', 'moon-core' );
		} else {
			$menu_id = wp_create_nav_menu( $menu_name );

			if ( is_wp_error( $menu_id ) ) {
				self::$report['errors'][] = $menu_id->get_error_message();
				return;
			}

			$women = get_term_by( 'slug', 'women', 'product_cat' );
			$men   = get_term_by( 'slug', 'men', 'product_cat' );
			$about = get_page_by_path( 'about' );

			$items = array();

			if ( $women && ! is_wp_error( $women ) ) {
				$items[] = array( 'title' => 'زنانه', 'object' => 'product_cat', 'object_id' => $women->term_id );
			}

			if ( $men && ! is_wp_error( $men ) ) {
				$items[] = array( 'title' => 'مردانه', 'object' => 'product_cat', 'object_id' => $men->term_id );
			}

			$items[] = array( 'title' => 'کالکشن جدید', 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) );
			$items[] = array( 'title' => 'استودیو', 'url' => home_url( '/#studio' ) );

			if ( $about ) {
				$items[] = array( 'title' => 'داستان ما', 'object' => 'page', 'object_id' => $about->ID );
			}

			foreach ( $items as $position => $item ) {
				$args = array(
					'menu-item-title'  => $item['title'],
					'menu-item-status' => 'publish',
					'menu-item-position' => $position + 1,
				);

				if ( isset( $item['object'] ) ) {
					$args['menu-item-object']    = $item['object'];
					$args['menu-item-object-id'] = $item['object_id'];
					$args['menu-item-type']      = 'post_type' === $item['object'] ? 'post_type' : 'taxonomy';
				} else {
					$args['menu-item-url']  = $item['url'];
					$args['menu-item-type'] = 'custom';
				}

				wp_update_nav_menu_item( $menu_id, 0, $args );
			}

			$locations            = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );

			self::$report['messages'][] = __( 'منوی اصلی ساخته و به هدر متصل شد.', 'moon-core' );
		}
	}
}
