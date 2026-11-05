<?php
/**
 * One-click site setup: demo content, Elementor pages, menus, media and the
 * theme settings, with a backup taken before anything changes and a rollback.
 *
 * @package DaniSite_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo importer.
 */
class Dani_Importer {

	/**
	 * Backup option name.
	 */
	const BACKUP = 'dani_import_backup';

	/**
	 * Meta key that marks content created by the importer (idempotency).
	 */
	const KEY = '_dani_demo_key';

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 12 );
		add_action( 'admin_post_dani_import', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_post_dani_rollback', array( __CLASS__, 'handle_rollback' ) );
	}

	/**
	 * Admin menu entry.
	 *
	 * @return void
	 */
	public static function menu() {
		add_submenu_page(
			'dani-settings',
			'راه‌اندازی و درون‌ریزی سایت',
			'راه‌اندازی و درون‌ریزی',
			'manage_options',
			'dani-importer',
			array( __CLASS__, 'page' )
		);
	}

	/* ---------------------------------------------------------------------
	 * Admin screen
	 * ------------------------------------------------------------------ */

	/**
	 * Import screen.
	 *
	 * @return void
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$log    = get_transient( 'dani_import_log' );
		$backup = get_option( self::BACKUP );
		$state  = self::state();
		?>
		<div class="wrap dani-admin-wrap">
			<h1><?php esc_html_e( 'راه‌اندازی و درون‌ریزی سایت', 'danisite-core' ); ?></h1>
			<p class="description" style="max-width:70ch">
				<?php esc_html_e( 'با یک کلیک، تمام صفحات، سکشن‌ها، منوها، خدمات، نمونه‌کارها، اعضای تیم، نظرات، پرسش‌ها و مقالات نمونه ساخته می‌شوند. اجرای مجدد، محتوا را تکرار نمی‌کند و صفحات هم‌معنی را به‌روزرسانی می‌کند.', 'danisite-core' ); ?>
			</p>

			<?php if ( is_array( $log ) ) : ?>
				<div class="notice notice-info">
					<p><strong><?php echo esc_html( $log['title'] ); ?></strong></p>
					<ul style="list-style:disc;padding-inline-start:1.5rem">
						<?php foreach ( $log['lines'] as $line ) : ?>
							<li><?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width:100%;padding:1.25rem 1.5rem">
				<h2 style="margin-top:0"><?php esc_html_e( 'وضعیت فعلی', 'danisite-core' ); ?></h2>
				<table class="widefat striped" style="max-width:640px">
					<tbody>
						<tr>
							<td><?php esc_html_e( 'صفحه اصلی', 'danisite-core' ); ?></td>
							<td><?php echo $state['front'] ? esc_html( $state['front'] ) : '<em>تنظیم نشده</em>'; ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'صفحه وبلاگ', 'danisite-core' ); ?></td>
							<td><?php echo $state['blog'] ? esc_html( $state['blog'] ) : '<em>تنظیم نشده</em>'; ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Elementor', 'danisite-core' ); ?></td>
							<td><?php echo $state['elementor'] ? esc_html( 'فعال — نسخه ' . $state['elementor'] ) : '<em>غیرفعال (صفحات با شورت‌کد نمایش داده می‌شوند)</em>'; ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'خدمات / نمونه‌کار / تیم / نظرات / پرسش‌ها', 'danisite-core' ); ?></td>
							<td><?php echo esc_html( $state['counts'] ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'آخرین نسخه پشتیبان', 'danisite-core' ); ?></td>
							<td><?php echo $backup && isset( $backup['time'] ) ? esc_html( $backup['time'] ) : '<em>ندارد</em>'; ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'dani_import', 'dani_import_nonce' ); ?>
					<input type="hidden" name="action" value="dani_import">
					<p>
						<label>
							<input type="checkbox" name="dani_confirm" value="1" required>
							<?php esc_html_e( 'صفحات هم‌معنی به‌روزرسانی می‌شوند و پیش از تغییر، نسخه پشتیبان گرفته می‌شود.', 'danisite-core' ); ?>
						</label>
					</p>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'اجرای درون‌ریز کامل', 'danisite-core' ); ?></button>
				</form>

				<?php if ( $backup ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('بازگشت به وضعیت قبل از درون‌ریزی. ادامه می‌دهید؟');">
						<?php wp_nonce_field( 'dani_rollback', 'dani_rollback_nonce' ); ?>
						<input type="hidden" name="action" value="dani_rollback">
						<button type="submit" class="button button-secondary button-hero"><?php esc_html_e( 'بازگردانی به وضعیت قبل از درون‌ریزی', 'danisite-core' ); ?></button>
					</form>
				<?php endif; ?>
			</div>

			<hr style="margin:2rem 0">
			<h2><?php esc_html_e( 'چه چیزی ساخته می‌شود؟', 'danisite-core' ); ?></h2>
			<ul style="list-style:disc;padding-inline-start:1.5rem;max-width:70ch">
				<li><?php esc_html_e( 'صفحه اصلی، درباره ما، خدمات، نمونه‌کارها، وبلاگ، تماس با ما، پرسش‌های متداول و سه صفحه حقوقی', 'danisite-core' ); ?></li>
				<li><?php esc_html_e( 'صفحات داخلی با ویجت‌های اختصاصی Elementor ساخته می‌شوند و در صورت غیرفعال بودن Elementor با شورت‌کد نمایش داده می‌شوند.', 'danisite-core' ); ?></li>
				<li><?php esc_html_e( '۵ خدمت، ۵ نمونه‌کار، ۳ عضو تیم، ۳ نظر، ۶ پرسش متداول و ۴ مقاله نمونه', 'danisite-core' ); ?></li>
				<li><?php esc_html_e( 'منوهای اصلی، فوتر و قوانین، ساختار پیوند یکتا و تنظیمات قالب', 'danisite-core' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Short status summary.
	 *
	 * @return array
	 */
	private static function state() {
		$front = (int) get_option( 'page_on_front' );
		$blog  = (int) get_option( 'page_for_posts' );

		$counts = array();
		foreach ( array( 'dani_service' => 'خدمت', 'dani_project' => 'نمونه‌کار', 'dani_team' => 'عضو تیم', 'dani_quote' => 'نظر', 'dani_faq' => 'پرسش', 'post' => 'نوشته' ) as $type => $label ) {
			$count          = wp_count_posts( $type );
			$counts[]       = $label . ': ' . (int) $count->publish;
		}

		return array(
			'front'     => $front ? get_the_title( $front ) : '',
			'blog'      => $blog ? get_the_title( $blog ) : '',
			'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '',
			'counts'    => implode( ' · ', $counts ),
		);
	}

	/* ---------------------------------------------------------------------
	 * Handlers
	 * ------------------------------------------------------------------ */

	/**
	 * Run the import.
	 *
	 * @return void
	 */
	public static function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'danisite-core' ) );
		}
		if ( ! isset( $_POST['dani_import_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dani_import_nonce'] ) ), 'dani_import' ) ) {
			wp_die( esc_html__( 'نشست منقضی شده است. صفحه را تازه کنید.', 'danisite-core' ) );
		}

		$lines = self::run();

		set_transient(
			'dani_import_log',
			array(
				'title' => 'درون‌ریزی با موفقیت انجام شد — ' . current_time( 'Y/m/d H:i' ),
				'lines' => $lines,
			),
			120
		);

		wp_safe_redirect( admin_url( 'admin.php?page=dani-importer&imported=1' ) );
		exit;
	}

	/**
	 * Programmatic import (also used by WP-CLI and the setup script).
	 *
	 * @return array Log lines.
	 */
	public static function run() {
		$lines = array();

		self::backup();
		$lines[] = 'نسخه پشتیبان از وضعیت فعلی گرفته شد.';

		$media = self::import_media();
		$lines[] = sprintf( '%d تصویر در کتابخانه رسانه آماده شد.', count( $media ) );

		$terms = self::import_terms();
		$lines[] = sprintf( '%d دسته نمونه‌کار آماده شد.', $terms );

		$lines[] = self::import_services() . ' خدمت ثبت شد.';
		$lines[] = self::import_projects( $media ) . ' نمونه‌کار ثبت شد.';
		$lines[] = self::import_team( $media ) . ' عضو تیم ثبت شد.';
		$lines[] = self::import_quotes() . ' نظر مشتری ثبت شد.';
		$lines[] = self::import_faqs() . ' پرسش متداول ثبت شد.';
		$lines[] = self::import_posts( $media ) . ' مقاله نمونه ثبت شد.';
		$lines[] = self::import_pages() . ' صفحه ساخته یا به‌روزرسانی شد.';
		$stock   = self::cleanup_stock_content();
		if ( $stock ) {
			$lines[] = sprintf( '%d محتوای پیش‌فرض وردپرس به سطل زباله منتقل شد.', $stock );
		}
		$lines[] = self::setup_menus();
		$lines[] = self::setup_front_page();
		$lines[] = self::setup_settings();
		$lines[] = self::setup_elementor();
		$lines[] = 'ساختار پیوندهای یکتا بازنویسی شد.';

		update_option( 'dani_demo_imported', current_time( 'mysql' ) );

		return $lines;
	}

	/**
	 * Move WordPress' stock sample post and page to the trash.
	 *
	 * Only the untouched defaults (`hello-world` and `sample-page`) are removed,
	 * and only to the trash, so the site does not ship with demo placeholders.
	 *
	 * @return int Number of items trashed.
	 */
	private static function cleanup_stock_content() {
		$count = 0;

		$stock_post = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $stock_post && 'publish' === $stock_post->post_status ) {
			wp_trash_post( $stock_post->ID );
			++$count;
		}

		$stock_page = get_page_by_path( 'sample-page', OBJECT, 'page' );
		if ( $stock_page && 'publish' === $stock_page->post_status ) {
			wp_trash_post( $stock_page->ID );
			++$count;
		}

		return $count;
	}

	/**
	 * Restore the backup.
	 *
	 * @return void
	 */
	public static function handle_rollback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'danisite-core' ) );
		}
		if ( ! isset( $_POST['dani_rollback_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dani_rollback_nonce'] ) ), 'dani_rollback' ) ) {
			wp_die( esc_html__( 'نشست منقضی شده است. صفحه را تازه کنید.', 'danisite-core' ) );
		}

		$result = self::rollback();

		set_transient(
			'dani_import_log',
			array(
				'title' => 'بازگردانی انجام شد — ' . current_time( 'Y/m/d H:i' ),
				'lines' => $result,
			),
			120
		);

		wp_safe_redirect( admin_url( 'admin.php?page=dani-importer&rolled=1' ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Backup / rollback
	 * ------------------------------------------------------------------ */

	/**
	 * Snapshot everything the importer may touch.
	 *
	 * @return void
	 */
	private static function backup() {
		$pages    = array();
		$existing = get_option( self::BACKUP );

		foreach ( self::page_definitions() as $definition ) {
			$page = get_page_by_path( $definition['slug'] );
			if ( ! $page ) {
				continue;
			}
			$pages[ $page->ID ] = array(
				'post_title'   => $page->post_title,
				'post_content' => $page->post_content,
				'post_excerpt' => $page->post_excerpt,
				'post_status'  => $page->post_status,
				'meta'         => array(
					'_elementor_data'         => get_post_meta( $page->ID, '_elementor_data', true ),
					'_elementor_edit_mode'    => get_post_meta( $page->ID, '_elementor_edit_mode', true ),
					'_elementor_template_type' => get_post_meta( $page->ID, '_elementor_template_type', true ),
					'_elementor_version'      => get_post_meta( $page->ID, '_elementor_version', true ),
					'_wp_page_template'       => get_post_meta( $page->ID, '_wp_page_template', true ),
					self::KEY                 => get_post_meta( $page->ID, self::KEY, true ),
				),
			);
		}

		$menu_snapshot = array();
		foreach ( array( 'primary', 'footer', 'legal' ) as $location ) {
			$locations                  = get_nav_menu_locations();
			$menu_snapshot[ $location ] = isset( $locations[ $location ] ) ? $locations[ $location ] : 0;
		}

		$option_snapshot = array(
			'show_on_front'      => get_option( 'show_on_front' ),
			'page_on_front'      => get_option( 'page_on_front' ),
			'page_for_posts'     => get_option( 'page_for_posts' ),
			'permalink_structure' => get_option( 'permalink_structure' ),
			'dani_options'       => get_option( 'dani_options' ),
			'elementor_cpt_support' => get_option( 'elementor_cpt_support' ),
		);

		update_option(
			self::BACKUP,
			array(
				'time'          => current_time( 'Y/m/d H:i' ),
				'pages'         => $pages,
				'existed_keys'  => null,
				'menus'         => $menu_snapshot,
				'options'       => $option_snapshot,
				'previous'      => $existing && isset( $existing['time'] ) ? $existing['time'] : '',
			),
			false
		);
	}

	/**
	 * Undo the import.
	 *
	 * @return array Log lines.
	 */
	public static function rollback() {
		$backup = get_option( self::BACKUP );
		$lines  = array();

		if ( ! $backup ) {
			return array( 'نسخه پشتیبانی یافت نشد.' );
		}

		// Restore or remove the pages the importer touched.
		$restored = 0;
		foreach ( (array) $backup['pages'] as $page_id => $snapshot ) {
			$page = get_post( $page_id );
			if ( ! $page ) {
				continue;
			}
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_title'   => $snapshot['post_title'],
					'post_content' => $snapshot['post_content'],
					'post_excerpt' => $snapshot['post_excerpt'],
					'post_status'  => $snapshot['post_status'],
				)
			);
			foreach ( (array) $snapshot['meta'] as $meta_key => $meta_value ) {
				if ( '' === $meta_value || null === $meta_value || array() === $meta_value ) {
					delete_post_meta( $page_id, $meta_key );
				} else {
					update_post_meta( $page_id, $meta_key, wp_slash( $meta_value ) );
				}
			}
			$restored++;
		}
		$lines[] = sprintf( '%d صفحه به وضعیت قبلی بازگشت.', $restored );

		// Delete demo records the importer created.
		$deleted = 0;
		foreach ( array( 'page', 'post', 'dani_service', 'dani_project', 'dani_team', 'dani_quote', 'dani_faq' ) as $type ) {
			$ids = get_posts(
				array(
					'post_type'      => $type,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array( 'key' => self::KEY, 'compare' => 'EXISTS' ),
					),
				)
			);
			foreach ( $ids as $id ) {
				if ( isset( $backup['pages'][ $id ] ) ) {
					continue; // Existing page, already restored above.
				}
				wp_delete_post( $id, true );
				$deleted++;
			}
		}
		$lines[] = sprintf( '%d محتوای نمونه حذف شد.', $deleted );

		// Restore options.
		foreach ( (array) $backup['options'] as $key => $value ) {
			if ( null === $value || '' === $value ) {
				delete_option( $key );
			} else {
				update_option( $key, $value );
			}
		}
		$lines[] = 'تنظیمات سایت بازگردانی شد.';

		// Restore menu locations.
		$locations = (array) get_nav_menu_locations();
		foreach ( (array) $backup['menus'] as $location => $menu_id ) {
			$locations[ $location ] = (int) $menu_id;
		}
		set_theme_mod( 'nav_menu_locations', $locations );

		delete_option( self::BACKUP );
		flush_rewrite_rules();

		$lines[] = 'بازگردانی کامل شد.';

		return $lines;
	}

	/* ---------------------------------------------------------------------
	 * Media
	 * ------------------------------------------------------------------ */

	/**
	 * Sideload the bundled demo images once.
	 *
	 * @return array filename => URL.
	 */
	private static function import_media() {
		$images = array(
			'experience.jpg'   => 'تجربه دیجیتال — پیش‌نمایش رابط',
			'studio.jpg'       => 'فضای کاری استودیو دنی سایت',
			'work-shoes.jpg'   => 'نمونه‌کار فروشگاه کفش',
			'work-mobile.jpg'  => 'نمونه‌کار فروشگاه موبایل',
			'work-cosmetics.jpg' => 'نمونه‌کار فروشگاه آرایشی',
			'work-beauty.jpg'  => 'نمونه‌کار خدمات زیبایی',
			'work-corporate.jpg' => 'نمونه‌کار وب‌سایت شرکتی',
		);

		$map = array();

		foreach ( $images as $file => $title ) {
			$existing = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array( 'key' => '_dani_demo_asset', 'value' => $file ),
					),
				)
			);

			if ( ! empty( $existing ) ) {
				$map[ $file ] = wp_get_attachment_url( $existing[0] );
				continue;
			}

			$path = DANI_CORE_PATH . 'assets/img/' . $file;
			if ( ! file_exists( $path ) ) {
				continue;
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$tmp = wp_tempnam( $file );
			if ( ! $tmp || ! copy( $path, $tmp ) ) {
				continue;
			}

			$attachment_id = media_handle_sideload(
				array(
					'name'     => $file,
					'tmp_name' => $tmp,
				),
				0,
				$title
			);

			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				continue;
			}

			update_post_meta( $attachment_id, '_dani_demo_asset', $file );
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
			$map[ $file ] = wp_get_attachment_url( $attachment_id );
		}

		return $map;
	}

	/* ---------------------------------------------------------------------
	 * Terms
	 * ------------------------------------------------------------------ */

	/**
	 * Portfolio categories.
	 *
	 * @return int Number of terms.
	 */
	private static function import_terms() {
		$terms = array(
			'shop'      => 'فروشگاهی',
			'service'   => 'خدماتی',
			'corporate' => 'شرکتی',
		);

		$count = 0;
		foreach ( $terms as $slug => $name ) {
			if ( ! term_exists( $slug, 'dani_project_cat' ) ) {
				wp_insert_term( $name, 'dani_project_cat', array( 'slug' => $slug ) );
			}
			$count++;
		}

		return $count;
	}

	/* ---------------------------------------------------------------------
	 * Content records
	 * ------------------------------------------------------------------ */

	/**
	 * Create or update one record keyed by the demo key.
	 *
	 * @param string $type Post type.
	 * @param string $key  Stable demo key.
	 * @param array  $data Post data.
	 * @param array  $meta Meta values (without prefix).
	 * @return int Post ID.
	 */
	private static function upsert( $type, $key, $data, $meta = array() ) {
		$existing = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array( 'key' => self::KEY, 'value' => $key ),
				),
			)
		);

		$data = array_merge(
			array(
				'post_type'   => $type,
				'post_status' => 'publish',
			),
			$data
		);

		if ( ! empty( $existing ) ) {
			$data['ID'] = (int) $existing[0];
			$post_id    = wp_update_post( $data, true );
		} else {
			$post_id = wp_insert_post( $data, true );
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, self::KEY, $key );
			}
		}

		if ( is_wp_error( $post_id ) ) {
			return 0;
		}

		foreach ( $meta as $meta_key => $meta_value ) {
			update_post_meta( $post_id, '_dani_' . $meta_key, $meta_value );
		}

		return (int) $post_id;
	}

	/**
	 * Services.
	 *
	 * @return int Count.
	 */
	private static function import_services() {
		$services = array(
			array(
				'key'   => 'service-ui',
				'title' => 'طراحی رابط و تجربه کاربری',
				'desc'  => 'طراحی رابط‌هایی که بدون توضیح فهمیده می‌شوند؛ از وایرفریم و جریان کاربر تا دیزاین سیستم.',
				'icon'  => 'palette',
				'price' => 'از ۲۵ میلیون تومان',
				'features' => "وایرفریم و معماری اطلاعات\nطراحی در فیگما\nدیزاین سیستم و کتابخانه اجزا\nتست کاربری",
			),
			array(
				'key'   => 'service-wordpress',
				'title' => 'طراحی و توسعه وردپرس',
				'desc'  => 'سایت‌های سریع، امن و قابل مدیریت؛ بدون نیاز به کدنویسی برای به‌روزرسانی محتوا.',
				'icon'  => 'code',
				'price' => 'از ۳۵ میلیون تومان',
				'features' => "قالب اختصاصی وردپرس\nساخت صفحات با Elementor\nفروشگاه ووکامرس\nآموزش مدیریت سایت",
			),
			array(
				'key'   => 'service-frontend',
				'title' => 'توسعه فرانت‌اند',
				'desc'  => 'پیاده‌سازی دقیق طراحی با کد تمیز، انیمیشن‌های سبک و عملکرد بالا در همه دستگاه‌ها.',
				'icon'  => 'layers',
				'price' => 'از ۲۰ میلیون تومان',
				'features' => "HTML، CSS و JavaScript مدرن\nانیمیشن‌های سبک\nواکنش‌گرایی کامل\nبهینه برای Core Web Vitals",
			),
			array(
				'key'   => 'service-brand',
				'title' => 'هویت دیجیتال برند',
				'desc'  => 'یک زبان بصری یکپارچه در وب، شبکه‌های اجتماعی و تمام نقاط تماس دیجیتال برند.',
				'icon'  => 'sparkles',
				'price' => 'از ۱۸ میلیون تومان',
				'features' => "لوگو و نشان\nرنگ و تایپوگرافی\nراهنمای برند\nقالب‌های شبکه اجتماعی",
			),
			array(
				'key'   => 'service-speed',
				'title' => 'بهینه‌سازی سرعت و سئو',
				'desc'  => 'Core Web Vitals، ساختار فنی سئو و رشد ارگانیک پایدار برای بلندمدت.',
				'icon'  => 'gauge',
				'price' => 'از ۱۲ میلیون تومان',
				'features' => "تحلیل Core Web Vitals\nسئوی فنی\nساختار داده و اسکیما\nگزارش ماهانه",
			),
		);

		$count = 0;
		$order = 0;
		foreach ( $services as $service ) {
			$order++;
			$id = self::upsert(
				'dani_service',
				$service['key'],
				array(
					'post_title'   => $service['title'],
					'post_excerpt' => $service['desc'],
					'post_content' => $service['desc'],
					'menu_order'   => $order,
				),
				array(
					'icon'     => $service['icon'],
					'price'    => $service['price'],
					'features' => $service['features'],
				)
			);
			if ( $id ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Portfolio projects.
	 *
	 * @param array $media Media map.
	 * @return int Count.
	 */
	private static function import_projects( $media ) {
		$projects = array(
			array(
				'key'      => 'project-shoes',
				'title'    => 'فروشگاه اینترنتی کفش',
				'desc'     => 'ویترینی مینیمال که محصول را در مرکز توجه نگه می‌دارد.',
				'image'    => 'work-shoes.jpg',
				'ratio'    => '16-10',
				'category' => 'shop',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۴',
				'scope'    => 'طراحی رابط، توسعه وردپرس',
			),
			array(
				'key'      => 'project-mobile',
				'title'    => 'فروشگاه موبایل و لوازم دیجیتال',
				'desc'     => 'تجربه‌ای سریع برای مقایسه و خرید.',
				'image'    => 'work-mobile.jpg',
				'ratio'    => '4-5',
				'category' => 'shop',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۴',
				'scope'    => 'طراحی رابط، فروشگاه ووکامرس',
			),
			array(
				'key'      => 'project-cosmetics',
				'title'    => 'فروشگاه محصولات آرایشی',
				'desc'     => 'زبان بصری لطیف با تمرکز بر حس اعتماد.',
				'image'    => 'work-cosmetics.jpg',
				'ratio'    => '4-5',
				'category' => 'shop',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۳',
				'scope'    => 'هویت دیجیتال، طراحی رابط',
			),
			array(
				'key'      => 'project-beauty',
				'title'    => 'وب‌سایت خدمات زیبایی',
				'desc'     => 'رزرو آسان و فضایی آرام و دعوت‌کننده.',
				'image'    => 'work-beauty.jpg',
				'ratio'    => '4-3',
				'category' => 'service',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۳',
				'scope'    => 'طراحی رابط، رزرو آنلاین',
			),
			array(
				'key'      => 'project-corporate',
				'title'    => 'وب‌سایت شرکتی',
				'desc'     => 'هویتی مطمئن و ساختاری روشن برای معرفی برند.',
				'image'    => 'work-corporate.jpg',
				'ratio'    => '16-9',
				'category' => 'corporate',
				'client'   => 'نمونه نمایشی',
				'year'     => '۱۴۰۲',
				'scope'    => 'طراحی رابط، توسعه',
			),
		);

		$count = 0;
		$order = 0;

		foreach ( $projects as $project ) {
			$order++;
			$id = self::upsert(
				'dani_project',
				$project['key'],
				array(
					'post_title'   => $project['title'],
					'post_excerpt' => $project['desc'],
					'post_content' => $project['desc'],
					'menu_order'   => $order,
				),
				array(
					'client'   => $project['client'],
					'year'     => $project['year'],
					'scope'    => $project['scope'],
					'ratio'    => $project['ratio'],
					'featured' => '1',
				)
			);

			if ( ! $id ) {
				continue;
			}

			$count++;
			wp_set_object_terms( $id, array( $project['category'] ), 'dani_project_cat' );

			if ( ! empty( $media[ $project['image'] ] ) ) {
				self::attach_image( $id, $media[ $project['image'] ], $project['title'] );
			}
		}

		return $count;
	}

	/**
	 * Team members.
	 *
	 * @param array $media Media map.
	 * @return int Count.
	 */
	private static function import_team( $media ) {
		$members = array(
			array(
				'key'   => 'team-danial',
				'title' => 'دانیال حسینی',
				'role'  => 'بنیان‌گذار و مدیر خلاقیت',
				'desc'  => 'طراح و توسعه‌دهنده‌ای که طراحی را نقطه تلاقی استراتژی، زیبایی و فناوری می‌داند.',
				'image' => 'studio.jpg',
			),
			array(
				'key'   => 'team-sara',
				'title' => 'سارا محمدی',
				'role'  => 'طراح رابط و تجربه کاربری',
				'desc'  => 'روی وضوح و سادگی وسواس دارد؛ از وایرفریم تا دیزاین سیستم نهایی.',
				'image' => 'work-cosmetics.jpg',
			),
			array(
				'key'   => 'team-amir',
				'title' => 'امیر رضایی',
				'role'  => 'توسعه‌دهنده فرانت‌اند',
				'desc'  => 'طراحی‌ها را با کدی سبک و دقیق به تجربه‌ای سریع و روان تبدیل می‌کند.',
				'image' => 'experience.jpg',
			),
		);

		$count = 0;
		$order = 0;

		foreach ( $members as $member ) {
			$order++;
			$id = self::upsert(
				'dani_team',
				$member['key'],
				array(
					'post_title'   => $member['title'],
					'post_excerpt' => $member['desc'],
					'post_content' => $member['desc'],
					'menu_order'   => $order,
				),
				array( 'role' => $member['role'] )
			);

			if ( ! $id ) {
				continue;
			}

			$count++;
			if ( ! empty( $media[ $member['image'] ] ) ) {
				self::attach_image( $id, $media[ $member['image'] ], $member['title'] );
			}
		}

		return $count;
	}

	/**
	 * Testimonials copied verbatim from the Lovable design (marked as demo).
	 *
	 * @return int Count.
	 */
	private static function import_quotes() {
		$quotes = array(
			'این بخش برای نمایش نظر واقعی یکی از مشتریان رزرو شده است. پس از تکمیل پروژه‌ها، متن تأییدشده جایگزین می‌شود.',
			'نمونه متن نظر مشتری: توضیحی کوتاه درباره تجربه همکاری، کیفیت طراحی و نتیجه نهایی پروژه.',
			'جای خالی برای بازخورد مشتری. این محتوا صرفاً نمایشی است و نظر هیچ شخص واقعی نیست.',
		);

		$count = 0;
		$order = 0;

		foreach ( $quotes as $index => $text ) {
			$order++;
			$id = self::upsert(
				'dani_quote',
				'quote-' . ( $index + 1 ),
				array(
					'post_title'   => 'نظر نمونه ' . dani_fa_number( $index + 1 ),
					'post_content' => $text,
					'menu_order'   => $order,
				),
				array(
					'author' => 'نام مشتری',
					'role'   => 'نمونه نمایشی',
					'rating' => '5',
				)
			);
			if ( $id ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * FAQ entries.
	 *
	 * @return int Count.
	 */
	private static function import_faqs() {
		$faqs = Dani_Query::faqs( 6 );

		$count = 0;
		$order = 0;

		foreach ( $faqs as $faq ) {
			$order++;
			$slug = 'faq-' . $order;
			$id   = self::upsert(
				'dani_faq',
				$slug,
				array(
					'post_title'   => $faq['question'],
					'post_content' => $faq['answer'],
					'menu_order'   => $order,
				),
				array( 'group' => 'عمومی' )
			);
			if ( $id ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Sample articles with categories and featured images.
	 *
	 * @param array $media Media map.
	 * @return int Count.
	 */
	private static function import_posts( $media ) {
		$posts = array(
			array(
				'key'   => 'post-shop-design',
				'title' => 'طراحی فروشگاه اینترنتی؛ از ویترین تا لحظه خرید',
				'cat'   => 'طراحی وب',
				'image' => 'work-shoes.jpg',
				'body'  => "<p>یک فروشگاه اینترنتی موفق با تعداد محصولات زیاد ساخته نمی‌شود؛ با مسیری ساخته می‌شود که کاربر بدون تردید از دیدن محصول به تکمیل سفارش برسد. در این نوشته، سه تصمیم طراحی را بررسی می‌کنیم که بیشترین اثر را بر نرخ تبدیل دارند.</p><h2>۱. ویترین، نه انبار</h2><p>صفحه دسته‌بندی باید محصول را در مرکز توجه نگه دارد. تصویر بزرگ، قیمت خوانا و یک دکمه روشن کافی است؛ هر عنصر اضافه، تصمیم کاربر را عقب می‌اندازد.</p><h2>۲. اعتماد، پیش از پرداخت</h2><p>شرایط مرجوعی، روش ارسال و نشان اعتماد را همان‌جا نشان دهید که تردید شکل می‌گیرد: کنار قیمت و درست قبل از دکمه پرداخت.</p><h2>۳. سرعت، بخشی از تجربه است</h2><p>هر ثانیه تأخیر، بخشی از کاربران را حذف می‌کند. تصاویر بهینه، بارگذاری مرحله‌ای و حذف اسکریپت‌های غیرضروری، سریع‌ترین راه برای بهبود حس خرید است.</p>",
			),
			array(
				'key'   => 'post-speed',
				'title' => 'چرا سرعت سایت مهم‌تر از چیزی است که فکر می‌کنید',
				'cat'   => 'سئو و عملکرد',
				'image' => 'work-corporate.jpg',
				'body'  => "<p>سرعت سایت فقط یک عدد در ابزارهای سنجش نیست؛ همان چیزی است که کاربر در اولین ثانیه حس می‌کند. Core Web Vitals این حس را به سه شاخص قابل اندازه‌گیری تبدیل کرده است.</p><h2>LCP؛ لحظه دیده شدن</h2><p>بزرگ‌ترین عنصر صفحه باید در کمتر از ۲.۵ ثانیه نمایش داده شود. تصویر هیرو، فونت و درخواست‌های مسدودکننده، سه عامل اصلی تأخیر هستند.</p><h2>CLS؛ پایداری چیدمان</h2><p>اگر متن هنگام بارگذاری جابه‌جا شود، تجربه ناخوشایند می‌شود. تعیین ابعاد تصاویر و رزرو فضای فونت، این مشکل را از بین می‌برد.</p><h2>INP؛ پاسخ‌گویی</h2><p>تعامل‌ها باید سریع پاسخ دهند. کاهش جاوااسکریپت و تقسیم آن به بخش‌های لازم، بهترین راه است.</p>",
			),
			array(
				'key'   => 'post-persian-font',
				'title' => 'راهنمای انتخاب فونت فارسی برای وب‌سایت',
				'cat'   => 'طراحی وب',
				'image' => 'work-beauty.jpg',
				'body'  => "<p>فونت، بیش از هر عنصر دیگری لحن برند را منتقل می‌کند. برای متن فارسی، خوانایی در اندازه‌های کوچک و وزن‌های مختلف مهم‌ترین معیار است.</p><h2>وزن متغیر، انتخاب هوشمند</h2><p>یک فونت متغیر وزن‌های متعدد را در یک فایل ارائه می‌دهد؛ هم حجم کمتری دانلود می‌شود و هم تایپوگرافی یکدست می‌ماند. وزیرمتن یکی از گزینه‌های آزاد و خوانا برای متن فارسی است.</p><h2>ارتفاع خط و فاصله حروف</h2><p>برای فارسی، ارتفاع خط بین ۱.۷ تا ۲ برابر اندازه فونت راحت‌ترین خواندن را می‌دهد. فاصله حروف را تغییر ندهید؛ حروف فارسی به هم متصل‌اند.</p>",
			),
			array(
				'key'   => 'post-elementor',
				'title' => 'Elementor یا توسعه اختصاصی؟ راهنمای انتخاب',
				'cat'   => 'وردپرس',
				'image' => 'work-mobile.jpg',
				'body'  => "<p>سؤال «Elementor یا کدنویسی اختصاصی» پاسخ ثابتی ندارد؛ به نیاز تیم شما به تغییرات مستمر بستگی دارد.</p><h2>Elementor کجا برنده است؟</h2><p>وقتی تیم محتوا باید بدون توسعه‌دهنده صفحه بسازد، Elementor سریع‌ترین راه است. با ویجت‌های طراحی‌شده و دیزاین سیستم، خروجی حرفه‌ای و یکدست می‌ماند.</p><h2>توسعه اختصاصی کجا لازم است؟</h2><p>در منطق پیچیده کسب‌وکار، یکپارچه‌سازی‌های سنگین یا نیاز به حداکثر سرعت، کد اختصاصی کنترل بیشتری می‌دهد.</p><h2>ترکیب درست</h2><p>در بیشتر پروژه‌ها بهترین نتیجه از ترکیب می‌آید: ساختار و اجزای اختصاصی، همراه با ویرایشگر بصری برای محتوای روزمره.</p>",
			),
		);

		$count = 0;

		foreach ( $posts as $post ) {
			$term = term_exists( $post['cat'], 'category' );
			if ( ! $term ) {
				$term = wp_insert_term( $post['cat'], 'category' );
			}
			$term_id = is_wp_error( $term ) ? 0 : (int) ( is_array( $term ) ? $term['term_id'] : $term );

			$id = self::upsert(
				'post',
				$post['key'],
				array(
					'post_title'   => $post['title'],
					'post_content' => $post['body'],
					'post_excerpt' => wp_trim_words( wp_strip_all_tags( $post['body'] ), 28 ),
				)
			);

			if ( ! $id ) {
				continue;
			}

			$count++;
			if ( $term_id ) {
				wp_set_post_categories( $id, array( $term_id ) );
			}
			if ( ! empty( $media[ $post['image'] ] ) ) {
				self::attach_image( $id, $media[ $post['image'] ], $post['title'] );
			}
		}

		return $count;
	}

	/**
	 * Attach an imported image as the featured image.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $url     Attachment URL.
	 * @param string $title   Alt title.
	 * @return void
	 */
	private static function attach_image( $post_id, $url, $title ) {
		$attachment_id = attachment_url_to_postid( $url );

		if ( ! $attachment_id ) {
			return;
		}

		set_post_thumbnail( $post_id, $attachment_id );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	}

	/* ---------------------------------------------------------------------
	 * Pages
	 * ------------------------------------------------------------------ */

	/**
	 * Page definitions: slug, titles, Elementor sections and shortcode fallback.
	 *
	 * @return array
	 */
	private static function page_definitions() {
		return array(
			array(
				'key'      => 'home',
				'title'    => 'خانه',
				'slug'     => 'home',
				'template' => 'default',
				'sections' => array(
					array( 'widget' => 'dani_hero' ),
					array( 'widget' => 'dani_services' ),
					array( 'widget' => 'dani_experience' ),
					array( 'widget' => 'dani_portfolio' ),
					array( 'widget' => 'dani_about' ),
					array( 'widget' => 'dani_workflow' ),
					array( 'widget' => 'dani_testimonials' ),
					array( 'widget' => 'dani_cta' ),
				),
				'content'  => '[dani_home]',
				'excerpt'  => 'استودیوی طراحی و توسعه وب دنی سایت؛ ترکیب خلاقیت، طراحی و تکنولوژی برای ساختن تجربه‌های دیجیتال متمایز.',
			),
			array(
				'key'      => 'about',
				'title'    => 'درباره ما',
				'slug'     => 'about',
				'sections' => array(
					array( 'widget' => 'dani_page_hero', 'settings' => array( 'eyebrow' => 'ABOUT DANISITE', 'title' => 'درباره دنی سایت', 'text' => 'استودیویی کوچک با تمرکز بر طراحی دقیق، کد تمیز و تجربه‌هایی که به یاد می‌مانند.' ) ),
					array( 'widget' => 'dani_about' ),
					array( 'widget' => 'dani_stats' ),
					array( 'widget' => 'dani_team', 'settings' => array( 'title' => 'تیم دنی سایت' ) ),
					array( 'widget' => 'dani_cta' ),
				),
				'content'  => '[dani_about][dani_stats][dani_team title="تیم دنی سایت"][dani_cta]',
				'excerpt'  => 'دنی سایت استودیوی طراحی و توسعه وب است؛ با تمرکز بر تجربه کاربری، توسعه وردپرس و هویت دیجیتال برند.',
			),
			array(
				'key'      => 'services',
				'title'    => 'خدمات',
				'slug'     => 'services',
				'sections' => array(
					array( 'widget' => 'dani_page_hero', 'settings' => array( 'eyebrow' => 'SERVICES', 'title' => 'خدمات دنی سایت', 'text' => 'از طراحی رابط تا توسعه وردپرس و بهینه‌سازی سرعت؛ هر خدمت با هدف رشد کسب‌وکار شما طراحی شده است.' ) ),
					array( 'widget' => 'dani_service_cards', 'settings' => array( 'title' => 'آنچه انجام می‌دهیم.', 'text' => 'هر پروژه با شناخت نیاز واقعی کسب‌وکار شروع می‌شود و با یک راهکار متناسب پایان می‌گیرد.', 'columns' => '3' ) ),
					array( 'widget' => 'dani_workflow' ),
					array( 'widget' => 'dani_faq', 'settings' => array( 'title' => 'پرسش‌های رایج درباره خدمات' ) ),
					array( 'widget' => 'dani_cta' ),
				),
				'content'  => '[dani_service_cards title="آنچه انجام می‌دهیم."][dani_workflow][dani_faq title="پرسش‌های رایج درباره خدمات"][dani_cta]',
				'excerpt'  => 'خدمات دنی سایت: طراحی رابط و تجربه کاربری، توسعه وردپرس، فرانت‌اند، هویت دیجیتال و بهینه‌سازی سرعت و سئو.',
			),
			array(
				'key'      => 'portfolio',
				'title'    => 'نمونه‌کارها',
				'slug'     => 'portfolio',
				'sections' => array(
					array( 'widget' => 'dani_page_hero', 'settings' => array( 'eyebrow' => 'SELECTED WORK', 'title' => 'نمونه‌کارها', 'text' => 'مجموعه‌ای از پروژه‌هایی که با نگاه متفاوت، توجه به جزئیات و تمرکز بر نتیجه ساخته شده‌اند.' ) ),
					array( 'widget' => 'dani_portfolio', 'settings' => array( 'title' => '', 'text' => '', 'layout' => 'grid', 'filters' => 'yes', 'button' => '', 'limit' => 12, 'eyebrow' => 'PROJECTS' ) ),
					array( 'widget' => 'dani_stats' ),
					array( 'widget' => 'dani_cta' ),
				),
				'content'  => '[dani_portfolio title="" eyebrow="PROJECTS" layout="grid" filters="1" button="0" limit="12"][dani_stats][dani_cta]',
				'excerpt'  => 'نمونه‌کارهای دنی سایت در طراحی فروشگاه، وب‌سایت شرکتی و خدمات؛ همراه با جزئیات هر پروژه.',
			),
			array(
				'key'      => 'blog',
				'title'    => 'وبلاگ',
				'slug'     => 'blog',
				'sections' => array(),
				'content'  => '',
				'excerpt'  => 'یادداشت‌ها و تحلیل‌های تیم دنی سایت درباره طراحی، توسعه، سرعت و سئو.',
			),
			array(
				'key'      => 'contact',
				'title'    => 'تماس با ما',
				'slug'     => 'contact',
				'sections' => array(
					array( 'widget' => 'dani_page_hero', 'settings' => array( 'eyebrow' => 'CONTACT', 'title' => 'تماس با ما', 'text' => 'برای شروع همکاری یا پرسش درباره یک پروژه، فرم زیر را پر کنید یا مستقیم تماس بگیرید.' ) ),
					array( 'widget' => 'dani_contact', 'settings' => array( 'title' => 'درباره پروژه‌تان حرف بزنیم.', 'eyebrow' => 'LET US TALK' ) ),
					array( 'widget' => 'dani_faq', 'settings' => array( 'title' => 'پیش از تماس، شاید پاسخ این‌جا باشد', 'limit' => 4, 'surface' => 'yes' ) ),
				),
				'content'  => '[dani_contact title="درباره پروژه‌تان حرف بزنیم."][dani_faq title="پیش از تماس، شاید پاسخ این‌جا باشد" limit="4" surface="1"]',
				'excerpt'  => 'راه‌های تماس با دنی سایت: ایمیل، تلفن و فرم درخواست پروژه.',
			),
			array(
				'key'      => 'faq',
				'title'    => 'پرسش‌های متداول',
				'slug'     => 'faq',
				'sections' => array(
					array( 'widget' => 'dani_page_hero', 'settings' => array( 'eyebrow' => 'FAQ', 'title' => 'پرسش‌های متداول', 'text' => 'پاسخ پرتکرارترین پرسش‌ها درباره زمان، هزینه، پشتیبانی و روند همکاری.' ) ),
					array( 'widget' => 'dani_faq', 'settings' => array( 'title' => '', 'text' => '', 'limit' => 20 ) ),
					array( 'widget' => 'dani_cta' ),
				),
				'content'  => '[dani_faq title="" text="" limit="20"][dani_cta]',
				'excerpt'  => 'پاسخ پرسش‌های متداول درباره طراحی سایت، هزینه، زمان‌بندی، پشتیبانی و مدیریت محتوا در دنی سایت.',
			),
		);
	}

	/**
	 * Import pages with Elementor data and shortcode fallback content.
	 *
	 * @return int Count.
	 */
	private static function import_pages() {
		$count = 0;

		foreach ( array_merge( self::page_definitions(), self::legal_pages() ) as $definition ) {
			$page = get_page_by_path( $definition['slug'] );

			$args = array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $definition['title'],
				'post_name'    => $definition['slug'],
				'post_content' => isset( $definition['content'] ) ? $definition['content'] : '',
				'post_excerpt' => isset( $definition['excerpt'] ) ? $definition['excerpt'] : '',
			);

			if ( $page ) {
				$args['ID'] = $page->ID;
				$page_id    = wp_update_post( $args, true );
			} else {
				$page_id = wp_insert_post( $args, true );
			}

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				continue;
			}

			$count++;
			update_post_meta( $page_id, self::KEY, $definition['key'] );

			if ( ! empty( $definition['sections'] ) && defined( 'ELEMENTOR_VERSION' ) ) {
				$data = array();
				foreach ( $definition['sections'] as $section ) {
					$data[] = self::elementor_section( $section['widget'], isset( $section['settings'] ) ? $section['settings'] : array() );
				}
				update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
				update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
				update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
				update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
				update_post_meta( $page_id, '_wp_page_template', isset( $definition['template'] ) ? $definition['template'] : 'default' );
			}
		}

		return $count;
	}

	/**
	 * Legal pages with long-form Persian content.
	 *
	 * @return array
	 */
	private static function legal_pages() {
		return array(
			array(
				'key'     => 'privacy',
				'title'   => 'حریم خصوصی',
				'slug'    => 'privacy-policy',
				'excerpt' => 'سیاست حفظ حریم خصوصی دنی سایت و نحوه نگهداری اطلاعات کاربران.',
				'content' => '<h2>۱. اطلاعاتی که جمع‌آوری می‌کنیم</h2><p>هنگام ارسال فرم تماس، نام، نشانی ایمیل، شماره تماس و متن پیام شما ذخیره می‌شود. اطلاعات فنی مانند نوع مرورگر و دستگاه ممکن است برای بهبود تجربه کاربری و امنیت سایت ثبت شود.</p><h2>۲. استفاده از اطلاعات</h2><p>اطلاعات شما فقط برای پاسخ به درخواست، ارائه پیشنهاد همکاری و پیگیری پروژه استفاده می‌شود. اطلاعات شما فروخته یا در اختیار اشخاص ثالث غیرمرتبط قرار نمی‌گیرد.</p><h2>۳. کوکی‌ها</h2><p>این سایت از کوکی‌های ضروری برای حفظ ترجیحات شما (مانند حالت تیره) استفاده می‌کند. کوکی‌های تحلیلی فقط در صورت رضایت شما فعال می‌شوند.</p><h2>۴. مدت نگهداری</h2><p>پیام‌های فرم تماس تا حداکثر دو سال نگهداری می‌شوند مگر آن‌که بخواهید حذف شوند.</p><h2>۵. حقوق شما</h2><p>می‌توانید در هر زمان درخواست مشاهده، اصلاح یا حذف اطلاعات خود را از طریق ایمیل <a href="mailto:info@danisite.ir">info@danisite.ir</a> ارسال کنید.</p>',
			),
			array(
				'key'     => 'terms',
				'title'   => 'قوانین و مقررات',
				'slug'    => 'terms',
				'excerpt' => 'شرایط استفاده از وب‌سایت و قواعد همکاری با دنی سایت.',
				'content' => '<h2>۱. پذیرش قوانین</h2><p>با استفاده از این وب‌سایت، قوانین مندرج در این صفحه را می‌پذیرید. این قوانین ممکن است در آینده به‌روزرسانی شود و نسخه معتبر همان نسخه منتشرشده در این صفحه است.</p><h2>۲. خدمات</h2><p>محدوده، زمان‌بندی و هزینه هر پروژه در پیشنهاد رسمی مشخص می‌شود. محتوای این سایت جنبه معرفی دارد و تعهدی فراتر از قرارداد ایجاد نمی‌کند.</p><h2>۳. مالکیت معنوی</h2><p>طراحی‌ها، کدها و محتوای تولیدشده پس از تسویه کامل به کارفرما منتقل می‌شود. نمونه‌کارها ممکن است با اجازه کارفرما در این سایت نمایش داده شوند.</p><h2>۴. مسئولیت</h2><p>دنی سایت مسئول خسارات غیرمستقیم ناشی از استفاده از سایت یا تأخیر در ارائه خدمات نیست. تلاش ما ارائه اطلاعات دقیق و خدمات با کیفیت است.</p><h2>۵. قانون حاکم</h2><p>این قوانین تابع قوانین جمهوری اسلامی ایران است.</p>',
			),
			array(
				'key'     => 'cookies',
				'title'   => 'سیاست کوکی‌ها',
				'slug'    => 'cookie-policy',
				'excerpt' => 'توضیح کوکی‌های استفاده‌شده در وب‌سایت دنی سایت.',
				'content' => '<h2>کوکی چیست؟</h2><p>کوکی فایل کوچکی است که مرورگر شما هنگام بازدید از سایت ذخیره می‌کند و به سایت کمک می‌کند تنظیمات شما را به یاد بیاورد.</p><h2>کوکی‌هایی که استفاده می‌کنیم</h2><ul><li><strong>ضروری:</strong> برای ورود مدیران و حفظ ترجیح حالت روشن یا تیره.</li><li><strong>عملکردی:</strong> برای یادآوری زبان و تنظیمات نمایش.</li><li><strong>تحلیلی (اختیاری):</strong> برای درک رفتار بازدیدکنندگان و بهبود محتوا.</li></ul><h2>مدیریت کوکی‌ها</h2><p>می‌توانید کوکی‌ها را از تنظیمات مرورگر خود حذف یا غیرفعال کنید. غیرفعال کردن کوکی‌های ضروری ممکن است بخشی از عملکرد سایت را محدود کند.</p>',
			),
		);
	}

	/**
	 * Elementor section wrapper around one widget.
	 *
	 * @param string $widget   Widget name.
	 * @param array  $settings Widget settings.
	 * @return array
	 */
	private static function elementor_section( $widget, $settings = array() ) {
		return array(
			'id'       => self::uid(),
			'elType'   => 'section',
			'settings' => array(
				'layout'   => 'full_width',
				'gap'      => 'no',
				'structure' => '10',
				'padding'  => array(
					'unit'     => 'px',
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'isLinked' => true,
				),
			),
			'elements' => array(
				array(
					'id'       => self::uid(),
					'elType'   => 'column',
					'settings' => array(
						'_column_size' => 100,
						'_inline_size' => null,
					),
					'elements' => array(
						array(
							'id'         => self::uid(),
							'elType'     => 'widget',
							'settings'   => (object) $settings,
							'elements'   => array(),
							'widgetType' => $widget,
						),
					),
					'isInner'  => false,
				),
			),
			'isInner'  => false,
		);
	}

	/**
	 * Random Elementor-style element id.
	 *
	 * @return string
	 */
	private static function uid() {
		return substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 7 );
	}

	/* ---------------------------------------------------------------------
	 * Menus, front page and settings
	 * ------------------------------------------------------------------ */

	/**
	 * Build and assign the menus.
	 *
	 * @return string Log line.
	 */
	private static function setup_menus() {
		$structure = array(
			'منوی اصلی' => array( 'primary', array( 'home', 'about', 'services', 'portfolio', 'blog', 'contact' ) ),
			'منوی فوتر' => array( 'footer', array( 'home', 'about', 'services', 'portfolio', 'blog', 'contact' ) ),
			'قوانین و حریم خصوصی' => array( 'legal', array( 'privacy-policy', 'terms', 'cookie-policy' ) ),
		);

		$locations = (array) get_nav_menu_locations();

		foreach ( $structure as $menu_name => $config ) {
			list( $location, $slugs ) = $config;

			$menu = wp_get_nav_menu_object( $menu_name );
			if ( $menu ) {
				$menu_id = (int) $menu->term_id;
				// Rebuild items so repeated imports never duplicate links.
				foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
					wp_delete_post( $item->ID, true );
				}
			} else {
				$menu_id = wp_create_nav_menu( $menu_name );
				if ( is_wp_error( $menu_id ) ) {
					continue;
				}
			}

			foreach ( $slugs as $slug ) {
				$page = get_page_by_path( $slug );
				if ( ! $page ) {
					continue;
				}
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $slug === 'home' ? 'خانه' : $page->post_title,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page->ID,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}

			$locations[ $location ] = $menu_id;
		}

		set_theme_mod( 'nav_menu_locations', $locations );

		return 'منوهای اصلی، فوتر و قوانین ساخته و به جایگاه‌ها متصل شدند.';
	}

	/**
	 * Point the site at the imported homepage and blog page.
	 *
	 * @return string Log line.
	 */
	private static function setup_front_page() {
		$home = get_page_by_path( 'home' );
		$blog = get_page_by_path( 'blog' );

		if ( $home ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home->ID );
		}
		if ( $blog ) {
			update_option( 'page_for_posts', $blog->ID );
		}

		return 'صفحه اصلی و صفحه وبلاگ تنظیم شدند.';
	}

	/**
	 * Theme options and permalinks.
	 *
	 * @return string Log line.
	 */
	private static function setup_settings() {
		$defaults = dani_default_options();
		$current  = get_option( 'dani_options', array() );
		$current  = is_array( $current ) ? $current : array();

		$merged = array();
		foreach ( $defaults as $key => $value ) {
			$merged[ $key ] = isset( $current[ $key ] ) && '' !== $current[ $key ] ? $current[ $key ] : $value;
		}

		update_option( 'dani_options', $merged );

		update_option( 'blogname', 'DaniSite' );
		update_option( 'blogdescription', 'استودیوی طراحی و توسعه وب' );
		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'timezone_string', 'Asia/Tehran' );

		flush_rewrite_rules();

		return 'تنظیمات قالب، ساختار پیوند یکتا و منطقه زمانی اعمال شد.';
	}

	/**
	 * Elementor settings that keep imported pages editable.
	 *
	 * @return string Log line.
	 */
	private static function setup_elementor() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return 'Elementor فعال نیست؛ صفحات با شورت‌کد نمایش داده می‌شوند.';
		}

		$cpt = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		$cpt = is_array( $cpt ) ? $cpt : array( 'page', 'post' );
		$cpt = array_unique( array_merge( $cpt, array( 'page', 'post' ) ) );
		update_option( 'elementor_cpt_support', $cpt );

		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		update_option( 'elementor_load_fa4_shim', 'yes' );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		return 'تنظیمات Elementor برای ویرایش صفحات اعمال شد.';
	}
}
