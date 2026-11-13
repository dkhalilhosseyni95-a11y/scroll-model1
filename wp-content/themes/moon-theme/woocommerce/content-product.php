<?php
/**
 * Product card inside the shop loop.
 *
 * @package Moon_Theme
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$moon_item = moon_woo_carousel_item( $product );
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	get_template_part(
		'template-parts/product/card',
		null,
		array(
			'id'      => $moon_item['id'],
			'name'    => $moon_item['name'],
			'en'      => $moon_item['en'],
			'price'   => $moon_item['price'],
			'image'   => $moon_item['image'],
			'url'     => $moon_item['url'],
			'tag'     => $moon_item['tag'],
			'contain' => false,
			'sizes'   => $moon_item['sizes'],
			'add_url' => $moon_item['add_url'],
			'add_id'  => $moon_item['add_id'],
			'desc'    => $moon_item['excerpt'],
		)
	);
	?>
</li>
