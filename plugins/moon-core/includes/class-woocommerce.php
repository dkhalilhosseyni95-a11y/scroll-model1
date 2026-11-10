<?php
/**
 * WooCommerce integration: shop filters, badges, wishlist, cart fragments.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Everything the storefront needs from WooCommerce.
 */
class Moon_Core_WooCommerce {

	/**
	 * Cookie name for the guest wishlist.
	 */
	const WISHLIST_COOKIE = 'moon_wishlist';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'woocommerce_product_query', array( __CLASS__, 'apply_archive_filters' ) );
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'render_filter_bar' ), 15 );
		add_filter( 'woocommerce_sale_flash', array( __CLASS__, 'sale_flash' ), 10, 3 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'new_badge' ), 9 );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'size_guide_tab' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'cart_fragments' ) );

		add_action( 'wp_ajax_moon_filter_products', array( __CLASS__, 'ajax_filter_products' ) );
		add_action( 'wp_ajax_nopriv_moon_filter_products', array( __CLASS__, 'ajax_filter_products' ) );

		// Settings are read on `init`, never on `plugins_loaded`, so translations
		// are not loaded before WordPress allows them.
		add_action( 'init', array( __CLASS__, 'register_feature_hooks' ), 5 );
	}

	/**
	 * Register the optional storefront features that depend on settings.
	 */
	public static function register_feature_hooks() {
		if ( (int) moon_core_setting( 'enable_wishlist', 1 ) !== 1 || defined( 'YITH_WCWL' ) ) {
			return;
		}

		add_action( 'woocommerce_after_shop_loop_item', array( __CLASS__, 'loop_wishlist_button' ), 12 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_wishlist_button' ), 31 );
		add_action( 'wp_ajax_moon_toggle_wishlist', array( __CLASS__, 'ajax_toggle_wishlist' ) );
		add_action( 'wp_ajax_nopriv_moon_toggle_wishlist', array( __CLASS__, 'ajax_toggle_wishlist' ) );
	}

	/**
	 * Shortcodes used by the demo pages.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'moon_wishlist', array( __CLASS__, 'wishlist_shortcode' ) );
		add_shortcode( 'moon_filters', array( __CLASS__, 'filter_shortcode' ) );
	}

	/* ---------------------------------------------------------------------
	 * Filters
	 * ------------------------------------------------------------------ */

	/**
	 * Read and sanitize the filter parameters from the current request.
	 *
	 * @return array
	 */
	public static function read_filters() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filtering; values are sanitized below.
		// Archives submit a GET form, the AJAX endpoint posts the same field names.
		$source = $_POST ? $_POST : $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$filters = array(
			'min_price' => isset( $source['moon_min_price'] ) ? (float) wc_format_decimal( wp_unslash( $source['moon_min_price'] ) ) : 0,
			'max_price' => isset( $source['moon_max_price'] ) ? (float) wc_format_decimal( wp_unslash( $source['moon_max_price'] ) ) : 0,
			'size'      => self::sanitize_list( isset( $source['moon_size'] ) ? wp_unslash( $source['moon_size'] ) : array() ),
			'color'     => self::sanitize_list( isset( $source['moon_color'] ) ? wp_unslash( $source['moon_color'] ) : array() ),
			'fabric'    => self::sanitize_list( isset( $source['moon_fabric'] ) ? wp_unslash( $source['moon_fabric'] ) : array() ),
			'in_stock'  => ! empty( $source['moon_in_stock'] ),
			'orderby'   => isset( $source['orderby'] ) ? sanitize_key( wp_unslash( $source['orderby'] ) ) : '',
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $filters;
	}

	/**
	 * Sanitize a list of slugs from a request value.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	private static function sanitize_list( $value ) {
		$value = is_array( $value ) ? $value : array( $value );
		$out   = array();

		foreach ( $value as $item ) {
			$slug = sanitize_title( (string) $item );
			if ( '' !== $slug ) {
				$out[] = $slug;
			}
		}

		return array_slice( array_unique( $out ), 0, 12 );
	}

	/**
	 * Apply the URL filters to the shop main query.
	 *
	 * @param WP_Query $query Product query.
	 */
	public static function apply_archive_filters( $query ) {
		if ( is_admin() ) {
			return;
		}

		$filters = self::read_filters();

		if ( $filters['min_price'] > 0 || $filters['max_price'] > 0 ) {
			$existing   = (array) $query->get( 'meta_query' );
			$existing[] = self::price_meta_clause( $filters['min_price'], $filters['max_price'] );
			$query->set( 'meta_query', $existing );
		}

		$tax_query = (array) $query->get( 'tax_query' );
		$tax_query = array_merge( $tax_query, self::attribute_clauses( $filters ) );

		if ( $filters['in_stock'] ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'outofstock',
				'operator' => 'NOT IN',
			);
		}

		if ( $tax_query ) {
			$tax_query['relation'] = 'AND';
			$query->set( 'tax_query', $tax_query );
		}

		if ( $filters['orderby'] ) {
			$query->set( 'orderby', self::orderby_to_query( $filters['orderby'] ) );
		}
	}

	/**
	 * Price meta clause for the lookup-friendly `_price` meta key.
	 *
	 * @param float $min Minimum price.
	 * @param float $max Maximum price.
	 * @return array
	 */
	private static function price_meta_clause( $min, $max ) {
		$clause = array(
			'key'     => '_price',
			'type'    => 'DECIMAL',
			'compare' => 'BETWEEN',
		);

		if ( $min > 0 && $max > 0 ) {
			$clause['value'] = array( $min, $max );
		} elseif ( $min > 0 ) {
			$clause['value']   = $min;
			$clause['compare'] = '>=';
		} else {
			$clause['value']   = $max;
			$clause['compare'] = '<=';
		}

		return $clause;
	}

	/**
	 * Build tax_query clauses for the size / colour / fabric attributes.
	 *
	 * @param array $filters Sanitized filters.
	 * @return array
	 */
	private static function attribute_clauses( $filters ) {
		$clauses = array();

		$map = array(
			'size'   => 'pa_size',
			'color'  => 'pa_color',
			'fabric' => 'pa_fabric',
		);

		foreach ( $map as $key => $taxonomy ) {
			if ( empty( $filters[ $key ] ) || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$clauses[] = array(
				'taxonomy'         => $taxonomy,
				'field'            => 'slug',
				'terms'            => $filters[ $key ],
				'operator'         => 'IN',
				'include_children' => false,
			);
		}

		return $clauses;
	}

	/**
	 * Translate an orderby key into WP_Query arguments.
	 *
	 * @param string $orderby Order key.
	 * @return string|array
	 */
	private static function orderby_to_query( $orderby ) {
		switch ( $orderby ) {
			case 'price':
				return array(
					'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_num',
					'order'    => 'ASC',
				);
			case 'price-desc':
				return array(
					'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_num',
					'order'    => 'DESC',
				);
			case 'date':
				return 'date';
			case 'rating':
				return array(
					'meta_key' => '_wc_average_rating', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_num',
					'order'    => 'DESC',
				);
			case 'popularity':
				return array(
					'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_num',
					'order'    => 'DESC',
				);
			default:
				return 'menu_order title';
		}
	}

	/**
	 * Store-wide price range taken from WooCommerce's own lookup table.
	 *
	 * @return array{min:float,max:float}
	 */
	public static function price_range() {
		global $wpdb;

		$table = $wpdb->prefix . 'wc_product_meta_lookup';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT MIN(min_price) AS min_price, MAX(max_price) AS max_price FROM {$table} WHERE min_price IS NOT NULL AND min_price >= %f", 0 ) );

		return array(
			'min' => $row && null !== $row->min_price ? floor( (float) $row->min_price ) : 0,
			'max' => $row && null !== $row->max_price ? ceil( (float) $row->max_price ) : 1000,
		);
	}

	/**
	 * Attribute terms for the filter panel.
	 *
	 * @param string $taxonomy Attribute taxonomy.
	 * @return WP_Term[]
	 */
	private static function terms( $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Output the archive filter bar.
	 */
	public static function render_filter_bar() {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		if ( (int) moon_core_setting( 'enable_filters', 1 ) !== 1 ) {
			return;
		}

		self::filter_form();
	}

	/**
	 * Shortcode wrapper so the filters can also be placed inside Elementor.
	 *
	 * @return string
	 */
	public static function filter_shortcode() {
		ob_start();
		self::filter_form();
		return (string) ob_get_clean();
	}

	/**
	 * The filter form markup (works with JS enhancement and plain GET submit).
	 */
	public static function filter_form() {
		$filters = self::read_filters();
		$range   = self::price_range();
		$sizes   = self::terms( 'pa_size' );
		$colors  = self::terms( 'pa_color' );
		$fabrics = self::terms( 'pa_fabric' );

		$action      = is_shop() || is_product_taxonomy() ? '' : wc_get_page_permalink( 'shop' );
		$queried     = is_product_taxonomy() ? get_queried_object() : null;
		$current_cat = $queried instanceof WP_Term ? $queried->slug : '';
		?>
		<form class="moon-filters" method="get" data-moon-filters
			data-cat="<?php echo esc_attr( $current_cat ); ?>"
			action="<?php echo esc_url( $action ? $action : get_permalink() ); ?>">
			<div class="moon-filters__head">
				<span class="moon-filters__title"><?php esc_html_e( 'فیلترها', 'moon-core' ); ?></span>
				<button type="button" class="moon-filters__clear" data-moon-filters-clear>
					<?php esc_html_e( 'حذف همه', 'moon-core' ); ?>
				</button>
			</div>

			<div class="moon-filters__grid">
				<fieldset class="moon-filters__group moon-filters__group--price">
					<legend><?php esc_html_e( 'محدوده قیمت', 'moon-core' ); ?></legend>
					<div class="moon-price-range">
						<label>
							<span><?php esc_html_e( 'از', 'moon-core' ); ?></span>
							<input type="number" inputmode="numeric" min="0" step="1"
								name="moon_min_price" value="<?php echo esc_attr( $filters['min_price'] > 0 ? (string) (int) $filters['min_price'] : '' ); ?>"
								placeholder="<?php echo esc_attr( (string) $range['min'] ); ?>" />
						</label>
						<label>
							<span><?php esc_html_e( 'تا', 'moon-core' ); ?></span>
							<input type="number" inputmode="numeric" min="0" step="1"
								name="moon_max_price" value="<?php echo esc_attr( $filters['max_price'] > 0 ? (string) (int) $filters['max_price'] : '' ); ?>"
								placeholder="<?php echo esc_attr( (string) $range['max'] ); ?>" />
						</label>
					</div>
				</fieldset>

				<?php if ( $sizes ) : ?>
					<fieldset class="moon-filters__group">
						<legend><?php esc_html_e( 'سایز', 'moon-core' ); ?></legend>
						<div class="moon-chips">
							<?php foreach ( $sizes as $term ) : ?>
								<label class="moon-chip">
									<input type="checkbox" name="moon_size[]" value="<?php echo esc_attr( $term->slug ); ?>"
										<?php checked( in_array( $term->slug, $filters['size'], true ) ); ?> />
									<span><?php echo esc_html( $term->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>

				<?php if ( $colors ) : ?>
					<fieldset class="moon-filters__group">
						<legend><?php esc_html_e( 'رنگ', 'moon-core' ); ?></legend>
						<div class="moon-chips moon-chips--colors">
							<?php foreach ( $colors as $term ) : ?>
								<label class="moon-chip moon-chip--color" title="<?php echo esc_attr( $term->name ); ?>">
									<input type="checkbox" name="moon_color[]" value="<?php echo esc_attr( $term->slug ); ?>"
										<?php checked( in_array( $term->slug, $filters['color'], true ) ); ?> />
									<span class="moon-chip__swatch" data-moon-swatch="<?php echo esc_attr( $term->slug ); ?>">
										<?php echo esc_html( $term->name ); ?>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>

				<?php if ( $fabrics ) : ?>
					<fieldset class="moon-filters__group">
						<legend><?php esc_html_e( 'جنس پارچه', 'moon-core' ); ?></legend>
						<div class="moon-chips">
							<?php foreach ( $fabrics as $term ) : ?>
								<label class="moon-chip">
									<input type="checkbox" name="moon_fabric[]" value="<?php echo esc_attr( $term->slug ); ?>"
										<?php checked( in_array( $term->slug, $filters['fabric'], true ) ); ?> />
									<span><?php echo esc_html( $term->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>

				<fieldset class="moon-filters__group moon-filters__group--stock">
					<legend><?php esc_html_e( 'موجودی', 'moon-core' ); ?></legend>
					<label class="moon-switch">
						<input type="checkbox" name="moon_in_stock" value="1" <?php checked( $filters['in_stock'] ); ?> />
						<span><?php esc_html_e( 'فقط محصولات موجود', 'moon-core' ); ?></span>
					</label>
				</fieldset>
			</div>

			<div class="moon-filters__actions">
				<button class="moon-btn moon-btn--solid" type="submit"><?php esc_html_e( 'اعمال فیلتر', 'moon-core' ); ?></button>
			</div>
			<?php wp_nonce_field( 'moon_core_nonce', 'moon_nonce', false ); ?>
		</form>
		<?php
	}

	/**
	 * AJAX: filtered product cards.
	 */
	public static function ajax_filter_products() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'moon_core_nonce' ) ) {
			// Filtering is a read-only operation; the nonce only guards stale caches.
			$nonce = '';
		}

		$paged     = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
		$per_page  = (int) apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
		$per_page  = $per_page > 0 ? $per_page : 12;
		$filters   = self::read_filters();
		$tax_query = self::visibility_tax_query();

		$product_cat = isset( $_POST['product_cat'] ) ? sanitize_title( wp_unslash( $_POST['product_cat'] ) ) : '';
		if ( $product_cat ) {
			$tax_query[] = array(
				'taxonomy'         => 'product_cat',
				'field'            => 'slug',
				'terms'            => $product_cat,
				'include_children' => true,
			);
		}

		$tax_query = array_merge( $tax_query, self::attribute_clauses( $filters ) );

		if ( $filters['in_stock'] ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'outofstock',
				'operator' => 'NOT IN',
			);
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'paged'          => $paged,
			'posts_per_page' => $per_page,
		);

		if ( $tax_query ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		if ( $filters['min_price'] > 0 || $filters['max_price'] > 0 ) {
			$args['meta_query'] = array( self::price_meta_clause( $filters['min_price'], $filters['max_price'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		$orderby = $filters['orderby'];
		if ( $orderby ) {
			$mapped = self::orderby_to_query( $orderby );
			if ( is_array( $mapped ) ) {
				$args = array_merge( $args, $mapped );
			} else {
				$args['orderby'] = $mapped;
			}
		}

		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			echo '<ul class="products columns-' . esc_attr( (string) wc_get_default_products_per_row() ) . ' moon-products">';
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			echo '</ul>';
		}
		$html = (string) ob_get_clean();

		wp_reset_postdata();

		wp_send_json_success(
			array(
				'html'        => $html,
				'found'       => (int) $query->found_posts,
				'paged'       => $paged,
				'max_pages'   => (int) $query->max_num_pages,
				'has_more'    => $paged < (int) $query->max_num_pages,
				'empty'       => ! $query->have_posts(),
				'empty_label' => __( 'محصولی با این فیلترها پیدا نشد.', 'moon-core' ),
			)
		);
	}

	/**
	 * Standard catalog visibility exclusions.
	 *
	 * @return array
	 */
	private static function visibility_tax_query() {
		$visibility = function_exists( 'wc_get_product_visibility_term_ids' ) ? wc_get_product_visibility_term_ids() : array();
		$clauses    = array();

		if ( ! empty( $visibility['exclude-from-catalog'] ) ) {
			$clauses[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => array( (int) $visibility['exclude-from-catalog'] ),
				'operator' => 'NOT IN',
			);
		}

		return $clauses;
	}

	/* ---------------------------------------------------------------------
	 * Badges
	 * ------------------------------------------------------------------ */

	/**
	 * Sale badge with the discount percentage.
	 *
	 * @param string     $html    Original markup.
	 * @param \WP_Post   $post    Post object.
	 * @param \WC_Product $product Product.
	 * @return string
	 */
	public static function sale_flash( $html, $post = null, $product = null ) {
		if ( (int) moon_core_setting( 'enable_badges', 1 ) !== 1 ) {
			return $html;
		}

		if ( ! $product instanceof WC_Product ) {
			$product = $post ? wc_get_product( $post->ID ) : null;
		}

		if ( ! $product || ! $product->is_on_sale() ) {
			return $html;
		}

		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		$label   = __( 'تخفیف', 'moon-core' );

		if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
			$percent = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
			/* translators: %d: discount percentage. */
			$label = sprintf( __( '%d٪ تخفیف', 'moon-core' ), $percent );
		}

		return '<span class="moon-badge moon-badge--sale">' . esc_html( $label ) . '</span>';
	}

	/**
	 * "New" badge for products published recently.
	 */
	public static function new_badge() {
		if ( (int) moon_core_setting( 'enable_badges', 1 ) !== 1 ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		$days = ( time() - strtotime( $post->post_date_gmt . ' GMT' ) ) / DAY_IN_SECONDS;
		if ( $days <= 21 ) {
			echo '<span class="moon-badge moon-badge--new">' . esc_html__( 'جدید', 'moon-core' ) . '</span>';
		}
	}

	/* ---------------------------------------------------------------------
	 * Wishlist (only when YITH Wishlist is not handling it)
	 * ------------------------------------------------------------------ */

	/**
	 * Wishlist ids for the current visitor.
	 *
	 * @return int[]
	 */
	public static function wishlist_ids() {
		if ( is_user_logged_in() ) {
			$ids = get_user_meta( get_current_user_id(), 'moon_wishlist', true );
			$ids = is_array( $ids ) ? array_map( 'absint', $ids ) : array();
		} else {
			$raw = isset( $_COOKIE[ self::WISHLIST_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::WISHLIST_COOKIE ] ) ) : '';
			$ids = array();
			foreach ( explode( ',', $raw ) as $part ) {
				$id = absint( $part );
				if ( $id ) {
					$ids[] = $id;
				}
			}
		}

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Wishlist item count.
	 *
	 * @return int
	 */
	public static function wishlist_count() {
		return count( self::wishlist_ids() );
	}

	/**
	 * Persist the wishlist and return the new count.
	 *
	 * @param int[] $ids Product ids.
	 * @return int
	 */
	private static function save_wishlist( $ids ) {
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), 'moon_wishlist', $ids );
		} else {
			setcookie(
				self::WISHLIST_COOKIE,
				implode( ',', array_slice( $ids, 0, 50 ) ),
				time() + ( 30 * DAY_IN_SECONDS ),
				COOKIEPATH ? COOKIEPATH : '/',
				COOKIE_DOMAIN,
				is_ssl(),
				false
			);
		}

		return count( $ids );
	}

	/**
	 * AJAX: add or remove a product from the wishlist.
	 */
	public static function ajax_toggle_wishlist() {
		check_ajax_referer( 'moon_core_nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			wp_send_json_error( array( 'message' => __( 'محصول نامعتبر است.', 'moon-core' ) ), 400 );
		}

		$ids    = self::wishlist_ids();
		$active = in_array( $product_id, $ids, true );

		if ( $active ) {
			$ids = array_diff( $ids, array( $product_id ) );
		} else {
			$ids[] = $product_id;
		}

		$count = self::save_wishlist( $ids );

		wp_send_json_success(
			array(
				'active' => ! $active,
				'count'  => $count,
				'label'  => $active ? __( 'افزودن به علاقه‌مندی‌ها', 'moon-core' ) : __( 'حذف از علاقه‌مندی‌ها', 'moon-core' ),
			)
		);
	}

	/**
	 * Wishlist button in the product loop.
	 */
	public static function loop_wishlist_button() {
		$product_id = get_the_ID();
		if ( ! $product_id ) {
			return;
		}

		self::wishlist_button( $product_id, 'small' );
	}

	/**
	 * Wishlist button on the single product page.
	 */
	public static function single_wishlist_button() {
		$product_id = get_the_ID();
		if ( ! $product_id ) {
			return;
		}

		self::wishlist_button( $product_id, 'large' );
	}

	/**
	 * Render a wishlist toggle button.
	 *
	 * @param int    $product_id Product id.
	 * @param string $size       small|large.
	 */
	public static function wishlist_button( $product_id, $size = 'small' ) {
		$active = in_array( (int) $product_id, self::wishlist_ids(), true );
		$label  = $active ? __( 'حذف از علاقه‌مندی‌ها', 'moon-core' ) : __( 'افزودن به علاقه‌مندی‌ها', 'moon-core' );
		?>
		<button type="button" class="moon-wishlist-btn moon-wishlist-btn--<?php echo esc_attr( $size ); ?><?php echo $active ? ' is-active' : ''; ?>"
			data-moon-wishlist="<?php echo esc_attr( (string) absint( $product_id ) ); ?>"
			aria-pressed="<?php echo $active ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $label ); ?>">
			<?php Moon_Core_Header::icon( 'heart' ); ?>
			<span class="moon-wishlist-btn__label"><?php echo esc_html( $label ); ?></span>
		</button>
		<?php
	}

	/**
	 * `[moon_wishlist]` page content.
	 *
	 * @return string
	 */
	public static function wishlist_shortcode() {
		$ids = self::wishlist_ids();

		if ( ! $ids ) {
			return '<div class="moon-empty"><p>' . esc_html__( 'لیست علاقه‌مندی‌های شما خالی است.', 'moon-core' ) . '</p>' .
				'<a class="moon-btn moon-btn--solid" href="' . esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ) . '">' .
				esc_html__( 'مشاهده فروشگاه', 'moon-core' ) . '</a></div>';
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $ids ),
			)
		);

		ob_start();
		echo '<ul class="products columns-3 moon-products moon-wishlist-grid">';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
		}
		echo '</ul>';
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/* ---------------------------------------------------------------------
	 * Single product extras
	 * ------------------------------------------------------------------ */

	/**
	 * Add a real size-guide tab to the product tabs.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function size_guide_tab( $tabs ) {
		if ( (int) moon_core_setting( 'enable_size_guide', 1 ) !== 1 ) {
			return $tabs;
		}

		$tabs['moon_size_guide'] = array(
			'title'    => __( 'راهنمای سایز', 'moon-core' ),
			'priority' => 25,
			'callback' => array( __CLASS__, 'size_guide_content' ),
		);

		return $tabs;
	}

	/**
	 * Size guide tab content.
	 */
	public static function size_guide_content() {
		$custom = (string) moon_core_setting( 'size_guide_html', '' );

		if ( '' !== trim( $custom ) ) {
			echo wp_kses_post( wpautop( $custom ) );
			return;
		}

		$rows = array(
			array( 'XS', '84–88', '66–70', '90–94' ),
			array( 'S', '88–92', '70–74', '94–98' ),
			array( 'M', '92–96', '74–78', '98–102' ),
			array( 'L', '96–102', '78–84', '102–108' ),
			array( 'XL', '102–108', '84–90', '108–114' ),
		);
		?>
		<table class="moon-size-table">
			<caption class="screen-reader-text"><?php esc_html_e( 'جدول راهنمای سایز (سانتی‌متر)', 'moon-core' ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'سایز', 'moon-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'دور سینه', 'moon-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'دور کمر', 'moon-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'دور باسن', 'moon-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<?php foreach ( $row as $cell ) : ?>
						<td><?php echo esc_html( $cell ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="moon-size-table__note"><?php esc_html_e( 'اندازه‌ها به سانتی‌متر است؛ در صورت تردید یک سایز بزرگ‌تر انتخاب کنید.', 'moon-core' ); ?></p>
		<?php
	}

	/**
	 * Keep the header cart counter in sync with WooCommerce's own fragments.
	 *
	 * @param array $fragments Fragments.
	 * @return array
	 */
	public static function cart_fragments( $fragments ) {
		$count = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;

		$fragments['.moon-cart-count'] = '<span class="moon-count" data-moon-cart-count>' . esc_html( (string) $count ) . '</span>';

		return $fragments;
	}
}
