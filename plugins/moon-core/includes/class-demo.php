<?php
/**
 * One-click demo content: categories, attributes, products, pages, Elementor
 * documents and templates. The importer is idempotent — re-running it updates
 * existing documents instead of duplicating them.
 *
 * @package MoonCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Importer and admin screen.
 */
class Moon_Core_Demo {

	/**
	 * Option flag.
	 */
	const FLAG = 'moon_core_demo_imported';

	/**
	 * Register the admin screen.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_moon_core_import', array( __CLASS__, 'handle_import' ) );
	}

	/**
	 * Add the "راه‌اندازی" submenu.
	 */
	public static function menu() {
		add_submenu_page(
			Moon_Core_Settings::PAGE,
			__( 'راه‌اندازی و درون‌ریزی', 'moon-core' ),
			__( 'راه‌اندازی', 'moon-core' ),
			'manage_options',
			'moon-core-setup',
			array( __CLASS__, 'page' )
		);
	}

	/**
	 * Admin page.
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$done = (bool) get_option( self::FLAG );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		$code = isset( $_GET['moon_import'] ) ? sanitize_key( wp_unslash( $_GET['moon_import'] ) ) : '';
		?>
		<div class="wrap moon-admin">
			<h1><?php esc_html_e( 'راه‌اندازی MOON', 'moon-core' ); ?></h1>

			<?php if ( 'ok' === $code ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'محتوای نمایشی با موفقیت ساخته شد.', 'moon-core' ); ?></p></div>
			<?php elseif ( 'error' === $code ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'درون‌ریزی کامل نشد. گزارش زیر را بررسی کنید.', 'moon-core' ); ?></p></div>
			<?php endif; ?>

			<p><?php esc_html_e( 'این ابزار دسته‌بندی‌ها، ویژگی‌ها (سایز، رنگ، جنس)، ۱۲ محصول متغیر با Variations، صفحات فروشگاه و صفحه اصلی المنتور را می‌سازد. اجرای دوباره آن محتوای تکراری ایجاد نمی‌کند.', 'moon-core' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'moon_core_import' ); ?>
				<input type="hidden" name="action" value="moon_core_import" />
				<p>
					<button class="button button-primary" type="submit">
						<?php echo $done ? esc_html__( 'به‌روزرسانی محتوای نمایشی', 'moon-core' ) : esc_html__( 'ساخت محتوای نمایشی', 'moon-core' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle the import form.
	 */
	public static function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی مجاز نیست.', 'moon-core' ), 403 );
		}

		check_admin_referer( 'moon_core_import' );

		$result = self::run();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => 'moon-core-setup',
					'moon_import' => is_wp_error( $result ) ? 'error' : 'ok',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Run every import step.
	 *
	 * @return true|WP_Error
	 */
	public static function run() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return new WP_Error( 'no-woocommerce', __( 'ووکامرس فعال نیست.', 'moon-core' ) );
		}

		$categories = self::import_categories();
		$attributes = self::import_attributes();
		self::import_store_settings();
		$products = self::import_products( $categories, $attributes );
		self::import_pages();
		self::import_elementor_templates();
		self::import_menus();

		update_option( self::FLAG, time() );
		wc_delete_product_transients();

		unset( $products );

		return true;
	}

	/* ---------------------------------------------------------------------
	 * Terms and attributes
	 * ------------------------------------------------------------------ */

	/**
	 * Create or update a term.
	 *
	 * @param string $name     Term name.
	 * @param string $slug     Slug.
	 * @param string $taxonomy Taxonomy.
	 * @param int    $parent   Parent term id.
	 * @param int    $image_id Attachment id stored as term meta.
	 * @return int
	 */
	private static function term( $name, $slug, $taxonomy, $parent = 0, $image_id = 0 ) {
		$existing = get_term_by( 'slug', $slug, $taxonomy );

		if ( $existing instanceof WP_Term ) {
			$term_id = (int) $existing->term_id;
			wp_update_term( $term_id, $taxonomy, array( 'name' => $name, 'parent' => $parent ) );
		} else {
			$created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent ) );
			if ( is_wp_error( $created ) ) {
				return 0;
			}
			$term_id = (int) $created['term_id'];
		}

		if ( $image_id ) {
			update_term_meta( $term_id, 'thumbnail_id', $image_id );
		}

		return $term_id;
	}

	/**
	 * Product categories.
	 *
	 * @return array<string,int>
	 */
	public static function import_categories() {
		$map = array();

		$women_img = self::attach_image( 'category-women.jpg', __( 'کالکشن زنانه MOON', 'moon-core' ) );
		$men_img   = self::attach_image( 'category-men.jpg', __( 'کالکشن مردانه MOON', 'moon-core' ) );

		$map['women'] = self::term( __( 'زنانه', 'moon-core' ), 'women', 'product_cat', 0, $women_img );
		$map['men']   = self::term( __( 'مردانه', 'moon-core' ), 'men', 'product_cat', 0, $men_img );

		$children = array(
			'women-coats'       => array( __( 'پالتو و کت', 'moon-core' ), $map['women'] ),
			'women-dresses'     => array( __( 'پیراهن و بلوز', 'moon-core' ), $map['women'] ),
			'women-bottoms'     => array( __( 'شلوار و دامن', 'moon-core' ), $map['women'] ),
			'women-accessories' => array( __( 'شال و اکسسوری', 'moon-core' ), $map['women'] ),
			'men-jackets'       => array( __( 'کت و جلیقه', 'moon-core' ), $map['men'] ),
			'men-shirts'        => array( __( 'پیراهن', 'moon-core' ), $map['men'] ),
			'men-pants'         => array( __( 'شلوار', 'moon-core' ), $map['men'] ),
		);

		foreach ( $children as $slug => $data ) {
			$map[ $slug ] = self::term( $data[0], $slug, 'product_cat', (int) $data[1] );
		}

		return $map;
	}

	/**
	 * Create the pa_size / pa_color / pa_fabric attributes and their terms.
	 *
	 * @return array<string,array{id:int,terms:array<string,int>}>
	 */
	public static function import_attributes() {
		$definitions = array(
			'size'   => array(
				'name'  => __( 'سایز', 'moon-core' ),
				'terms' => array(
					'xs' => 'XS',
					's'  => 'S',
					'm'  => 'M',
					'l'  => 'L',
					'xl' => 'XL',
				),
			),
			'color'  => array(
				'name'  => __( 'رنگ', 'moon-core' ),
				'terms' => array(
					'black'    => __( 'مشکی', 'moon-core' ),
					'cream'    => __( 'کرم', 'moon-core' ),
					'gray'     => __( 'خاکستری', 'moon-core' ),
					'gold'     => __( 'طلایی مات', 'moon-core' ),
					'charcoal' => __( 'زغالی', 'moon-core' ),
					'white'    => __( 'سفید', 'moon-core' ),
				),
			),
			'fabric' => array(
				'name'  => __( 'جنس پارچه', 'moon-core' ),
				'terms' => array(
					'wool'     => __( 'پشم', 'moon-core' ),
					'cashmere' => __( 'کشمیر', 'moon-core' ),
					'silk'     => __( 'ابریشم', 'moon-core' ),
					'cotton'   => __( 'پنبه', 'moon-core' ),
					'linen'    => __( 'کتان', 'moon-core' ),
				),
			),
		);

		$existing = array();
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$existing[ $attribute->attribute_name ] = (int) $attribute->attribute_id;
		}

		$map = array();

		foreach ( $definitions as $slug => $definition ) {
			if ( isset( $existing[ $slug ] ) ) {
				$attribute_id = $existing[ $slug ];
			} else {
				$attribute_id = wc_create_attribute(
					array(
						'name'         => $definition['name'],
						'slug'         => $slug,
						'type'         => 'select',
						'order_by'     => 'menu_order',
						'has_archives' => false,
					)
				);

				if ( is_wp_error( $attribute_id ) ) {
					continue;
				}
				delete_transient( 'wc_attribute_taxonomies' );
			}

			$taxonomy = wc_attribute_taxonomy_name( $slug );

			if ( ! taxonomy_exists( $taxonomy ) ) {
				register_taxonomy(
					$taxonomy,
					array( 'product' ),
					array(
						'hierarchical' => false,
						'show_ui'      => false,
						'query_var'    => true,
						'rewrite'      => false,
					)
				);
			}

			$term_ids = array();
			foreach ( $definition['terms'] as $term_slug => $term_name ) {
				$term_ids[ $term_slug ] = self::term( $term_name, $term_slug, $taxonomy );
			}

			$map[ $slug ] = array(
				'id'    => (int) $attribute_id,
				'terms' => $term_ids,
			);
		}

		return $map;
	}

	/**
	 * Store settings so prices and checkout behave like a Persian shop.
	 */
	public static function import_store_settings() {
		$options = array(
			'woocommerce_currency'                => 'IRT',
			'woocommerce_price_num_decimals'      => 0,
			'woocommerce_currency_pos'            => 'right_space',
			'woocommerce_price_thousand_sep'      => '٬',
			'woocommerce_price_decimal_sep'       => '.',
			'woocommerce_default_country'         => 'IR:THR',
			'woocommerce_enable_guest_checkout'   => 'yes',
			'woocommerce_cart_redirect_after_add' => 'no',
			'woocommerce_enable_reviews'          => 'yes',
			'woocommerce_review_rating_required'  => 'no',
			'woocommerce_default_catalog_orderby' => 'date',
			'woocommerce_thumbnail_cropping'      => '3:4',
			'woocommerce_thumbnail_image_width'   => 600,
			'woocommerce_coming_soon'             => 'no',
		);

		foreach ( $options as $key => $value ) {
			update_option( $key, $value );
		}

		update_option( 'timezone_string', 'Asia/Tehran' );
		update_option( 'blogdescription', __( 'پوشاک لوکس و مدرن', 'moon-core' ) );
	}

	/* ---------------------------------------------------------------------
	 * Products
	 * ------------------------------------------------------------------ */

	/**
	 * The demo catalogue.
	 *
	 * @return array
	 */
	private static function catalogue() {
		return array(
			array(
				'name'     => __( 'پالتو کشمیر نویر', 'moon-core' ),
				'cat'      => 'women-coats',
				'price'    => 9850000,
				'sale'     => 7880000,
				'fabric'   => 'cashmere',
				'sizes'    => array( 's', 'm', 'l' ),
				'colors'   => array( 'black', 'charcoal' ),
				'featured' => true,
				'image'    => 'product-01.jpg',
				'desc'     => __( 'پالتوی بلند با یقه انگلیسی و دوخت دستی؛ کشمیر خالص با آستر ابریشمی.', 'moon-core' ),
			),
			array(
				'name'   => __( 'کت تک دکمه فلزی', 'moon-core' ),
				'cat'    => 'women-coats',
				'price'  => 6450000,
				'fabric' => 'wool',
				'sizes'  => array( 's', 'm', 'l' ),
				'colors' => array( 'cream', 'black' ),
				'image'  => 'product-02.jpg',
				'desc'   => __( 'کت ساختارمند با شانه‌های نرم و دکمه برنجی مات.', 'moon-core' ),
			),
			array(
				'name'     => __( 'پیراهن ابریشم ساتن', 'moon-core' ),
				'cat'      => 'women-dresses',
				'price'    => 4250000,
				'fabric'   => 'silk',
				'sizes'    => array( 'xs', 's', 'm' ),
				'colors'   => array( 'cream', 'gold' ),
				'featured' => true,
				'image'    => 'product-03.jpg',
				'desc'     => __( 'پیراهن ماکسی با درپینگ ملایم و درخشش مات ابریشم.', 'moon-core' ),
			),
			array(
				'name'   => __( 'بلوز آستین حجم‌دار', 'moon-core' ),
				'cat'    => 'women-dresses',
				'price'  => 3180000,
				'fabric' => 'cotton',
				'sizes'  => array( 'xs', 's', 'm' ),
				'colors' => array( 'white', 'black' ),
				'image'  => 'product-04.jpg',
				'desc'   => __( 'بلوز پنبه پیما با آستین بالونی و مچ کشباف.', 'moon-core' ),
			),
			array(
				'name'   => __( 'دامن میدی پلیسه', 'moon-core' ),
				'cat'    => 'women-bottoms',
				'price'  => 3950000,
				'fabric' => 'wool',
				'sizes'  => array( 's', 'm', 'l' ),
				'colors' => array( 'charcoal', 'black' ),
				'image'  => 'product-05.jpg',
				'desc'   => __( 'دامن پلیسه با حرکت روان و کمر بلند.', 'moon-core' ),
			),
			array(
				'name'   => __( 'شلوار پارچه پشمی', 'moon-core' ),
				'cat'    => 'women-bottoms',
				'price'  => 4150000,
				'sale'   => 3450000,
				'fabric' => 'wool',
				'sizes'  => array( 's', 'm', 'l' ),
				'colors' => array( 'gray', 'black' ),
				'image'  => 'product-06.jpg',
				'desc'   => __( 'شلوار دم‌پا گشاد با پارچه فاستونی سبک.', 'moon-core' ),
			),
			array(
				'name'     => __( 'کت بلند اورسایز', 'moon-core' ),
				'cat'      => 'men-jackets',
				'price'    => 8900000,
				'fabric'   => 'wool',
				'sizes'    => array( 'm', 'l', 'xl' ),
				'colors'   => array( 'charcoal', 'black' ),
				'featured' => true,
				'image'    => 'product-07.jpg',
				'desc'     => __( 'کت اوورسایز با آستر کتان و جیب‌های مخفی.', 'moon-core' ),
			),
			array(
				'name'   => __( 'جلیقه کتان', 'moon-core' ),
				'cat'    => 'men-jackets',
				'price'  => 3650000,
				'fabric' => 'linen',
				'sizes'  => array( 'm', 'l', 'xl' ),
				'colors' => array( 'cream', 'charcoal' ),
				'image'  => 'product-08.jpg',
				'desc'   => __( 'جلیقه چهار دکمه با پارچه کتان شسته.', 'moon-core' ),
			),
			array(
				'name'   => __( 'پیراهن پنبه مصری', 'moon-core' ),
				'cat'    => 'men-shirts',
				'price'  => 2980000,
				'fabric' => 'cotton',
				'sizes'  => array( 's', 'm', 'l', 'xl' ),
				'colors' => array( 'white', 'black' ),
				'image'  => 'product-09.jpg',
				'desc'   => __( 'پیراهن کلاسیک با یقه ایتالیایی و دوخت ۱۸ استیچ.', 'moon-core' ),
			),
			array(
				'name'     => __( 'پیراهن ابریشم مات', 'moon-core' ),
				'cat'      => 'men-shirts',
				'price'    => 4890000,
				'sale'     => 4150000,
				'fabric'   => 'silk',
				'sizes'    => array( 'm', 'l' ),
				'colors'   => array( 'black', 'cream' ),
				'featured' => true,
				'image'    => 'product-10.jpg',
				'desc'     => __( 'پیراهن ابریشم با درخشش خفیف برای شب‌های خاص.', 'moon-core' ),
			),
			array(
				'name'   => __( 'شلوار پارچه فاستونی', 'moon-core' ),
				'cat'    => 'men-pants',
				'price'  => 3750000,
				'fabric' => 'wool',
				'sizes'  => array( 'm', 'l', 'xl' ),
				'colors' => array( 'charcoal', 'gray' ),
				'image'  => 'product-11.jpg',
				'desc'   => __( 'شلوار راسته با کمر تنظیم‌شونده.', 'moon-core' ),
			),
			array(
				'name'   => __( 'شال کشمیر', 'moon-core' ),
				'cat'    => 'women-accessories',
				'price'  => 2450000,
				'fabric' => 'cashmere',
				'sizes'  => array( 's', 'm', 'l' ),
				'colors' => array( 'cream', 'gold' ),
				'image'  => 'product-12.jpg',
				'desc'   => __( 'شال پهن کشمیر با ریشه‌های دست‌بافت.', 'moon-core' ),
			),
		);
	}

	/**
	 * Create the variable products with real variations.
	 *
	 * @param array $categories Category ids by slug.
	 * @param array $attributes Attribute data.
	 * @return int[]
	 */
	public static function import_products( $categories, $attributes ) {
		$created = array();

		foreach ( self::catalogue() as $index => $data ) {
			$sku      = 'MOON-' . str_pad( (string) ( $index + 1 ), 3, '0', STR_PAD_LEFT );
			$existing = wc_get_product_id_by_sku( $sku );
			$product  = $existing ? wc_get_product( $existing ) : new WC_Product_Variable();

			if ( ! $product ) {
				continue;
			}

			$product->set_name( $data['name'] );
			$product->set_sku( $sku );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_description( $data['desc'] );
			$product->set_short_description( $data['desc'] );
			$product->set_featured( ! empty( $data['featured'] ) );

			if ( ! empty( $categories[ $data['cat'] ] ) ) {
				$product->set_category_ids( array( (int) $categories[ $data['cat'] ] ) );
			}

			$image_id = self::attach_image( $data['image'], $data['name'] );
			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}

			$product_attributes = array();

			if ( ! empty( $attributes['size'] ) ) {
				$size_options = array();
				foreach ( $data['sizes'] as $size_slug ) {
					if ( ! empty( $attributes['size']['terms'][ $size_slug ] ) ) {
						$size_options[] = (int) $attributes['size']['terms'][ $size_slug ];
					}
				}
				if ( $size_options ) {
					$attribute = new WC_Product_Attribute();
					$attribute->set_id( (int) $attributes['size']['id'] );
					$attribute->set_name( 'pa_size' );
					$attribute->set_options( $size_options );
					$attribute->set_position( 0 );
					$attribute->set_visible( true );
					$attribute->set_variation( true );
					$product_attributes[] = $attribute;
				}
			}

			if ( ! empty( $attributes['color'] ) ) {
				$color_options = array();
				foreach ( $data['colors'] as $color_slug ) {
					if ( ! empty( $attributes['color']['terms'][ $color_slug ] ) ) {
						$color_options[] = (int) $attributes['color']['terms'][ $color_slug ];
					}
				}
				if ( $color_options ) {
					$attribute = new WC_Product_Attribute();
					$attribute->set_id( (int) $attributes['color']['id'] );
					$attribute->set_name( 'pa_color' );
					$attribute->set_options( $color_options );
					$attribute->set_position( 1 );
					$attribute->set_visible( true );
					$attribute->set_variation( true );
					$product_attributes[] = $attribute;
				}
			}

			if ( ! empty( $attributes['fabric']['terms'][ $data['fabric'] ] ) ) {
				$attribute = new WC_Product_Attribute();
				$attribute->set_id( (int) $attributes['fabric']['id'] );
				$attribute->set_name( 'pa_fabric' );
				$attribute->set_options( array( (int) $attributes['fabric']['terms'][ $data['fabric'] ] ) );
				$attribute->set_position( 2 );
				$attribute->set_visible( true );
				$attribute->set_variation( false );
				$product_attributes[] = $attribute;
			}

			$product->set_attributes( $product_attributes );
			$parent_id = $product->save();

			if ( ! $parent_id ) {
				continue;
			}

			self::sync_variations( $parent_id, $data, $attributes );

			$created[] = (int) $parent_id;
		}

		return $created;
	}

	/**
	 * Rebuild the variations of one product.
	 *
	 * @param int   $parent_id  Parent product id.
	 * @param array $data       Product definition.
	 * @param array $attributes Attribute data.
	 */
	private static function sync_variations( $parent_id, $data, $attributes ) {
		$existing = array();

		foreach ( ( new WC_Product_Variable( $parent_id ) )->get_children() as $child_id ) {
			$child = wc_get_product( $child_id );
			if ( ! $child ) {
				continue;
			}
			$key              = self::variation_key( $child->get_attributes() );
			$existing[ $key ] = $child_id;
		}

		foreach ( $data['sizes'] as $size_slug ) {
			foreach ( $data['colors'] as $color_slug ) {
				$attrs = array(
					'pa_size'  => $size_slug,
					'pa_color' => $color_slug,
				);

				if ( empty( $attributes['size']['terms'][ $size_slug ] ) || empty( $attributes['color']['terms'][ $color_slug ] ) ) {
					continue;
				}

				$key       = self::variation_key( $attrs );
				$variation = isset( $existing[ $key ] ) ? wc_get_product( $existing[ $key ] ) : new WC_Product_Variation();

				if ( ! $variation ) {
					continue;
				}

				$variation->set_parent_id( $parent_id );
				$variation->set_attributes( $attrs );
				$variation->set_regular_price( (string) $data['price'] );
				$variation->set_sale_price( ! empty( $data['sale'] ) ? (string) $data['sale'] : '' );
				$variation->set_manage_stock( false );
				$variation->set_stock_status( 'instock' );
				$variation->set_sku( 'MOON-' . $parent_id . '-' . $size_slug . '-' . $color_slug );
				$variation->save();
			}
		}

		delete_transient( 'wc_product_children_' . $parent_id );
	}

	/**
	 * Stable key for a variation's attribute set.
	 *
	 * @param array $attributes Attributes.
	 * @return string
	 */
	private static function variation_key( $attributes ) {
		$normalized = array();

		foreach ( $attributes as $name => $value ) {
			$name                = str_replace( 'attribute_', '', $name );
			$normalized[ $name ] = (string) $value;
		}

		ksort( $normalized );

		return md5( (string) wp_json_encode( $normalized ) );
	}

	/* ---------------------------------------------------------------------
	 * Media
	 * ------------------------------------------------------------------ */

	/**
	 * Copy a bundled demo image into the media library (idempotent).
	 *
	 * @param string $filename File inside assets/images.
	 * @param string $title    Attachment title.
	 * @return int
	 */
	public static function attach_image( $filename, $title = '' ) {
		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_moon_demo_file', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $filename, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( $existing ) {
			return (int) $existing[0];
		}

		$source = MOON_CORE_PATH . 'assets/images/' . $filename;
		if ( ! file_exists( $source ) ) {
			return 0;
		}

		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}

		wp_mkdir_p( $upload['path'] );
		$destination = trailingslashit( $upload['path'] ) . wp_unique_filename( $upload['path'], $filename );

		if ( ! copy( $source, $destination ) ) {
			return 0;
		}

		$filetype = wp_check_filetype( basename( $destination ), null );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/jpeg',
				'post_title'     => $title ? $title : pathinfo( $filename, PATHINFO_FILENAME ),
				'post_status'    => 'inherit',
				'post_content'   => '',
			),
			$destination
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $destination ) );
		update_post_meta( $attachment_id, '_moon_demo_file', $filename );

		return (int) $attachment_id;
	}

	/* ---------------------------------------------------------------------
	 * Elementor documents
	 * ------------------------------------------------------------------ */

	/**
	 * Random Elementor element id.
	 *
	 * @return string
	 */
	private static function eid() {
		return substr( md5( uniqid( '', true ) ), 0, 7 );
	}

	/**
	 * Elementor container.
	 *
	 * @param array $elements Child elements.
	 * @param array $settings Container settings.
	 * @return array
	 */
	public static function container( $elements, $settings = array() ) {
		return array(
			'id'       => self::eid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array_merge(
				array(
					'content_width' => 'boxed',
					'flex_direction' => 'column',
				),
				$settings
			),
			'elements' => $elements,
		);
	}

	/**
	 * Elementor widget.
	 *
	 * @param string $type     Widget type.
	 * @param array  $settings Widget settings.
	 * @return array
	 */
	public static function widget( $type, $settings ) {
		return array(
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	/**
	 * Media settings helper.
	 *
	 * @param int $attachment_id Attachment id.
	 * @return array
	 */
	private static function media( $attachment_id ) {
		return array(
			'id'  => (int) $attachment_id,
			'url' => $attachment_id ? (string) wp_get_attachment_url( $attachment_id ) : '',
		);
	}

	/**
	 * Full-bleed container settings.
	 *
	 * @param array $extra Extra settings.
	 * @return array
	 */
	private static function bleeds( $extra = array() ) {
		return array_merge(
			array(
				'content_width' => 'full',
				'padding'       => array(
					'unit'     => 'px',
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'isLinked' => true,
				),
			),
			$extra
		);
	}

	/**
	 * Home page Elementor data.
	 *
	 * @return array
	 */
	public static function home_content() {
		$hero_poster = self::attach_image( 'hero-poster.jpg', __( 'هیرو MOON', 'moon-core' ) );
		$women       = self::attach_image( 'category-women.jpg', __( 'زنانه', 'moon-core' ) );
		$men         = self::attach_image( 'category-men.jpg', __( 'مردانه', 'moon-core' ) );
		$campaign    = self::attach_image( 'campaign.jpg', __( 'کمپین MOON', 'moon-core' ) );
		$story       = self::attach_image( 'story.jpg', __( 'آتلیه MOON', 'moon-core' ) );

		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

		return array(
			self::container(
				array(
					self::widget(
						'moon-cinematic-hero',
						array(
							'eyebrow'      => __( 'کالکشن پاییز / زمستان ۱۴۰۴', 'moon-core' ),
							'title'        => __( 'شب را با MOON بپوش', 'moon-core' ),
							'subtitle'     => __( 'برش‌های ساده، پارچه‌های سنگین و سکوتی که حرف می‌زند.', 'moon-core' ),
							'poster'       => self::media( $hero_poster ),
							'video_url'    => array(
								'id'  => 0,
								'url' => MOON_CORE_URL . 'assets/videos/hero.mp4',
							),
							'cta_one_text' => __( 'کالکشن زنانه', 'moon-core' ),
							'cta_one_url'  => array( 'url' => home_url( '/product-category/women/' ) ),
							'cta_two_text' => __( 'کالکشن مردانه', 'moon-core' ),
							'cta_two_url'  => array( 'url' => home_url( '/product-category/men/' ) ),
						)
					),
				),
				self::bleeds()
			),
			self::container(
				array(
					self::widget(
						'moon-category-cards',
						array(
							'cards' => array(
								array(
									'kicker'   => __( 'کالکشن', 'moon-core' ),
									'title'    => __( 'زنانه', 'moon-core' ),
									'subtitle' => __( 'خط‌های نرم، پارچه‌های سنگین', 'moon-core' ),
									'image'    => self::media( $women ),
									'link'     => array( 'url' => home_url( '/product-category/women/' ) ),
									'cta_text' => __( 'مشاهده کالکشن', 'moon-core' ),
								),
								array(
									'kicker'   => __( 'کالکشن', 'moon-core' ),
									'title'    => __( 'مردانه', 'moon-core' ),
									'subtitle' => __( 'ساختار، سکوت، دوخت دقیق', 'moon-core' ),
									'image'    => self::media( $men ),
									'link'     => array( 'url' => home_url( '/product-category/men/' ) ),
									'cta_text' => __( 'مشاهده کالکشن', 'moon-core' ),
								),
							),
						)
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '96', 'right' => '24', 'bottom' => '24', 'left' => '24', 'isLinked' => false ) ) )
			),
			self::container(
				array(
					self::widget(
						'moon-product-grid',
						array(
							'heading'    => __( 'جدیدترین محصولات', 'moon-core' ),
							'subheading' => __( 'تازه از آتلیه MOON', 'moon-core' ),
							'source'     => 'recent',
							'limit'      => 8,
							'columns'    => '4',
							'view_all'   => array( 'url' => $shop_url ),
						)
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '24', 'right' => '24', 'bottom' => '72', 'left' => '24', 'isLinked' => false ) ) )
			),
			self::container(
				array(
					self::widget(
						'moon-campaign-banner',
						array(
							'kicker'          => __( 'کمپین شب‌های ماه', 'moon-core' ),
							'title'           => __( 'تا ۳۰٪ تخفیف روی کالکشن شب', 'moon-core' ),
							'text'            => __( 'انتخاب محدود از پارچه‌های پشمی و ابریشم مات، فقط تا پایان فصل.', 'moon-core' ),
							'cta_text'        => __( 'خرید کالکشن', 'moon-core' ),
							'cta_url'         => array( 'url' => $shop_url ),
							'image'           => self::media( $campaign ),
							'show_countdown'  => 'yes',
							'countdown_end'   => gmdate( 'Y-m-d H:i', time() + ( 21 * DAY_IN_SECONDS ) ),
							'layout'          => 'overlay',
						)
					),
				),
				self::bleeds()
			),
			self::container(
				array(
					self::widget(
						'moon-product-grid',
						array(
							'heading'    => __( 'کالکشن منتخب فصل', 'moon-core' ),
							'subheading' => __( 'انتخاب استودیو', 'moon-core' ),
							'source'     => 'featured',
							'limit'      => 4,
							'columns'    => '4',
						)
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '88', 'right' => '24', 'bottom' => '32', 'left' => '24', 'isLinked' => false ) ) )
			),
			self::container(
				array(
					self::widget(
						'moon-product-grid',
						array(
							'heading'    => __( 'پرفروش‌ترین‌ها', 'moon-core' ),
							'subheading' => __( 'بیشترین انتخاب مشتریان MOON', 'moon-core' ),
							'source'     => 'best',
							'limit'      => 4,
							'columns'    => '4',
							'view_all'   => array( 'url' => $shop_url ),
						)
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '32', 'right' => '24', 'bottom' => '88', 'left' => '24', 'isLinked' => false ) ) )
			),
			self::container(
				array(
					self::widget(
						'moon-brand-story',
						array(
							'label'     => __( 'درباره MOON', 'moon-core' ),
							'title'     => __( 'سکوت، بهترین جنس لباس است', 'moon-core' ),
							'body'      => __( '<p>MOON از یک کارگاه کوچک شروع شد؛ با این باور که لباس لوکس نباید پرصدا باشد. خط‌های ساده، پارچه‌های دست‌چین و دوخت آرام، امضای ما هستند.</p>', 'moon-core' ),
							'signature' => __( 'MOON ATELIER — EST. 2019', 'moon-core' ),
							'image'     => self::media( $story ),
							'cta_text'  => __( 'درباره ما', 'moon-core' ),
							'cta_url'   => array( 'url' => home_url( '/about/' ) ),
						)
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '24', 'bottom' => '88', 'left' => '24', 'isLinked' => false ) ) )
			),
			self::container(
				array(
					self::widget(
						'moon-benefits',
						array(
							'items' => array(
								array(
									'icon'  => 'truck',
									'title' => __( 'ارسال سریع', 'moon-core' ),
									'text'  => __( 'تهران ۲۴ ساعت، سایر شهرها ۴۸ ساعت', 'moon-core' ),
								),
								array(
									'icon'  => 'refresh',
									'title' => __( '۷ روز مرجوعی', 'moon-core' ),
									'text'  => __( 'بدون پرسش، بدون هزینه', 'moon-core' ),
								),
								array(
									'icon'  => 'shield',
									'title' => __( 'اصالت پارچه', 'moon-core' ),
									'text'  => __( 'تضمین کیفیت دوخت و جنس', 'moon-core' ),
								),
								array(
									'icon'  => 'chat',
									'title' => __( 'مشاوره استایل', 'moon-core' ),
									'text'  => __( 'راهنمایی سایز توسط تیم MOON', 'moon-core' ),
								),
							),
						)
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '64', 'right' => '24', 'bottom' => '64', 'left' => '24', 'isLinked' => false ) ) )
			),
			self::container(
				array(
					self::widget(
						'shortcode',
						array( 'shortcode' => '[moon_newsletter heading="عضویت در خبرنامه MOON" text="کالکشن‌های محدود، پیش از انتشار."]' ),
					),
				),
				self::bleeds( array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) ) )
			),
		);
	}

	/**
	 * Header template Elementor data.
	 *
	 * @return array
	 */
	public static function header_content() {
		return array(
			self::container(
				array(
					self::widget(
						'moon-glass-header',
						array(
							'brand'         => (string) moon_core_setting( 'brand_name', 'MOON' ),
							'show_search'   => 'yes',
							'show_account'  => 'yes',
							'show_wishlist' => 'yes',
							'show_cart'     => 'yes',
							'show_toggle'   => 'yes',
							'sticky'        => 'yes',
							'shrink'        => 'yes',
						)
					),
				),
				self::bleeds()
			),
		);
	}

	/**
	 * Footer template Elementor data.
	 *
	 * @return array
	 */
	public static function footer_content() {
		return array(
			self::container(
				array(
					self::widget(
						'shortcode',
						array( 'shortcode' => '[moon_newsletter heading="عضویت در خبرنامه MOON" text="کالکشن بعدی را اول ببینید."]' ),
					),
					self::widget(
						'text-editor',
						array(
							'editor' => '<p>' . esc_html__( 'MOON — پوشاک لوکس و مدرن. تهران، خیابان ولیعصر.', 'moon-core' ) . '</p>',
						)
					),
				),
				array(
					'content_width' => 'boxed',
					'padding'       => array( 'unit' => 'px', 'top' => '76', 'right' => '24', 'bottom' => '76', 'left' => '24', 'isLinked' => false ),
					'background_background' => 'classic',
					'background_color'      => '#11110F',
				)
			),
		);
	}

	/**
	 * Create or update an Elementor document.
	 *
	 * @param string $key      Stable key used to find the document.
	 * @param array  $document Document definition.
	 * @return int
	 */
	public static function upsert_document( $key, $document ) {
		$existing = get_posts(
			array(
				'post_type'      => $document['post_type'],
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'post_status'    => 'any',
				'meta_key'       => '_moon_doc', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		$postarr = array(
			'post_title'   => $document['title'],
			'post_name'    => isset( $document['slug'] ) ? $document['slug'] : '',
			'post_status'  => 'publish',
			'post_type'    => $document['post_type'],
			'post_content' => isset( $document['content'] ) ? $document['content'] : '',
		);

		if ( $existing ) {
			$postarr['ID'] = (int) $existing[0];
			$post_id       = wp_update_post( $postarr );
		} else {
			$post_id = wp_insert_post( $postarr );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		update_post_meta( $post_id, '_moon_doc', $key );

		if ( ! empty( $document['elementor'] ) ) {
			update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $post_id, '_elementor_template_type', $document['elementor_type'] );
			update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $document['elementor'] ) ) );
			update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
			// Elementor caches rendered markup and generated CSS per document; drop
			// both so a re-import is reflected immediately.
			delete_post_meta( $post_id, '_elementor_css' );
			delete_post_meta( $post_id, '_elementor_element_cache' );
			delete_post_meta( $post_id, '_elementor_page_assets' );
		}

		return (int) $post_id;
	}

	/**
	 * Pages, wishlist page and the static front page.
	 */
	public static function import_pages() {
		$pages = array(
			'home' => array(
				'title'     => __( 'صفحه اصلی', 'moon-core' ),
				'slug'      => 'home',
				'elementor' => self::home_content(),
			),
			'about' => array(
				'title'     => __( 'درباره ما', 'moon-core' ),
				'slug'      => 'about',
				'elementor' => array(
					self::container(
						array(
							self::widget(
								'heading',
								array(
									'title'       => __( 'درباره MOON', 'moon-core' ),
									'header_size' => 'h1',
								)
							),
							self::widget(
								'text-editor',
								array(
									'editor' => '<p>' . esc_html__( 'MOON یک برند پوشاک لوکس ایرانی است که از سال ۱۳۹۸ در تهران فعالیت می‌کند. تمرکز ما بر پارچه‌های طبیعی، برش‌های ساده و دوخت ماندگار است.', 'moon-core' ) . '</p>',
								)
							),
						),
						array( 'padding' => array( 'unit' => 'px', 'top' => '120', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) )
					),
					self::container(
						array(
							self::widget(
								'moon-brand-story',
								array(
									'label'     => __( 'آتلیه MOON', 'moon-core' ),
									'title'     => __( 'هر دوخت، یک تصمیم آرام', 'moon-core' ),
									'body'      => '<p>' . esc_html__( 'تیم ما از ۱۲ نفر تشکیل شده است: طراح، الگوساز، خیاط و کنترل کیفیت. هر قطعه پیش از ارسال، سه بار بازرسی می‌شود.', 'moon-core' ) . '</p>',
									'signature' => __( 'MOON ATELIER', 'moon-core' ),
									'image'     => self::media( self::attach_image( 'story.jpg', __( 'آتلیه MOON', 'moon-core' ) ) ),
								)
							),
						),
						array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) )
					),
				),
			),
			'contact' => array(
				'title'     => __( 'تماس با ما', 'moon-core' ),
				'slug'      => 'contact',
				'elementor' => array(
					self::container(
						array(
							self::widget(
								'heading',
								array(
									'title'       => __( 'تماس با MOON', 'moon-core' ),
									'header_size' => 'h1',
								)
							),
							self::widget(
								'text-editor',
								array(
									'editor' => '<p>' . esc_html__( 'تهران، خیابان ولیعصر، برج نگین، طبقه ۷ — شنبه تا پنجشنبه ۱۰ تا ۱۹', 'moon-core' ) . '<br />' .
										esc_html__( 'تلفن: ۰۲۱-۹۱۰۰۲۲۰۰ — ایمیل: hello@moon.example', 'moon-core' ) . '</p>',
								)
							),
							self::widget(
								'shortcode',
								array( 'shortcode' => '[moon_contact_form]' ),
							),
						),
						array( 'padding' => array( 'unit' => 'px', 'top' => '120', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) )
					),
				),
			),
			'faq' => array(
				'title'     => __( 'سوالات متداول', 'moon-core' ),
				'slug'      => 'faq',
				'elementor' => array(
					self::container(
						array(
							self::widget(
								'heading',
								array(
									'title'       => __( 'سوالات متداول', 'moon-core' ),
									'header_size' => 'h1',
								)
							),
							self::widget(
								'accordion',
								array(
									'tabs' => array(
										array(
											'tab_title'   => __( 'سایز مناسب را چطور انتخاب کنم؟', 'moon-core' ),
											'tab_content' => '<p>' . esc_html__( 'در هر صفحه محصول، تب «راهنمای سایز» اندازه‌های دقیق را نشان می‌دهد. در صورت تردید یک سایز بزرگ‌تر انتخاب کنید.', 'moon-core' ) . '</p>',
										),
										array(
											'tab_title'   => __( 'ارسال چند روز طول می‌کشد؟', 'moon-core' ),
											'tab_content' => '<p>' . esc_html__( 'تهران ۲۴ ساعت کاری و سایر شهرها ۴۸ ساعت کاری.', 'moon-core' ) . '</p>',
										),
										array(
											'tab_title'   => __( 'شرایط مرجوعی چیست؟', 'moon-core' ),
											'tab_content' => '<p>' . esc_html__( 'تا ۷ روز پس از دریافت، در صورت نداشتن اثر استفاده، مرجوعی رایگان است.', 'moon-core' ) . '</p>',
										),
									),
								)
							),
						),
						array( 'padding' => array( 'unit' => 'px', 'top' => '120', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) )
					),
				),
			),
			'terms' => array(
				'title'     => __( 'قوانین و مقررات', 'moon-core' ),
				'slug'      => 'terms',
				'elementor' => array(
					self::container(
						array(
							self::widget(
								'heading',
								array(
									'title'       => __( 'قوانین و مقررات', 'moon-core' ),
									'header_size' => 'h1',
								)
							),
							self::widget(
								'text-editor',
								array(
									'editor' => '<p>' . esc_html__( 'سفارش‌ها پس از پرداخت وارد مرحله آماده‌سازی می‌شوند. قیمت‌ها به تومان و شامل مالیات بر ارزش افزوده است.', 'moon-core' ) . '</p><p>' .
										esc_html__( 'در صورت انصراف پیش از ارسال، مبلغ کامل بازگردانده می‌شود. اطلاعات مشتریان تنها برای پردازش سفارش استفاده می‌شود.', 'moon-core' ) . '</p>',
								)
							),
						),
						array( 'padding' => array( 'unit' => 'px', 'top' => '120', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) )
					),
				),
			),
			'track-order' => array(
				'title'     => __( 'پیگیری سفارش', 'moon-core' ),
				'slug'      => 'track-order',
				'elementor' => array(
					self::container(
						array(
							self::widget(
								'heading',
								array(
									'title'       => __( 'پیگیری سفارش', 'moon-core' ),
									'header_size' => 'h1',
								)
							),
							self::widget(
								'shortcode',
								array( 'shortcode' => '[woocommerce_order_tracking]' ),
							),
						),
						array( 'padding' => array( 'unit' => 'px', 'top' => '120', 'right' => '24', 'bottom' => '96', 'left' => '24', 'isLinked' => false ) )
					),
				),
			),
			'wishlist' => array(
				'title'     => __( 'علاقه‌مندی‌ها', 'moon-core' ),
				'slug'      => 'wishlist',
				'content'   => '[moon_wishlist]',
			),
		);

		$ids = array();

		foreach ( $pages as $key => $page ) {
			$document = array(
				'post_type' => 'page',
				'title'     => $page['title'],
				'slug'      => $page['slug'],
				'content'   => isset( $page['content'] ) ? $page['content'] : '',
			);

			if ( isset( $page['elementor'] ) ) {
				$document['elementor']      = $page['elementor'];
				$document['elementor_type'] = 'wp-page';
			}

			$ids[ $key ] = self::upsert_document( 'page-' . $key, $document );
		}

		if ( ! empty( $ids['wishlist'] ) ) {
			update_option( 'moon_core_wishlist_page', (int) $ids['wishlist'] );
		}

		if ( ! empty( $ids['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $ids['home'] );
		}

		return $ids;
	}

	/**
	 * Header and footer Elementor templates (used by Elementor Pro Theme Builder).
	 */
	public static function import_elementor_templates() {
		self::upsert_document(
			'template-header',
			array(
				'post_type'      => 'elementor_library',
				'title'          => 'MOON — Header',
				'slug'           => 'moon-header',
				'elementor'      => self::header_content(),
				'elementor_type' => 'header',
			)
		);

		self::upsert_document(
			'template-footer',
			array(
				'post_type'      => 'elementor_library',
				'title'          => 'MOON — Footer',
				'slug'           => 'moon-footer',
				'elementor'      => self::footer_content(),
				'elementor_type' => 'footer',
			)
		);
	}

	/**
	 * Primary menu with the shop links.
	 */
	public static function import_menus() {
		$menu_name = __( 'منوی اصلی MOON', 'moon-core' );
		$menu      = wp_get_nav_menu_object( $menu_name );

		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
		} else {
			$menu_id = (int) $menu->term_id;
		}

		if ( is_wp_error( $menu_id ) || ! $menu_id ) {
			return;
		}

		$items = array(
			array( __( 'فروشگاه', 'moon-core' ), function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ),
			array( __( 'زنانه', 'moon-core' ), home_url( '/product-category/women/' ) ),
			array( __( 'مردانه', 'moon-core' ), home_url( '/product-category/men/' ) ),
			array( __( 'درباره ما', 'moon-core' ), home_url( '/about/' ) ),
			array( __( 'تماس با ما', 'moon-core' ), home_url( '/contact/' ) ),
		);

		$existing = wp_get_nav_menu_items( $menu_id );
		$titles   = array();
		if ( is_array( $existing ) ) {
			foreach ( $existing as $item ) {
				$titles[] = $item->title;
			}
		}

		foreach ( $items as $item ) {
			if ( in_array( $item[0], $titles, true ) ) {
				continue;
			}

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => $item[0],
					'menu-item-url'    => $item[1],
					'menu-item-status' => 'publish',
				)
			);
		}

		$locations          = get_theme_mod( 'nav_menu_locations', array() );
		$locations['menu-1'] = (int) $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
