<?php
/**
 * Real store statistics for the panel dashboard.
 *
 * Everything is read from WordPress/WooCommerce: no sample numbers. Queries are
 * capped (a dashboard never needs every row) and results are cached for five
 * minutes in a transient, so opening the panel does not slow the shop.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard data.
 */
class Moon_Stats {

	/**
	 * Cache key.
	 */
	const CACHE = 'moon_dashboard_stats';

	/**
	 * Cached dashboard payload.
	 *
	 * @param bool $refresh Force a fresh read.
	 * @return array
	 */
	public static function all( $refresh = false ) {
		$cached = get_transient( self::CACHE );

		if ( ! $refresh && is_array( $cached ) ) {
			return $cached;
		}

		$data = array(
			'products'    => self::product_counts(),
			'orders'      => self::order_counts(),
			'sales'       => self::sales(),
			'customers'   => self::customers(),
			'top'         => self::top_products(),
			'latest'      => self::latest_orders(),
			'chart'       => self::chart(),
			'status'      => self::status(),
			'warnings'    => self::warnings(),
		);

		set_transient( self::CACHE, $data, 5 * MINUTE_IN_SECONDS );

		return $data;
	}

	/**
	 * Drop the cache (called after an import or a settings save).
	 */
	public static function flush() {
		delete_transient( self::CACHE );
	}

	/**
	 * Product totals.
	 *
	 * @return array
	 */
	public static function product_counts() {
		$counts = (array) wp_count_posts( 'product' );
		$publish = isset( $counts['publish'] ) ? (int) $counts['publish'] : 0;
		$draft   = ( isset( $counts['draft'] ) ? (int) $counts['draft'] : 0 ) + ( isset( $counts['pending'] ) ? (int) $counts['pending'] : 0 );
		$out     = 0;

		if ( post_type_exists( 'product' ) ) {
			global $wpdb;

			$out = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = '_stock_status' AND pm.meta_value = %s AND p.post_status = 'publish' AND p.post_type = 'product'",
				'outofstock'
			) );
		}

		return array(
			'published' => $publish,
			'draft'     => $draft,
			'out'       => $out,
			'on_sale'   => self::count_on_sale(),
		);
	}

	/**
	 * Number of published products currently on sale.
	 *
	 * @return int
	 */
	private static function count_on_sale() {
		if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
			return 0;
		}

		return count( wc_get_product_ids_on_sale() );
	}

	/**
	 * Order counts by status.
	 *
	 * @return array
	 */
	public static function order_counts() {
		$empty = array( 'total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0 );

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return $empty;
		}

		$count = function ( $status ) {
			$result = wc_get_orders( array(
				'limit'    => 1,
				'paginate' => true,
				'status'   => $status,
				'return'   => 'ids',
			) );

			return isset( $result->total ) ? (int) $result->total : 0;
		};

		return array(
			'total'      => $count( array_keys( wc_get_order_statuses() ) ),
			'pending'    => $count( 'pending' ),
			'processing' => $count( 'processing' ),
			'completed'  => $count( 'completed' ),
		);
	}

	/**
	 * Sales totals: all time, today, this month.
	 *
	 * @return array
	 */
	public static function sales() {
		$empty = array( 'total' => 0.0, 'today' => 0.0, 'month' => 0.0, 'currency' => '' );

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return $empty;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'wc_order_stats';
		$paid  = array_map( 'esc_sql', (array) wc_get_is_paid_statuses() );
		$list  = "'" . implode( "','", array_map( function ( $status ) {
			return 'wc-' . $status;
		}, $paid ) ) . "'";

		$sum = null;

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$sum = array(
				'total' => (float) $wpdb->get_var( "SELECT COALESCE(SUM(total_sales),0) FROM {$table} WHERE status IN ({$list})" ), // phpcs:ignore WordPress.DB.PreparedSQL
				'today' => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(total_sales),0) FROM {$table} WHERE status IN ({$list}) AND DATE(date_created) = %s", gmdate( 'Y-m-d' ) ) ), // phpcs:ignore WordPress.DB.PreparedSQL
				'month' => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(total_sales),0) FROM {$table} WHERE status IN ({$list}) AND YEAR(date_created) = %d AND MONTH(date_created) = %d", (int) gmdate( 'Y' ), (int) gmdate( 'n' ) ) ), // phpcs:ignore WordPress.DB.PreparedSQL
			);
		}

		if ( null === $sum ) {
			// Analytics table missing (older WooCommerce): read the orders directly.
			$orders = wc_get_orders( array(
				'limit'   => 200,
				'status'  => $paid,
				'orderby' => 'date',
				'order'   => 'DESC',
			) );

			$sum = array( 'total' => 0.0, 'today' => 0.0, 'month' => 0.0 );

			foreach ( $orders as $order ) {
				$total   = (float) $order->get_total();
				$created = $order->get_date_created();

				$sum['total'] += $total;

				if ( $created && $created->format( 'Y-m-d' ) === gmdate( 'Y-m-d' ) ) {
					$sum['today'] += $total;
				}

				if ( $created && $created->format( 'Y-m' ) === gmdate( 'Y-m' ) ) {
					$sum['month'] += $total;
				}
			}
		}

		$sum['currency'] = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';

		return $sum;
	}

	/**
	 * Customer count.
	 *
	 * @return array
	 */
	public static function customers() {
		$counts = count_users();

		return array(
			'total'  => isset( $counts['total_users'] ) ? (int) $counts['total_users'] : 0,
			'by_role' => isset( $counts['avail_roles'] ) ? $counts['avail_roles'] : array(),
		);
	}

	/**
	 * Best selling products.
	 *
	 * @return array
	 */
	public static function top_products() {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$products = wc_get_products( array(
			'limit'   => 5,
			'status'  => 'publish',
			'orderby' => 'meta_value_num',
			'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
			'order'   => 'DESC',
		) );

		$rows = array();

		foreach ( $products as $product ) {
			$rows[] = array(
				'name'  => $product->get_name(),
				'url'   => get_edit_post_link( $product->get_id() ),
				'sales' => (int) $product->get_total_sales(),
				'price' => $product->get_price_html(),
				'thumb' => $product->get_image( 'thumbnail' ),
			);
		}

		return $rows;
	}

	/**
	 * Latest orders.
	 *
	 * @return array
	 */
	public static function latest_orders() {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return array();
		}

		$orders = wc_get_orders( array(
			'limit'   => 6,
			'orderby' => 'date',
			'order'   => 'DESC',
		) );

		$rows = array();

		foreach ( $orders as $order ) {
			$rows[] = array(
				'id'     => $order->get_id(),
				'number' => $order->get_order_number(),
				'url'    => $order->get_edit_order_url(),
				'status' => wc_get_order_status_name( $order->get_status() ),
				'total'  => $order->get_formatted_order_total(),
				'date'   => $order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y/m/d H:i' ) : '',
				'name'   => trim( $order->get_formatted_billing_full_name() ),
			);
		}

		return $rows;
	}

	/**
	 * Daily paid sales for the last 14 days.
	 *
	 * @return array
	 */
	public static function chart() {
		$days  = array();
		$start = strtotime( '-13 days', strtotime( gmdate( 'Y-m-d' ) ) );

		for ( $i = 0; $i < 14; $i++ ) {
			$key          = gmdate( 'Y-m-d', $start + $i * DAY_IN_SECONDS );
			$days[ $key ] = 0.0;
		}

		if ( function_exists( 'wc_get_orders' ) ) {
			global $wpdb;

			$table = $wpdb->prefix . 'wc_order_stats';
			$paid  = array_map( 'esc_sql', (array) wc_get_is_paid_statuses() );
			$list  = "'" . implode( "','", array_map( function ( $status ) {
				return 'wc-' . $status;
			}, $paid ) ) . "'";

			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$rows = $wpdb->get_results( $wpdb->prepare(
					"SELECT DATE(date_created) AS day, SUM(total_sales) AS total
					 FROM {$table}
					 WHERE status IN ({$list}) AND date_created >= %s
					 GROUP BY DATE(date_created)",
					gmdate( 'Y-m-d', $start )
				), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL

				foreach ( (array) $rows as $row ) {
					if ( isset( $days[ $row['day'] ] ) ) {
						$days[ $row['day'] ] = (float) $row['total'];
					}
				}
			}
		}

		$max = max( $days ? $days : array( 0 ) );

		return array(
			'days' => $days,
			'max'  => $max > 0 ? $max : 1,
		);
	}

	/**
	 * Environment status rows.
	 *
	 * @return array
	 */
	public static function status() {
		global $wp_version;

		$theme   = wp_get_theme();
		$hpos    = 'no';

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			$hpos = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 'yes' : 'no';
		}

		return array(
			'wordpress' => array(
				'label' => __( 'وردپرس', 'moon-theme' ),
				'value' => $wp_version,
				'ok'    => version_compare( $wp_version, '6.4', '>=' ),
			),
			'woocommerce' => array(
				'label' => __( 'ووکامرس', 'moon-theme' ),
				'value' => defined( 'WC_VERSION' ) ? WC_VERSION : __( 'غیرفعال', 'moon-theme' ),
				'ok'    => defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '8.0', '>=' ),
			),
			'php' => array(
				'label' => __( 'نسخه PHP', 'moon-theme' ),
				'value' => PHP_VERSION,
				'ok'    => version_compare( PHP_VERSION, '7.4', '>=' ),
			),
			'theme' => array(
				'label' => __( 'قالب MOON', 'moon-theme' ),
				'value' => $theme->get( 'Version' ),
				'ok'    => true,
			),
			'hpos' => array(
				'label' => __( 'جداول سفارش HPOS', 'moon-theme' ),
				'value' => 'yes' === $hpos ? __( 'فعال', 'moon-theme' ) : __( 'غیرفعال', 'moon-theme' ),
				'ok'    => true,
			),
			'permalinks' => array(
				'label' => __( 'پیوندهای یکتا', 'moon-theme' ),
				'value' => '' !== get_option( 'permalink_structure' ) ? __( 'فعال', 'moon-theme' ) : __( 'ساده', 'moon-theme' ),
				'ok'    => '' !== get_option( 'permalink_structure' ),
			),
			'https' => array(
				'label' => __( 'HTTPS', 'moon-theme' ),
				'value' => is_ssl() ? __( 'فعال', 'moon-theme' ) : __( 'غیرفعال', 'moon-theme' ),
				'ok'    => is_ssl(),
			),
			'memory' => array(
				'label' => __( 'حافظه PHP', 'moon-theme' ),
				'value' => (string) ini_get( 'memory_limit' ),
				'ok'    => wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) ) >= 134217728,
			),
		);
	}

	/**
	 * Actionable warnings.
	 *
	 * @return array
	 */
	public static function warnings() {
		$warnings = array();

		if ( ! function_exists( 'wc_get_orders' ) ) {
			$warnings[] = __( 'ووکامرس فعال نیست؛ فروشگاه در دسترس کاربران نیست.', 'moon-theme' );
		} else {
			// Ask WooCommerce itself which gateways are enabled, so a gateway
			// registered by a plugin is counted too.
			$enabled  = 0;
			$registry = function_exists( 'WC' ) && WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();

			foreach ( (array) $registry as $gateway ) {
				if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
					$enabled++;
				}
			}

			if ( 0 === $enabled ) {
				$warnings[] = __( 'هیچ روش پرداختی فعال نیست؛ مشتری نمی‌تواند سفارش را نهایی کند.', 'moon-theme' );
			}

			if ( function_exists( 'WC_Shipping_Zones' ) ) {
				$methods = 0;

				foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
					$methods += count( isset( $zone['shipping_methods'] ) ? $zone['shipping_methods'] : array() );
				}

				if ( 0 === $methods ) {
					$warnings[] = __( 'هیچ روش ارسالی تعریف نشده است.', 'moon-theme' );
				}
			}

			$counts = self::product_counts();

			if ( 0 === $counts['published'] ) {
				$warnings[] = __( 'هنوز محصولی منتشر نشده است.', 'moon-theme' );
			}
		}

		if ( '' === (string) Moon_Settings::get( 'contact_phone' ) ) {
			$warnings[] = __( 'شماره تماس فروشگاه در پنل ثبت نشده است (فوتر → تماس).', 'moon-theme' );
		}

		if ( '' === (string) Moon_Settings::get( 'social_instagram' ) && '' === (string) Moon_Settings::get( 'footer_instagram' ) ) {
			$warnings[] = __( 'لینک شبکه‌های اجتماعی ثبت نشده است.', 'moon-theme' );
		}

		if ( ! is_ssl() ) {
			$warnings[] = __( 'سایت روی HTTPS اجرا نمی‌شود؛ برای پرداخت و امنیت حساب، گواهی SSL لازم است.', 'moon-theme' );
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && 'production' === wp_get_environment_type() ) {
			$warnings[] = __( 'WP_DEBUG روی محیط عملیاتی فعال است؛ خطاها را در مرورگر نمایش ندهید.', 'moon-theme' );
		}

		return $warnings;
	}

	/**
	 * Format an amount for display.
	 *
	 * @param float $amount Amount.
	 * @return string
	 */
	public static function money( $amount ) {
		if ( function_exists( 'wc_price' ) ) {
			return wp_strip_all_tags( wc_price( (float) $amount ) );
		}

		return number_format_i18n( (float) $amount );
	}
}
