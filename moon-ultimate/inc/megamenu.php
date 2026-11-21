<?php
/**
 * Mega menu builder (Digikala-style category menu).
 *
 * Data lives in the `moon_megamenu` option. The admin page (MOON → مگامنو)
 * edits it; the front end renders it in the header, the mobile drawer, on
 * product-category pages and anywhere via [moon_megamenu].
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defaults.
 *
 * @return array
 */
function moon_mega_defaults() {
	return array(
		'enabled' => 0,
		'label'   => __( 'دسته‌بندی کالاها', 'moon-theme' ),
		'items'   => array(),
	);
}

/**
 * Saved data merged with defaults.
 *
 * @return array
 */
function moon_mega_get() {
	$data = get_option( 'moon_megamenu', array() );
	$data = is_array( $data ) ? $data : array();

	return wp_parse_args( $data, moon_mega_defaults() );
}

/**
 * Whether the mega menu has something to show.
 *
 * @return bool
 */
function moon_mega_active() {
	$data = moon_mega_get();

	return ! empty( $data['enabled'] ) && ! empty( $data['items'] );
}

/**
 * Sanitize a posted structure.
 *
 * @param mixed $raw Decoded JSON.
 * @return array
 */
function moon_mega_sanitize( $raw ) {
	$raw = is_array( $raw ) ? $raw : array();
	$out = array(
		'enabled' => empty( $raw['enabled'] ) ? 0 : 1,
		'label'   => isset( $raw['label'] ) ? sanitize_text_field( $raw['label'] ) : '',
		'items'   => array(),
	);

	if ( '' === $out['label'] ) {
		$out['label'] = moon_mega_defaults()['label'];
	}

	$items = isset( $raw['items'] ) && is_array( $raw['items'] ) ? array_slice( $raw['items'], 0, 20 ) : array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) || empty( $item['title'] ) ) {
			continue;
		}

		$clean = array(
			'title'  => sanitize_text_field( $item['title'] ),
			'url'    => isset( $item['url'] ) ? esc_url_raw( $item['url'] ) : '',
			'image'  => isset( $item['image'] ) ? absint( $item['image'] ) : 0,
			'cols'   => array(),
			'banner' => array( 'image' => 0, 'url' => '', 'title' => '' ),
		);

		if ( ! empty( $item['banner'] ) && is_array( $item['banner'] ) ) {
			$clean['banner'] = array(
				'image' => isset( $item['banner']['image'] ) ? absint( $item['banner']['image'] ) : 0,
				'url'   => isset( $item['banner']['url'] ) ? esc_url_raw( $item['banner']['url'] ) : '',
				'title' => isset( $item['banner']['title'] ) ? sanitize_text_field( $item['banner']['title'] ) : '',
			);
		}

		$cols = isset( $item['cols'] ) && is_array( $item['cols'] ) ? array_slice( $item['cols'], 0, 8 ) : array();

		foreach ( $cols as $col ) {
			if ( ! is_array( $col ) || empty( $col['title'] ) ) {
				continue;
			}

			$links = array();

			foreach ( isset( $col['links'] ) && is_array( $col['links'] ) ? array_slice( $col['links'], 0, 30 ) : array() as $link ) {
				if ( is_array( $link ) && ! empty( $link['title'] ) ) {
					$links[] = array(
						'title' => sanitize_text_field( $link['title'] ),
						'url'   => isset( $link['url'] ) ? esc_url_raw( $link['url'] ) : '',
						'image' => isset( $link['image'] ) ? absint( $link['image'] ) : 0,
					);
				}
			}

			$clean['cols'][] = array(
				'title' => sanitize_text_field( $col['title'] ),
				'url'   => isset( $col['url'] ) ? esc_url_raw( $col['url'] ) : '',
				'links' => $links,
			);
		}

		$out['items'][] = $clean;
	}

	return $out;
}

/* --------------------------------------------------------------------------
 * Admin
 * ------------------------------------------------------------------------ */

/**
 * Admin menu entry.
 */
function moon_mega_admin_menu() {
	add_submenu_page( 'moon-theme', __( 'مگامنو', 'moon-theme' ), __( 'مگامنو', 'moon-theme' ), 'manage_options', 'moon-megamenu', 'moon_mega_admin_page' );
}
add_action( 'admin_menu', 'moon_mega_admin_menu', 20 );

/**
 * Category tree for the "fill from category" helper.
 *
 * @param int $parent Parent term ID.
 * @param int $depth  Recursion guard.
 * @return array
 */
function moon_mega_category_tree( $parent = 0, $depth = 0 ) {
	if ( ! taxonomy_exists( 'product_cat' ) || $depth > 3 ) {
		return array();
	}

	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent, 'hide_empty' => false, 'number' => 100 ) );
	$out   = array();

	if ( is_wp_error( $terms ) ) {
		return $out;
	}

	foreach ( $terms as $term ) {
		if ( 'uncategorized' === $term->slug ) {
			continue;
		}

		$thumb = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

		$out[] = array(
			'id'       => $term->term_id,
			'name'     => $term->name,
			'url'      => get_term_link( $term ),
			'image'    => $thumb,
			'imageUrl' => $thumb ? wp_get_attachment_image_url( $thumb, 'thumbnail' ) : '',
			'children' => moon_mega_category_tree( $term->term_id, $depth + 1 ),
		);
	}

	return $out;
}

/**
 * Assets for the admin page.
 *
 * @param string $hook Hook suffix.
 */
function moon_mega_admin_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'moon-megamenu' ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_style( 'moon-mega-admin', MOON_THEME_URI . '/assets/admin/megamenu-admin.css', array(), MOON_THEME_VERSION );
	wp_enqueue_script( 'moon-mega-admin', MOON_THEME_URI . '/assets/admin/megamenu-admin.js', array( 'jquery', 'jquery-ui-sortable' ), MOON_THEME_VERSION, true );

	$data = moon_mega_get();

	foreach ( $data['items'] as &$item ) {
		$item['imageUrl']           = $item['image'] ? wp_get_attachment_image_url( $item['image'], 'thumbnail' ) : '';
		$item['banner']['imageUrl'] = $item['banner']['image'] ? wp_get_attachment_image_url( $item['banner']['image'], 'medium' ) : '';

		foreach ( $item['cols'] as &$col ) {
			foreach ( $col['links'] as &$link ) {
				$link['imageUrl'] = $link['image'] ? wp_get_attachment_image_url( $link['image'], 'thumbnail' ) : '';
			}
		}
		unset( $col, $link );
	}
	unset( $item );

	wp_localize_script( 'moon-mega-admin', 'MOON_MEGA', array(
		'data' => $data,
		'tree' => moon_mega_category_tree(),
	) );
}
add_action( 'admin_enqueue_scripts', 'moon_mega_admin_assets' );

/**
 * Admin page.
 */
function moon_mega_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
	}

	$notice = isset( $_GET['moon_notice'] ) ? sanitize_key( wp_unslash( $_GET['moon_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap moon-mega-admin" dir="rtl">
		<h1><?php esc_html_e( 'سازنده مگامنو', 'moon-theme' ); ?></h1>
		<?php if ( 'saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'مگامنو ذخیره شد.', 'moon-theme' ); ?></p></div>
		<?php endif; ?>
		<p class="description"><?php esc_html_e( 'دسته‌های اصلی را در ستون راست و محتوای هر دسته را (ستون‌ها، لینک‌ها و بنر) در ستون چپ بسازید؛ مثل منوی دسته‌بندی دیجی‌کالا. می‌توانید با دکمه «پر کردن از دسته ووکامرس» ساختار را خودکار بسازید. شورت‌کد [moon_megamenu] همین منو را در هر صفحه‌ای نمایش می‌دهد.', 'moon-theme' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="moon-mega-form">
			<input type="hidden" name="action" value="moon_mega_save">
			<?php wp_nonce_field( 'moon_mega_save', 'moon_mega_nonce' ); ?>
			<textarea name="moon_mega_json" id="moon-mega-json" hidden></textarea>

			<div class="moon-mega-admin__top">
				<label><input type="checkbox" id="moon-mega-enabled"> <?php esc_html_e( 'فعال‌سازی مگامنو در هدر و موبایل', 'moon-theme' ); ?></label>
				<label><?php esc_html_e( 'عنوان دکمه هدر', 'moon-theme' ); ?> <input type="text" id="moon-mega-label" class="regular-text"></label>
				<button type="button" class="button" id="moon-mega-fill-all"><?php esc_html_e( 'ساخت خودکار از تمام دسته‌های ووکامرس', 'moon-theme' ); ?></button>
			</div>

			<div class="moon-mega-admin__layout">
				<ul class="moon-mega-admin__rail" id="moon-mega-rail"></ul>
				<div class="moon-mega-admin__editor" id="moon-mega-editor"></div>
			</div>

			<p>
				<button type="button" class="button" id="moon-mega-add"><?php esc_html_e( '+ دسته اصلی جدید', 'moon-theme' ); ?></button>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره مگامنو', 'moon-theme' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Save handler.
 */
function moon_mega_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
	}

	$nonce = isset( $_POST['moon_mega_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['moon_mega_nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'moon_mega_save' ) ) {
		wp_die( esc_html__( 'درخواست نامعتبر است.', 'moon-theme' ), 403 );
	}

	$json = isset( $_POST['moon_mega_json'] ) ? wp_unslash( $_POST['moon_mega_json'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded and sanitized field by field below.
	$raw  = json_decode( (string) $json, true );

	update_option( 'moon_megamenu', moon_mega_sanitize( $raw ), false );

	wp_safe_redirect( add_query_arg( array( 'page' => 'moon-megamenu', 'moon_notice' => 'saved' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_moon_mega_save', 'moon_mega_save' );

/* --------------------------------------------------------------------------
 * Front end
 * ------------------------------------------------------------------------ */

/**
 * Load the assets when needed.
 */
function moon_mega_enqueue() {
	if ( ! moon_mega_active() ) {
		return;
	}

	wp_enqueue_style( 'moon-mega', MOON_THEME_URI . '/assets/css/megamenu.css', array( 'moon-base' ), MOON_THEME_VERSION );
	wp_enqueue_script( 'moon-mega', MOON_THEME_URI . '/assets/js/megamenu.js', array(), MOON_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}
add_action( 'wp_enqueue_scripts', 'moon_mega_enqueue', 30 );

/**
 * Image tag for an attachment, empty when missing.
 *
 * @param int    $id   Attachment.
 * @param string $size Size.
 * @return string
 */
function moon_mega_img( $id, $size = 'thumbnail' ) {
	return $id ? wp_get_attachment_image( $id, $size, false, array( 'loading' => 'lazy', 'alt' => '' ) ) : '';
}

/**
 * One pane (columns + banner) for an item.
 *
 * @param array $item Item.
 */
function moon_mega_pane( $item ) {
	?>
	<div class="moon-mega__cols">
		<?php if ( $item['url'] ) : ?>
			<a class="moon-mega__all" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( sprintf( /* translators: %s: category name. */ __( 'مشاهده همه %s', 'moon-theme' ), $item['title'] ) ); ?></a>
		<?php endif; ?>
		<div class="moon-mega__grid">
			<?php foreach ( $item['cols'] as $col ) : ?>
				<div class="moon-mega__col">
					<h3 class="moon-mega__col-title">
						<?php if ( $col['url'] ) : ?>
							<a href="<?php echo esc_url( $col['url'] ); ?>"><?php echo esc_html( $col['title'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $col['title'] ); ?>
						<?php endif; ?>
					</h3>
					<ul>
						<?php foreach ( $col['links'] as $link ) : ?>
							<li>
								<a href="<?php echo esc_url( $link['url'] ? $link['url'] : '#' ); ?>">
									<?php echo moon_mega_img( $link['image'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php echo esc_html( $link['title'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if ( ! empty( $item['banner']['image'] ) ) : ?>
		<a class="moon-mega__banner" href="<?php echo esc_url( $item['banner']['url'] ? $item['banner']['url'] : '#' ); ?>">
			<?php echo moon_mega_img( $item['banner']['image'], 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $item['banner']['title'] ) : ?>
				<span><?php echo esc_html( $item['banner']['title'] ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>
	<?php
}

/**
 * Rail + panes. Used in the header panel and inline via shortcode.
 *
 * @param bool $inline Render without the header chrome.
 * @return string
 */
function moon_mega_render( $inline = false ) {
	$data = moon_mega_get();

	if ( empty( $data['items'] ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="moon-mega<?php echo $inline ? ' moon-mega--inline is-open' : ''; ?>" <?php echo $inline ? '' : 'id="moon-mega" hidden'; ?> data-moon-mega>
		<ul class="moon-mega__rail" role="tablist" aria-label="<?php echo esc_attr( $data['label'] ); ?>">
			<?php foreach ( $data['items'] as $i => $item ) : ?>
				<li role="presentation">
					<a class="moon-mega__tab<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tab" href="<?php echo esc_url( $item['url'] ? $item['url'] : '#' ); ?>" data-moon-mega-tab="<?php echo esc_attr( (string) $i ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
						<?php echo moon_mega_img( $item['image'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $item['title'] ); ?></span>
						<?php moon_the_icon( 'arrow-left', 14 ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="moon-mega__panes">
			<?php foreach ( $data['items'] as $i => $item ) : ?>
				<div class="moon-mega__pane<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tabpanel" data-moon-mega-pane="<?php echo esc_attr( (string) $i ); ?>">
					<?php moon_mega_pane( $item ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Header button + panel.
 */
function moon_mega_header() {
	if ( ! moon_mega_active() ) {
		return;
	}

	$data = moon_mega_get();
	?>
	<button type="button" class="moon-mega-toggle" aria-expanded="false" aria-controls="moon-mega" data-moon-mega-toggle>
		<?php moon_the_icon( 'menu', 16 ); ?>
		<span><?php echo esc_html( $data['label'] ); ?></span>
	</button>
	<?php
}

/**
 * Header panel, printed outside the <nav> so menu styles cannot leak into it.
 */
function moon_mega_panel() {
	if ( moon_mega_active() ) {
		echo moon_mega_render( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
	}
}

/**
 * Mobile drawer accordion.
 *
 * One collapsible "دسته‌بندی کالاها" panel built from the WooCommerce category
 * tree, so phones and tablets get a real dropdown instead of a flat link list.
 * Sub-categories are listed under their parent when a category has children.
 */
function moon_mega_mobile() {
	if ( ! function_exists( 'get_terms' ) || ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$moon_cats = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	) );

	if ( is_wp_error( $moon_cats ) || empty( $moon_cats ) ) {
		return;
	}
	?>
	<div class="moon-mega-m">
		<details class="moon-mega-m__group">
			<summary>
				<span class="moon-mega-m__label"><?php esc_html_e( 'دسته‌بندی کالاها', 'moon-theme' ); ?></span>
				<span class="moon-mega-m__chev" aria-hidden="true"><?php moon_the_icon( 'chevron-left', 16 ); ?></span>
			</summary>

			<ul class="moon-mega-m__cats">
				<?php foreach ( $moon_cats as $moon_cat ) : ?>
					<?php
					$moon_cat_link = get_term_link( $moon_cat );

					if ( is_wp_error( $moon_cat_link ) ) {
						continue;
					}

					$moon_kids = get_terms( array(
						'taxonomy'   => 'product_cat',
						'parent'     => $moon_cat->term_id,
						'hide_empty' => false,
					) );
					?>
					<li class="moon-mega-m__cat">
						<a class="moon-mega-m__cat-link" href="<?php echo esc_url( $moon_cat_link ); ?>">
							<span class="moon-mega-m__cat-name"><?php echo esc_html( $moon_cat->name ); ?></span>
							<?php if ( (int) $moon_cat->count > 0 ) : ?>
								<span class="moon-mega-m__count"><?php echo esc_html( moon_fa_digits( (string) (int) $moon_cat->count ) ); ?></span>
							<?php endif; ?>
						</a>

						<?php if ( ! is_wp_error( $moon_kids ) && ! empty( $moon_kids ) ) : ?>
							<ul class="moon-mega-m__sub">
								<?php foreach ( $moon_kids as $moon_kid ) : ?>
									<?php $moon_kid_link = get_term_link( $moon_kid ); ?>
									<?php if ( ! is_wp_error( $moon_kid_link ) ) : ?>
										<li><a href="<?php echo esc_url( $moon_kid_link ); ?>"><?php echo esc_html( $moon_kid->name ); ?></a></li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<a class="moon-mega-m__all" href="<?php echo esc_url( moon_shop_url() ); ?>"><?php esc_html_e( 'مشاهده همه محصولات', 'moon-theme' ); ?></a>
		</details>
	</div>
	<?php
}

/**
 * Subcategory tiles on a product-category page, taken from the mega menu item
 * that points at the current category (or one of its ancestors).
 */
function moon_mega_category_panel() {
	if ( ! moon_mega_active() || ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return;
	}

	$term = get_queried_object();

	if ( ! $term instanceof WP_Term ) {
		return;
	}

	$ids  = array_merge( array( $term->term_id ), get_ancestors( $term->term_id, 'product_cat' ) );
	$urls = array();

	foreach ( $ids as $id ) {
		$link = get_term_link( (int) $id, 'product_cat' );

		if ( ! is_wp_error( $link ) ) {
			$urls[ untrailingslashit( $link ) ] = (int) $id;
		}
	}

	$data = moon_mega_get();
	$best = null;
	$rank = 99;

	foreach ( $data['items'] as $item ) {
		$key = untrailingslashit( (string) $item['url'] );

		if ( isset( $urls[ $key ] ) ) {
			$pos = array_search( $urls[ $key ], $ids, true );

			if ( $pos < $rank ) {
				$best = $item;
				$rank = $pos;
			}
		}
	}

	if ( ! $best || empty( $best['cols'] ) ) {
		return;
	}
	?>
	<section class="moon-catnav" aria-label="<?php esc_attr_e( 'زیردسته‌ها', 'moon-theme' ); ?>">
		<?php foreach ( $best['cols'] as $col ) : ?>
			<div class="moon-catnav__group">
				<h2 class="moon-catnav__title">
					<?php if ( $col['url'] ) : ?><a href="<?php echo esc_url( $col['url'] ); ?>"><?php echo esc_html( $col['title'] ); ?></a><?php else : echo esc_html( $col['title'] ); endif; ?>
				</h2>
				<ul class="moon-catnav__list">
					<?php foreach ( $col['links'] as $link ) : ?>
						<li>
							<a href="<?php echo esc_url( $link['url'] ? $link['url'] : '#' ); ?>">
								<?php echo moon_mega_img( $link['image'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo esc_html( $link['title'] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</section>
	<?php
}

/**
 * [moon_megamenu] shortcode.
 *
 * @return string
 */
function moon_mega_shortcode() {
	$data = moon_mega_get();

	if ( empty( $data['items'] ) ) {
		return '';
	}

	wp_enqueue_style( 'moon-mega', MOON_THEME_URI . '/assets/css/megamenu.css', array( 'moon-base' ), MOON_THEME_VERSION );
	wp_enqueue_script( 'moon-mega', MOON_THEME_URI . '/assets/js/megamenu.js', array(), MOON_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	return moon_mega_render( true );
}
add_shortcode( 'moon_megamenu', 'moon_mega_shortcode' );
