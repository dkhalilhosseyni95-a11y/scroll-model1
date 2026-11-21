<?php
/**
 * Meta boxes for rack garments and studio outfits, plus the data feed the theme consumes.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rack and outfit fields.
 */
class Moon_Fields {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_boxes' ) );
		add_action( 'save_post_moon_rack', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'save_post_moon_outfit', array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'moon_rack_items', array( __CLASS__, 'rack_items' ) );
		add_filter( 'moon_outfits', array( __CLASS__, 'outfit_items' ) );
	}

	/**
	 * Register meta boxes.
	 */
	public static function add_boxes() {
		add_meta_box( 'moon_rack_fields', __( 'مشخصات قلم رگال', 'moon-theme' ), array( __CLASS__, 'rack_box' ), 'moon_rack', 'normal', 'high' );
		add_meta_box( 'moon_outfit_fields', __( 'مشخصات استایل', 'moon-theme' ), array( __CLASS__, 'outfit_box' ), 'moon_outfit', 'normal', 'high' );
	}

	/**
	 * Rack meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function rack_box( $post ) {
		wp_nonce_field( 'moon_rack_save', 'moon_rack_nonce' );

		$en      = get_post_meta( $post->ID, '_moon_en', true );
		$price   = get_post_meta( $post->ID, '_moon_price', true );
		$colors  = get_post_meta( $post->ID, '_moon_colors', true );
		$product = (int) get_post_meta( $post->ID, '_moon_product', true );
		?>
		<p>
			<label for="moon_en"><strong><?php esc_html_e( 'نام انگلیسی', 'moon-theme' ); ?></strong></label><br>
			<input type="text" class="widefat" dir="ltr" id="moon_en" name="moon_en" value="<?php echo esc_attr( $en ); ?>">
		</p>
		<p>
			<label for="moon_price"><strong><?php esc_html_e( 'قیمت نمایشی', 'moon-theme' ); ?></strong></label><br>
			<input type="text" class="widefat" id="moon_price" name="moon_price" value="<?php echo esc_attr( $price ); ?>" placeholder="۴٬۲۰۰٬۰۰۰ تومان">
			<span class="description"><?php esc_html_e( 'اگر محصول ووکامرس انتخاب شود، قیمت واقعی محصول نمایش داده می‌شود.', 'moon-theme' ); ?></span>
		</p>
		<p>
			<label for="moon_colors"><strong><?php esc_html_e( 'رنگ‌ها', 'moon-theme' ); ?></strong></label><br>
			<input type="text" class="widefat" dir="ltr" id="moon_colors" name="moon_colors" value="<?php echo esc_attr( $colors ); ?>" placeholder="#101010, #d8c9b0">
			<span class="description"><?php esc_html_e( 'کدهای رنگ را با کاما جدا کنید.', 'moon-theme' ); ?></span>
		</p>
		<p>
			<label for="moon_product"><strong><?php esc_html_e( 'محصول ووکامرس', 'moon-theme' ); ?></strong></label><br>
			<?php echo self::product_select( 'moon_product', $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<p class="description"><?php esc_html_e( 'تصویر شاخص این قلم باید PNG با پس‌زمینه شفاف باشد تا روی رگال درست دیده شود.', 'moon-theme' ); ?></p>
		<?php
	}

	/**
	 * Outfit meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function outfit_box( $post ) {
		wp_nonce_field( 'moon_outfit_save', 'moon_outfit_nonce' );

		$en      = get_post_meta( $post->ID, '_moon_en', true );
		$colors  = get_post_meta( $post->ID, '_moon_colors', true );
		$product = (int) get_post_meta( $post->ID, '_moon_product', true );
		?>
		<p>
			<label for="moon_outfit_en"><strong><?php esc_html_e( 'نام انگلیسی', 'moon-theme' ); ?></strong></label><br>
			<input type="text" class="widefat" dir="ltr" id="moon_outfit_en" name="moon_en" value="<?php echo esc_attr( $en ); ?>">
		</p>
		<p>
			<label for="moon_outfit_colors"><strong><?php esc_html_e( 'پالت رنگ', 'moon-theme' ); ?></strong></label><br>
			<input type="text" class="widefat" dir="ltr" id="moon_outfit_colors" name="moon_colors" value="<?php echo esc_attr( $colors ); ?>" placeholder="#101010, #FFFFFF">
		</p>
		<p>
			<label for="moon_outfit_product"><strong><?php esc_html_e( 'محصول ووکامرس', 'moon-theme' ); ?></strong></label><br>
			<?php echo self::product_select( 'moon_outfit_product', $product, 'moon_product' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<p>
			<label for="moon_outfit_products"><strong><?php esc_html_e( 'اقلام این استایل', 'moon-theme' ); ?></strong></label><br>
			<?php echo self::products_multiselect( 'moon_outfit_products', 'moon_products', (array) get_post_meta( $post->ID, '_moon_products', true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="description"><?php esc_html_e( 'همه لباس‌هایی که این استایل را می‌سازند (کت، شلوار، شال…). با Ctrl روی چند مورد کلیک کنید.', 'moon-theme' ); ?></span>
		</p>
		<p class="description"><?php esc_html_e( 'تصویر شاخص، تصویر مدل با این استایل است (نسبت پیشنهادی ۲:۳).', 'moon-theme' ); ?></p>
		<?php
	}

	/**
	 * Multi product picker.
	 *
	 * @param string $id      Field id.
	 * @param string $name    Field name.
	 * @param array  $current Selected product IDs.
	 * @return string
	 */
	private static function products_multiselect( $id, $name, $current ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '<em>' . esc_html__( 'ووکامرس فعال نیست.', 'moon-theme' ) . '</em>';
		}

		$products = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => array( 'publish', 'draft' ),
		) );

		$current = array_map( 'intval', (array) $current );
		$html    = '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '[]" class="widefat" multiple size="6">';

		foreach ( $products as $product ) {
			$html .= '<option value="' . esc_attr( (string) $product->ID ) . '"' . ( in_array( (int) $product->ID, $current, true ) ? ' selected' : '' ) . '>' . esc_html( $product->post_title ) . '</option>';
		}

		return $html . '</select>';
	}

	/**
	 * Product dropdown.
	 *
	 * @param string $id      Field id.
	 * @param int    $current Selected product.
	 * @param string $name    Field name.
	 * @return string
	 */
	private static function product_select( $id, $current, $name = 'moon_product' ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '<em>' . esc_html__( 'ووکامرس فعال نیست.', 'moon-theme' ) . '</em>';
		}

		$products = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => array( 'publish', 'draft' ),
		) );

		$html = '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" class="widefat"><option value="0">' . esc_html__( '— بدون اتصال —', 'moon-theme' ) . '</option>';

		foreach ( $products as $product ) {
			$html .= '<option value="' . esc_attr( (string) $product->ID ) . '"' . selected( $current, $product->ID, false ) . '>' . esc_html( $product->post_title ) . '</option>';
		}

		return $html . '</select>';
	}

	/**
	 * Persist meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$is_rack = 'moon_rack' === $post->post_type;
		$nonce   = $is_rack ? 'moon_rack_nonce' : 'moon_outfit_nonce';
		$action  = $is_rack ? 'moon_rack_save' : 'moon_outfit_save';

		if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), $action ) ) {
			return;
		}

		$en     = isset( $_POST['moon_en'] ) ? sanitize_text_field( wp_unslash( $_POST['moon_en'] ) ) : '';
		$colors = isset( $_POST['moon_colors'] ) ? sanitize_text_field( wp_unslash( $_POST['moon_colors'] ) ) : '';
		$prod   = isset( $_POST['moon_product'] ) ? absint( wp_unslash( $_POST['moon_product'] ) ) : 0;

		update_post_meta( $post_id, '_moon_en', $en );
		update_post_meta( $post_id, '_moon_colors', $colors );
		update_post_meta( $post_id, '_moon_product', $prod );

		if ( $is_rack ) {
			$price = isset( $_POST['moon_price'] ) ? sanitize_text_field( wp_unslash( $_POST['moon_price'] ) ) : '';
			update_post_meta( $post_id, '_moon_price', $price );
		} else {
			$products = isset( $_POST['moon_products'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['moon_products'] ) ) : array();
			$products = array_values( array_filter( array_unique( $products ) ) );

			update_post_meta( $post_id, '_moon_products', $products );
		}
	}

	/**
	 * Feed rack items to the theme.
	 *
	 * @param array $items Existing items.
	 * @return array
	 */
	public static function rack_items( $items ) {
		$posts = get_posts( array(
			'post_type'      => 'moon_rack',
			'posts_per_page' => 20,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return $items;
		}

		$result = array();

		foreach ( $posts as $post ) {
			$result[] = array(
				'id'         => 'rack-' . $post->ID,
				'name'       => get_the_title( $post ),
				'en'         => get_post_meta( $post->ID, '_moon_en', true ),
				'price'      => get_post_meta( $post->ID, '_moon_price', true ),
				'image'      => (string) get_the_post_thumbnail_url( $post->ID, 'moon-card' ),
				'colors'     => self::colors( get_post_meta( $post->ID, '_moon_colors', true ) ),
				'product_id' => (int) get_post_meta( $post->ID, '_moon_product', true ),
			);
		}

		return $result;
	}

	/**
	 * Feed outfits to the theme.
	 *
	 * @param array $items Existing items.
	 * @return array
	 */
	public static function outfit_items( $items ) {
		$posts = get_posts( array(
			'post_type'      => 'moon_outfit',
			'posts_per_page' => 20,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return $items;
		}

		$result = array();

		foreach ( $posts as $post ) {
			$products = (array) get_post_meta( $post->ID, '_moon_products', true );
			$products = array_values( array_filter( array_map( 'absint', $products ) ) );

			$result[] = array(
				'id'         => $post->ID,
				'name'       => get_the_title( $post ),
				'en'         => get_post_meta( $post->ID, '_moon_en', true ),
				'image'      => (string) get_the_post_thumbnail_url( $post->ID, 'moon-card' ),
				'colors'     => self::colors( get_post_meta( $post->ID, '_moon_colors', true ) ),
				'product_id' => (int) get_post_meta( $post->ID, '_moon_product', true ),
				'products'   => $products,
				'look'       => get_permalink( $post->ID ),
				'desc'       => (string) get_post_meta( $post->ID, '_moon_look_desc', true ),
			);
		}

		return $result;
	}

	/**
	 * Normalise a comma separated colour list.
	 *
	 * @param string $raw Raw value.
	 * @return array
	 */
	private static function colors( $raw ) {
		$colors = array();

		foreach ( explode( ',', (string) $raw ) as $color ) {
			$color = sanitize_hex_color( trim( $color ) );

			if ( $color ) {
				$colors[] = $color;
			}
		}

		return $colors;
	}
}
