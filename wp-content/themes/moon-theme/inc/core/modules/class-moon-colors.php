<?php
/**
 * Product colourways and attribute swatches.
 *
 * Colour data comes from two places, so both a plain catalogue and a
 * fully variable one work the same way in the theme:
 *
 * - variable products: the `pa_color` attribute, one swatch per variation value
 *   and the variation image as its preview;
 * - simple products: the `_moon_colorways` meta, an ordered list of
 *   `{name, hex, image}` rows.
 *
 * The swatches replace WooCommerce's `<select>` fields while keeping the native
 * select in the DOM, so the variations script and the cart keep working.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Colourways and swatch rendering.
 */
class Moon_Colors {

	/**
	 * Term meta key holding the swatch hex value.
	 */
	const META = 'moon_hex';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( __CLASS__, 'swatch_html' ), 20, 2 );
		add_filter( 'woocommerce_post_class', array( __CLASS__, 'post_class' ), 10, 2 );

		add_action( 'pa_color_add_form_fields', array( __CLASS__, 'add_field' ) );
		add_action( 'pa_color_edit_form_fields', array( __CLASS__, 'edit_field' ) );
		add_action( 'created_pa_color', array( __CLASS__, 'save_field' ) );
		add_action( 'edited_pa_color', array( __CLASS__, 'save_field' ) );
	}

	/**
	 * Fallback swatch colours for the built-in palette.
	 *
	 * @return array
	 */
	public static function palette() {
		return apply_filters( 'moon_core_color_palette', array(
			'black'     => '#101010',
			'meshki'    => '#101010',
			'white'     => '#ffffff',
			'snow'      => '#f5f1e9',
			'ivory'     => '#efe7da',
			'cream'     => '#efe7da',
			'sand'      => '#d8c9b0',
			'beige'     => '#d8c9b0',
			'camel'     => '#c8a97e',
			'brown'     => '#5b4a3a',
			'charcoal'  => '#3a3a3a',
			'graphite'  => '#3a3a3a',
			'grey'      => '#99958f',
			'gray'      => '#99958f',
			'olive'     => '#6b6b4f',
			'sage'      => '#a9b39a',
			'navy'      => '#1f2a44',
			'blue'      => '#3f5b8f',
			'denim'     => '#4a648c',
			'wine'      => '#6e1f2b',
			'burgundy'  => '#6e1f2b',
			'red'       => '#b23a3a',
			'pink'      => '#d9a7a7',
			'green'     => '#4c6b52',
			'mustard'   => '#c9a227',
			'silver'    => '#c7c9cc',
			'gold'      => '#c2a15a',
			'purple'    => '#5a4368',
		) );
	}

	/**
	 * Swatch colour for a term.
	 *
	 * @param WP_Term|string|null $term Term object, slug or name.
	 * @return string
	 */
	public static function hex( $term ) {
		$hex  = '';
		$name = '';

		if ( $term instanceof WP_Term ) {
			$hex  = (string) get_term_meta( $term->term_id, self::META, true );
			$name = $term->slug . ' ' . $term->name;
		} elseif ( is_string( $term ) ) {
			$name = $term;
		}

		$hex = (string) preg_replace( '/[^#0-9a-fA-F]/', '', $hex );

		if ( $hex && 3 !== strlen( $hex ) && 6 !== strlen( $hex ) ) {
			$hex = '';
		} elseif ( $hex && '#' !== substr( $hex, 0, 1 ) ) {
			$hex = '#' . $hex;
		}

		if ( $hex ) {
			return $hex;
		}

		$palette = self::palette();
		$haystack = strtolower( $name );

		foreach ( $palette as $key => $value ) {
			if ( $key && false !== strpos( $haystack, $key ) ) {
				return $value;
			}
		}

		return '#c9c4bb';
	}

	/**
	 * Colourways for a product.
	 *
	 * @param WC_Product $product Product.
	 * @return array List of `slug, name, hex, image` rows.
	 */
	public static function product_colors( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return array();
		}

		$colors = array();

		if ( $product->is_type( 'variable' ) ) {
			$attributes = $product->get_variation_attributes();
			$slugs      = isset( $attributes['pa_color'] ) ? (array) $attributes['pa_color'] : array();

			foreach ( $slugs as $slug ) {
				if ( '' === $slug ) {
					continue;
				}

				$term = get_term_by( 'slug', $slug, 'pa_color' );

				$colors[] = array(
					'slug'  => $slug,
					'name'  => $term && ! is_wp_error( $term ) ? $term->name : $slug,
					'hex'   => self::hex( $term ? $term : $slug ),
					'image' => self::variation_image( $product, $slug ),
				);
			}

			return $colors;
		}

		$rows = get_post_meta( $product->get_id(), '_moon_colorways', true );

		if ( is_string( $rows ) && $rows ) {
			$rows = json_decode( $rows, true );
		}

		foreach ( (array) $rows as $index => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$image = isset( $row['image'] ) ? $row['image'] : 0;

			if ( is_numeric( $image ) && $image ) {
				$image = wp_get_attachment_image_url( (int) $image, 'moon-card' );
			}

			$colors[] = array(
				'slug'  => 'color-' . $index,
				'name'  => isset( $row['name'] ) ? $row['name'] : '',
				'hex'   => self::hex( isset( $row['hex'] ) ? $row['hex'] : ( isset( $row['name'] ) ? $row['name'] : '' ) ),
				'image' => $image ? $image : '',
			);
		}

		return $colors;
	}

	/**
	 * Image of the first variation matching a colour.
	 *
	 * @param WC_Product $product Variable product.
	 * @param string     $color   Colour slug.
	 * @return string
	 */
	private static function variation_image( $product, $color ) {
		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );

			if ( ! $variation ) {
				continue;
			}

			$attrs = $variation->get_attributes();

			if ( empty( $attrs['pa_color'] ) || $attrs['pa_color'] !== $color ) {
				continue;
			}

			if ( $variation->get_image_id() ) {
				$url = wp_get_attachment_image_url( $variation->get_image_id(), 'moon-card' );

				if ( $url ) {
					return $url;
				}
			}
		}

		return '';
	}

	/**
	 * Product gallery image URLs.
	 *
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function gallery( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return array();
		}

		$ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
		$out = array();

		foreach ( $ids as $id ) {
			$url = wp_get_attachment_image_url( $id, 'large' );

			if ( $url ) {
				$out[] = $url;
			}
		}

		return $out;
	}

	/**
	 * Replace the attribute select with swatches, keeping the select in place.
	 *
	 * @param string $html Select markup.
	 * @param array  $args Attribute arguments.
	 * @return string
	 */
	public static function swatch_html( $html, $args ) {
		$attribute = isset( $args['attribute'] ) ? $args['attribute'] : '';
		$options   = isset( $args['options'] ) ? (array) $args['options'] : array();
		$product   = isset( $args['product'] ) ? $args['product'] : null;
		$selected  = isset( $args['selected'] ) ? $args['selected'] : '';
		$name      = isset( $args['name'] ) ? $args['name'] : 'attribute_' . sanitize_title( $attribute );

		if ( ! in_array( $attribute, array( 'pa_color', 'pa_size' ), true ) || empty( $options ) || ! $product instanceof WC_Product ) {
			return $html;
		}

		$is_color = 'pa_color' === $attribute;
		$taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute ) );
		$terms    = array();

		foreach ( $options as $option ) {
			$term = get_term_by( 'slug', $option, $taxonomy );

			if ( $term && ! is_wp_error( $term ) ) {
				$terms[ $option ] = $term->name;
			} else {
				$terms[ $option ] = $option;
			}
		}

		if ( empty( $terms ) ) {
			return $html;
		}

		$out  = '<div class="moon-swatches moon-swatches--' . ( $is_color ? 'color' : 'size' ) . '" data-moon-swatches="' . esc_attr( $attribute ) . '" role="radiogroup" aria-label="' . esc_attr( wc_attribute_label( $attribute ) ) . '">';

		foreach ( $terms as $slug => $label ) {
			$active = (string) $selected === (string) $slug;

			if ( $is_color ) {
				$hex = self::hex( get_term_by( 'slug', $slug, $taxonomy ) ? get_term_by( 'slug', $slug, $taxonomy ) : $label );
				$out .= '<button type="button" class="moon-swatch moon-swatch--color' . ( $active ? ' is-active' : '' ) . '" data-value="' . esc_attr( $slug ) . '" role="radio" aria-checked="' . ( $active ? 'true' : 'false' ) . '" title="' . esc_attr( $label ) . '"><span class="moon-swatch__dot" style="--swatch:' . esc_attr( $hex ) . '"></span><span class="moon-swatch__text">' . esc_html( $label ) . '</span></button>';
			} else {
				$out .= '<button type="button" class="moon-swatch moon-swatch--size' . ( $active ? ' is-active' : '' ) . '" data-value="' . esc_attr( $slug ) . '" role="radio" aria-checked="' . ( $active ? 'true' : 'false' ) . '">' . esc_html( $label ) . '</button>';
			}
		}

		$out .= '</div>';
		$out .= '<div class="moon-swatches__native">' . $html . '</div>';

		return $out;
	}

	/**
	 * Add a colour dot to shop cards that define colourways.
	 *
	 * @param array      $classes Post classes.
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function post_class( $classes, $product ) {
		if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
			$classes[] = 'moon-has-swatches';
		}

		return $classes;
	}

	/**
	 * Hex field on the "add colour" form.
	 */
	public static function add_field() {
		?>
		<div class="form-field">
			<label for="moon_hex"><?php esc_html_e( 'کد رنگ', 'moon-theme' ); ?></label>
			<input type="text" name="moon_hex" id="moon_hex" value="" placeholder="#101010">
			<p><?php esc_html_e( 'کد هگز رنگ برای نمایش در سواچ محصول (مثلاً #101010).', 'moon-theme' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Hex field on the "edit colour" form.
	 *
	 * @param WP_Term $term Term.
	 */
	public static function edit_field( $term ) {
		$value = get_term_meta( $term->term_id, self::META, true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="moon_hex"><?php esc_html_e( 'کد رنگ', 'moon-theme' ); ?></label></th>
			<td>
				<input type="text" name="moon_hex" id="moon_hex" value="<?php echo esc_attr( $value ); ?>" placeholder="#101010">
				<p class="description"><?php esc_html_e( 'کد هگز رنگ برای نمایش در سواچ محصول.', 'moon-theme' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Persist the hex field.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save_field( $term_id ) {
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}

		if ( ! isset( $_POST['moon_hex'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$hex = sanitize_hex_color( wp_unslash( $_POST['moon_hex'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( $hex ) {
			update_term_meta( $term_id, self::META, $hex );
		} else {
			delete_term_meta( $term_id, self::META );
		}
	}
}
