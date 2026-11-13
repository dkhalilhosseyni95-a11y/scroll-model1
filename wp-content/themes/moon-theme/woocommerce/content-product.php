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
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	get_template_part(
		'template-parts/product/card',
		null,
		array( 'product' => $product )
	);
	?>
</li>
