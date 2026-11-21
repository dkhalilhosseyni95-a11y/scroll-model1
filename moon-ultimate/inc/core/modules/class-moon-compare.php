<?php
/**
 * Product comparison.
 *
 * A visitor's list lives in localStorage so it works without an account; the
 * same list is mirrored to user meta for signed-in customers, which keeps the
 * comparison in sync between devices. The table itself is rendered on the
 * server (`Moon_Compare::table()`) so prices, stock and attributes always come
 * from WooCommerce rather than from cached markup in the browser.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Comparison list and table.
 */
class Moon_Compare {

	/**
	 * Products a comparison may hold.
	 */
	const LIMIT = 4;

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'wp_ajax_moon_compare_render', array( __CLASS__, 'ajax_render' ) );
		add_action( 'wp_ajax_nopriv_moon_compare_render', array( __CLASS__, 'ajax_render' ) );

		add_action( 'wp_ajax_moon_compare_sync', array( __CLASS__, 'ajax_sync' ) );

		add_shortcode( 'moon_compare', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Comparison page URL.
	 *
	 * @return string
	 */
	public static function page_url() {
		$page = get_page_by_path( 'compare' );

		if ( $page ) {
			return (string) get_permalink( $page );
		}

		return (string) apply_filters( 'moon_compare_page_url', home_url( '/compare/' ) );
	}

	/**
	 * Ensure the comparison page exists (called from the theme activation).
	 */
	public static function ensure_page() {
		if ( get_page_by_path( 'compare' ) ) {
			return;
		}

		$page_id = wp_insert_post( array(
			'post_title'   => __( 'مقایسه محصولات', 'moon-theme' ),
			'post_name'    => 'compare',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[moon_compare]',
		) );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-compare.php' );
		}
	}

	/**
	 * Sanitise a list of product ids.
	 *
	 * @param mixed $ids Raw ids.
	 * @return int[]
	 */
	public static function clean_ids( $ids ) {
		if ( is_string( $ids ) ) {
			$ids = explode( ',', $ids );
		}

		if ( ! is_array( $ids ) ) {
			return array();
		}

		$clean = array();

		foreach ( $ids as $id ) {
			$id = absint( $id );

			if ( $id && 'product' === get_post_type( $id ) && ! isset( $clean[ $id ] ) ) {
				$clean[ $id ] = $id;
			}
		}

		return array_slice( array_values( $clean ), 0, self::LIMIT );
	}

	/**
	 * Rows used in the comparison table.
	 *
	 * @return array
	 */
	public static function rows() {
		return apply_filters( 'moon_compare_rows', array(
			'image'       => __( 'تصویر', 'moon-theme' ),
			'price'       => __( 'قیمت', 'moon-theme' ),
			'stock'       => __( 'موجودی', 'moon-theme' ),
			'rating'      => __( 'امتیاز', 'moon-theme' ),
			'sku'         => __( 'شناسه (SKU)', 'moon-theme' ),
			'attributes'  => __( 'ویژگی‌ها', 'moon-theme' ),
			'description' => __( 'توضیح کوتاه', 'moon-theme' ),
			'actions'     => __( 'خرید', 'moon-theme' ),
		) );
	}

	/**
	 * Render the comparison table.
	 *
	 * @param array $ids Product ids.
	 * @return string
	 */
	public static function table( $ids ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}

		$ids      = self::clean_ids( $ids );
		$products = array();

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );

			if ( $product ) {
				$products[ $id ] = $product;
			}
		}

		if ( count( $products ) < 1 ) {
			ob_start();
			?>
			<div class="moon-compare__empty">
				<p><?php esc_html_e( 'هنوز محصولی برای مقایسه اضافه نکرده‌اید. از دکمه مقایسه روی کارت محصول استفاده کنید.', 'moon-theme' ); ?></p>
				<a class="moon-btn moon-btn--dark" href="<?php echo esc_url( moon_shop_url() ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'moon-theme' ); ?></a>
			</div>
			<?php
			return (string) ob_get_clean();
		}

		$rows = self::rows();

		ob_start();
		?>
		<div class="moon-compare" data-moon-compare-table>
			<table class="moon-compare__table">
				<caption class="screen-reader-text"><?php esc_html_e( 'جدول مقایسه محصولات', 'moon-theme' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'مشخصات', 'moon-theme' ); ?></th>
						<?php foreach ( $products as $product ) : ?>
							<th scope="col" class="moon-compare__product">
								<button type="button" class="moon-compare__remove" data-moon-compare-remove="<?php echo esc_attr( (string) $product->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'حذف %s از مقایسه', 'moon-theme' ), $product->get_name() ) ); ?>">×</button>
								<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
									<?php echo $product->get_image( 'moon-thumb' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php echo esc_html( $product->get_name() ); ?></span>
								</a>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $key => $label ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $label ); ?></th>
							<?php foreach ( $products as $product ) : ?>
								<td><?php self::cell( $key, $product ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * One table cell.
	 *
	 * @param string     $key     Row key.
	 * @param WC_Product $product Product.
	 */
	private static function cell( $key, $product ) {
		switch ( $key ) {
			case 'image':
				echo $product->get_image( 'moon-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;

			case 'price':
				echo wp_kses_post( $product->get_price_html() );
				break;

			case 'stock':
				if ( $product->is_in_stock() ) {
					$qty = $product->managing_stock() ? $product->get_stock_quantity() : 0;

					echo '<span class="moon-stock--in">' . esc_html__( 'موجود', 'moon-theme' ) . '</span>';

					if ( $qty ) {
						echo ' <small>(' . esc_html( sprintf( __( '%s عدد', 'moon-theme' ), number_format_i18n( (int) $qty ) ) ) . ')</small>';
					}
				} else {
					echo '<span class="moon-stock--out">' . esc_html__( 'ناموجود', 'moon-theme' ) . '</span>';
				}
				break;

			case 'rating':
				$rating = (float) $product->get_average_rating();

				if ( $rating > 0 ) {
					echo '<span class="moon-stars" style="--rating:' . esc_attr( (string) ( $rating / 5 * 100 ) ) . '%" aria-hidden="true"></span>';
					echo '<span class="screen-reader-text">' . esc_html( sprintf( __( 'امتیاز %s از ۵', 'moon-theme' ), number_format_i18n( $rating, 1 ) ) ) . '</span>';
				} else {
					echo '—';
				}
				break;

			case 'sku':
				$sku = $product->get_sku();
				echo $sku ? esc_html( $sku ) : '—';
				break;

			case 'attributes':
				$attributes = $product->get_attributes();
				$lines      = array();

				foreach ( $attributes as $attribute ) {
					if ( ! $attribute instanceof WC_Product_Attribute ) {
						continue;
					}

					$name   = wc_attribute_label( $attribute->get_name() );
					$values = $attribute->is_taxonomy()
						? wp_list_pluck( wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) ), 'name' )
						: $attribute->get_options();

					if ( $values ) {
						$lines[] = '<strong>' . esc_html( $name ) . ':</strong> ' . esc_html( implode( '، ', $values ) );
					}
				}

				echo $lines ? wp_kses_post( implode( '<br>', $lines ) ) : '—';
				break;

			case 'description':
				echo esc_html( wp_trim_words( $product->get_short_description() ? $product->get_short_description() : $product->get_description(), 24 ) );
				break;

			case 'actions':
				echo '<a class="moon-btn moon-btn--dark" href="' . esc_url( get_permalink( $product->get_id() ) ) . '">' . esc_html__( 'مشاهده محصول', 'moon-theme' ) . '</a>';
				break;
		}
	}

	/**
	 * Shortcode wrapper.
	 *
	 * @return string
	 */
	public static function shortcode() {
		$saved = is_user_logged_in() ? get_user_meta( get_current_user_id(), 'moon_compare', true ) : array();

		ob_start();
		?>
		<div class="moon-compare-page" data-moon-compare-page data-saved="<?php echo esc_attr( implode( ',', self::clean_ids( $saved ) ) ); ?>">
			<div data-moon-compare-output>
				<?php
				if ( $saved ) {
					echo self::table( $saved ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
			<p class="moon-compare__hint" data-moon-compare-hint<?php echo $saved ? ' hidden' : ''; ?>>
				<?php esc_html_e( 'برای افزودن محصول، از دکمه مقایسه (↺) روی کارت محصول استفاده کنید.', 'moon-theme' ); ?>
			</p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * AJAX: render the table for a list of ids.
	 */
	public static function ajax_render() {
		check_ajax_referer( 'moon_shop', 'nonce' );

		$ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised by clean_ids().

		wp_send_json_success( array(
			'html'  => self::table( is_array( $ids ) ? $ids : explode( ',', (string) $ids ) ),
			'count' => count( self::clean_ids( is_array( $ids ) ? $ids : explode( ',', (string) $ids ) ) ),
		) );
	}

	/**
	 * AJAX: mirror the guest list into the signed-in customer's account.
	 */
	public static function ajax_sync() {
		check_ajax_referer( 'moon_shop', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'برای همگام‌سازی وارد شوید.', 'moon-theme' ) ), 401 );
		}

		$ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised by clean_ids().

		update_user_meta( get_current_user_id(), 'moon_compare', self::clean_ids( is_array( $ids ) ? $ids : explode( ',', (string) $ids ) ) );

		wp_send_json_success();
	}
}
