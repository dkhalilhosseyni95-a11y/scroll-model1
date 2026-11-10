<?php
/**
 * Floating glass header shared by the site header and the Elementor widget.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the capsule header.
 */
class Moon_Core_Header {

	/**
	 * Register the site-wide header output.
	 */
	public static function init() {
		add_action( 'wp_body_open', array( __CLASS__, 'render_site_header' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'render_site_footer' ), 5 );
	}

	/**
	 * Render the Moon footer when Moon owns the chrome.
	 */
	public static function render_site_footer() {
		if ( ! self::is_active() ) {
			return;
		}

		self::render_footer();
	}

	/**
	 * The footer: brand column, menus, newsletter and legal row.
	 */
	public static function render_footer() {
		$brand   = (string) moon_core_setting( 'brand_name', 'MOON' );
		$tagline = (string) moon_core_setting( 'brand_tagline', '' );
		$logo    = (int) moon_core_setting( 'logo_dark_id', 0 );

		$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

		$menus = array(
			__( 'فروشگاه', 'moon-core' ) => array(
				array( __( 'همه محصولات', 'moon-core' ), $shop ),
				array( __( 'زنانه', 'moon-core' ), $shop ),
				array( __( 'مردانه', 'moon-core' ), $shop ),
				array( __( 'علاقه‌مندی‌ها', 'moon-core' ), self::wishlist_url() ),
			),
			__( 'خدمات مشتریان', 'moon-core' ) => array(
				array( __( 'پیگیری سفارش', 'moon-core' ), function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ),
				array( __( 'سوالات متداول', 'moon-core' ), home_url( '/faq/' ) ),
				array( __( 'قوانین و مقررات', 'moon-core' ), home_url( '/terms/' ) ),
				array( __( 'تماس با ما', 'moon-core' ), home_url( '/contact/' ) ),
			),
		);
		?>
		<footer class="moon-footer" role="contentinfo">
			<div class="moon-footer__inner">
				<div class="moon-footer__brand">
					<a class="moon-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php
						if ( $logo ) {
							echo wp_get_attachment_image( $logo, 'full', false, array( 'class' => 'moon-header__logo', 'alt' => $brand ) );
						} else {
							echo esc_html( $brand );
						}
						?>
					</a>
					<?php if ( $tagline ) : ?>
						<p class="moon-footer__tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
					<?php echo do_shortcode( '[moon_newsletter heading="" text="' . esc_attr__( 'کالکشن بعدی را اول ببینید.', 'moon-core' ) . '"]' ); ?>
				</div>

				<?php foreach ( $menus as $title => $links ) : ?>
					<nav class="moon-footer__col" aria-label="<?php echo esc_attr( $title ); ?>">
						<h2 class="moon-footer__title"><?php echo esc_html( $title ); ?></h2>
						<ul>
							<?php foreach ( $links as $link ) : ?>
								<li><a href="<?php echo esc_url( $link[1] ); ?>"><?php echo esc_html( $link[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endforeach; ?>
			</div>

			<div class="moon-footer__bottom">
				<p><?php echo esc_html( (string) moon_core_setting( 'footer_note', '' ) ); ?></p>
				<p class="moon-footer__credit"><?php esc_html_e( 'طراحی و توسعه: MOON Studio · وردپرس + المنتور', 'moon-core' ); ?></p>
			</div>
		</footer>
		<?php
	}

	/**
	 * Whether Moon Core owns the header on this request.
	 *
	 * @return bool
	 */
	public static function is_active() {
		if ( is_admin() || ! function_exists( 'wp_body_open' ) ) {
			return false;
		}

		return 'moon' === moon_core_setting( 'header_source', 'moon' );
	}

	/**
	 * Render the site-wide header with settings as arguments.
	 */
	public static function render_site_header() {
		if ( ! self::is_active() ) {
			return;
		}

		$logo_id = (int) moon_core_setting( 'logo_id', 0 );

		self::render(
			array(
				'logo_id'      => $logo_id,
				'logo_dark_id' => (int) moon_core_setting( 'logo_dark_id', 0 ),
				'menu'         => (string) moon_core_setting( 'header_menu', '' ),
				'show_search'  => (int) moon_core_setting( 'header_show_search', 1 ),
				'show_account' => (int) moon_core_setting( 'header_show_account', 1 ),
				'show_wishlist' => (int) moon_core_setting( 'header_show_wishlist', 1 ),
				'show_cart'    => (int) moon_core_setting( 'header_show_cart', 1 ),
				'show_toggle'  => (int) moon_core_setting( 'header_show_toggle', 1 ),
				'sticky'       => (int) moon_core_setting( 'header_sticky', 1 ),
				'shrink'       => (int) moon_core_setting( 'header_shrink', 1 ),
				'cta_text'     => (string) moon_core_setting( 'header_cta_text', '' ),
				'cta_url'      => (string) moon_core_setting( 'header_cta_url', '' ),
			)
		);
	}

	/**
	 * Wishlist page URL, honouring YITH WooCommerce Wishlist when installed.
	 *
	 * @return string
	 */
	public static function wishlist_url() {
		if ( defined( 'YITH_WCWL' ) ) {
			$page_id = (int) get_option( 'yith_wcwl_wishlist_page_id' );
			if ( $page_id ) {
				return get_permalink( $page_id );
			}
		}

		$page_id = (int) get_option( 'moon_core_wishlist_page' );

		return $page_id ? get_permalink( $page_id ) : home_url( '/' );
	}

	/**
	 * Number of wishlist items for the current visitor.
	 *
	 * @return int
	 */
	public static function wishlist_count() {
		if ( defined( 'YITH_WCWL' ) && function_exists( 'YITH_WCWL' ) ) {
			$count = YITH_WCWL()->count_products();
			return is_numeric( $count ) ? (int) $count : 0;
		}

		return Moon_Core_WooCommerce::wishlist_count();
	}

	/**
	 * The menu entries: either a real WordPress menu or the product categories.
	 *
	 * @param string $menu Menu id, slug or name.
	 * @return array{label:string,url:string,children:array}
	 */
	public static function nav_items( $menu = '' ) {
		$items = array();

		if ( $menu && wp_get_nav_menu_object( $menu ) ) {
			$locations = wp_get_nav_menu_items( $menu );
			if ( is_array( $locations ) ) {
				foreach ( $locations as $item ) {
					if ( ! empty( $item->menu_item_parent ) ) {
						continue;
					}
					$children = array();
					foreach ( $locations as $child ) {
						if ( (int) $child->menu_item_parent === (int) $item->ID ) {
							$children[] = array(
								'label'    => $child->title,
								'url'      => $child->url,
								'children' => array(),
							);
						}
					}
					$items[] = array(
						'label'    => $item->title,
						'url'      => $item->url,
						'children' => $children,
					);
				}
			}

			return $items;
		}

		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $items;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => 0,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
				'number'     => 8,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return $items;
		}

		foreach ( $terms as $term ) {
			if ( 'uncategorized' === $term->slug ) {
				continue;
			}

			$children_terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'parent'     => $term->term_id,
					'number'     => 6,
				)
			);
			$children       = array();

			if ( ! is_wp_error( $children_terms ) ) {
				foreach ( $children_terms as $child ) {
					$children[] = array(
						'label'    => $child->name,
						'url'      => get_term_link( $child ),
						'children' => array(),
					);
				}
			}

			$items[] = array(
				'label'    => $term->name,
				'url'      => get_term_link( $term ),
				'children' => $children,
			);
		}

		return $items;
	}

	/**
	 * Render the header.
	 *
	 * @param array $args Header arguments.
	 */
	public static function render( $args = array() ) {
		if ( is_admin() ) {
			return;
		}

		$defaults = array(
			'logo_id'       => 0,
			'logo_dark_id'  => 0,
			'brand'         => (string) moon_core_setting( 'brand_name', 'MOON' ),
			'menu'          => '',
			'show_search'   => 1,
			'show_account'  => 1,
			'show_wishlist' => 1,
			'show_cart'     => 1,
			'show_toggle'   => 1,
			'show_cta'      => 0,
			'sticky'        => 1,
			'shrink'        => 1,
			'cta_text'      => '',
			'cta_url'       => '',
			'extra_class'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$classes = array( 'moon-header-wrap' );
		if ( $args['sticky'] ) {
			$classes[] = 'is-sticky';
		}
		if ( $args['shrink'] ) {
			$classes[] = 'can-shrink';
		}
		if ( ! empty( $args['extra_class'] ) ) {
			$classes[] = $args['extra_class'];
		}

		$nav        = self::nav_items( $args['menu'] );
		$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
		$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
		$account    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
		$search_url = home_url( '/' );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-moon-header>
			<header class="moon-header" role="banner">
				<div class="moon-header__glass" aria-hidden="true"></div>
				<div class="moon-header__inner">
					<button class="moon-icon-btn moon-header__burger" type="button"
						aria-expanded="false" aria-controls="moon-mobile-nav"
						aria-label="<?php esc_attr_e( 'منو', 'moon-core' ); ?>">
						<?php self::icon( 'burger' ); ?>
					</button>

					<a class="moon-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"
						aria-label="<?php echo esc_attr( $args['brand'] ); ?>">
						<?php
						if ( $args['logo_id'] ) {
							echo wp_get_attachment_image(
								(int) $args['logo_id'],
								'full',
								false,
								array(
									'class' => 'moon-header__logo moon-header__logo--light',
									'alt'   => $args['brand'],
								)
							);
						}
						if ( $args['logo_dark_id'] ) {
							echo wp_get_attachment_image(
								(int) $args['logo_dark_id'],
								'full',
								false,
								array(
									'class' => 'moon-header__logo moon-header__logo--dark',
									'alt'   => $args['brand'],
								)
							);
						}
						if ( ! $args['logo_id'] && ! $args['logo_dark_id'] ) :
							?>
							<span class="moon-header__wordmark"><?php echo esc_html( $args['brand'] ); ?></span>
						<?php endif; ?>
					</a>

					<nav class="moon-header__nav" aria-label="<?php esc_attr_e( 'دسته‌بندی‌ها', 'moon-core' ); ?>">
						<ul class="moon-menu">
							<?php foreach ( $nav as $item ) : ?>
								<li class="moon-menu__item<?php echo $item['children'] ? ' has-children' : ''; ?>">
									<a class="moon-menu__link" href="<?php echo esc_url( $item['url'] ); ?>">
										<?php echo esc_html( $item['label'] ); ?>
									</a>
									<?php if ( $item['children'] ) : ?>
										<ul class="moon-menu__sub">
											<?php foreach ( $item['children'] as $child ) : ?>
												<li>
													<a href="<?php echo esc_url( $child['url'] ); ?>"><?php echo esc_html( $child['label'] ); ?></a>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>

					<div class="moon-header__actions">
						<?php if ( $args['show_search'] ) : ?>
							<button class="moon-icon-btn" type="button" data-moon-search-toggle
								aria-expanded="false" aria-controls="moon-search-panel"
								aria-label="<?php esc_attr_e( 'جست‌وجوی محصولات', 'moon-core' ); ?>">
								<?php self::icon( 'search' ); ?>
							</button>
						<?php endif; ?>

						<?php if ( $args['show_account'] ) : ?>
							<a class="moon-icon-btn moon-hide-mobile" href="<?php echo esc_url( $account ); ?>"
								aria-label="<?php esc_attr_e( 'حساب کاربری', 'moon-core' ); ?>">
								<?php self::icon( 'user' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $args['show_wishlist'] ) : ?>
							<a class="moon-icon-btn moon-hide-mobile" href="<?php echo esc_url( self::wishlist_url() ); ?>"
								aria-label="<?php esc_attr_e( 'علاقه‌مندی‌ها', 'moon-core' ); ?>">
								<?php self::icon( 'heart' ); ?>
								<span class="moon-count" data-moon-wishlist-count><?php echo esc_html( (string) self::wishlist_count() ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( $args['show_cart'] && function_exists( 'WC' ) ) : ?>
							<a class="moon-icon-btn" href="<?php echo esc_url( $cart_url ); ?>"
								aria-label="<?php esc_attr_e( 'سبد خرید', 'moon-core' ); ?>">
								<?php self::icon( 'bag' ); ?>
								<span class="moon-count" data-moon-cart-count><?php echo esc_html( (string) $cart_count ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( $args['show_toggle'] ) : ?>
							<button class="moon-theme-toggle" type="button" data-moon-theme-toggle aria-pressed="false"
								aria-label="<?php esc_attr_e( 'تغییر حالت روشن و تاریک', 'moon-core' ); ?>">
								<span class="moon-theme-toggle__icon moon-theme-toggle__icon--sun"><?php self::icon( 'sun' ); ?></span>
								<span class="moon-theme-toggle__icon moon-theme-toggle__icon--moon"><?php self::icon( 'moon' ); ?></span>
								<span class="screen-reader-text" data-moon-theme-label>
									<?php echo esc_html( moon_core_setting( 'dark_toggle_label_light', __( 'حالت شب', 'moon-core' ) ) ); ?>
								</span>
							</button>
						<?php endif; ?>

						<?php if ( $args['show_cta'] && $args['cta_text'] ) : ?>
							<a class="moon-btn moon-btn--ghost moon-header__cta" href="<?php echo esc_url( $args['cta_url'] ? $args['cta_url'] : '#' ); ?>">
								<?php echo esc_html( $args['cta_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<div class="moon-search" id="moon-search-panel" hidden>
					<form role="search" method="get" class="moon-search__form" action="<?php echo esc_url( $search_url ); ?>">
						<label class="screen-reader-text" for="moon-search-input"><?php esc_html_e( 'جست‌وجوی محصولات', 'moon-core' ); ?></label>
						<input id="moon-search-input" type="search" name="s" class="moon-search__input"
							placeholder="<?php esc_attr_e( 'نام محصول، دسته‌بندی…', 'moon-core' ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>" />
						<?php if ( function_exists( 'wc_get_page_id' ) ) : ?>
							<input type="hidden" name="post_type" value="product" />
						<?php endif; ?>
						<button class="moon-btn moon-btn--solid" type="submit"><?php esc_html_e( 'جست‌وجو', 'moon-core' ); ?></button>
					</form>
				</div>
			</header>

			<div class="moon-mobile-nav" id="moon-mobile-nav" hidden>
				<div class="moon-mobile-nav__head">
					<span class="moon-mobile-nav__title"><?php echo esc_html( $args['brand'] ); ?></span>
					<button class="moon-icon-btn" type="button" data-moon-mobile-close
						aria-label="<?php esc_attr_e( 'بستن منو', 'moon-core' ); ?>"><?php self::icon( 'close' ); ?></button>
				</div>
				<nav aria-label="<?php esc_attr_e( 'منوی موبایل', 'moon-core' ); ?>">
					<ul class="moon-mobile-nav__list">
						<?php foreach ( $nav as $item ) : ?>
							<li>
								<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
								<?php if ( $item['children'] ) : ?>
									<ul class="moon-mobile-nav__sub">
										<?php foreach ( $item['children'] as $child ) : ?>
											<li><a href="<?php echo esc_url( $child['url'] ); ?>"><?php echo esc_html( $child['label'] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
				<div class="moon-mobile-nav__foot">
					<a href="<?php echo esc_url( $account ); ?>"><?php esc_html_e( 'حساب کاربری', 'moon-core' ); ?></a>
					<a href="<?php echo esc_url( self::wishlist_url() ); ?>"><?php esc_html_e( 'علاقه‌مندی‌ها', 'moon-core' ); ?></a>
					<a href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'سبد خرید', 'moon-core' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Inline SVG icon set (no icon-font request).
	 *
	 * @param string $name Icon name.
	 */
	public static function icon( $name ) {
		$icons = array(
			'search' => '<path d="M11 4a7 7 0 1 0 4.31 12.52l3.58 3.58 1.42-1.42-3.58-3.58A7 7 0 0 0 11 4Zm0 2a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z"/>',
			'user'   => '<path d="M12 4a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4Z"/>',
			'heart'  => '<path d="M12 20.35 10.55 19C5.4 14.36 2 11.28 2 7.5A4.5 4.5 0 0 1 6.5 3c1.74 0 3.41.81 4.5 2.09A6.01 6.01 0 0 1 15.5 3 4.5 4.5 0 0 1 20 7.5c0 3.78-3.4 6.86-8.55 11.54L12 20.35Z" fill="none" stroke="currentColor" stroke-width="1.5"/>',
			'bag'    => '<path d="M7 8V6a5 5 0 0 1 10 0v2h3v13H4V8h3Zm2 0h6V6a3 3 0 0 0-6 0v2Z"/>',
			'sun'    => '<path d="M12 6a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm0-4v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4m0-14.2-1.4 1.4M6.3 17.7l-1.4 1.4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
			'moon'   => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
			'burger' => '<path d="M3 6h18M3 12h18M3 18h18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
			'close'  => '<path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
			'truck'  => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
			'shield' => '<path d="M12 3l7 3v6c0 4.2-2.9 7.7-7 9-4.1-1.3-7-4.8-7-9V6l7-3Zm-2.5 9 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
			'refresh' => '<path d="M4 12a8 8 0 0 1 13.7-5.6L20 8M20 4v4h-4M20 12a8 8 0 0 1-13.7 5.6L4 16M4 20v-4h4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
			'gift'   => '<path d="M4 10h16v10H4zM2 6h20v4H2zM12 6v14M12 6c-1.5-3-6-3-6 0m6 0c1.5-3 6-3 6 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
			'chat'   => '<path d="M4 5h16v11H9l-5 4V5Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
			'star'   => '<path d="m12 3.5 2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6L3.3 9.9l6.1-.8L12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
			'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
		);

		$path = isset( $icons[ $name ] ) ? $icons[ $name ] : '';

		printf(
			'<svg class="moon-icon moon-icon--%1$s" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">%2$s</svg>',
			esc_attr( $name ),
			$path // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
		);
	}
}
