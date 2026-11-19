<?php
/**
 * Site header.
 *
 * @package Netelec
 */

$netelec_brand      = netelec_opt( 'brand_name' );
$netelec_logo_id    = (int) netelec_opt( 'logo_id' );
$netelec_cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$netelec_account    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$netelec_cart_total = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_subtotal() : '';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main">رفتن به محتوای اصلی</a>

<?php if ( netelec_opt( 'show_topbar' ) ) : ?>
	<div class="nc-topbar<?php echo netelec_opt( 'mobile_hide_topbar' ) ? ' nc-topbar--hide-mobile' : ''; ?>">
		<div class="container nc-topbar__inner">
			<p class="nc-topbar__note"><?php netelec_icon( 'truck', 'nc-icon--sm' ); ?><?php echo esc_html( netelec_opt( 'topbar_text' ) ); ?></p>
			<div class="nc-topbar__links">
				<a href="<?php echo esc_url( netelec_page_url( 'about' ) ); ?>">درباره ما</a>
				<a href="<?php echo esc_url( netelec_page_url( 'contact' ) ); ?>">تماس با ما</a>
				<?php if ( netelec_opt( 'phone' ) ) : ?>
					<a class="nc-topbar__phone" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', netelec_opt( 'phone' ) ) ); ?>">
						<?php netelec_icon( 'phone', 'nc-icon--sm' ); ?>
						<?php echo esc_html( netelec_opt( 'phone' ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<header class="site-header" id="site-header">
	<div class="container site-header__inner">
		<div class="site-header__start">
			<button class="icon-btn nav-toggle" type="button" aria-controls="mobile-drawer" aria-expanded="false" aria-label="باز کردن منو">
				<?php netelec_icon( 'menu' ); ?>
			</button>

			<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php if ( $netelec_logo_id ) : ?>
					<?php echo wp_get_attachment_image( $netelec_logo_id, 'full', false, array( 'class' => 'site-brand__logo', 'alt' => $netelec_brand ) ); ?>
				<?php else : ?>
					<span class="site-brand__mark" aria-hidden="true">N</span>
					<span class="site-brand__text">
						<strong><?php echo esc_html( $netelec_brand ); ?></strong>
						<small><?php echo esc_html( netelec_opt( 'brand_tagline' ) ); ?></small>
					</span>
				<?php endif; ?>
			</a>
		</div>

		<?php if ( netelec_opt( 'show_search' ) ) : ?>
			<div class="header-search" data-live-search>
				<form role="search" method="get" class="header-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="screen-reader-text" for="header-search-input">جستجوی محصولات</label>
					<?php netelec_icon( 'search', 'header-search__icon' ); ?>
					<input
						id="header-search-input"
						type="search"
						name="s"
						value="<?php echo get_search_query(); ?>"
						placeholder="جستجوی محصول، برند یا کد کالا…"
						autocomplete="off"
						aria-expanded="false"
						aria-controls="live-search-results"
					>
					<input type="hidden" name="post_type" value="product">
					<button type="submit" class="header-search__submit" aria-label="جستجو">
						<?php netelec_icon( 'search' ); ?>
					</button>
				</form>
				<div class="live-search" id="live-search-results" role="listbox" aria-label="نتایج جستجو" hidden></div>
			</div>
		<?php endif; ?>

		<div class="site-header__actions">
			<?php if ( netelec_opt( 'show_account' ) ) : ?>
				<a class="header-action" href="<?php echo esc_url( $netelec_account ); ?>">
					<span class="header-action__icon"><?php netelec_icon( 'user' ); ?></span>
					<span class="header-action__text">
						<small><?php echo is_user_logged_in() ? 'حساب من' : 'ورود / ثبت‌نام'; ?></small>
						<strong><?php echo is_user_logged_in() ? esc_html( wp_get_current_user()->display_name ) : 'کاربر مهمان'; ?></strong>
					</span>
				</a>
			<?php endif; ?>

			<?php if ( netelec_opt( 'show_wishlist' ) && netelec_opt( 'wishlist_enabled' ) ) : ?>
				<a class="icon-btn header-wishlist" href="<?php echo esc_url( netelec_page_url( 'wishlist' ) ); ?>" aria-label="علاقه‌مندی‌ها">
					<?php netelec_icon( 'heart' ); ?>
					<span class="icon-btn__badge wishlist-count" data-wishlist-count><?php echo esc_html( netelec_wishlist_count() ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( netelec_opt( 'show_cart' ) ) : ?>
				<a class="header-cart" href="<?php echo esc_url( $netelec_cart_url ); ?>" aria-label="سبد خرید">
					<span class="header-cart__icon">
						<?php netelec_icon( 'cart' ); ?>
						<span class="icon-btn__badge cart-count"><?php echo esc_html( netelec_cart_count() ); ?></span>
					</span>
					<span class="header-cart__text">
						<small>سبد خرید</small>
						<strong class="cart-total"><?php echo wp_kses_post( $netelec_cart_total ? $netelec_cart_total : '۰ تومان' ); ?></strong>
					</span>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<div class="site-nav">
		<div class="container site-nav__inner">
			<div class="cat-nav">
				<button class="cat-nav__toggle" type="button" aria-expanded="false" aria-controls="cat-nav-panel">
					<?php netelec_icon( 'grid', 'nc-icon--sm' ); ?>
					دسته‌بندی محصولات
					<?php netelec_icon( 'chevron', 'nc-icon--sm cat-nav__chevron' ); ?>
				</button>
				<?php if ( taxonomy_exists( 'product_cat' ) ) : ?>
					<ul class="cat-nav__panel" id="cat-nav-panel">
						<?php
						$netelec_cats = get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => true,
								'parent'     => 0,
								'number'     => 14,
								'orderby'    => 'count',
								'order'      => 'DESC',
							)
						);
						if ( ! is_wp_error( $netelec_cats ) ) :
							foreach ( $netelec_cats as $netelec_cat ) :
								?>
								<li>
									<a href="<?php echo esc_url( get_term_link( $netelec_cat ) ); ?>">
										<span><?php echo esc_html( $netelec_cat->name ); ?></span>
										<small><?php echo esc_html( $netelec_cat->count ); ?> محصول</small>
									</a>
								</li>
								<?php
							endforeach;
						endif;
						?>
					</ul>
				<?php endif; ?>
			</div>

			<nav class="primary-nav" aria-label="منوی اصلی">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'primary-nav__list',
						'depth'          => 2,
						'fallback_cb'    => 'netelec_fallback_menu',
					)
				);
				?>
			</nav>

			<div class="site-nav__extra">
				<?php if ( netelec_opt( 'compare_enabled' ) ) : ?>
					<a class="site-nav__link" href="<?php echo esc_url( netelec_page_url( 'compare' ) ); ?>">
						<?php netelec_icon( 'compare', 'nc-icon--sm' ); ?>
						مقایسه
						<span class="compare-count" data-compare-count><?php echo esc_html( netelec_compare_count() ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( netelec_opt( 'phone' ) ) : ?>
					<span class="site-nav__support">
						<?php netelec_icon( 'headset', 'nc-icon--sm' ); ?>
						<span>
							<small><?php echo esc_html( netelec_opt( 'header_note' ) ); ?></small>
							<strong><?php echo esc_html( netelec_opt( 'phone' ) ); ?></strong>
						</span>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>

<div class="mobile-drawer" id="mobile-drawer" aria-hidden="true">
	<div class="mobile-drawer__head">
		<span class="site-brand__text">
			<strong><?php echo esc_html( netelec_opt( 'brand_name' ) ); ?></strong>
		</span>
		<button class="icon-btn mobile-drawer__close" type="button" aria-label="بستن منو">
			<?php netelec_icon( 'close' ); ?>
		</button>
	</div>

	<form role="search" method="get" class="mobile-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="mobile-search-input">جستجوی محصولات</label>
		<input id="mobile-search-input" type="search" name="s" placeholder="جستجوی محصول…" value="<?php echo get_search_query(); ?>">
		<input type="hidden" name="post_type" value="product">
		<button type="submit" aria-label="جستجو"><?php netelec_icon( 'search' ); ?></button>
	</form>

	<nav class="mobile-nav" aria-label="منوی موبایل">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'mobile-nav__list',
				'depth'          => 2,
				'fallback_cb'    => 'netelec_fallback_menu',
			)
		);
		?>
	</nav>

	<div class="mobile-drawer__links">
		<a href="<?php echo esc_url( $netelec_account ); ?>"><?php netelec_icon( 'user', 'nc-icon--sm' ); ?> حساب کاربری</a>
		<a href="<?php echo esc_url( netelec_page_url( 'wishlist' ) ); ?>"><?php netelec_icon( 'heart', 'nc-icon--sm' ); ?> علاقه‌مندی‌ها</a>
		<a href="<?php echo esc_url( netelec_page_url( 'compare' ) ); ?>"><?php netelec_icon( 'compare', 'nc-icon--sm' ); ?> مقایسه محصولات</a>
		<a href="<?php echo esc_url( netelec_page_url( 'contact' ) ); ?>"><?php netelec_icon( 'headset', 'nc-icon--sm' ); ?> تماس با ما</a>
	</div>

	<?php if ( netelec_opt( 'phone' ) ) : ?>
		<a class="mobile-drawer__phone" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', netelec_opt( 'phone' ) ) ); ?>">
			<?php netelec_icon( 'phone', 'nc-icon--sm' ); ?>
			<?php echo esc_html( netelec_opt( 'phone' ) ); ?>
		</a>
	<?php endif; ?>
</div>

<div class="nc-overlay" hidden></div>

<main id="main" class="site-main">
