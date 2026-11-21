<?php
/**
 * Header builder: layout variants, ordered elements, top bar and mega menu.
 *
 * The markup keeps the class names the shipped stylesheet already targets, so
 * every layout variant is a class on the same structure and no existing style
 * breaks. The mega menu itself is the newer MOON 2.2 builder (inc/megamenu.php);
 * this class only places its trigger and panel.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site header.
 */
class Moon_Header {

	/**
	 * Hook registration.
	 */
	public static function init() {
		// Rendered by template-parts/header/site-header.php.
	}

	/**
	 * Ordered, enabled header elements.
	 *
	 * @return array Element => bool.
	 */
	public static function elements() {
		$raw      = (string) Moon_Settings::get( 'header_elements', 'search:1,account:1,wishlist:1,cart:1,theme:1,compare:1' );
		$elements = array();

		foreach ( explode( ',', $raw ) as $chunk ) {
			$parts = explode( ':', $chunk );

			if ( empty( $parts[0] ) ) {
				continue;
			}

			$elements[ sanitize_key( $parts[0] ) ] = ! empty( $parts[1] );
		}

		return $elements;
	}

	/**
	 * Whether one element is switched on.
	 *
	 * @param string $key Element key.
	 * @return bool
	 */
	public static function has( $key ) {
		$elements = self::elements();

		return ! empty( $elements[ $key ] );
	}

	/**
	 * Render the header.
	 */
	public static function render() {
		$layout      = (string) Moon_Settings::get( 'header_layout', 'glass' );
		$sticky      = (bool) Moon_Settings::get( 'header_sticky', '1' );
		$transparent = (bool) Moon_Settings::get( 'header_transparent' ) && is_front_page();
		$elements    = self::elements();

		$classes = array( 'moon-header', 'moon-header--' . sanitize_html_class( $layout ) );

		if ( ! $sticky ) {
			$classes[] = 'is-static';
		}

		if ( $transparent ) {
			$classes[] = 'is-transparent';
		}

		if ( Moon_Settings::get( 'header_hide_scroll' ) ) {
			$classes[] = 'is-hide-on-scroll';
		}

		if ( Moon_Settings::get( 'topbar_enabled' ) ) {
			self::topbar();
		}
		?>
		<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="moon-header" data-moon-header>
			<?php if ( 'standard' === $layout ) : ?>
				<div class="moon-header__bar">
			<?php else : ?>
				<div class="moon-header__pill moon-glass">
			<?php endif; ?>

				<?php if ( 'centered' === $layout ) : ?>
					<div class="moon-header__row">
						<span class="moon-header__spacer" aria-hidden="true"></span>
						<?php self::brand(); ?>
						<span class="moon-header__spacer" aria-hidden="true"></span>
					</div>
				<?php else : ?>
					<?php self::brand(); ?>
				<?php endif; ?>

				<?php if ( 'minimal' !== $layout ) : ?>
					<nav class="moon-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'moon-theme' ); ?>">
						<?php self::nav(); ?>
					</nav>
				<?php endif; ?>

				<div class="moon-actions">
					<?php
					foreach ( $elements as $key => $enabled ) {
						if ( $enabled ) {
							self::element( $key );
						}
					}
					?>

					<button type="button" class="moon-iconbtn moon-iconbtn--lg-hide" data-moon-open="menu" aria-label="<?php esc_attr_e( 'منو', 'moon-theme' ); ?>" aria-expanded="false">
						<?php moon_the_icon( 'menu', 18 ); ?>
					</button>
				</div>
			</div>

			<?php if ( 'centered' === $layout ) : ?>
				<div class="moon-header__secondary">
					<nav class="moon-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'moon-theme' ); ?>">
						<?php self::nav(); ?>
					</nav>
				</div>
			<?php endif; ?>

			<?php
			if ( function_exists( 'moon_mega_panel' ) ) {
				moon_mega_panel();
			}
			?>
		</header>
		<?php
	}

	/**
	 * Announcement bar above the header.
	 */
	private static function topbar() {
		$text = (string) Moon_Settings::get( 'topbar_text' );
		$url  = (string) Moon_Settings::get( 'topbar_url' );

		if ( '' === trim( $text ) ) {
			return;
		}
		?>
		<div class="moon-topbar">
			<div class="moon-container moon-topbar__inner">
				<?php if ( $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $text ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $text ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Brand / logo (desktop, mobile and light/dark variants from the panel).
	 */
	private static function brand() {
		$light  = (int) Moon_Settings::get( 'logo_light_id', '0' );
		$dark   = (int) Moon_Settings::get( 'logo_dark_id', '0' );
		$mobile = (int) Moon_Settings::get( 'logo_mobile_id', '0' );
		?>
		<a class="moon-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( $light && $dark ) : ?>
				<?php
				echo wp_get_attachment_image( $light, 'full', false, array( 'class' => 'moon-brand__img moon-brand__img--desktop moon-brand__img--light', 'alt' => get_bloginfo( 'name' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wp_get_attachment_image( $dark, 'full', false, array( 'class' => 'moon-brand__img moon-brand__img--desktop moon-brand__img--dark', 'alt' => get_bloginfo( 'name' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			<?php elseif ( $mobile ) : ?>
				<?php echo wp_get_attachment_image( $mobile, 'full', false, array( 'class' => 'moon-brand__img moon-brand__img--mobile', 'alt' => get_bloginfo( 'name' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php elseif ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php elseif ( function_exists( 'moon_core_logo' ) && moon_core_logo() ) : ?>
				<?php moon_core_logo(); ?>
			<?php else : ?>
				<?php moon_the_brand_mark( 34 ); ?>
				<span class="moon-brand__text<?php echo moon_is_arabic_script( moon_brand_text() ) ? ' is-fa' : ''; ?>"><?php echo esc_html( moon_brand_text() ); ?></span>
			<?php endif; ?>

			<?php if ( $mobile && ( $light || $dark ) ) : ?>
				<?php echo wp_get_attachment_image( $mobile, 'full', false, array( 'class' => 'moon-brand__img moon-brand__img--mobile', 'alt' => get_bloginfo( 'name' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</a>
		<?php
	}

	/**
	 * Primary menu with the theme fallback and the mega menu trigger.
	 */
	private static function nav() {
		if ( function_exists( 'moon_mega_header' ) ) {
			moon_mega_header();
		}

		if ( moon_has_primary_menu() ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'depth'          => 2,
				'fallback_cb'    => false,
			) );

			return;
		}
		?>
		<ul>
			<?php foreach ( moon_default_nav_items() as $item ) : ?>
				<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * One ordered header element.
	 *
	 * @param string $key Element key.
	 */
	private static function element( $key ) {
		switch ( $key ) {
			case 'search':
				?>
				<button type="button" class="moon-iconbtn moon-iconbtn--sm-hide" data-moon-open="search" aria-label="<?php esc_attr_e( 'جستجو', 'moon-theme' ); ?>" aria-expanded="false">
					<?php moon_the_icon( 'search', 17 ); ?>
				</button>
				<?php
				break;

			case 'account':
				if ( ! moon_is_woo() ) {
					break;
				}
				?>
				<a class="moon-iconbtn moon-iconbtn--sm-hide" href="<?php echo esc_url( moon_account_url() ); ?>" aria-label="<?php esc_attr_e( 'حساب کاربری', 'moon-theme' ); ?>">
					<?php moon_the_icon( 'user', 17 ); ?>
				</a>
				<?php
				break;

			case 'wishlist':
				?>
				<button type="button" class="moon-iconbtn moon-iconbtn--sm-hide" data-moon-open="wishlist" aria-label="<?php esc_attr_e( 'علاقه‌مندی‌ها', 'moon-theme' ); ?>" aria-expanded="false">
					<?php moon_the_icon( 'heart', 17 ); ?>
					<span class="moon-wish-count" data-moon-wish-count hidden></span>
				</button>
				<?php
				break;

			case 'compare':
				if ( ! class_exists( 'Moon_Compare' ) ) {
					break;
				}
				?>
				<a class="moon-iconbtn moon-iconbtn--sm-hide" href="<?php echo esc_url( Moon_Compare::page_url() ); ?>" aria-label="<?php esc_attr_e( 'مقایسه محصولات', 'moon-theme' ); ?>" data-moon-compare-link>
					<?php moon_the_icon( 'compare', 17 ); ?>
					<span class="moon-compare-count" data-moon-compare-count hidden></span>
				</a>
				<?php
				break;

			case 'cart':
				if ( ! moon_is_woo() ) {
					break;
				}

				$count = moon_cart_count();
				?>
				<a class="moon-iconbtn moon-cart-toggle" href="<?php echo esc_url( moon_cart_url() ); ?>" data-moon-open="cart" aria-controls="moon-cart" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( __( 'سبد خرید، %s کالا', 'moon-theme' ), number_format_i18n( $count ) ) ); ?>">
					<?php moon_the_icon( 'shopping-bag', 17 ); ?>
					<span class="moon-cart-count<?php echo $count > 0 ? ' has-count' : ''; ?>" data-count="<?php echo esc_attr( (string) $count ); ?>"><?php echo $count > 0 ? esc_html( moon_fa_digits( (string) $count ) ) : ''; ?></span>
				</a>
				<?php
				break;

			case 'theme':
				?>
				<button type="button" class="moon-switch" data-moon-theme aria-pressed="false" aria-label="<?php esc_attr_e( 'تغییر حالت روشن و تاریک', 'moon-theme' ); ?>">
					<span class="moon-switch__knob">
						<?php moon_the_icon( 'sun', 13, 'moon-icon--sun' ); ?>
						<?php moon_the_icon( 'moon', 13, 'moon-icon--moon' ); ?>
					</span>
				</button>
				<?php
				break;

			case 'cta':
				$text = (string) Moon_Settings::get( 'header_cta_text' );
				$url  = (string) Moon_Settings::get( 'header_cta_url' );

				if ( '' !== trim( $text ) ) {
					?>
					<a class="moon-btn moon-btn--dark moon-header__cta moon-iconbtn--sm-hide" href="<?php echo esc_url( $url ? $url : moon_shop_url() ); ?>"><?php echo esc_html( $text ); ?></a>
					<?php
				}
				break;

			case 'social':
				$networks = class_exists( 'Moon_Footer' ) ? Moon_Footer::social_links() : array();

				if ( $networks ) {
					?>
					<span class="moon-header__social moon-iconbtn--sm-hide">
						<?php foreach ( $networks as $network ) : ?>
							<a href="<?php echo esc_url( $network['url'] ); ?>" aria-label="<?php echo esc_attr( $network['label'] ); ?>" target="_blank" rel="noopener">
								<?php moon_the_icon( $network['icon'], 15 ); ?>
							</a>
						<?php endforeach; ?>
					</span>
					<?php
				}
				break;
		}
	}
}
