<?php
/**
 * Demo content importer.
 *
 * Safe by design: nothing is deleted or overwritten. Existing pages, products,
 * terms and media are detected and skipped, and every run is idempotent.
 *
 * @package Moon_Demo_Importer
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
	 * Whether created IDs are being recorded for the import history.
	 *
	 * @var bool
	 */
	private static $collecting = false;

	/**
	 * Import steps: option key => callable(s).
	 *
	 * @return array
	 */
	public static function steps() {
		return array(
			'media'      => array( 'import_media' ),
			'pages'      => array( 'import_pages', 'import_journal' ),
			'terms'      => array( 'import_terms' ),
			'store'      => array( 'prepare_store' ),
			'products'   => array( 'import_products' ),
			'rack'       => array( 'import_rack', 'import_outfits' ),
			'options'    => array( 'import_options' ),
			'menu'       => array( 'import_menu' ),
			'megamenu'   => array( 'import_megamenu' ),
		);
	}

	/**
	 * Selection labels used by the import screen.
	 *
	 * @return array
	 */
	public static function step_labels() {
		return array(
			'media'    => 'تصاویر و رسانه‌ها',
			'pages'    => 'صفحه‌های پایه و مقالات ژورنال',
			'terms'    => 'دسته‌بندی محصولات',
			'store'    => 'تنظیمات صفحه‌های ووکامرس (سبد، تسویه، فروشگاه)',
			'products' => 'محصولات نمایشی ووکامرس',
			'rack'     => 'اقلام رگال و استایل‌های استودیو',
			'options'  => 'تنظیمات ظاهری قالب (تصاویر بخش‌ها، ترتیب بخش‌ها)',
			'menu'     => 'منوی اصلی',
			'megamenu' => 'مگامنوی دسته‌بندی کالاها',
		);
	}

	/**
	 * Hook WP-CLI.
	 */
	public static function init() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'moon-demo', array( __CLASS__, 'cli' ) );
		}

		// IDs created during a run feed the history and the rollback.
		add_action( 'save_post', array( __CLASS__, 'collect_post' ), 10, 1 );
		add_action( 'created_term', array( __CLASS__, 'collect_term' ), 10, 1 );

		add_action( 'wp_ajax_moon_demo_step', array( __CLASS__, 'ajax_step' ) );
		add_action( 'wp_ajax_moon_demo_rollback', array( __CLASS__, 'ajax_rollback' ) );
	}

	/**
	 * Transient key holding the posts created in this import.
	 *
	 * @return string
	 */
	private static function created_key() {
		return 'moon_demo_created_' . get_current_user_id();
	}

	/**
	 * Transient key holding the terms created in this import.
	 *
	 * @return string
	 */
	private static function created_terms_key() {
		return 'moon_demo_created_terms_' . get_current_user_id();
	}

	/**
	 * Record a created post while an import is running.
	 *
	 * @param int $post_id Post id.
	 */
	public static function collect_post( $post_id ) {
		if ( ! self::$collecting || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$ids = (array) get_transient( self::created_key() );

		if ( ! in_array( (int) $post_id, array_map( 'intval', $ids ), true ) ) {
			$ids[] = (int) $post_id;
			set_transient( self::created_key(), $ids, DAY_IN_SECONDS );
		}
	}

	/**
	 * Record a created term while an import is running.
	 *
	 * @param int $term_id Term id.
	 */
	public static function collect_term( $term_id ) {
		if ( ! self::$collecting ) {
			return;
		}

		$ids = (array) get_transient( self::created_terms_key() );

		if ( ! in_array( (int) $term_id, array_map( 'intval', $ids ), true ) ) {
			$ids[] = (int) $term_id;
			set_transient( self::created_terms_key(), $ids, DAY_IN_SECONDS );
		}
	}

	/**
	 * WP-CLI entry point: wp moon-demo import --products --media ...
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Flags.
	 */
	public static function cli( $args, $assoc_args ) {
		if ( isset( $args[0] ) && 'store' === $args[0] && class_exists( 'Moon_Store_Catalog' ) ) {
			$store = Moon_Store_Catalog::run();

			foreach ( $store['messages'] as $message ) {
				WP_CLI::log( '✓ ' . $message );
			}

			foreach ( $store['errors'] as $message ) {
				WP_CLI::warning( $message );
			}

			return;
		}

		if ( isset( $args[0] ) && 'catalog' === $args[0] ) {
			$catalog = Moon_Catalog::upgrade();

			foreach ( $catalog['messages'] as $message ) {
				WP_CLI::log( '✓ ' . $message );
			}

			foreach ( $catalog['errors'] as $message ) {
				WP_CLI::warning( $message );
			}

			return;
		}

		$options = array(
			'media'    => true,
			'pages'    => true,
			'terms'    => true,
			'store'    => ! isset( $assoc_args['no-store'] ),
			'rack'     => true,
			'options'  => true,
			'menu'     => true,
			'megamenu' => true,
			'products' => isset( $assoc_args['products'] ),
		);

		$report = self::run( $options );

		if ( isset( $assoc_args['catalog'] ) ) {
			$catalog = Moon_Catalog::upgrade();

			$report['messages'] = array_merge( $report['messages'], $catalog['messages'] );
			$report['errors']   = array_merge( $report['errors'], $catalog['errors'] );
		}

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
		self::reset_report();
		self::start_collect();

		$options = self::selection( $options );

		foreach ( self::steps() as $step => $callbacks ) {
			// A step only runs when it was selected: choosing "images only" must
			// never touch store pages, the menu or the theme options.
			if ( empty( $options[ $step ] ) ) {
				self::$report['skipped'][] = sprintf(
					/* translators: %s: step label. */
					__( 'مرحله «%s» انتخاب نشده بود و اجرا نشد.', 'moon-theme' ),
					self::step_labels()[ $step ]
				);
				continue;
			}

			foreach ( $callbacks as $callback ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}

		self::finish( $options );

		return self::$report;
	}

	/**
	 * Normalise an import selection.
	 *
	 * @param array $options Raw selection.
	 * @return array
	 */
	public static function selection( $options = array() ) {
		$options = is_array( $options ) ? $options : array();
		$clean   = array();

		foreach ( array_keys( self::steps() ) as $step ) {
			$clean[ $step ] = ! empty( $options[ $step ] );
		}

		// Aliases kept so the WP-CLI flags and older callers keep working.
		if ( ! empty( $options['taxonomies'] ) ) {
			$clean['terms'] = true;
		}

		return $clean;
	}

	/**
	 * Reset the per-run report.
	 */
	private static function reset_report() {
		self::$report = array(
			'messages' => array(),
			'skipped'  => array(),
			'errors'   => array(),
		);
	}

	/**
	 * Start recording created content.
	 */
	private static function start_collect() {
		self::$collecting = true;
	}

	/**
	 * Stop recording, save the history and mark the import finished.
	 *
	 * @param array $options Executed selection.
	 */
	private static function finish( $options ) {
		self::$collecting = false;

		$history = array(
			'time'     => time(),
			'user'     => get_current_user_id(),
			'options'  => $options,
			'created'  => array(
				'posts' => array_map( 'intval', (array) get_transient( self::created_key() ) ),
				'terms' => array_map( 'intval', (array) get_transient( self::created_terms_key() ) ),
			),
			'backup'   => self::take_backup(),
			'report'   => self::$report,
			'complete' => empty( self::$report['errors'] ),
		);

		update_option( 'moon_demo_history', $history, false );
		update_option( 'moon_demo_last_report', self::$report, false );

		// The next run starts a fresh list of created content.
		delete_transient( self::created_key() );
		delete_transient( self::created_terms_key() );

		update_option( 'moon_core_imported', array(
			'time'     => date_i18n( 'Y-m-d H:i' ),
			'sections' => count( self::$report['messages'] ),
			'complete' => $history['complete'],
		), false );

		if ( self::$report['errors'] ) {
			self::$report['messages'][] = __( 'این اجرا با خطا به پایان رسید؛ بخش‌های ناموفق را در فهرست خطاها ببینید.', 'moon-theme' );
		} else {
			self::$report['messages'][] = __( 'نصب با موفقیت به پایان رسید.', 'moon-theme' );
		}

		update_option( 'moon_demo_last_report', self::$report, false );
	}

	/**
	 * Snapshot of the settings an import can change.
	 *
	 * @return array
	 */
	public static function take_backup() {
		return array(
			'moon_options'        => get_option( 'moon_options', array() ),
			'moon_sections'       => get_option( 'moon_sections', array() ),
			'moon_footer_columns' => get_option( 'moon_footer_columns', array() ),
			'moon_megamenu'       => get_option( 'moon_megamenu', array() ),
			'show_on_front'       => get_option( 'show_on_front' ),
			'page_on_front'       => get_option( 'page_on_front' ),
			'page_for_posts'      => get_option( 'page_for_posts' ),
			'woocommerce_coming_soon' => get_option( 'woocommerce_coming_soon' ),
		);
	}

	/**
	 * Last import, for the panel.
	 *
	 * @return array|null
	 */
	public static function history() {
		$history = get_option( 'moon_demo_history' );

		return is_array( $history ) ? $history : null;
	}

	/**
	 * Undo the last import: remove what it created and restore the settings it
	 * changed. Content the import did not create is never touched.
	 *
	 * @return array Report.
	 */
	public static function rollback() {
		$history = self::history();

		if ( ! $history || empty( $history['created'] ) ) {
			return array( 'messages' => array( __( 'نسخه‌ای برای بازگردانی وجود ندارد.', 'moon-theme' ) ), 'errors' => array() );
		}

		$report = array( 'messages' => array(), 'errors' => array() );

		foreach ( (array) $history['created']['posts'] as $post_id ) {
			$post = get_post( (int) $post_id );

			if ( ! $post ) {
				continue;
			}

			// Only demo content is removed, and only when it was not repurposed.
			$deleted = wp_delete_post( (int) $post_id, true );

			if ( $deleted ) {
				$report['messages'][] = sprintf(
					/* translators: %s: post title. */
					__( '«%s» حذف شد.', 'moon-theme' ),
					$post->post_title
				);
			} else {
				$report['errors'][] = sprintf(
					/* translators: %s: post title. */
					__( 'حذف «%s» انجام نشد.', 'moon-theme' ),
					$post->post_title
				);
			}
		}

		foreach ( (array) $history['created']['terms'] as $term_id ) {
			$term = get_term( (int) $term_id );

			if ( $term && ! is_wp_error( $term ) ) {
				wp_delete_term( (int) $term_id, $term->taxonomy );
			}
		}

		$backup = isset( $history['backup'] ) && is_array( $history['backup'] ) ? $history['backup'] : array();

		foreach ( array( 'moon_options', 'moon_sections', 'moon_footer_columns', 'moon_megamenu' ) as $option ) {
			if ( array_key_exists( $option, $backup ) ) {
				update_option( $option, $backup[ $option ] );
			}
		}

		foreach ( array( 'show_on_front', 'page_on_front', 'page_for_posts', 'woocommerce_coming_soon' ) as $option ) {
			if ( array_key_exists( $option, $backup ) && '' !== $backup[ $option ] ) {
				update_option( $option, $backup[ $option ] );
			}
		}

		delete_option( 'moon_demo_history' );

		$report['messages'][] = __( 'تنظیمات به وضعیت پیش از نصب بازگشت.', 'moon-theme' );

		return $report;
	}

	/**
	 * AJAX: run one import step, so a long import cannot time out.
	 */
	public static function ajax_step() {
		check_ajax_referer( 'moon_demo', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'دسترسی کافی ندارید.', 'moon-theme' ) ), 403 );
		}

		$step    = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		$options = isset( $_POST['options'] ) ? (array) wp_unslash( $_POST['options'] ) : array();
		$steps   = self::steps();

		self::reset_report();

		if ( ! isset( $steps[ $step ] ) ) {
			wp_send_json_error( array( 'message' => __( 'مرحله نامعتبر است.', 'moon-theme' ) ) );
		}

		// A backup is taken before the first step, so a rollback always has a
		// snapshot of the state the store was in.
		if ( 'first' === $step ) {
			set_transient( 'moon_demo_backup_' . get_current_user_id(), self::take_backup(), DAY_IN_SECONDS );
			delete_transient( self::created_key() );
			delete_transient( self::created_terms_key() );

			wp_send_json_success( array(
				'messages' => array( __( 'نسخه پشتیبان از تنظیمات گرفته شد.', 'moon-theme' ) ),
				'errors'   => array(),
				'skipped'  => array(),
			) );
		}

		self::start_collect();

		self::$media = get_option( 'moon_core_media', array() );
		self::$media = is_array( self::$media ) ? self::$media : array();

		foreach ( $steps[ $step ] as $callback ) {
			call_user_func( array( __CLASS__, $callback ) );
		}

		self::$collecting = false;

		update_option( 'moon_core_media', self::$media, false );

		$is_last = 'menu' === $step;

		if ( $is_last ) {
			$backup = get_transient( 'moon_demo_backup_' . get_current_user_id() );

			$history = array(
				'time'     => time(),
				'user'     => get_current_user_id(),
				'options'  => self::selection( $options ),
				'created'  => array(
					'posts' => array_map( 'intval', (array) get_transient( self::created_key() ) ),
					'terms' => array_map( 'intval', (array) get_transient( self::created_terms_key() ) ),
				),
				'backup'   => is_array( $backup ) ? $backup : self::take_backup(),
				'complete' => empty( self::$report['errors'] ),
			);

			update_option( 'moon_demo_history', $history, false );
			update_option( 'moon_demo_last_report', self::$report, false );

			delete_transient( 'moon_demo_backup_' . get_current_user_id() );
			delete_transient( self::created_key() );
			delete_transient( self::created_terms_key() );
		}

		wp_send_json_success( array(
			'messages' => self::$report['messages'],
			'errors'   => self::$report['errors'],
			'skipped'  => self::$report['skipped'],
			'done'     => $is_last,
		) );
	}

	/**
	 * AJAX: roll back the last import.
	 */
	public static function ajax_rollback() {
		check_ajax_referer( 'moon_demo', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'دسترسی کافی ندارید.', 'moon-theme' ) ), 403 );
		}

		wp_send_json_success( self::rollback() );
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
			'g-coat.webp', 'g-hoodie.webp', 'g-jacket.webp', 'g-shirt.webp', 'g-sweat.webp',
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
			self::$report['messages'][] = sprintf( __( '%s تصویر در کتابخانه رسانه آماده شد.', 'moon-theme' ), number_format_i18n( $created ) );
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
			self::$report['errors'][] = sprintf( __( 'فایل تصویر پیدا نشد: %s', 'moon-theme' ), $file );
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
			self::$report['errors'][] = __( 'ثبت تصویر در کتابخانه رسانه ناموفق بود.', 'moon-theme' );
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
				self::$report['skipped'][] = sprintf( __( 'صفحه «%s» از قبل وجود دارد.', 'moon-theme' ), $page['title'] );
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

		self::$report['messages'][] = __( 'صفحه‌های پایه بررسی و تنظیم شدند.', 'moon-theme' );
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
				self::$report['messages'][] = sprintf( __( 'صفحه «%s» به قالب کلاسیک ووکامرس تبدیل شد.', 'moon-theme' ), $title );
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
		// The "Hello world!" sample post that ships with WordPress has no image
		// and an English body, so it would stand out on the Persian journal.
		$sample = get_page_by_path( 'hello-world', OBJECT, 'post' );

		if ( $sample && false !== strpos( (string) $sample->post_content, 'Welcome to WordPress' ) ) {
			wp_trash_post( $sample->ID );
		}

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

			$existing = moon_demo_find_by_title( $post['title'], 'post' );

			if ( $existing ) {
				/* translators: %s: post title. */
				self::$report['skipped'][] = sprintf( __( 'نوشته «%s» از قبل وجود دارد.', 'moon-theme' ), $post['title'] );
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

		self::$report['messages'][] = __( 'نوشته‌های ژورنال آماده شدند.', 'moon-theme' );
	}

	/* ----------------------------------------------------------------------
	 * Terms
	 * -------------------------------------------------------------------- */

	/**
	 * Product categories.
	 */
	private static function import_terms() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			self::$report['skipped'][] = __( 'ووکامرس فعال نیست؛ دسته‌بندی محصول ساخته نشد.', 'moon-theme' );
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

		self::$report['messages'][] = __( 'دسته‌بندی‌های محصول آماده شدند.', 'moon-theme' );
	}

	/* ----------------------------------------------------------------------
	 * Products
	 * -------------------------------------------------------------------- */

	/**
	 * Demo products.
	 */
	private static function import_products() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			self::$report['skipped'][] = __( 'ووکامرس فعال نیست؛ محصولی ساخته نشد.', 'moon-theme' );
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
				self::$report['skipped'][] = sprintf( __( 'محصول «%s» از قبل وجود دارد.', 'moon-theme' ), $item['name'] );
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
			self::$report['messages'][] = sprintf( __( '%s محصول نمایشی ساخته شد.', 'moon-theme' ), number_format_i18n( $created ) );
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
			array( 'title' => 'پالتو پشمی لونا', 'en' => 'Luna Wool Coat', 'price' => '۱۸٬۹۰۰٬۰۰۰ تومان', 'image' => 'g-coat.webp', 'colors' => '#101010, #5b4a3a, #99958F', 'product' => 'luna-wool-coat' ),
			array( 'title' => 'هودی اورسایز دون', 'en' => 'Dune Hoodie', 'price' => '۴٬۲۰۰٬۰۰۰ تومان', 'image' => 'g-hoodie.webp', 'colors' => '#d8c9b0, #101010, #F5F1E9', 'product' => 'dune-hoodie' ),
			array( 'title' => 'کاپشن چرم اکلیپس', 'en' => 'Eclipse Leather', 'price' => '۲۴٬۵۰۰٬۰۰۰ تومان', 'image' => 'g-jacket.webp', 'colors' => '#101010, #3b2a20', 'product' => 'eclipse-leather' ),
			array( 'title' => 'پیراهن پاپلین نوا', 'en' => 'Nova Poplin Shirt', 'price' => '۲٬۸۰۰٬۰۰۰ تومان', 'image' => 'g-shirt.webp', 'colors' => '#FFFFFF, #cfd8e3, #101010', 'product' => 'nova-poplin-shirt' ),
			array( 'title' => 'سویشرت کربن', 'en' => 'Carbon Crewneck', 'price' => '۳٬۶۰۰٬۰۰۰ تومان', 'image' => 'g-sweat.webp', 'colors' => '#3a3a3a, #d8c9b0', 'product' => 'carbon-crewneck' ),
		);

		$created = 0;
		$order   = 0;

		foreach ( $items as $item ) {
			$order++;

			$existing = moon_demo_find_by_title( $item['title'], 'moon_rack' );

			if ( $existing ) {
				self::$report['skipped'][] = sprintf( __( 'قلم رگال «%s» از قبل وجود دارد.', 'moon-theme' ), $item['title'] );
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
			self::$report['messages'][] = sprintf( __( '%s قلم رگال ساخته شد.', 'moon-theme' ), number_format_i18n( $created ) );
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

			$existing = moon_demo_find_by_title( $item['title'], 'moon_outfit' );

			if ( $existing ) {
				self::$report['skipped'][] = sprintf( __( 'استایل «%s» از قبل وجود دارد.', 'moon-theme' ), $item['title'] );
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
			self::$report['messages'][] = sprintf( __( '%s استایل برای استودیو ساخته شد.', 'moon-theme' ), number_format_i18n( $created ) );
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
	 * Mega menu (MOON 2.2 builder) built from the imported product categories.
	 *
	 * An existing mega menu is never overwritten: a shop that already arranged
	 * its own columns keeps them.
	 */
	private static function import_megamenu() {
		if ( get_option( 'moon_megamenu' ) ) {
			self::$report['skipped'][] = __( 'مگامنو از قبل تنظیم شده است.', 'moon-theme' );
			return;
		}

		if ( ! taxonomy_exists( 'product_cat' ) || ! function_exists( 'moon_mega_sanitize' ) ) {
			self::$report['skipped'][] = __( 'ووکامرس یا ماژول مگامنو در دسترس نیست.', 'moon-theme' );
			return;
		}

		$items = array();

		foreach ( array( 'women', 'men', 'kids', 'scarves' ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$item = array(
				'title'  => $term->name,
				'url'    => get_term_link( $term ),
				'image'  => (int) get_term_meta( $term->term_id, 'thumbnail_id', true ),
				'cols'   => array(),
				'banner' => array( 'image' => 0, 'url' => '', 'title' => '' ),
			);

			$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $term->term_id, 'hide_empty' => false ) );

			if ( ! is_wp_error( $children ) && $children ) {
				$links = array();

				foreach ( array_slice( $children, 0, 8 ) as $child ) {
					$links[] = array(
						'title' => $child->name,
						'url'   => get_term_link( $child ),
						'image' => (int) get_term_meta( $child->term_id, 'thumbnail_id', true ),
					);
				}

				$item['cols'][] = array(
					'title' => $term->name,
					'url'   => get_term_link( $term ),
					'links' => $links,
				);
			}

			$items[] = $item;
		}

		if ( ! $items ) {
			self::$report['skipped'][] = __( 'دسته‌بندی محصولی برای ساخت مگامنو پیدا نشد.', 'moon-theme' );
			return;
		}

		$data = moon_mega_sanitize( array(
			'enabled' => 1,
			'label'   => 'دسته‌بندی کالاها',
			'items'   => $items,
		) );

		update_option( 'moon_megamenu', $data );

		self::$report['messages'][] = __( 'مگامنو از دسته‌بندی‌های واردشده ساخته شد.', 'moon-theme' );
	}

	/**
	 * Primary navigation.
	 */
	private static function import_menu() {
		$menu_name = 'منوی اصلی';
		$menu      = wp_get_nav_menu_object( $menu_name );

		if ( $menu ) {
			self::$report['skipped'][] = __( 'منوی اصلی از قبل وجود دارد.', 'moon-theme' );
			self::build_mega_menus( (int) $menu->term_id );
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

			self::build_mega_menus( (int) $menu_id );

			self::$report['messages'][] = __( 'منوی اصلی ساخته و به هدر متصل شد.', 'moon-theme' );
		}
	}

	/**
	 * Two-level mega panels under the main category links.
	 *
	 * An item that already has children is skipped, so a menu the owner has
	 * reorganised is never overwritten.
	 *
	 * @param int $menu_id Menu id.
	 */
	private static function build_mega_menus( $menu_id ) {
		$items = wp_get_nav_menu_items( $menu_id );

		if ( ! $items || ! function_exists( 'wc_get_page_permalink' ) ) {
			return;
		}

		$shop_url = untrailingslashit( (string) wc_get_page_permalink( 'shop' ) );
		$groups   = array();

		foreach ( array( 'women', 'men', 'kids', 'scarves' ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$link = get_term_link( $term );

			if ( is_wp_error( $link ) ) {
				continue;
			}

			$groups[] = array(
				'label' => $term->name,
				'link'  => $link,
				'items' => array(
					__( 'جدیدترین‌ها', 'moon-theme' ) => add_query_arg( 'orderby', 'date', $link ),
					__( 'پرفروش‌ها', 'moon-theme' )   => add_query_arg( 'orderby', 'popularity', $link ),
					__( 'ارزان‌ترین', 'moon-theme' )  => add_query_arg( 'orderby', 'price', $link ),
				),
			);
		}

		if ( ! $groups ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( 0 !== (int) $item->menu_item_parent || untrailingslashit( (string) $item->url ) !== $shop_url ) {
				continue;
			}

			$has_children = false;

			foreach ( $items as $child ) {
				if ( (int) $child->menu_item_parent === (int) $item->ID ) {
					$has_children = true;
					break;
				}
			}

			update_post_meta( $item->ID, '_moon_mega', 1 );
			update_post_meta( $item->ID, '_moon_mega_title', __( 'کالکشن پاییز', 'moon-theme' ) );
			update_post_meta( $item->ID, '_moon_mega_text', __( 'لایه‌های گرم برای روزهای سرد', 'moon-theme' ) );
			update_post_meta( $item->ID, '_moon_mega_url', $item->url );

			$image_id = (int) get_post_meta( $item->ID, '_moon_mega_image', true );

			if ( ! $image_id ) {
				$image_id = self::attachment_by_file( 'campaign.jpg' );
			}

			if ( $image_id ) {
				update_post_meta( $item->ID, '_moon_mega_image', $image_id );
			}

			if ( $has_children ) {
				continue;
			}

			$position = 0;

			foreach ( $groups as $group ) {
				$group_id = wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $group['label'],
					'menu-item-url'       => $group['link'],
					'menu-item-type'      => 'custom',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $item->ID,
					'menu-item-position'  => ++$position,
				) );

				$sub = 0;

				foreach ( $group['items'] as $label => $url ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => $label,
						'menu-item-url'       => $url,
						'menu-item-type'      => 'custom',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => $group_id,
						'menu-item-position'  => ++$sub,
					) );
				}
			}

			self::$report['messages'][] = __( 'مگامنوی دسته‌بندی‌ها ساخته و به آیتم «فروشگاه» وصل شد.', 'moon-theme' );
		}
	}

	/**
	 * An already imported demo image, by file name.
	 *
	 * @param string $file File name in the theme assets.
	 * @return int Attachment id or 0.
	 */
	private static function attachment_by_file( $file ) {
		$uploads = wp_upload_dir();

		if ( empty( $uploads['error'] ) ) {
			$id = attachment_url_to_postid( trailingslashit( $uploads['baseurl'] ) . $file );

			if ( $id ) {
				return (int) $id;
			}
		}

		// An earlier import may have stored the file with a numeric suffix
		// (campaign-2.jpg), so look the attachment up by its title too.
		$base  = preg_replace( '/\.[^.]+$/', '', $file );
		$found = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'DESC',
			's'              => ucfirst( str_replace( array( '-', '_' ), ' ', (string) $base ) ),
			'fields'         => 'ids',
		) );

		return $found ? (int) $found[0] : 0;
	}
}
