<?php
/**
 * Site chrome: the floating capsule header, the premium footer and their
 * overlays (search, mobile navigation, mini cart drawer).
 *
 * The theme prints this chrome when no Elementor Pro header/footer template is
 * assigned, and the MOON Header/Footer widgets reuse the same markup so the
 * design stays identical whether it is edited in Elementor or configured once
 * in Moon Core settings.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Site_Chrome {

	/** Overlays must be printed once per page, even with several headers. */
	private static bool $overlays_printed = false;

	/**
	 * Header arguments, merged with settings defaults.
	 *
	 * @param array<string,mixed> $args Overrides.
	 * @return array<string,mixed>
	 */
	public static function header_args( array $args = array() ): array {
		$settings = Settings::all();

		return wp_parse_args(
			$args,
			array(
				'brand_name'     => $settings['brand_name'],
				'tagline'        => $settings['brand_tagline'],
				'logo'           => array(),
				'menu_location'  => 'primary',
				'show_search'    => true,
				'show_account'   => true,
				'show_cart'      => true,
				'show_scheme'    => true,
				'show_wishlist'  => true,
				'sticky'         => $settings['header_sticky'] === 'yes',
				'hide_on_scroll' => $settings['header_hide'] === 'yes',
				'cta_text'       => '',
				'cta_link'       => array( 'url' => '' ),
				'announcement'   => '',
				'classes'        => '',
			)
		);
	}

	/**
	 * Render the header (+ overlays once).
	 *
	 * @param array<string,mixed> $args Header arguments.
	 */
	public static function render_header( array $args = array() ): void {
		$args = self::header_args( $args );

		$classes = array( 'moon-header', 'moon-header--floating' );
		$classes[] = $args['sticky'] ? 'is-sticky' : 'is-static';
		if ( $args['classes'] ) {
			$classes[] = $args['classes'];
		}

		$home  = home_url( '/' );
		$cart  = self::cart_url();
		$count = self::cart_count();
		?>
		<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-hide-on-scroll="<?php echo esc_attr( $args['hide_on_scroll'] ? 'yes' : 'no' ); ?>">
			<?php if ( '' !== trim( (string) $args['announcement'] ) ) : ?>
				<div class="moon-announcement"><?php echo esc_html( $args['announcement'] ); ?></div>
			<?php endif; ?>

			<div class="moon-header__shell">
				<a class="moon-brand" href="<?php echo esc_url( $home ); ?>" rel="home">
					<?php if ( ! empty( $args['logo']['url'] ) ) : ?>
						<span class="moon-brand__logo"><?php echo wp_kses_post( Helpers::image( (array) $args['logo'], 'medium', array( 'alt' => $args['brand_name'] ), false ) ); ?></span>
					<?php else : ?>
						<span class="moon-brand__word"><?php echo esc_html( $args['brand_name'] ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== (string) $args['tagline'] ) : ?>
						<span class="moon-brand__tag"><?php echo esc_html( $args['tagline'] ); ?></span>
					<?php endif; ?>
				</a>

				<nav class="moon-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'moon-core' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $args['menu_location'],
							'menu_class'     => 'moon-nav__list',
							'container'      => false,
							'depth'          => 2,
							'fallback_cb'    => array( self::class, 'menu_fallback' ),
						)
					);
					?>
				</nav>

				<div class="moon-header__actions">
					<?php if ( $args['show_search'] ) : ?>
						<button type="button" class="moon-icon-btn" data-moon-open-search aria-label="<?php esc_attr_e( 'Search', 'moon-core' ); ?>" aria-controls="moon-search-overlay" aria-expanded="false">
							<?php echo Helpers::svg( Helpers::icon( 'search', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						</button>
					<?php endif; ?>

					<?php if ( $args['show_account'] && function_exists( 'wc_get_page_permalink' ) ) : ?>
						<a class="moon-icon-btn" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'moon-core' ); ?>">
							<?php echo Helpers::svg( Helpers::icon( 'user', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>

					<?php if ( $args['show_wishlist'] ) : ?>
						<?php echo self::wishlist_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
					<?php endif; ?>

					<?php if ( $args['show_cart'] && function_exists( 'WC' ) ) : ?>
						<button type="button" class="moon-icon-btn moon-cart-trigger" data-moon-open-cart aria-label="<?php esc_attr_e( 'Open cart', 'moon-core' ); ?>" aria-controls="moon-cart-drawer" aria-expanded="false">
							<?php echo Helpers::svg( Helpers::icon( 'bag', 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo self::cart_count_markup( $count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					<?php endif; ?>

					<?php if ( $args['show_scheme'] ) : ?>
						<button type="button" class="moon-icon-btn moon-scheme-toggle" data-moon-toggle-scheme aria-pressed="false" data-label-dark="<?php esc_attr_e( 'Switch to dark mode', 'moon-core' ); ?>" data-label-light="<?php esc_attr_e( 'Switch to light mode', 'moon-core' ); ?>" aria-label="<?php esc_attr_e( 'Switch colour mode', 'moon-core' ); ?>">
							<span class="moon-scheme-toggle__icon moon-scheme-toggle__icon--sun"><?php echo Helpers::svg( Helpers::icon( 'sun', 17 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="moon-scheme-toggle__icon moon-scheme-toggle__icon--moon"><?php echo Helpers::svg( Helpers::icon( 'moon', 17 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</button>
					<?php endif; ?>

					<?php if ( '' !== trim( (string) $args['cta_text'] ) ) : ?>
						<?php self::render_link( (array) $args['cta_link'], 'moon-header__cta moon-btn moon-btn--solid', (string) $args['cta_text'] ); ?>
					<?php endif; ?>

					<button type="button" class="moon-icon-btn moon-header__burger" data-moon-menu-toggle="#moon-mobile-nav" aria-label="<?php esc_attr_e( 'Open menu', 'moon-core' ); ?>" aria-controls="moon-mobile-nav" aria-expanded="false">
						<?php echo Helpers::svg( Helpers::icon( 'menu', 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
			</div>
		</header>
		<?php
		self::render_overlays( $args );
	}

	/**
	 * Search overlay, mobile drawer and cart drawer. Printed once per request.
	 *
	 * @param array<string,mixed> $args Header arguments.
	 */
	private static function render_overlays( array $args ): void {
		if ( self::$overlays_printed ) {
			return;
		}
		self::$overlays_printed = true;
		$home = home_url( '/' );
		?>
		<div class="moon-search-overlay" id="moon-search-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Search the collection', 'moon-core' ); ?>" aria-hidden="true">
			<div class="moon-search-overlay__inner">
				<button type="button" class="moon-icon-btn moon-overlay-close" data-moon-close-search aria-label="<?php esc_attr_e( 'Close search', 'moon-core' ); ?>">
					<?php echo Helpers::svg( Helpers::icon( 'close', 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<p class="moon-eyebrow"><?php esc_html_e( 'Search', 'moon-core' ); ?></p>
				<form class="moon-search-overlay__form" role="search" method="get" action="<?php echo esc_url( $home ); ?>">
					<label class="screen-reader-text" for="moon-overlay-search"><?php esc_html_e( 'Search products', 'moon-core' ); ?></label>
					<input id="moon-overlay-search" class="moon-search-overlay__input" type="search" name="s" autocomplete="off" placeholder="<?php esc_attr_e( 'Try “wool coat” or “silk shirt”', 'moon-core' ); ?>" data-moon-live-search>
					<input type="hidden" name="post_type" value="product">
					<button type="submit" class="moon-btn moon-btn--solid"><?php esc_html_e( 'Search', 'moon-core' ); ?></button>
				</form>
				<div class="moon-search-results" data-moon-search-results aria-live="polite"></div>
			</div>
		</div>

		<div class="moon-drawer" id="moon-mobile-nav" aria-hidden="true">
			<div class="moon-drawer__head">
				<span class="moon-brand__word"><?php echo esc_html( Settings::get( 'brand_name' ) ); ?></span>
				<button type="button" class="moon-icon-btn" data-moon-menu-toggle="#moon-mobile-nav" aria-label="<?php esc_attr_e( 'Close menu', 'moon-core' ); ?>">
					<?php echo Helpers::svg( Helpers::icon( 'close', 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>
			<nav class="moon-drawer__nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'moon-core' ); ?>">
				<?php
				$mobile_location = has_nav_menu( 'mobile' ) ? 'mobile' : $args['menu_location'];
				wp_nav_menu(
					array(
						'theme_location' => $mobile_location,
						'menu_class'     => 'moon-drawer__list',
						'container'      => false,
						'depth'          => 2,
						'fallback_cb'    => array( self::class, 'menu_fallback' ),
					)
				);
				?>
			</nav>
			<div class="moon-drawer__foot">
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
					<a class="moon-link-underline" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My account', 'moon-core' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( function_exists( 'WC' ) ) : ?>
			<div class="moon-cart-drawer" id="moon-cart-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Shopping cart', 'moon-core' ); ?>" aria-hidden="true">
				<div class="moon-cart-drawer__head">
					<span><?php esc_html_e( 'Your selection', 'moon-core' ); ?></span>
					<button type="button" class="moon-icon-btn" data-moon-close-cart aria-label="<?php esc_attr_e( 'Close cart', 'moon-core' ); ?>">
						<?php echo Helpers::svg( Helpers::icon( 'close', 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
				<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
			</div>
		<?php endif; ?>

		<div class="moon-scrim" data-moon-scrim hidden></div>
		<?php
	}

	/**
	 * Footer arguments.
	 *
	 * @param array<string,mixed> $args Overrides.
	 * @return array<string,mixed>
	 */
	public static function footer_args( array $args = array() ): array {
		$settings = Settings::all();

		return wp_parse_args(
			$args,
			array(
				'brand_name'   => $settings['brand_name'],
				'blurb'        => __( 'Considered garments, made in small runs. Designed in the studio, worn everywhere else.', 'moon-core' ),
				'menu_location' => 'footer',
				'columns'      => array(),
				'socials'      => array(),
				'note'         => sprintf( /* translators: %s: year */ __( '© %s MOON. All rights reserved.', 'moon-core' ), gmdate( 'Y' ) ),
				'show_newsletter' => false,
				'newsletter_title' => __( 'Join the list', 'moon-core' ),
				'newsletter_note'  => __( 'New drops, studio notes, no noise.', 'moon-core' ),
			)
		);
	}

	/**
	 * Render the footer.
	 *
	 * @param array<string,mixed> $args Footer arguments.
	 */
	public static function render_footer( array $args = array() ): void {
		$args = self::footer_args( $args );
		?>
		<footer class="moon-footer">
			<div class="moon-container">
				<div class="moon-footer__top">
					<div class="moon-footer__brand">
						<a class="moon-brand__word" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( $args['brand_name'] ); ?></a>
						<p class="moon-footer__blurb"><?php echo esc_html( $args['blurb'] ); ?></p>
						<?php if ( ! empty( $args['socials'] ) ) : ?>
							<ul class="moon-footer__socials">
								<?php foreach ( (array) $args['socials'] as $social ) : ?>
									<?php if ( empty( $social['label'] ) || empty( $social['url'] ) ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<li><a href="<?php echo esc_url( $social['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $social['label'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>

					<div class="moon-footer__cols">
						<?php if ( ! empty( $args['columns'] ) ) : ?>
							<?php foreach ( (array) $args['columns'] as $column ) : ?>
								<div class="moon-footer__col">
									<?php if ( ! empty( $column['title'] ) ) : ?>
										<p class="moon-footer__col-title"><?php echo esc_html( $column['title'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $column['links'] ) ) : ?>
										<ul class="moon-footer__list">
											<?php foreach ( (array) $column['links'] as $link ) : ?>
												<?php if ( empty( $link['label'] ) ) : ?>
													<?php continue; ?>
												<?php endif; ?>
												<li><a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="moon-footer__col">
								<p class="moon-footer__col-title"><?php esc_html_e( 'Shop', 'moon-core' ); ?></p>
								<?php
								wp_nav_menu(
									array(
										'theme_location' => $args['menu_location'],
										'menu_class'     => 'moon-footer__list',
										'container'      => false,
										'depth'          => 1,
										'fallback_cb'    => array( self::class, 'footer_fallback_menu' ),
									)
								);
								?>
							</div>
							<div class="moon-footer__col">
								<p class="moon-footer__col-title"><?php esc_html_e( 'Help', 'moon-core' ); ?></p>
								<ul class="moon-footer__list">
									<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
										<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All products', 'moon-core' ); ?></a></li>
										<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My account', 'moon-core' ); ?></a></li>
										<li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'moon-core' ); ?></a></li>
									<?php endif; ?>
									<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'moon-core' ); ?></a></li>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $args['show_newsletter'] ) ) : ?>
					<div class="moon-footer__newsletter">
						<div>
							<p class="moon-footer__col-title"><?php echo esc_html( $args['newsletter_title'] ); ?></p>
							<p class="moon-footer__newsletter-note"><?php echo esc_html( $args['newsletter_note'] ); ?></p>
						</div>
						<form class="moon-newsletter-form moon-newsletter-form--inline" data-moon-newsletter>
							<label class="screen-reader-text" for="moon-footer-email"><?php esc_html_e( 'Email address', 'moon-core' ); ?></label>
							<input id="moon-footer-email" type="email" name="email" required placeholder="<?php esc_attr_e( 'you@example.com', 'moon-core' ); ?>">
							<input type="text" name="moon_company" class="moon-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
							<button type="submit" class="moon-btn moon-btn--solid"><?php esc_html_e( 'Subscribe', 'moon-core' ); ?></button>
							<p class="moon-newsletter-form__status" data-moon-newsletter-status role="status" aria-live="polite"></p>
						</form>
					</div>
				<?php endif; ?>

				<div class="moon-footer__bottom">
					<p><?php echo esc_html( $args['note'] ); ?></p>
					<ul class="moon-footer__legal">
						<li><a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Privacy', 'moon-core' ); ?></a></li>
						<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
							<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'moon-core' ); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</footer>
		<?php
	}

	/**
	 * Is a header rendered for this request?
	 */
	public static function has_header(): bool {
		return true;
	}

	/**
	 * Is a footer rendered for this request?
	 */
	public static function has_footer(): bool {
		return true;
	}

	/**
	 * Wishlist link when a wishlist plugin provides one.
	 */
	private static function wishlist_link(): string {
		$html = '';

		if ( shortcode_exists( 'yith_wcwl_add_to_wishlist' ) || class_exists( 'YITH_WCWL' ) ) {
			$count = function_exists( 'yith_wcwl_count_all_products' ) ? (int) yith_wcwl_count_all_products() : 0;
			$html  = sprintf(
				'<a class="moon-icon-btn moon-wishlist-link" href="%1$s" aria-label="%2$s">%3$s<span class="moon-cart-count">%4$s</span></a>',
				esc_url( home_url( '/wishlist/' ) ),
				esc_attr__( 'Wishlist', 'moon-core' ),
				Helpers::svg( Helpers::icon( 'heart', 18 ) ),
				esc_html( (string) $count )
			);
		} elseif ( defined( 'TINVWL_FVERSION' ) ) {
			$html = sprintf(
				'<a class="moon-icon-btn moon-wishlist-link" href="%1$s" aria-label="%2$s">%3$s</a>',
				esc_url( home_url( '/wishlist/' ) ),
				esc_attr__( 'Wishlist', 'moon-core' ),
				Helpers::svg( Helpers::icon( 'heart', 18 ) )
			);
		}

		/**
		 * Filter the wishlist slot in the header.
		 *
		 * @param string $html Markup (empty when no wishlist plugin is active).
		 */
		return (string) apply_filters( 'moon_core_wishlist_link', $html );
	}

	/**
	 * Cart count markup, kept in sync by WooCommerce fragments.
	 *
	 * @param int $count Item count.
	 */
	public static function cart_count_markup( int $count ): string {
		return sprintf(
			'<span class="moon-cart-count" data-count="%1$d">%2$s</span>',
			$count,
			esc_html( $count > 99 ? '99+' : (string) $count )
		);
	}

	/**
	 * Current cart item count.
	 */
	public static function cart_count(): int {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return 0;
		}

		return (int) WC()->cart->get_cart_contents_count();
	}

	/**
	 * Cart page URL.
	 */
	public static function cart_url(): string {
		return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
	}

	/**
	 * Sanitised link markup.
	 *
	 * @param array<string,mixed> $link  Elementor URL value.
	 * @param string              $class CSS classes.
	 * @param string              $label Label text.
	 */
	private static function render_link( array $link, string $class, string $label ): void {
		echo Helpers::link( $link, $class, esc_html( $label ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Helpers.
	}

	/**
	 * Fallback navigation for administrators before a menu is assigned.
	 *
	 * @param array<string,mixed> $args Menu args.
	 */
	public static function menu_fallback( array $args = array() ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$links = array( __( 'Shop', 'moon-core' ) => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) );

		$terms = taxonomy_exists( 'product_cat' ) ? get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 3 ) ) : array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$links[ $term->name ] = get_term_link( $term );
			}
		}

		echo '<ul class="' . esc_attr( $args['menu_class'] ?? 'moon-nav__list' ) . '">';
		foreach ( $links as $label => $url ) {
			printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( (string) $url ), esc_html( $label ) );
		}
		echo '</ul>';
	}

	/**
	 * Fallback footer menu for administrators.
	 *
	 * @param array<string,mixed> $args Menu args.
	 */
	public static function footer_fallback_menu( array $args = array() ): void {
		echo '<ul class="moon-footer__list">';
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( wc_get_page_permalink( 'shop' ) ), esc_html__( 'Shop', 'moon-core' ) );
		}
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( home_url( '/' ) ), esc_html__( 'Journal', 'moon-core' ) );
		echo '</ul>';
	}
}
