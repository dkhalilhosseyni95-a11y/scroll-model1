<?php
/**
 * Catalogue upgrade: colour attributes, colourway variations and look pages.
 *
 * The original demo shipped simple products and single-piece "styles". This step
 * turns the catalogue into a real WooCommerce one — `pa_color` + `pa_size`
 * variations, a colour image per colourway, kid/accessory categories and
 * multi-piece looks — without deleting anything that already exists.
 *
 * Run with `wp moon-demo catalog`. Idempotent: colours, variations and look
 * links are only added when missing.
 *
 * @package Moon_Demo_Importer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catalogue structure.
 */
class Moon_Catalog {

	/**
	 * Colour palette used for the demo attributes.
	 *
	 * @return array slug => [name, hex]
	 */
	private static function colors() {
		return array(
			'black'    => array( 'مشکی', '#101010' ),
			'snow'     => array( 'استخوانی', '#F5F1E9' ),
			'cream'    => array( 'کرم', '#EFE7DA' ),
			'sand'     => array( 'شنی', '#D8C9B0' ),
			'camel'    => array( 'شتری', '#C8A97E' ),
			'brown'    => array( 'قهوه‌ای', '#5B4A3A' ),
			'charcoal' => array( 'ذغالی', '#3A3A3A' ),
			'grey'     => array( 'خاکستری', '#99958F' ),
			'blue'     => array( 'آبی روشن', '#CFD8E3' ),
			'olive'    => array( 'زیتونی', '#6B6B4F' ),
			'wine'     => array( 'زغالی', '#6E1F2B' ),
			'navy'     => array( 'سرمه‌ای', '#1F2A44' ),
		);
	}

	/**
	 * Colourways per demo product: slug => [ [color slug, image file] ].
	 *
	 * @return array
	 */
	private static function colorways() {
		return array(
			'luna-wool-coat'         => array(
				array( 'black', 'women.jpg' ),
				array( 'camel', 'g-coat.webp' ),
				array( 'grey', 'hero.jpg' ),
			),
			'dune-hoodie'            => array(
				array( 'black', 'outfit-1.jpg' ),
				array( 'sand', 'g-hoodie.webp' ),
				array( 'snow', 'atelier.jpg' ),
			),
			'eclipse-leather'        => array(
				array( 'black', 'outfit-4.jpg' ),
				array( 'brown', 'g-jacket.webp' ),
			),
			'nova-poplin-shirt'      => array(
				array( 'snow', 'outfit-3.jpg' ),
				array( 'blue', 'g-shirt.webp' ),
				array( 'black', 'men.jpg' ),
			),
			'carbon-crewneck'        => array(
				array( 'charcoal', 'outfit-2.jpg' ),
				array( 'sand', 'g-sweat.webp' ),
			),
			'ivory-silk-set'         => array(
				array( 'snow', 'campaign.jpg' ),
				array( 'cream', 'women.jpg' ),
			),
			'charcoal-overcoat'      => array(
				array( 'charcoal', 'men.jpg' ),
				array( 'camel', 'g-coat.webp' ),
			),
			'slate-cotton-trousers'  => array(
				array( 'grey', 'outfit-5.jpg' ),
				array( 'sand', 'hero.jpg' ),
			),
			'moon-wool-scarf'        => array(
				array( 'camel', 'atelier.jpg' ),
				array( 'charcoal', 'g-coat.webp' ),
				array( 'wine', 'campaign.jpg' ),
			),
			'ava-silk-scarf'         => array(
				array( 'snow', 'women.jpg' ),
				array( 'navy', 'hero.jpg' ),
			),
			'nova-kids-coat'         => array(
				array( 'sand', 'outfit-3.jpg' ),
				array( 'black', 'men.jpg' ),
			),
			'abr-kids-hoodie'        => array(
				array( 'grey', 'outfit-1.jpg' ),
				array( 'blue', 'g-hoodie.webp' ),
			),
			'linen-shirt-atlas'      => array(
				array( 'snow', 'outfit-3.jpg' ),
				array( 'olive', 'g-shirt.webp' ),
			),
			'dune-knit-vest'         => array(
				array( 'cream', 'outfit-2.jpg' ),
				array( 'charcoal', 'g-sweat.webp' ),
			),
		);
	}

	/**
	 * Extra demo products created by the upgrade.
	 *
	 * @return array
	 */
	private static function extra_products() {
		return array(
			array(
				'name'    => 'شال پشمی مون',
				'slug'    => 'moon-wool-scarf',
				'price'   => '1900000',
				'cat'     => 'scarves',
				'image'   => 'atelier.jpg',
				'desc'    => 'شال پشمی مرینوس با بافت نرم و حاشیه ریشه‌دار؛ گرمای سبک برای روزهای سرد.',
				'sizes'   => array( 'ONE SIZE' ),
			),
			array(
				'name'    => 'روسری ابریشم آوا',
				'slug'    => 'ava-silk-scarf',
				'price'   => '2400000',
				'sale'    => '1920000',
				'cat'     => 'scarves',
				'image'   => 'women.jpg',
				'desc'    => 'روسری ابریشم خالص با چاپ دستی و لبه‌های دست‌دوز؛ سبک، خنک و درخشان.',
				'sizes'   => array( 'ONE SIZE' ),
			),
			array(
				'name'    => 'پالتو کودک نوا',
				'slug'    => 'nova-kids-coat',
				'price'   => '5200000',
				'cat'     => 'kids',
				'image'   => 'outfit-3.jpg',
				'desc'    => 'پالتوی سبک کودک با آستر پنبه‌ای و دکمه‌های چوبی؛ آزاد برای حرکت راحت.',
				'sizes'   => array( '2-3Y', '4-5Y', '6-7Y', '8-9Y' ),
			),
			array(
				'name'    => 'هودی کودک ابر',
				'slug'    => 'abr-kids-hoodie',
				'price'   => '2300000',
				'cat'     => 'kids',
				'image'   => 'outfit-1.jpg',
				'desc'    => 'هودی کودک از پنبه ارگانیک با کلاه دو لایه و کشسانی ملایم.',
				'sizes'   => array( '2-3Y', '4-5Y', '6-7Y', '8-9Y' ),
			),
			array(
				'name'    => 'پیراهن کتان اطلس',
				'slug'    => 'linen-shirt-atlas',
				'price'   => '3400000',
				'cat'     => 'men',
				'image'   => 'outfit-3.jpg',
				'desc'    => 'پیراهن کتان با یقه باز و برش راحت؛ مناسب روزهای گرم و استایل لایه‌ای.',
				'sizes'   => array( 'S', 'M', 'L', 'XL' ),
			),
			array(
				'name'    => 'جلیقه بافت دون',
				'slug'    => 'dune-knit-vest',
				'price'   => '3900000',
				'cat'     => 'women',
				'image'   => 'outfit-2.jpg',
				'desc'    => 'جلیقه بافت درشت با یقه هفت و بافت نرم؛ لایه‌ای ایده‌آل روی پیراهن.',
				'sizes'   => array( 'S', 'M', 'L', 'XL' ),
			),
		);
	}

	/**
	 * Looks (multi-piece styles) and the products each one is built from.
	 *
	 * @return array
	 */
	private static function looks() {
		return array(
			'هودی اورسایز مشکی'    => array(
				'products'  => array( 'dune-hoodie', 'slate-cotton-trousers', 'moon-wool-scarf' ),
				'desc'      => 'هودی اورسایز روی شلوار کتان و شال پشمی؛ راحت، لایه‌ای و آماده شهر.',
			),
			'سویشرت بژ'            => array(
				'products'  => array( 'carbon-crewneck', 'slate-cotton-trousers', 'dune-knit-vest' ),
				'desc'      => 'ترکیب بافت‌های خنثی: سویشرت شنی، جلیقه بافت و شلوار کتان.',
			),
			'پیراهن سفید مینیمال'  => array(
				'products'  => array( 'nova-poplin-shirt', 'slate-cotton-trousers', 'ava-silk-scarf' ),
				'desc'      => 'پیراهن پاپلین سفید با شلوار اسلیت و روسری ابریشم؛ مینیمال و تمام‌شده.',
			),
			'کاپشن چرم مشکی'       => array(
				'products'  => array( 'eclipse-leather', 'nova-poplin-shirt', 'slate-cotton-trousers' ),
				'desc'      => 'کاپشن چرم روی پیراهن پاپلین؛ کنتراست جنس و رنگ در ساده‌ترین شکل.',
			),
			'استایل لایه‌ای'        => array(
				'products'  => array( 'charcoal-overcoat', 'linen-shirt-atlas', 'slate-cotton-trousers', 'moon-wool-scarf' ),
				'desc'      => 'پالتوی چارکول، پیراهن کتان و شال پشمی؛ فرمول سه‌لایه زمستان شهری.',
			),
			'استایل زنانه زمستانی' => array(
				'products'  => array( 'luna-wool-coat', 'dune-knit-vest', 'slate-cotton-trousers', 'ava-silk-scarf' ),
				'desc'      => 'پالتو پشمی لونا با جلیقه بافت و روسری ابریشم؛ گرم، آرام و شهری.',
				'en'        => 'Winter Layers',
				'colors'    => '#101010, #C8A97E, #6E1F2B',
			),
			'استایل کودک'          => array(
				'products'  => array( 'nova-kids-coat', 'abr-kids-hoodie' ),
				'desc'      => 'پالتوی سبک و هودی پنبه‌ای برای کودک؛ آزاد، نرم و مقاوم.',
				'en'        => 'Little Moon',
				'colors'    => '#D8C9B0, #101010',
			),
		);
	}

	/**
	 * Base prices per demo product (regular, sale).
	 *
	 * @return array
	 */
	private static function prices() {
		return array(
			'luna-wool-coat'        => array( 18900000, 0 ),
			'dune-hoodie'           => array( 4200000, 3360000 ),
			'eclipse-leather'       => array( 24500000, 0 ),
			'nova-poplin-shirt'     => array( 2800000, 0 ),
			'carbon-crewneck'       => array( 3600000, 0 ),
			'ivory-silk-set'        => array( 9800000, 0 ),
			'charcoal-overcoat'     => array( 16400000, 0 ),
			'slate-cotton-trousers' => array( 3200000, 0 ),
			'moon-wool-scarf'       => array( 1900000, 0 ),
			'ava-silk-scarf'        => array( 2400000, 1920000 ),
			'nova-kids-coat'        => array( 5200000, 0 ),
			'abr-kids-hoodie'       => array( 2300000, 0 ),
			'linen-shirt-atlas'     => array( 3400000, 0 ),
			'dune-knit-vest'        => array( 3900000, 0 ),
		);
	}

	/**
	 * Run the upgrade.
	 *
	 * @return array Report.
	 */
	public static function upgrade() {
		$report = array(
			'messages' => array(),
			'errors'   => array(),
		);

		if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) ) {
			$report['errors'][] = __( 'ووکامرس فعال نیست.', 'moon-theme' );

			return $report;
		}

		self::ensure_categories();
		$colors = self::ensure_color_attribute();
		self::ensure_sizes();

		$map = get_option( 'moon_core_product_map', array() );
		$map = is_array( $map ) ? $map : array();

		$map = array_merge( $map, self::create_extra_products() );

		update_option( 'moon_core_product_map', $map, false );

		$report['messages'][] = __( 'دسته‌بندی‌های کودکان و شال و روسری آماده شد.', 'moon-theme' );

		foreach ( self::colorways() as $slug => $rows ) {
			if ( empty( $map[ $slug ] ) ) {
				continue;
			}

			$result = self::build_variations( (int) $map[ $slug ], $rows, $colors );

			if ( $result ) {
				$report['messages'][] = sprintf(
					/* translators: 1: product name, 2: colour count. */
					__( '«%1$s» با %2$s رنگ‌بندی آماده شد.', 'moon-theme' ),
					get_the_title( (int) $map[ $slug ] ),
					number_format_i18n( $result )
				);
			}
		}

		self::link_looks( $map );
		$report['messages'][] = __( 'استایل‌ها به چند قلم لباس متصل شدند.', 'moon-theme' );

		self::refresh_size_guide();
		$report['messages'][] = __( 'صفحه راهنمای سایز به‌روزرسانی شد.', 'moon-theme' );

		self::update_menu();
		self::update_footer_links();

		update_option( 'moon_core_catalog_version', MOON_THEME_VERSION, false );

		return $report;
	}

	/* ----------------------------------------------------------------------
	 * Terms
	 * -------------------------------------------------------------------- */

	/**
	 * Extra product categories.
	 */
	private static function ensure_categories() {
		$terms = array(
			'kids'    => 'کودکان',
			'scarves' => 'شال و روسری',
		);

		foreach ( $terms as $slug => $name ) {
			if ( term_exists( $slug, 'product_cat' ) ) {
				continue;
			}

			wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
	}

	/**
	 * Create the pa_color attribute and its terms.
	 *
	 * @return array Slug => term id.
	 */
	private static function ensure_color_attribute() {
		global $wpdb;

		$taxonomy = 'pa_color';

		if ( ! wc_attribute_taxonomy_id_by_name( 'color' ) ) {
			$existing = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", 'color' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			if ( ! $existing ) {
				wc_create_attribute( array(
					'name'         => 'رنگ',
					'slug'         => 'color',
					'type'         => 'color',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				) );
			}

			delete_transient( 'wc_attribute_taxonomies' );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false ) );
		}

		$ids = array();

		foreach ( self::colors() as $slug => $row ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );

			if ( ! $term || is_wp_error( $term ) ) {
				$created = wp_insert_term( $row[0], $taxonomy, array( 'slug' => $slug ) );

				if ( is_wp_error( $created ) ) {
					continue;
				}

				$term_id = (int) $created['term_id'];
			} else {
				$term_id = (int) $term->term_id;
			}

			update_term_meta( $term_id, 'moon_hex', $row[1] );
			$ids[ $slug ] = $term_id;
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients();
		}

		return $ids;
	}

	/**
	 * Make sure pa_size exists with the adult and kid size terms.
	 */
	private static function ensure_sizes() {
		global $wpdb;

		if ( ! wc_attribute_taxonomy_id_by_name( 'size' ) ) {
			$existing = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", 'size' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			if ( ! $existing ) {
				wc_create_attribute( array(
					'name'         => 'سایز',
					'slug'         => 'size',
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				) );

				delete_transient( 'wc_attribute_taxonomies' );
			}
		}

		if ( ! taxonomy_exists( 'pa_size' ) ) {
			register_taxonomy( 'pa_size', array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false ) );
		}

		$sizes = array( 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'ONE SIZE', '2-3Y', '4-5Y', '6-7Y', '8-9Y', '10-11Y' );

		foreach ( $sizes as $size ) {
			if ( ! get_term_by( 'name', $size, 'pa_size' ) ) {
				wp_insert_term( $size, 'pa_size' );
			}
		}
	}

	/* ----------------------------------------------------------------------
	 * Products
	 * -------------------------------------------------------------------- */

	/**
	 * Create the extra demo products (scarves and kids) as variable products.
	 *
	 * @return array slug => id
	 */
	private static function create_extra_products() {
		$media = get_option( 'moon_core_media', array() );
		$media = is_array( $media ) ? $media : array();
		$map   = array();

		foreach ( self::extra_products() as $item ) {
			$existing = get_page_by_path( $item['slug'], OBJECT, 'product' );

			if ( $existing ) {
				$map[ $item['slug'] ] = $existing->ID;
				continue;
			}

			try {
				$product = new WC_Product_Variable();
				$product->set_name( $item['name'] );
				$product->set_slug( $item['slug'] );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
				$product->set_description( $item['desc'] );
				$product->set_short_description( wp_trim_words( $item['desc'], 24 ) );

				$image_id = isset( $media[ $item['image'] ] ) ? (int) $media[ $item['image'] ] : 0;

				if ( $image_id ) {
					$product->set_image_id( $image_id );
				}

				$term = get_term_by( 'slug', $item['cat'], 'product_cat' );

				if ( $term && ! is_wp_error( $term ) ) {
					$product->set_category_ids( array( $term->term_id ) );
				}

				$product->set_regular_price( $item['price'] );

				if ( ! empty( $item['sale'] ) ) {
					$product->set_sale_price( $item['sale'] );
				}

				$product->set_attributes( self::attributes_for( $item['sizes'], array_keys( self::colors() ) ) );
				$product->save();

				update_post_meta( $product->get_id(), '_moon_base_price', $item['price'] );
				update_post_meta( $product->get_id(), '_moon_base_sale', ! empty( $item['sale'] ) ? $item['sale'] : '' );
				update_post_meta( $product->get_id(), '_moon_sizes', implode( ',', $item['sizes'] ) );

				$map[ $item['slug'] ] = $product->get_id();
			} catch ( Throwable $error ) {
				continue;
			}
		}

		return $map;
	}

	/**
	 * Attribute objects for a product.
	 *
	 * @param array $sizes Size names.
	 * @return array
	 */
	private static function attributes_for( $sizes, $color_slugs = null ) {
		$attributes = array();
		$color_id   = (int) wc_attribute_taxonomy_id_by_name( 'color' );
		$size_id    = (int) wc_attribute_taxonomy_id_by_name( 'size' );

		$color_terms = array();
		$color_slugs = null === $color_slugs ? array_keys( self::colors() ) : (array) $color_slugs;

		foreach ( $color_slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, 'pa_color' );

			if ( $term && ! is_wp_error( $term ) ) {
				$color_terms[] = (int) $term->term_id;
			}
		}

		$size_terms = array();

		foreach ( $sizes as $size ) {
			$term = get_term_by( 'name', $size, 'pa_size' );

			if ( $term && ! is_wp_error( $term ) ) {
				$size_terms[] = (int) $term->term_id;
			}
		}

		if ( $color_terms ) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_id( $color_id );
			$attribute->set_name( 'pa_color' );
			$attribute->set_options( $color_terms );
			$attribute->set_position( 0 );
			$attribute->set_visible( true );
			$attribute->set_variation( true );
			$attributes[] = $attribute;
		}

		if ( $size_terms ) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_id( $size_id );
			$attribute->set_name( 'pa_size' );
			$attribute->set_options( $size_terms );
			$attribute->set_position( 1 );
			$attribute->set_visible( true );
			$attribute->set_variation( true );
			$attributes[] = $attribute;
		}

		return $attributes;
	}

	/**
	 * Rebuild a product as a colour × size variable product.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $rows       Colour rows: [slug, image file].
	 * @param array $colors     Colour slug => term id.
	 * @return int Number of colours applied.
	 */
	private static function build_variations( $product_id, $rows, $colors ) {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return 0;
		}

		$media = get_option( 'moon_core_media', array() );
		$media = is_array( $media ) ? $media : array();

		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();

		if ( $regular <= 0 ) {
			$regular = (float) get_post_meta( $product_id, '_moon_base_price', true );
		}

		if ( $sale <= 0 ) {
			$sale = (float) get_post_meta( $product_id, '_moon_base_sale', true );
		}

		$slugs = self::prices();
		$slug  = $product->get_slug();

		if ( isset( $slugs[ $slug ] ) ) {
			if ( $regular <= 0 ) {
				$regular = (float) $slugs[ $slug ][0];
			}

			if ( $sale <= 0 && ! empty( $slugs[ $slug ][1] ) ) {
				$sale = (float) $slugs[ $slug ][1];
			}
		}

		if ( $regular > 0 ) {
			update_post_meta( $product_id, '_moon_base_price', $regular );
		}

		if ( $sale > 0 ) {
			update_post_meta( $product_id, '_moon_base_sale', $sale );
		}

		$sizes = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $product_id, '_moon_sizes', true ) ) ) );

		if ( empty( $sizes ) ) {
			if ( has_term( 'kids', 'product_cat', $product_id ) ) {
				$sizes = array( '2-3Y', '4-5Y', '6-7Y', '8-9Y' );
			} elseif ( has_term( array( 'scarves', 'accessories' ), 'product_cat', $product_id ) ) {
				$sizes = array( 'ONE SIZE' );
			} else {
				$sizes = array( 'S', 'M', 'L', 'XL' );
			}
		}

		$used_colors = array();

		foreach ( $rows as $row ) {
			if ( isset( $colors[ $row[0] ] ) ) {
				$used_colors[] = $row[0];
			}
		}

		if ( empty( $used_colors ) ) {
			return 0;
		}

		$variables = array();

		foreach ( $sizes as $size ) {
			$term = get_term_by( 'name', $size, 'pa_size' );

			if ( $term && ! is_wp_error( $term ) ) {
				$variables[ $size ] = $term->slug;
			}
		}

		if ( empty( $variables ) ) {
			return 0;
		}

		// Switch the product to a variable one, keeping its content.
		if ( ! $product->is_type( 'variable' ) ) {
			$variable = new WC_Product_Variable( $product_id );
			$variable->set_name( $product->get_name() );
			$variable->set_status( 'publish' );
			$variable->set_description( $product->get_description() );
			$variable->set_short_description( $product->get_short_description() );
			$variable->set_category_ids( $product->get_category_ids() );
			$variable->set_image_id( $product->get_image_id() );
			$variable->set_gallery_image_ids( $product->get_gallery_image_ids() );
			$variable->save();
		}

		$product = wc_get_product( $product_id );
		$product->set_attributes( self::attributes_for( $sizes, $used_colors ) );
		$product->save();

		// Rebuild the variation grid from scratch.
		foreach ( $product->get_children() as $child_id ) {
			$child = wc_get_product( $child_id );

			if ( $child ) {
				$child->delete( true );
			}
		}

		$gallery = array();
		$made    = 0;

		foreach ( $rows as $row ) {
			$color_slug = $row[0];

			if ( ! in_array( $color_slug, $used_colors, true ) ) {
				continue;
			}

			$image_id = isset( $media[ $row[1] ] ) ? (int) $media[ $row[1] ] : 0;

			if ( $image_id && ! in_array( $image_id, $gallery, true ) ) {
				$gallery[] = $image_id;
			}

			foreach ( $variables as $size_slug ) {
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $product_id );
				$variation->set_attributes( array(
					'pa_color' => $color_slug,
					'pa_size'  => $size_slug,
				) );
				$variation->set_regular_price( $regular > 0 ? (string) $regular : '0' );

				if ( $sale > 0 ) {
					$variation->set_sale_price( (string) $sale );
				}

				$variation->set_manage_stock( false );
				$variation->set_stock_status( 'instock' );

				if ( $image_id ) {
					$variation->set_image_id( $image_id );
				}

				$variation->save();
			}

			$made++;
		}

		$product = wc_get_product( $product_id );

		if ( $product && $gallery ) {
			$product->set_image_id( $gallery[0] );

			if ( count( $gallery ) > 1 ) {
				$product->set_gallery_image_ids( array_slice( $gallery, 1 ) );
			}

			$product->save();
		}

		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );

		return $made;
	}

	/**
	 * Attach several products to every style/look, creating the extra looks.
	 *
	 * @param array $map Slug => product ID.
	 */
	private static function link_looks( $map ) {
		$media = get_option( 'moon_core_media', array() );
		$media = is_array( $media ) ? $media : array();

		$extra_images = array(
			'استایل زنانه زمستانی' => 'women.jpg',
			'استایل کودک'         => 'outfit-3.jpg',
		);

		foreach ( self::looks() as $title => $look ) {
			$post = moon_demo_find_by_title( $title, 'moon_outfit' );

			if ( ! $post ) {
				$post_id = wp_insert_post( array(
					'post_title'  => $title,
					'post_type'   => 'moon_outfit',
					'post_status' => 'publish',
					'post_excerpt' => $look['desc'],
				), true );

				if ( is_wp_error( $post_id ) ) {
					continue;
				}

				$image = isset( $extra_images[ $title ] ) && isset( $media[ $extra_images[ $title ] ] ) ? (int) $media[ $extra_images[ $title ] ] : 0;

				if ( $image ) {
					set_post_thumbnail( $post_id, $image );
				}
			} else {
				$post_id = $post->ID;
			}

			$ids = array();

			foreach ( $look['products'] as $slug ) {
				if ( ! empty( $map[ $slug ] ) ) {
					$ids[] = (int) $map[ $slug ];
				}
			}

			$ids = array_values( array_unique( $ids ) );

			update_post_meta( $post_id, '_moon_products', $ids );
			update_post_meta( $post_id, '_moon_look_desc', $look['desc'] );

			if ( ! empty( $look['en'] ) && ! get_post_meta( $post_id, '_moon_en', true ) ) {
				update_post_meta( $post_id, '_moon_en', $look['en'] );
			}

			if ( ! empty( $look['colors'] ) && ! get_post_meta( $post_id, '_moon_colors', true ) ) {
				update_post_meta( $post_id, '_moon_colors', $look['colors'] );
			}

			if ( $ids && ! get_post_meta( $post_id, '_moon_product', true ) ) {
				update_post_meta( $post_id, '_moon_product', $ids[0] );
			}
		}
	}

	/**
	 * Add the kids and scarf categories to the primary menu.
	 */
	private static function update_menu() {
		$menu = wp_get_nav_menu_object( 'منوی اصلی' );

		if ( ! $menu ) {
			return;
		}

		$existing = wp_get_nav_menu_items( $menu->term_id );
		$have     = array();

		foreach ( (array) $existing as $item ) {
			$have[] = (int) $item->object_id;
		}

		$position = count( (array) $existing ) + 1;

		foreach ( array( 'kids' => 'کودکان', 'scarves' => 'شال و روسری' ) as $slug => $label ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) || in_array( (int) $term->term_id, $have, true ) ) {
				continue;
			}

			wp_update_nav_menu_item( $menu->term_id, 0, array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'product_cat',
				'menu-item-object-id' => (int) $term->term_id,
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
		}
	}

	/**
	 * Point the footer's quick links at the real pages.
	 */
	private static function update_footer_links() {
		$options = get_option( 'moon_options', array() );
		$options = is_array( $options ) ? $options : array();

		$size_guide = get_page_by_path( 'size-guide' );
		$shipping   = get_page_by_path( 'shipping' );
		$faq        = get_page_by_path( 'faq' );
		$account    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';

		$lines = array();

		if ( $size_guide ) {
			$lines[] = 'راهنمای سایز|' . get_permalink( $size_guide );
		}

		if ( $shipping ) {
			$lines[] = 'ارسال و بازگشت|' . get_permalink( $shipping );
		}

		if ( $account ) {
			$lines[] = 'پیگیری سفارش|' . $account;
		}

		if ( $faq ) {
			$lines[] = 'سوالات متداول|' . get_permalink( $faq );
		}

		$options['footer_col2'] = implode( "\n", $lines );
		update_option( 'moon_options', $options );
	}

	/**
	 * Point the size guide page at the theme's full guide (women/men/kids/accessories).
	 */
	private static function refresh_size_guide() {
		$page = get_page_by_path( 'size-guide' );

		if ( ! $page ) {
			return;
		}

		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => "[moon_size_guide]\n\n<p>اگر بین دو سایز هستید یا برای انتخاب هدیه مطمئن نیستید، با پشتیبانی تماس بگیرید؛ اندازه‌های دقیق هر محصول در صفحه آن نیز آمده است.</p>",
		) );
	}
}
