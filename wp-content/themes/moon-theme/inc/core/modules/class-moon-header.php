<?php
/**
 * Header builder: layout variants, ordered elements, top bar and mega menu.
 *
 * The markup keeps the class names the shipped stylesheet already targets, so
 * every layout variant is a class on the same structure and no existing style
 * breaks.
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
		add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'mark_mega_items' ), 10, 2 );
		add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, 'mega_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( __CLASS__, 'save_mega_fields' ), 10, 2 );
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
		$layout    = (string) Moon_Settings::get( 'header_layout', 'glass' );
		$sticky    = (bool) Moon_Settings::get( 'header_sticky', '1' );
		$transparent = (bool) Moon_Settings::get( 'header_transparent' ) && is_front_page();
		$elements  = self::elements();

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
	 * Brand / logo.
	 */
	private static function brand() {
		?>
		<a class="moon-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php elseif ( function_exists( 'moon_core_logo' ) && moon_core_logo() ) : ?>
				<?php moon_core_logo(); ?>
			<?php else : ?>
				<span class="moon-brand__text<?php echo moon_is_arabic_script( moon_brand_text() ) ? ' is-fa' : ''; ?>"><?php echo esc_html( moon_brand_text() ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}

	/**
	 * Primary menu with the theme fallback.
	 */
	private static function nav() {
		if ( moon_has_primary_menu() ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'depth'          => 3,
				'fallback_cb'    => false,
				'walker'         => new Moon_Mega_Walker(),
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
					<?php moon_the_icon( 'rotate-ccw', 17 ); ?>
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

	/* ----------------------------------------------------------------------
	 * Mega menu
	 * -------------------------------------------------------------------- */

	/**
	 * Flag menu items that carry a mega panel.
	 *
	 * @param array    $items Menu items.
	 * @param stdClass $args  Args.
	 * @return array
	 */
	public static function mark_mega_items( $items, $args ) {
		foreach ( $items as $item ) {
			$item->moon_mega = (bool) get_post_meta( $item->ID, '_moon_mega', true );
			$item->moon_mega_image = (int) get_post_meta( $item->ID, '_moon_mega_image', true );
		}

		return $items;
	}

	/**
	 * Mega menu fields in the menu item editor.
	 *
	 * @param int    $item_id Item id.
	 * @param object $item    Item.
	 * @param int    $depth   Depth.
	 * @param object $args    Args.
	 */
	public static function mega_fields( $item_id, $item, $depth, $args ) {
		if ( 0 !== (int) $depth ) {
			return;
		}

		$mega  = (bool) get_post_meta( $item_id, '_moon_mega', true );
		$image = (int) get_post_meta( $item_id, '_moon_mega_image', true );
		?>
		<p class="moon-mega-field description-wide">
			<label for="moon-mega-<?php echo esc_attr( (string) $item_id ); ?>">
				<input type="checkbox" id="moon-mega-<?php echo esc_attr( (string) $item_id ); ?>" name="moon_mega[<?php echo esc_attr( (string) $item_id ); ?>]" value="1" <?php checked( $mega ); ?>>
				<?php esc_html_e( 'نمایش به‌صورت مگامنو (زیرمنوها در ستون‌ها)', 'moon-theme' ); ?>
			</label>
		</p>
		<p class="moon-mega-field description-wide">
			<label for="moon-mega-image-<?php echo esc_attr( (string) $item_id ); ?>"><?php esc_html_e( 'تصویر تبلیغاتی مگامنو (شناسه تصویر)', 'moon-theme' ); ?></label>
			<input type="number" min="0" class="widefat" id="moon-mega-image-<?php echo esc_attr( (string) $item_id ); ?>" name="moon_mega_image[<?php echo esc_attr( (string) $item_id ); ?>]" value="<?php echo esc_attr( (string) $image ); ?>">
		</p>
		<?php
	}

	/**
	 * Persist mega menu fields.
	 *
	 * @param int $menu_id Menu id.
	 * @param int $item_id Item id.
	 */
	public static function save_mega_fields( $menu_id, $item_id ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$mega = isset( $_POST['moon_mega'][ $item_id ] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification -- WordPress verifies the menu nonce before this hook.

		if ( $mega ) {
			update_post_meta( $item_id, '_moon_mega', 1 );
		} else {
			delete_post_meta( $item_id, '_moon_mega' );
		}

		$image = isset( $_POST['moon_mega_image'][ $item_id ] ) ? absint( wp_unslash( $_POST['moon_mega_image'][ $item_id ] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( $image ) {
			update_post_meta( $item_id, '_moon_mega_image', $image );
		} else {
			delete_post_meta( $item_id, '_moon_mega_image' );
		}
	}
}

/**
 * Walker that turns flagged items into mega panels.
 */
class Moon_Mega_Walker extends Walker_Nav_Menu {

	/**
	 * Start a submenu level.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$classes = 0 === $depth ? 'moon-submenu' : 'moon-submenu moon-submenu--deep';

		$output .= '<ul class="' . esc_attr( $classes ) . '">';
	}

	/**
	 * Start one item.
	 *
	 * @param string   $output Output.
	 * @param WP_Post  $item   Item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 * @param int      $id     Id.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		if ( ! empty( $item->moon_mega ) ) {
			$classes[] = 'moon-nav__item--mega';
		}

		$class_names = implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );
		$output     .= '<li class="' . esc_attr( $class_names ) . '">';

		$link = '<a href="' . esc_url( $item->url ) . '"' . ( ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '' ) . '>' . esc_html( $item->title ) . '</a>';

		if ( ! empty( $item->moon_mega ) ) {
			$image_id = isset( $item->moon_mega_image ) ? (int) $item->moon_mega_image : 0;

			$link .= $image_id
				? '<span class="moon-mega__promo">' . wp_get_attachment_image( $image_id, 'medium', false, array( 'loading' => 'lazy', 'alt' => $item->title ) ) . '</span>'
				: '';
		}

		$output .= $link;
	}
}
