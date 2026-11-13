<?php
/**
 * AJAX endpoints: quick view, live product search, newsletter signup.
 *
 * Every endpoint verifies a nonce, validates and sanitises input, and returns
 * escaped, ready-to-render markup or a JSON error. Nothing here executes user
 * input, writes SQL directly or exposes customer data.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Ajax {

	public const NONCE_QUICK_VIEW  = 'moon_core_quick_view';
	public const NONCE_SEARCH      = 'moon_core_search';
	public const NONCE_NEWSLETTER  = 'moon_core_newsletter';
	public const NONCE_EXPORT      = 'moon_core_export_subscribers';

	public function init(): void {
		add_action( 'wp_ajax_moon_core_quick_view', array( $this, 'quick_view' ) );
		add_action( 'wp_ajax_nopriv_moon_core_quick_view', array( $this, 'quick_view' ) );

		add_action( 'wp_ajax_moon_core_search', array( $this, 'search' ) );
		add_action( 'wp_ajax_nopriv_moon_core_search', array( $this, 'search' ) );

		add_action( 'wp_ajax_moon_core_newsletter', array( $this, 'newsletter' ) );
		add_action( 'wp_ajax_nopriv_moon_core_newsletter', array( $this, 'newsletter' ) );
	}

	/**
	 * Product quick view: returns the store's own single-product summary markup.
	 */
	public function quick_view(): void {
		$this->verify( self::NONCE_QUICK_VIEW );

		if ( ! class_exists( 'WooCommerce' ) || Settings::get( 'quick_view' ) !== 'yes' ) {
			wp_send_json_error( array( 'message' => __( 'Quick view is unavailable.', 'moon-core' ) ), 400 );
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$moon_product = $product_id ? wc_get_product( $product_id ) : null;

		if ( ! $moon_product instanceof \WC_Product || ! $moon_product->is_visible() ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'moon-core' ) ), 404 );
		}

		// WooCommerce template functions read the global product.
		$GLOBALS['product'] = $moon_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- template context.

		ob_start();
		?>
		<div class="moon-quick-view__grid">
			<div class="moon-quick-view__media">
				<?php echo wp_kses_post( $moon_product->get_image( 'moon-product', array( 'loading' => 'eager' ) ) ); ?>
			</div>
			<div class="moon-quick-view__summary">
				<p class="moon-eyebrow"><?php echo esc_html( wp_strip_all_tags( wc_get_product_category_list( $moon_product->get_id() ) ) ); ?></p>
				<h2 class="moon-quick-view__title"><?php echo esc_html( $moon_product->get_name() ); ?></h2>
				<div class="moon-quick-view__price"><?php echo wp_kses_post( $moon_product->get_price_html() ); ?></div>
				<?php if ( $moon_product->get_average_rating() > 0 ) : ?>
					<div class="moon-quick-view__rating"><?php echo wp_kses_post( wc_get_rating_html( (float) $moon_product->get_average_rating() ) ); ?></div>
				<?php endif; ?>
				<div class="moon-quick-view__desc"><?php echo wp_kses_post( wpautop( $moon_product->get_short_description() ) ); ?></div>
				<div class="moon-quick-view__cart">
					<?php
					/**
					 * WooCommerce renders its own add-to-cart form, including the
					 * variation selector for variable products.
					 */
					woocommerce_template_single_add_to_cart();
					?>
				</div>
				<?php if ( $moon_product->is_in_stock() ) : ?>
					<p class="moon-quick-view__stock"><?php echo esc_html( $moon_product->get_stock_quantity() ? sprintf( /* translators: %s: stock count */ __( '%s in stock', 'moon-core' ), number_format_i18n( (int) $moon_product->get_stock_quantity() ) ) : __( 'In stock', 'moon-core' ) ); ?></p>
				<?php else : ?>
					<p class="moon-quick-view__stock is-out"><?php esc_html_e( 'Currently sold out', 'moon-core' ); ?></p>
				<?php endif; ?>
				<a class="moon-link-underline" href="<?php echo esc_url( get_permalink( $moon_product->get_id() ) ); ?>"><?php esc_html_e( 'View full details', 'moon-core' ); ?></a>
			</div>
		</div>
		<?php
		$html = (string) ob_get_clean();

		wp_send_json_success(
			array(
				'html'  => $html,
				'title' => $moon_product->get_name(),
			)
		);
	}

	/**
	 * Live product search for the header overlay.
	 */
	public function search(): void {
		$this->verify( self::NONCE_SEARCH );

		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Search is unavailable.', 'moon-core' ) ), 400 );
		}

		$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

		if ( strlen( $term ) < 2 ) {
			wp_send_json_success( array( 'html' => '' ) );
		}

		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 6,
				's'       => $term,
				'orderby' => 'relevance',
			)
		);

		ob_start();
		if ( empty( $products ) ) {
			echo '<p class="moon-search-results__empty">' . esc_html__( 'No pieces match that search.', 'moon-core' ) . '</p>';
		} else {
			echo '<ul class="moon-search-results__list">';
			foreach ( $products as $moon_product ) {
				if ( ! $moon_product instanceof \WC_Product ) {
					continue;
				}
				printf(
					'<li><a class="moon-search-results__item" href="%1$s"><span class="moon-search-results__thumb">%2$s</span><span class="moon-search-results__text"><strong>%3$s</strong><em>%4$s</em></span></a></li>',
					esc_url( get_permalink( $moon_product->get_id() ) ),
					wp_kses_post( $moon_product->get_image( 'thumbnail', array( 'loading' => 'lazy' ) ) ),
					esc_html( $moon_product->get_name() ),
					wp_kses_post( $moon_product->get_price_html() )
				);
			}
			echo '</ul>';
			printf(
				'<a class="moon-search-results__all moon-link-underline" href="%1$s">%2$s</a>',
				esc_url( add_query_arg( array( 's' => $term, 'post_type' => 'product' ), home_url( '/' ) ) ),
				esc_html__( 'See all results', 'moon-core' )
			);
		}
		$html = (string) ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Newsletter signup. Stored locally as a private subscriber entry, or proxied
	 * to the configured provider endpoint. Never pretends to succeed.
	 */
	public function newsletter(): void {
		$this->verify( self::NONCE_NEWSLETTER );

		// Honeypot: bots fill hidden fields.
		if ( ! empty( $_POST['moon_company'] ) ) {
			wp_send_json_success( array( 'message' => __( 'Thank you.', 'moon-core' ) ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'moon-core' ) ), 400 );
		}

		if ( empty( $_POST['consent'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Please accept the privacy note to subscribe.', 'moon-core' ) ), 400 );
		}

		$rate_key = 'moon_news_' . md5( $this->client_key() );
		if ( get_transient( $rate_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Please wait a moment before trying again.', 'moon-core' ) ), 429 );
		}
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS );

		if ( Settings::get( 'newsletter' ) === 'remote' ) {
			$endpoint = (string) Settings::get( 'newsletter_action' );

			if ( ! $endpoint ) {
				wp_send_json_error( array( 'message' => __( 'Newsletter signup is not configured yet.', 'moon-core' ) ), 400 );
			}

			$response = wp_remote_post(
				$endpoint,
				array(
					'timeout'    => 10,
					'user-agent' => 'MoonCore/' . MOON_CORE_VERSION,
					'body'       => array(
						'email' => $email,
						'source' => esc_url_raw( wp_get_referer() ?: home_url( '/' ) ),
					),
				)
			);

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) >= 300 ) {
				wp_send_json_error( array( 'message' => __( 'We could not reach the newsletter service. Please try again later.', 'moon-core' ) ), 502 );
			}

			wp_send_json_success( array( 'message' => __( 'You are on the list. Welcome to MOON.', 'moon-core' ) ) );
		}

		$stored = Subscribers::add( $email, esc_url_raw( wp_get_referer() ?: home_url( '/' ) ) );

		if ( is_wp_error( $stored ) ) {
			wp_send_json_error( array( 'message' => $stored->get_error_message() ), 400 );
		}

		wp_send_json_success( array( 'message' => __( 'You are on the list. Welcome to MOON.', 'moon-core' ) ) );
	}

	/**
	 * Nonce verification with a JSON failure.
	 *
	 * @param string $action Nonce action.
	 */
	private function verify( string $action ): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please reload the page.', 'moon-core' ) ), 403 );
		}
	}

	/**
	 * Pseudonymous client key used for simple rate limiting.
	 */
	private function client_key(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

		return $ip . '|' . ( wp_get_session_token() ?: '' );
	}
}
