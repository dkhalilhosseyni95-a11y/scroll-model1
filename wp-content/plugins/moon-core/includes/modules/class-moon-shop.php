<?php
/**
 * Shop helpers: product card data, quick view, AJAX filtering and the size guide.
 *
 * The shop uses the theme's own filter panel instead of WooCommerce widgets so
 * that colour, size and price filters can be combined and refreshed without a
 * page reload, while still producing shareable URLs.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shop module.
 */
class Moon_Shop {

	/**
	 * Products per page.
	 */
	const PER_PAGE = 12;

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_shortcode( 'moon_size_guide', array( __CLASS__, 'size_guide_shortcode' ) );

		add_action( 'wp_ajax_moon_quick_view', array( __CLASS__, 'quick_view' ) );
		add_action( 'wp_ajax_nopriv_moon_quick_view', array( __CLASS__, 'quick_view' ) );

		add_action( 'wp_ajax_moon_filter_products', array( __CLASS__, 'filter_products' ) );
		add_action( 'wp_ajax_nopriv_moon_filter_products', array( __CLASS__, 'filter_products' ) );

		add_action( 'wp_ajax_moon_look_add', array( __CLASS__, 'look_add' ) );
		add_action( 'wp_ajax_nopriv_moon_look_add', array( __CLASS__, 'look_add' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'filter_main_query' ) );
	}

	/* ----------------------------------------------------------------------
	 * Product data
	 * -------------------------------------------------------------------- */

	/**
	 * Everything a card needs for one product.
	 *
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function card_data( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return array();
		}

		$image_id = $product->get_image_id();
		$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'moon-card' ) : wc_placeholder_img_src( 'moon-card' );
		$gallery  = class_exists( 'Moon_Colors' ) ? Moon_Colors::gallery( $product ) : array();
		$hover    = '';

		foreach ( $gallery as $url ) {
			if ( $url && $url !== $image ) {
				$hover = $url;
				break;
			}
		}

		$colors = class_exists( 'Moon_Colors' ) ? Moon_Colors::product_colors( $product ) : array();

		if ( $hover && ! empty( $colors[0]['image'] ) ) {
			$hover = $colors[0]['image'] === $image ? $hover : $colors[0]['image'];
		}

		$tag   = '';
		$sale  = $product->is_on_sale();
		$regular = (float) $product->get_regular_price();
		$current = (float) $product->get_price();

		if ( $sale && $regular > 0 && $current > 0 ) {
			$tag = '−' . self::digits( (string) round( ( ( $regular - $current ) / $regular ) * 100 ) ) . '٪';
		} elseif ( $product->is_featured() ) {
			$tag = __( 'ویژه', 'moon-core' );
		}

		$english = trim( preg_replace( '/\s+/', ' ', ucwords( str_replace( array( '-', '_' ), ' ', $product->get_slug() ) ) ) );

		return array(
			'id'          => $product->get_id(),
			'name'        => $product->get_name(),
			'en'          => $english,
			'price'       => $product->get_price_html(),
			'image'       => $image,
			'hover'       => $hover,
			'url'         => get_permalink( $product->get_id() ),
			'tag'         => $tag,
			'on_sale'     => $sale,
			'in_stock'    => $product->is_in_stock(),
			'type'        => $product->get_type(),
			'is_variable' => $product->is_type( 'variable' ),
			'rating'      => (float) $product->get_average_rating(),
			'reviews'     => (int) $product->get_review_count(),
			'colors'      => $colors,
			'sizes'       => self::product_sizes( $product ),
			'add_id'      => ( ! $product->is_type( 'variable' ) && $product->is_in_stock() && $product->is_purchasable() ) ? $product->get_id() : 0,
			'add_url'     => $product->add_to_cart_url(),
			'excerpt'     => wp_strip_all_tags( $product->get_short_description() ),
			'category'    => self::primary_category( $product ),
		);
	}

	/**
	 * Sizes a product is available in.
	 *
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function product_sizes( $product ) {
		$sizes = array();

		if ( ! $product instanceof WC_Product ) {
			return $sizes;
		}

		if ( $product->is_type( 'variable' ) ) {
			$attributes = $product->get_variation_attributes();
			$slugs      = isset( $attributes['pa_size'] ) ? (array) $attributes['pa_size'] : array();

			foreach ( $slugs as $slug ) {
				$term = get_term_by( 'slug', $slug, 'pa_size' );

				if ( $term && ! is_wp_error( $term ) ) {
					$sizes[] = $term->name;
				}
			}
		} elseif ( $product->has_attributes() ) {
			$attributes = $product->get_attributes();

			foreach ( $attributes as $key => $attribute ) {
				if ( false === strpos( (string) $key, 'size' ) ) {
					continue;
				}

				$options = $attribute instanceof WC_Product_Attribute ? $attribute->get_options() : array();

				foreach ( $options as $option ) {
					$term  = get_term_by( 'slug', $option, 'pa_size' );
					$sizes[] = $term && ! is_wp_error( $term ) ? $term->name : $option;
				}
			}
		}

		return array_slice( array_values( array_unique( $sizes ) ), 0, 8 );
	}

	/**
	 * First product category name.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function primary_category( $product ) {
		$terms = get_the_terms( $product->get_id(), 'product_cat' );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}

		foreach ( $terms as $term ) {
			if ( 'uncategorized' !== $term->slug ) {
				return $term->name;
			}
		}

		return '';
	}

	/**
	 * Persian digits when the theme provides the helper.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function digits( $value ) {
		return function_exists( 'moon_fa_digits' ) ? moon_fa_digits( $value ) : $value;
	}

	/* ----------------------------------------------------------------------
	 * Quick view
	 * -------------------------------------------------------------------- */

	/**
	 * Render the quick view for one product.
	 */
	public static function quick_view() {
		check_ajax_referer( 'moon_shop', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product    = $product_id ? wc_get_product( $product_id ) : null;

		if ( ! $product || ! $product->is_visible() ) {
			wp_send_json_error( array( 'message' => __( 'این محصول در دسترس نیست.', 'moon-core' ) ) );
		}

		$GLOBALS['product'] = $product;

		ob_start();
		wc_get_template(
			'moon-quick-view.php',
			array(
				'product' => $product,
				'data'    => self::card_data( $product ),
			),
			'',
			MOON_CORE_DIR . 'templates/'
		);
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html, 'title' => $product->get_name() ) );
	}

	/* ----------------------------------------------------------------------
	 * Filtering
	 * -------------------------------------------------------------------- */

	/**
	 * Selected filter values from the current request.
	 *
	 * @return array
	 */
	public static function current_filters() {
		$get = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification

		$list = function ( $key ) use ( $get ) {
			if ( empty( $get[ $key ] ) ) {
				return array();
			}

			$values = is_array( $get[ $key ] ) ? $get[ $key ] : explode( ',', (string) $get[ $key ] );

			return array_values( array_filter( array_map( 'sanitize_title', array_map( 'trim', $values ) ) ) );
		};

		return array(
			'color'    => $list( 'color' ),
			'size'     => $list( 'size' ),
			'cat'      => $list( 'moon_cat' ),
			'min'      => isset( $get['min_price'] ) ? (float) $get['min_price'] : 0,
			'max'      => isset( $get['max_price'] ) ? (float) $get['max_price'] : 0,
			'orderby'  => isset( $get['orderby'] ) ? sanitize_key( $get['orderby'] ) : '',
			'search'   => isset( $get['s'] ) ? sanitize_text_field( $get['s'] ) : '',
		);
	}

	/**
	 * Turn filter values into WP_Query arguments.
	 *
	 * @param array $filters Filters.
	 * @return array
	 */
	public static function query_args( $filters ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => self::PER_PAGE,
			'tax_query'      => array( 'relation' => 'AND' ),
			'meta_query'     => array(),
		);

		if ( ! empty( $filters['color'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'pa_color',
				'field'    => 'slug',
				'terms'    => $filters['color'],
			);
		}

		if ( ! empty( $filters['size'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'pa_size',
				'field'    => 'slug',
				'terms'    => $filters['size'],
			);
		}

		if ( ! empty( $filters['cat'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $filters['cat'],
			);
		}

		if ( $filters['min'] > 0 || $filters['max'] > 0 ) {
			$range = array( 'key' => '_price', 'type' => 'NUMERIC' );

			if ( $filters['min'] > 0 && $filters['max'] > 0 ) {
				$range['value']   = array( $filters['min'], $filters['max'] );
				$range['compare'] = 'BETWEEN';
			} elseif ( $filters['min'] > 0 ) {
				$range['value']   = $filters['min'];
				$range['compare'] = '>=';
			} else {
				$range['value']   = $filters['max'];
				$range['compare'] = '<=';
			}

			$args['meta_query'][] = $range;
		}

		if ( empty( $args['meta_query'] ) ) {
			unset( $args['meta_query'] );
		}

		if ( $filters['search'] ) {
			$args['s'] = $filters['search'];
		}

		if ( function_exists( 'WC' ) && WC()->query ) {
			$ordering = WC()->query->get_catalog_ordering_args( $filters['orderby'] ? $filters['orderby'] : '' );

			foreach ( array( 'orderby', 'order', 'meta_key' ) as $key ) {
				if ( ! empty( $ordering[ $key ] ) ) {
					$args[ $key ] = $ordering[ $key ];
				}
			}
		}

		return apply_filters( 'moon_core_shop_query_args', $args, $filters );
	}

	/**
	 * Apply colour/size/price filters to the shop's main query.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function filter_main_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) ) {
			return;
		}

		$filters = self::current_filters();

		if ( empty( $filters['color'] ) && empty( $filters['size'] ) && empty( $filters['min'] ) && empty( $filters['max'] ) ) {
			return;
		}

		$args = self::query_args( $filters );

		$query->set( 'tax_query', $args['tax_query'] );

		if ( ! empty( $args['meta_query'] ) ) {
			$query->set( 'meta_query', $args['meta_query'] );
		}
	}

	/**
	 * AJAX: refresh the product grid for the selected filters.
	 */
	public static function filter_products() {
		check_ajax_referer( 'moon_shop', 'nonce' );

		$filters = array(
			'color'   => self::posted_list( 'color' ),
			'size'    => self::posted_list( 'size' ),
			'cat'     => self::posted_list( 'moon_cat' ),
			'min'     => isset( $_POST['min_price'] ) ? (float) $_POST['min_price'] : 0,
			'max'     => isset( $_POST['max_price'] ) ? (float) $_POST['max_price'] : 0,
			'orderby' => isset( $_POST['orderby'] ) ? sanitize_key( wp_unslash( $_POST['orderby'] ) ) : '',
			'search'  => isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '',
			'paged'   => isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1,
		);

		// Keep an archive's own context (a /product-category/women/ page).
		$archive = isset( $_POST['archive'] ) ? sanitize_title( wp_unslash( $_POST['archive'] ) ) : '';

		if ( 'shop' !== $archive && $archive ) {
			$term = get_term_by( 'slug', $archive, 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				$filters['cat'][] = $archive;
			}
		}

		$args             = self::query_args( $filters );
		$args['paged']    = $filters['paged'];
		$args['paginate'] = false;

		$query = new WP_Query( $args );

		$html = self::render_loop( $query, $filters );

		wp_send_json_success( array(
			'html'   => $html,
			'total'  => (int) $query->found_posts,
			'pages'  => (int) $query->max_num_pages,
			'paged'  => $filters['paged'],
			'count'  => sprintf(
				/* translators: %s: number of products. */
				__( 'نمایش %s محصول', 'moon-core' ),
				self::digits( number_format_i18n( (int) $query->found_posts ) )
			),
			'pager'  => self::pagination( (int) $query->max_num_pages, $filters['paged'] ),
			'empty'  => ! $query->have_posts(),
		) );
	}

	/**
	 * Posted list of values.
	 *
	 * @param string $key Key.
	 * @return array
	 */
	private static function posted_list( $key ) {
		if ( empty( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return array();
		}

		$raw    = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification
		$values = is_array( $raw ) ? $raw : explode( ',', (string) $raw );

		return array_values( array_filter( array_map( 'sanitize_title', array_map( 'trim', $values ) ) ) );
	}

	/**
	 * Product markup for a query.
	 *
	 * @param WP_Query $query   Query.
	 * @param array    $filters Filters.
	 * @return string
	 */
	private static function render_loop( $query, $filters ) {
		ob_start();

		if ( $query->have_posts() ) {
			wc_set_loop_prop( 'columns', 4 );
			wc_set_loop_prop( 'total', (int) $query->found_posts );

			woocommerce_product_loop_start();

			while ( $query->have_posts() ) {
				$query->the_post();
				global $product;

				if ( ! $product instanceof WC_Product ) {
					$product = wc_get_product( get_the_ID() );
				}

				if ( ! $product ) {
					continue;
				}

				wc_get_template_part( 'content', 'product' );
			}

			woocommerce_product_loop_end();
			wc_reset_loop();
		} else {
			echo '<div class="moon-shop__empty">';
			echo '<p>' . esc_html__( 'محصولی با این فیلترها پیدا نشد.', 'moon-core' ) . '</p>';
			echo '<button type="button" class="moon-btn moon-btn--dark" data-moon-filter-reset>' . esc_html__( 'پاک کردن فیلترها', 'moon-core' ) . '</button>';
			echo '</div>';
		}

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Pagination markup with AJAX-ready links.
	 *
	 * @param int $pages Total pages.
	 * @param int $current Current page.
	 * @return string
	 */
	public static function pagination( $pages, $current ) {
		if ( $pages < 2 ) {
			return '';
		}

		$out = '<nav class="moon-pager" aria-label="' . esc_attr__( 'صفحه‌بندی محصولات', 'moon-core' ) . '">';

		for ( $page = 1; $page <= $pages; $page++ ) {
			$class = $page === $current ? ' is-current' : '';
			$out  .= '<button type="button" class="moon-pager__link' . $class . '" data-moon-page="' . esc_attr( (string) $page ) . '"' . ( $page === $current ? ' aria-current="page"' : '' ) . '>' . esc_html( self::digits( (string) $page ) ) . '</button>';
		}

		$out .= '</nav>';

		return $out;
	}

	/* ----------------------------------------------------------------------
	 * Filter panel
	 * -------------------------------------------------------------------- */

	/**
	 * Filter values currently applied.
	 *
	 * @return bool
	 */
	public static function has_active_filters() {
		$filters = self::current_filters();

		return ! empty( $filters['color'] ) || ! empty( $filters['size'] ) || ! empty( $filters['cat'] ) || $filters['min'] > 0 || $filters['max'] > 0;
	}

	/**
	 * Render the shop filter panel.
	 *
	 * @param string $context `sidebar` or `drawer`.
	 */
	public static function filters_ui( $context = 'sidebar' ) {
		$filters = self::current_filters();
		$colors  = get_terms( array( 'taxonomy' => 'pa_color', 'hide_empty' => true ) );
		$sizes   = get_terms( array( 'taxonomy' => 'pa_size', 'hide_empty' => true ) );
		$cats    = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );

		$color_order = array( 'black', 'white', 'cream', 'sand', 'camel', 'brown', 'grey', 'navy', 'olive', 'wine' );

		if ( ! is_wp_error( $colors ) && $colors ) {
			usort( $colors, function ( $a, $b ) use ( $color_order ) {
				$ai = array_search( $a->slug, $color_order, true );
				$bi = array_search( $b->slug, $color_order, true );
				$ai = false === $ai ? 99 : $ai;
				$bi = false === $bi ? 99 : $bi;

				return $ai === $bi ? strcmp( $a->name, $b->name ) : $ai - $bi;
			} );
		}

		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		$archive  = 'shop';

		if ( is_product_taxonomy() ) {
			$term = get_queried_object();

			if ( $term && isset( $term->slug ) ) {
				$archive = $term->slug;
			}
		}
		?>
		<form
			class="moon-filters<?php echo 'drawer' === $context ? ' moon-filters--drawer' : ''; ?>"
			data-moon-filters
			data-archive="<?php echo esc_attr( $archive ); ?>"
			action="<?php echo esc_url( $shop_url ); ?>"
			method="get"
		>
			<div class="moon-filters__head">
				<p class="moon-filters__title"><?php esc_html_e( 'فیلترها', 'moon-core' ); ?></p>
				<button type="button" class="moon-filters__reset" data-moon-filter-reset><?php esc_html_e( 'پاک کردن', 'moon-core' ); ?></button>
				<button type="button" class="moon-filters__close" data-moon-filters-close aria-label="<?php esc_attr_e( 'بستن فیلترها', 'moon-core' ); ?>">
					<?php echo function_exists( 'moon_icon' ) ? moon_icon( 'x', 18 ) : '×'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>

			<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
				<div class="moon-filters__group">
					<p class="moon-filters__label"><?php esc_html_e( 'دسته‌بندی', 'moon-core' ); ?></p>
					<ul class="moon-filters__cats">
						<?php foreach ( $cats as $cat ) : ?>
							<?php if ( 'uncategorized' === $cat->slug ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<li>
								<label class="moon-check">
									<input type="checkbox" name="moon_cat[]" value="<?php echo esc_attr( $cat->slug ); ?>" <?php checked( in_array( $cat->slug, $filters['cat'], true ) ); ?>>
									<span class="moon-check__box" aria-hidden="true"></span>
									<span class="moon-check__label"><?php echo esc_html( $cat->name ); ?></span>
									<span class="moon-check__count"><?php echo esc_html( self::digits( number_format_i18n( (int) $cat->count ) ) ); ?></span>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! is_wp_error( $colors ) && $colors ) : ?>
				<div class="moon-filters__group">
					<p class="moon-filters__label"><?php esc_html_e( 'رنگ', 'moon-core' ); ?></p>
					<div class="moon-filters__colors">
						<?php foreach ( $colors as $color ) : ?>
							<?php $hex = Moon_Colors::hex( $color ); ?>
							<button
								type="button"
								class="moon-color-chip<?php echo in_array( $color->slug, $filters['color'], true ) ? ' is-active' : ''; ?>"
								data-moon-color="<?php echo esc_attr( $color->slug ); ?>"
								aria-pressed="<?php echo in_array( $color->slug, $filters['color'], true ) ? 'true' : 'false'; ?>"
								title="<?php echo esc_attr( $color->name ); ?>"
							>
								<span class="moon-color-chip__dot" style="--swatch:<?php echo esc_attr( $hex ); ?>"></span>
								<span class="moon-color-chip__name"><?php echo esc_html( $color->name ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
					<input type="hidden" name="color" value="<?php echo esc_attr( implode( ',', $filters['color'] ) ); ?>" data-moon-colors-input>
				</div>
			<?php endif; ?>

			<?php if ( ! is_wp_error( $sizes ) && $sizes ) : ?>
				<div class="moon-filters__group">
					<p class="moon-filters__label"><?php esc_html_e( 'سایز', 'moon-core' ); ?></p>
					<div class="moon-filters__sizes">
						<?php foreach ( $sizes as $size ) : ?>
							<label class="moon-size-chip<?php echo in_array( $size->slug, $filters['size'], true ) ? ' is-active' : ''; ?>">
								<input type="checkbox" name="size[]" value="<?php echo esc_attr( $size->slug ); ?>" <?php checked( in_array( $size->slug, $filters['size'], true ) ); ?>>
								<span><?php echo esc_html( $size->name ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="moon-filters__group">
				<p class="moon-filters__label"><?php esc_html_e( 'محدوده قیمت (تومان)', 'moon-core' ); ?></p>
				<div class="moon-filters__price">
					<label>
						<span><?php esc_html_e( 'از', 'moon-core' ); ?></span>
						<input type="number" name="min_price" min="0" step="100000" value="<?php echo esc_attr( $filters['min'] > 0 ? (string) (int) $filters['min'] : '' ); ?>" inputmode="numeric">
					</label>
					<label>
						<span><?php esc_html_e( 'تا', 'moon-core' ); ?></span>
						<input type="number" name="max_price" min="0" step="100000" value="<?php echo esc_attr( $filters['max'] > 0 ? (string) (int) $filters['max'] : '' ); ?>" inputmode="numeric">
					</label>
				</div>
			</div>

			<button type="submit" class="moon-btn moon-btn--dark moon-filters__apply"><?php esc_html_e( 'اعمال فیلتر', 'moon-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * AJAX: add a whole look, one variation per piece.
	 */
	public static function look_add() {
		check_ajax_referer( 'moon_shop', 'nonce' );

		$items = isset( $_POST['items'] ) ? json_decode( wp_unslash( $_POST['items'] ), true ) : array();

		if ( ! is_array( $items ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'سبد خرید در دسترس نیست.', 'moon-core' ) ) );
		}

		$added = 0;

		foreach ( $items as $item ) {
			$product_id   = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$variation_id = isset( $item['variation'] ) ? absint( $item['variation'] ) : 0;

			if ( ! $product_id ) {
				continue;
			}

			$passed = WC()->cart->add_to_cart( $product_id, 1, $variation_id );

			if ( $passed ) {
				$added++;
			}
		}

		if ( ! $added ) {
			wp_send_json_error( array( 'message' => __( 'هیچ‌کدام از اقلام این استایل موجود نیست.', 'moon-core' ) ) );
		}

		WC_AJAX::get_refreshed_fragments();
	}

	/* ----------------------------------------------------------------------
	 * Size guide
	 * -------------------------------------------------------------------- */

	/**
	 * Size tables shown in the size guide dialog.
	 *
	 * @return array
	 */
	public static function size_tables() {
		return apply_filters( 'moon_core_size_guide', array(
			'women' => array(
				'label'  => __( 'زنانه', 'moon-core' ),
				'intro'  => __( 'اندازه‌ها بر حسب سانتی‌متر و دور بدن است. اگر بین دو سایز هستید، سایز بزرگ‌تر را انتخاب کنید.', 'moon-core' ),
				'head'   => array( 'سایز', 'دور سینه', 'دور کمر', 'دور باسن', 'قد آستین' ),
				'rows'   => array(
					array( 'XS', '80–84', '62–66', '86–90', '58' ),
					array( 'S', '86–90', '68–72', '92–96', '59' ),
					array( 'M', '90–94', '72–76', '96–100', '60' ),
					array( 'L', '94–100', '76–82', '100–106', '61' ),
					array( 'XL', '100–106', '82–88', '106–112', '62' ),
					array( 'XXL', '106–112', '88–96', '112–118', '63' ),
				),
			),
			'men'   => array(
				'label' => __( 'مردانه', 'moon-core' ),
				'intro' => __( 'برای کت و پالتو، دور سینه را با یک لایه لباس زیر اندازه بگیرید.', 'moon-core' ),
				'head'  => array( 'سایز', 'دور سینه', 'دور کمر', 'دور باسن', 'دور یقه' ),
				'rows'  => array(
					array( 'S', '90–94', '76–80', '94–98', '38' ),
					array( 'M', '96–100', '82–86', '100–104', '40' ),
					array( 'L', '102–106', '88–92', '106–110', '42' ),
					array( 'XL', '108–112', '94–98', '112–116', '44' ),
					array( 'XXL', '114–120', '100–106', '118–124', '46' ),
				),
			),
			'kids'  => array(
				'label' => __( 'کودکان', 'moon-core' ),
				'intro' => __( 'سایز را بر اساس قد کودک انتخاب کنید؛ اگر کودک در حال رشد است یک سایز بالاتر مناسب‌تر است.', 'moon-core' ),
				'head'  => array( 'سایز', 'گروه سنی', 'قد (سانتی‌متر)', 'دور سینه', 'دور کمر' ),
				'rows'  => array(
					array( '۲–۳ سال', '2–3Y', '92–98', '52–54', '50–52' ),
					array( '۴–۵ سال', '4–5Y', '104–110', '56–58', '53–55' ),
					array( '۶–۷ سال', '6–7Y', '116–122', '60–63', '56–58' ),
					array( '۸–۹ سال', '8–9Y', '128–134', '65–68', '59–61' ),
					array( '۱۰–۱۱ سال', '10–11Y', '140–146', '70–73', '62–64' ),
					array( '۱۲–۱۳ سال', '12–13Y', '152–158', '76–80', '65–67' ),
				),
			),
			'accessories' => array(
				'label' => __( 'شال و روسری', 'moon-core' ),
				'intro' => __( 'ابعاد باز شده پس از اتوی ملایم اندازه‌گیری شده است؛ تلرانس ±۲ سانتی‌متر طبیعی است.', 'moon-core' ),
				'head'  => array( 'نوع', 'طول', 'عرض', 'جنس' ),
				'rows'  => array(
					array( 'شال پشمی', '۱۸۰', '۷۰', 'پشم مرینوس' ),
					array( 'روسری ابریشم', '۹۰', '۹۰', 'ابریشم خالص' ),
					array( 'شال کتان', '۱۷۰', '۶۵', 'کتان نرم' ),
					array( 'اسکارف ابریشم', '۱۲۰', '۲۰', 'ابریشم' ),
				),
			),
		) );
	}

	/**
	 * Shortcode wrapper for the size guide (used on the size-guide page).
	 *
	 * @return string
	 */
	public static function size_guide_shortcode() {
		return self::size_guide_html( false );
	}

	/**
	 * Size guide markup.
	 *
	 * @param bool $dialog Whether to render the dialog shell.
	 * @return string
	 */
	public static function size_guide_html( $dialog = true ) {
		$tables = self::size_tables();
		$first  = key( $tables );

		ob_start();
		?>
		<div class="moon-sizeguide<?php echo $dialog ? '' : ' moon-sizeguide--inline'; ?>"<?php echo $dialog ? ' data-moon-sizeguide' : ''; ?>>
			<div class="moon-sizeguide__head">
				<div>
					<p class="moon-eyebrow"><span dir="ltr">Size guide</span></p>
					<h2 class="moon-sizeguide__title"><?php esc_html_e( 'راهنمای سایز', 'moon-core' ); ?></h2>
				</div>
				<?php if ( $dialog ) : ?>
					<button type="button" class="moon-sizeguide__close" data-moon-close aria-label="<?php esc_attr_e( 'بستن', 'moon-core' ); ?>">
						<?php echo function_exists( 'moon_icon' ) ? moon_icon( 'x', 18 ) : '×'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				<?php endif; ?>
			</div>

			<div class="moon-sizeguide__tabs" role="tablist">
				<?php $i = 0; ?>
				<?php foreach ( $tables as $key => $table ) : ?>
					<button type="button" role="tab" class="moon-sizeguide__tab<?php echo 0 === $i ? ' is-active' : ''; ?>" data-moon-sizeguide-tab="<?php echo esc_attr( $key ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"><?php echo esc_html( $table['label'] ); ?></button>
					<?php $i++; ?>
				<?php endforeach; ?>
			</div>

			<?php $i = 0; ?>
			<?php foreach ( $tables as $key => $table ) : ?>
				<div class="moon-sizeguide__panel<?php echo 0 === $i ? ' is-active' : ''; ?>" data-moon-sizeguide-panel="<?php echo esc_attr( $key ); ?>">
					<p class="moon-sizeguide__intro"><?php echo esc_html( $table['intro'] ); ?></p>
					<div class="moon-sizeguide__scroll">
						<table>
							<thead>
								<tr>
									<?php foreach ( $table['head'] as $cell ) : ?>
										<th><?php echo esc_html( $cell ); ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $table['rows'] as $row ) : ?>
									<tr>
										<?php foreach ( $row as $cell ) : ?>
											<td><?php echo esc_html( $cell ); ?></td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php $i++; ?>
			<?php endforeach; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}
