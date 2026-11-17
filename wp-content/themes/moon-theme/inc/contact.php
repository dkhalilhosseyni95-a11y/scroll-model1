<?php
/**
 * Contact form: every message is stored as a private "moon_message" entry (so
 * nothing is lost if mail delivery fails) and e-mailed to the site admin.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Private inbox post type.
 */
function moon_contact_register_cpt() {
	register_post_type( 'moon_message', array(
		'labels'          => array(
			'name'          => __( 'پیام‌های تماس', 'moon-theme' ),
			'singular_name' => __( 'پیام تماس', 'moon-theme' ),
			'menu_name'     => __( 'پیام‌های تماس', 'moon-theme' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 27,
		'menu_icon'       => 'dashicons-email-alt',
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'moon_contact_register_cpt' );

/**
 * Handle a submission.
 */
function moon_contact_ajax() {
	check_ajax_referer( 'moon_shop', 'nonce' );

	// Honeypot: real visitors never fill it.
	if ( ! empty( $_POST['moon_website'] ) ) {
		wp_send_json_success( array( 'message' => __( 'پیام شما ثبت شد.', 'moon-theme' ) ) );
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$rate_key = 'moon_contact_' . md5( $ip );
	$count    = (int) get_transient( $rate_key );

	if ( $count >= 5 ) {
		wp_send_json_error( array( 'message' => __( 'کمی بعد دوباره تلاش کنید.', 'moon-theme' ) ), 429 );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( '' === $name || '' === $message || ( ! $email && '' === $phone ) || ( $email && ! is_email( $email ) ) ) {
		wp_send_json_error( array( 'message' => __( 'نام، متن پیام و ایمیل یا شماره تماس را کامل وارد کنید.', 'moon-theme' ) ), 400 );
	}

	if ( strlen( $message ) > 4000 ) {
		wp_send_json_error( array( 'message' => __( 'متن پیام بیش از حد طولانی است.', 'moon-theme' ) ), 400 );
	}

	set_transient( $rate_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$title   = $subject ? $subject . ' — ' . $name : $name;
	$post_id = wp_insert_post( array(
		'post_type'    => 'moon_message',
		'post_status'  => 'private',
		'post_title'   => $title,
		'post_content' => $message,
	), true );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'ثبت پیام انجام نشد. دوباره تلاش کنید.', 'moon-theme' ) ), 500 );
	}

	update_post_meta( $post_id, '_moon_email', $email );
	update_post_meta( $post_id, '_moon_phone', $phone );

	$body = sprintf( "%s: %s\n%s: %s\n%s: %s\n\n%s", __( 'نام', 'moon-theme' ), $name, __( 'ایمیل', 'moon-theme' ), $email ? $email : '—', __( 'تلفن', 'moon-theme' ), $phone ? $phone : '—', $message );

	// Best effort: the message is already saved in the inbox.
	wp_mail( get_option( 'admin_email' ), sprintf( __( 'پیام تازه از سایت: %s', 'moon-theme' ), $title ), $body );

	wp_send_json_success( array( 'message' => __( 'پیام شما ثبت شد. حداکثر تا یک روز کاری پاسخ می‌دهیم.', 'moon-theme' ) ) );
}
add_action( 'wp_ajax_moon_contact', 'moon_contact_ajax' );
add_action( 'wp_ajax_nopriv_moon_contact', 'moon_contact_ajax' );

/**
 * Contact script, only on the contact page.
 */
function moon_contact_enqueue() {
	if ( is_page_template( 'page-contact.php' ) ) {
		wp_enqueue_script( 'moon-contact', MOON_THEME_URI . '/assets/js/contact.js', array( 'moon-chrome' ), MOON_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => false ) );
	}
}
add_action( 'wp_enqueue_scripts', 'moon_contact_enqueue', 20 );
