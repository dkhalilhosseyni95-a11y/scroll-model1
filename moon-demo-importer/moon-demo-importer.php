<?php
/**
 * Plugin Name: MOON Demo Importer
 * Description: نصب محتوای نمونه MOON ULTIMATE (محصولات، دسته‌ها، صفحه‌ها، مگامنو، هدر/فوتر و تنظیمات) با پشتیبان‌گیری، گزارش درصد پیشرفت و بازگردانی. (محصولات، دسته‌ها، صفحه‌ها، رگال و استودیو). فقط برای راه‌اندازی اولیه؛ بعد از ایمپورت می‌توانید آن را غیرفعال یا حذف کنید.
 * Version: 3.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: MOON
 * Text Domain: moon-theme
 *
 * @package Moon_Demo_Importer
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_DEMO_DIR', plugin_dir_path( __FILE__ ) );
define( 'MOON_DEMO_VERSION', '3.0.0' );

/**
 * Is the MOON theme (and its panel) available?
 *
 * @return bool
 */
function moon_demo_theme_ready() {
	return defined( 'MOON_THEME_VERSION' ) && class_exists( 'Moon_Admin' );
}

if ( ! function_exists( 'moon_demo_find_by_title' ) ) {
	/**
	 * Find a post by exact title.
	 *
	 * @param string $title     Title.
	 * @param string $post_type Post type.
	 * @return WP_Post|null
	 */
	function moon_demo_find_by_title( $title, $post_type = 'post' ) {
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status NOT IN ('trash','auto-draft') LIMIT 1",
			$title,
			$post_type
		) );

		return $id ? get_post( (int) $id ) : null;
	}
}

/**
 * Load once all plugins and the theme are available.
 */
function moon_demo_boot() {
	// The legacy "moon-core" plugin ships a class with the same name; never load both.
	if ( class_exists( 'Moon_Demo_Importer', false ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>MOON Demo Importer:</strong> ' . esc_html__( 'افزونه قدیمی «MOON Core» فعال است و با این افزونه تداخل دارد. آن را غیرفعال و حذف کنید.', 'moon-theme' ) . '</p></div>';
		} );

		return;
	}

	if ( ! moon_demo_theme_ready() ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-warning"><p><strong>MOON Demo Importer:</strong> ' . esc_html__( 'ابتدا قالب MOON را فعال کنید.', 'moon-theme' ) . '</p></div>';
		} );

		return;
	}

	require_once MOON_DEMO_DIR . 'includes/class-moon-demo-importer.php';
	require_once MOON_DEMO_DIR . 'includes/class-moon-catalog.php';
	require_once MOON_DEMO_DIR . 'includes/class-moon-store-catalog.php';

	Moon_Demo_Importer::init();

	add_action( 'admin_menu', 'moon_demo_menu', 20 );
	add_action( 'admin_post_moon_demo_import', 'moon_demo_handle_import' );
}
add_action( 'after_setup_theme', 'moon_demo_boot', 20 );

/**
 * Submenu under the MOON panel.
 */
function moon_demo_menu() {
	add_submenu_page( 'moon-theme', __( 'نصب محتوا', 'moon-theme' ), __( 'نصب محتوا', 'moon-theme' ), 'manage_options', 'moon-demo', 'moon_demo_render' );
}

/**
 * Import screen.
 */
function moon_demo_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
	}

	wp_enqueue_style( 'moon-demo', plugins_url( 'assets/demo.css', __FILE__ ), array(), MOON_DEMO_VERSION );
	wp_enqueue_script( 'moon-demo', plugins_url( 'assets/demo.js', __FILE__ ), array(), MOON_DEMO_VERSION, true );
	wp_localize_script( 'moon-demo', 'MOON_DEMO', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'moon_demo' ),
		'i18n'    => array(
			'working' => __( 'در حال اجرا…', 'moon-theme' ),
			'done'    => __( 'نصب کامل شد.', 'moon-theme' ),
			'failed'  => __( 'نصب با خطا مواجه شد.', 'moon-theme' ),
			'none'    => __( 'حداقل یک بخش را انتخاب کنید.', 'moon-theme' ),
			'reload'  => __( 'برای دیدن گزارش کامل، صفحه را بازخوانی کنید.', 'moon-theme' ),
		),
	) );

	include MOON_DEMO_DIR . 'views/demo.php';
}

/**
 * Run the import (nonce + capability protected).
 */
function moon_demo_handle_import() {
	$back = admin_url( 'admin.php?page=moon-demo' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
	}

	if ( ! isset( $_POST['moon_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moon_demo_nonce'] ) ), 'moon_demo_import' ) ) {
		wp_safe_redirect( add_query_arg( 'moon_notice', 'failed', $back ) );
		exit;
	}

	$options = array();

	foreach ( array_keys( \Moon_Demo_Importer::steps() ) as $step ) {
		$options[ $step ] = ! empty( $_POST[ 'import_' . $step ] );
	}

	$report = Moon_Demo_Importer::run( $options );

	set_transient( 'moon_demo_report_' . get_current_user_id(), $report, 5 * MINUTE_IN_SECONDS );

	wp_safe_redirect( add_query_arg( 'moon_notice', 'imported', $back ) );
	exit;
}
