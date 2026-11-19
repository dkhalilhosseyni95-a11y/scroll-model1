<?php
/**
 * Theme pages, menus and the one-click setup tool.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Does a page with this slug exist?
 *
 * @param string $slug Slug.
 * @return bool
 */
function netelec_page_exists( $slug ) {
	$page = get_page_by_path( $slug );
	return $page && 'publish' === get_post_status( $page );
}

/**
 * URL of a theme page, falling back to the home page.
 *
 * @param string $slug Slug.
 * @return string
 */
function netelec_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === get_post_status( $page ) ) {
		return get_permalink( $page );
	}
	return home_url( '/' );
}

/**
 * Primary menu fallback.
 */
function netelec_fallback_menu() {
	$items = array(
		array( 'خانه', home_url( '/' ) ),
	);

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$items[] = array( 'فروشگاه', wc_get_page_permalink( 'shop' ) );
	}

	$items[] = array( 'وبلاگ', netelec_blog_url() );
	$items[] = array( 'درباره ما', netelec_page_url( 'about' ) );
	$items[] = array( 'تماس با ما', netelec_page_url( 'contact' ) );

	echo '<ul class="primary-nav__list">';
	foreach ( $items as $item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ) );
	}
	echo '</ul>';
}

/**
 * Blog archive URL.
 *
 * @return string
 */
function netelec_blog_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		return get_permalink( $posts_page );
	}
	return home_url( '/' );
}

/**
 * Footer menu fallback.
 */
function netelec_footer_fallback_menu() {
	$items = array(
		array( 'فروشگاه', function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ),
		array( 'وبلاگ', netelec_blog_url() ),
		array( 'درباره ما', netelec_page_url( 'about' ) ),
		array( 'تماس با ما', netelec_page_url( 'contact' ) ),
		array( 'قوانین و مقررات', netelec_page_url( 'terms' ) ),
		array( 'حریم خصوصی', netelec_page_url( 'privacy' ) ),
	);

	echo '<ul class="footer-menu">';
	foreach ( $items as $item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ) );
	}
	echo '</ul>';
}

/**
 * Footer product categories fallback.
 */
function netelec_footer_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 6,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	echo '<ul class="footer-menu">';
	foreach ( $terms as $term ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( get_term_link( $term ) ),
			esc_html( $term->name )
		);
	}
	echo '</ul>';
}

/**
 * Create (or complete) the theme pages and menus.
 *
 * @return array Created page ids.
 */
function netelec_create_pages() {
	$pages = array(
		'about'    => array( 'درباره ما', 'page-templates/template-about.php' ),
		'contact'  => array( 'تماس با ما', 'page-templates/template-contact.php' ),
		'wishlist' => array( 'علاقه‌مندی‌ها', 'page-templates/template-wishlist.php' ),
		'compare'  => array( 'مقایسه محصولات', 'page-templates/template-compare.php' ),
		'terms'    => array( 'قوانین و مقررات', '' ),
		'privacy'  => array( 'حریم خصوصی', '' ),
	);

	$created = array();

	foreach ( $pages as $slug => $config ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			if ( $config[1] && ! get_post_meta( $existing->ID, '_wp_page_template', true ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', $config[1] );
			}
			$created[ $slug ] = $existing->ID;
			continue;
		}

		$content = '';
		if ( 'terms' === $slug || 'privacy' === $slug ) {
			$content = '<p>این متن جایگزین است. لطفاً سیاست‌های واقعی فروشگاه خود را در همین صفحه بنویسید.</p>';
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $config[0],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $content,
			)
		);

		if ( is_wp_error( $page_id ) ) {
			continue;
		}

		if ( $config[1] ) {
			update_post_meta( $page_id, '_wp_page_template', $config[1] );
		}

		$created[ $slug ] = $page_id;
	}

	// Blog archive page, only when the site still uses the default home layout.
	if ( ! (int) get_option( 'page_for_posts' ) ) {
		$blog = get_page_by_path( 'blog' );
		if ( ! $blog ) {
			$blog_id = wp_insert_post(
				array(
					'post_title'  => 'وبلاگ',
					'post_name'   => 'blog',
					'post_status' => 'publish',
					'post_type'   => 'page',
				)
			);
			if ( ! is_wp_error( $blog_id ) ) {
				update_option( 'page_for_posts', $blog_id );
				$created['blog'] = $blog_id;
			}
		} else {
			update_option( 'page_for_posts', $blog->ID );
		}
	}

	netelec_create_menu( $created );

	return $created;
}

/**
 * Build the primary and footer menus when none are assigned yet.
 *
 * @param array $pages Created page ids.
 */
function netelec_create_menu( $pages ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	if ( empty( $locations['primary'] ) ) {
		$menu_id = wp_create_nav_menu( 'منوی اصلی NETELEC' );
		if ( ! is_wp_error( $menu_id ) ) {
			$links = array( 'خانه' => home_url( '/' ) );

			if ( function_exists( 'wc_get_page_permalink' ) ) {
				$links['فروشگاه'] = wc_get_page_permalink( 'shop' );
			}

			$links['وبلاگ']      = netelec_blog_url();
			$links['درباره ما']  = netelec_page_url( 'about' );
			$links['تماس با ما'] = netelec_page_url( 'contact' );

			$order = 0;
			foreach ( $links as $label => $url ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $label,
						'menu-item-url'    => $url,
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
						'menu-item-position' => $order++,
					)
				);
			}

			$locations['primary'] = $menu_id;
		}
	}

	if ( empty( $locations['footer'] ) ) {
		$footer_id = wp_create_nav_menu( 'منوی فوتر NETELEC' );
		if ( ! is_wp_error( $footer_id ) ) {
			$links = array(
				'فروشگاه'          => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
				'درباره ما'        => netelec_page_url( 'about' ),
				'تماس با ما'       => netelec_page_url( 'contact' ),
				'قوانین و مقررات'  => netelec_page_url( 'terms' ),
				'حریم خصوصی'       => netelec_page_url( 'privacy' ),
			);

			$order = 0;
			foreach ( $links as $label => $url ) {
				wp_update_nav_menu_item(
					$footer_id,
					0,
					array(
						'menu-item-title'  => $label,
						'menu-item-url'    => $url,
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
						'menu-item-position' => $order++,
					)
				);
			}

			$locations['footer'] = $footer_id;
		}
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * AJAX: create pages from the panel.
 */
function netelec_ajax_create_pages() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی مجاز نیست.' ), 403 );
	}

	check_ajax_referer( 'netelec_admin', 'nonce' );

	$created = netelec_create_pages();

	wp_send_json_success(
		array(
			'message' => 'صفحات و منوها آماده شدند.',
			'count'   => count( $created ),
		)
	);
}
add_action( 'wp_ajax_netelec_create_pages', 'netelec_ajax_create_pages' );

/**
 * Point the visitor to the setup screen after activation.
 */
function netelec_activation_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen && 'toplevel_page_netelec-settings' === $screen->id ) {
		return;
	}

	if ( get_option( 'netelec_setup_done' ) ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong>قالب NETELEC فعال شد.</strong>
			برای ساخت صفحات (تماس، مقایسه، علاقه‌مندی‌ها) و تنظیم رنگ‌ها به
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=netelec-settings' ) ); ?>">تنظیمات قالب NETELEC</a>
			بروید.
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'netelec_activation_notice' );

/**
 * Remember that the notice was dismissed once the panel is visited.
 */
function netelec_mark_setup_seen() {
	if ( isset( $_GET['page'] ) && 'netelec-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		update_option( 'netelec_setup_done', 1 );
	}
}
add_action( 'admin_init', 'netelec_mark_setup_seen' );
