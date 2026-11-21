<?php
/**
 * Shop extras: sticky buy bar, free-shipping progress, recently viewed,
 * sale countdown and the cart cross-sell limit.
 *
 * Every feature here is opt-in from the panel and, when switched off, adds no
 * markup and no script to the page.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce extras.
 */
class Moon_Woo_Extras {

	/**
	 * Cookie holding the recently viewed product ids.
	 */
	const COOKIE = 'moon_recent';

	/**
	 * Hook registration.
	 */
	public static function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'wp', array( __CLASS__, 'track_recent' ) );

		add_action( 'woocommerce_after_single_product_summary', array( __CLASS__, 'sticky_bar' ), 15 );
		add_action( 'woocommerce_after_single_product_summary', array( __CLASS__, 'recently_viewed' ), 25 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'sale_countdown' ), 25 );

		add_action( 'woocommerce_before_mini_cart', array( __CLASS__, 'shipping_bar' ), 5 );
		add_action( 'woocommerce_before_cart_table', array( __CLASS__, 'shipping_bar' ), 5 );

		add_filter( 'woocommerce_cross_sells_total', array( __CLASS__, 'cross_sells_total' ) );
		add_filter( 'woocommerce_cross_sells_columns', array( __CLASS__, 'cross_sells_columns' ) );
	}

	/**
	 * Panel switch helper.
	 *
	 * @param string $key     Key.
	 * @param string $default Default.
	 * @return bool
	 */
	private static function on( $key, $default = '1' ) {
		return (bool) Moon_Settings::get( $key, $default );
	}

	/* ----------------------------------------------------------------------
	 * Free shipping progress
	 * -------------------------------------------------------------------- */

	/**
	 * Progress bar toward the free-shipping threshold.
	 */
	public static function shipping_bar() {
		$threshold = (int) Moon_Settings::get( 'free_ship_threshold', '2000000' );

		if ( ! $threshold || ! self::on( 'free_ship_bar' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$subtotal = (float) WC()->cart->get_displayed_subtotal();
		$percent  = min( 100, ( $subtotal / $threshold ) * 100 );
		$left     = $threshold - $subtotal;

		echo '<div class="moon-shipbar">';

		if ( $left > 0 ) {
			echo '<p class="moon-shipbar__text">';
			echo esc_html( sprintf(
				/* translators: %s: remaining amount. */
				__( '%s تا ارسال رایگان باقی مانده', 'moon-theme' ),
				wp_strip_all_tags( wc_price( $left ) )
			) );
			echo '</p>';
		} else {
			echo '<p class="moon-shipbar__text moon-shipbar__text--ok">' . esc_html__( 'ارسال این سفارش رایگان است 🎉', 'moon-theme' ) . '</p>';
		}

		echo '<span class="moon-shipbar__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . esc_attr( (string) round( $percent ) ) . '">';
		echo '<span class="moon-shipbar__fill" style="width:' . esc_attr( (string) round( $percent ) ) . '%"></span>';
		echo '</span>';

		echo '</div>';
	}

	/* ----------------------------------------------------------------------
	 * Sticky add to cart
	 * -------------------------------------------------------------------- */

	/**
	 * Mobile sticky buy bar on the product page.
	 */
	public static function sticky_bar() {
		global $product;

		if ( ! self::on( 'sticky_atc' ) || ! $product instanceof WC_Product || ! $product->is_purchasable() ) {
			return;
		}

		$aria = sprintf( __( 'افزودن %s به سبد خرید', 'moon-theme' ), $product->get_name() );
		?>
		<div class="moon-sticky-buy" data-moon-sticky-buy hidden>
			<div class="moon-sticky-buy__info">
				<strong><?php echo esc_html( wp_trim_words( $product->get_name(), 6 ) ); ?></strong>
				<span class="moon-sticky-buy__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<?php if ( $product->is_type( 'simple' ) && $product->is_in_stock() ) : ?>
				<button type="button" class="moon-btn moon-btn--dark" data-moon-add="<?php echo esc_attr( (string) $product->get_id() ); ?>" aria-label="<?php echo esc_attr( $aria ); ?>">
					<?php moon_the_icon( 'shopping-bag', 16 ); ?>
					<span><?php esc_html_e( 'افزودن به سبد', 'moon-theme' ); ?></span>
				</button>
			<?php else : ?>
				<a class="moon-btn moon-btn--dark" href="#moon-buy" data-moon-sticky-focus>
					<?php esc_html_e( 'انتخاب گزینه‌ها', 'moon-theme' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ----------------------------------------------------------------------
	 * Sale countdown
	 * -------------------------------------------------------------------- */

	/**
	 * Countdown to the end of the sale price.
	 */
	public static function sale_countdown() {
		global $product;

		if ( ! self::on( 'product_countdown', '0' ) || ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
			return;
		}

		$end = (int) get_post_meta( $product->get_id(), '_sale_price_dates_to', true );

		if ( ! $end || $end < time() ) {
			return;
		}
		?>
		<div class="moon-countdown moon-countdown--product" data-moon-countdown="<?php echo esc_attr( (string) $end ); ?>" role="timer" aria-live="off">
			<span class="moon-countdown__label"><?php esc_html_e( 'پایان تخفیف', 'moon-theme' ); ?></span>
			<span data-moon-cd="days">۰</span><small><?php esc_html_e( 'روز', 'moon-theme' ); ?></small>
			<span data-moon-cd="hours">۰</span><small><?php esc_html_e( 'ساعت', 'moon-theme' ); ?></small>
			<span data-moon-cd="minutes">۰</span><small><?php esc_html_e( 'دقیقه', 'moon-theme' ); ?></small>
			<span data-moon-cd="seconds">۰</span><small><?php esc_html_e( 'ثانیه', 'moon-theme' ); ?></small>
		</div>
		<?php
	}

	/* ----------------------------------------------------------------------
	 * Recently viewed
	 * -------------------------------------------------------------------- */

	/**
	 * Remember the current product for this visitor.
	 */
	public static function track_recent() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		$id   = (int) get_queried_object_id();
		$list = self::recent_ids();

		$list = array_values( array_diff( $list, array( $id ) ) );
		array_unshift( $list, $id );
		$list = array_slice( $list, 0, 12 );

		setcookie(
			self::COOKIE,
			implode( ',', $list ),
			array(
				'expires'  => time() + MONTH_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Recently viewed product ids.
	 *
	 * @return int[]
	 */
	public static function recent_ids() {
		$raw = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';

		if ( '' === $raw ) {
			return array();
		}

		return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
	}

	/**
	 * Recently viewed products below the related products.
	 */
	public static function recently_viewed() {
		global $product;

		if ( ! self::on( 'product_recent' ) || ! function_exists( 'wc_get_products' ) ) {
			return;
		}

		$exclude = $product instanceof WC_Product ? array( $product->get_id() ) : array();
		$ids     = array_values( array_diff( self::recent_ids(), $exclude ) );

		if ( count( $ids ) < 2 ) {
			return;
		}

		$products = wc_get_products( array(
			'include' => array_slice( $ids, 0, 4 ),
			'limit'   => 4,
			'status'  => 'publish',
		) );

		if ( empty( $products ) ) {
			return;
		}

		echo '<section class="moon-recent"><h2 class="moon-recent__title">' . esc_html__( 'اخیراً مشاهده کرده‌اید', 'moon-theme' ) . '</h2>';
		echo '<ul class="moon-recent__grid products">';

		foreach ( $products as $recent ) {
			echo '<li class="product">';
			wc_get_template_part( 'content', 'product' );
			echo '</li>';
		}

		echo '</ul></section>';
	}

	/* ----------------------------------------------------------------------
	 * Cart cross-sells
	 * -------------------------------------------------------------------- */

	/**
	 * Cross-sell count from the panel.
	 *
	 * @param int $total Default.
	 * @return int
	 */
	public static function cross_sells_total( $total ) {
		return self::on( 'cart_crosssells' ) ? 3 : 0;
	}

	/**
	 * Cross-sell columns.
	 *
	 * @param int $columns Default.
	 * @return int
	 */
	public static function cross_sells_columns( $columns ) {
		return 3;
	}
}
