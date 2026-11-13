<?php
/**
 * Product carousel ("Latest").
 *
 * @package Moon_Theme
 */

$moon_source = moon_opt( 'products_source', 'recent' );
$moon_limit  = (int) moon_opt( 'products_limit', 8 );
$moon_items  = array();

if ( moon_is_woo() ) {
	$moon_items = moon_woo_carousel_products( $moon_source, $moon_limit );
} else {
	foreach ( moon_demo_products() as $demo ) {
		$moon_items[] = array(
			'id'      => $demo['id'],
			'name'    => $demo['name'],
			'en'      => $demo['en'],
			'price'   => $demo['price'],
			'image'   => $demo['image'],
			'url'     => $demo['url'],
			'tag'     => $demo['tag'],
			'cover'   => $demo['cover'],
			'add_url' => '',
			'sizes'   => array( 'S', 'M', 'L', 'XL' ),
			'desc'    => __( 'دوخته‌شده با دقت در جزئیات، از پارچه‌های منتخب. سایزبندی استاندارد اروپایی.', 'moon-theme' ),
		);
	}
}
?>
<section id="products" class="moon-products">
	<div class="moon-products__head">
		<?php moon_section_title( moon_opt( 'products_eyebrow' ), moon_opt( 'products_title' ) ); ?>
		<div class="moon-products__arrows">
			<button type="button" class="moon-products__arrow" data-moon-carousel-prev aria-label="<?php esc_attr_e( 'محصولات قبلی', 'moon-theme' ); ?>">
				<?php moon_the_icon( 'chevron-right', 18 ); ?>
			</button>
			<button type="button" class="moon-products__arrow" data-moon-carousel-next aria-label="<?php esc_attr_e( 'محصولات بعدی', 'moon-theme' ); ?>">
				<?php moon_the_icon( 'chevron-left', 18 ); ?>
			</button>
		</div>
	</div>

	<?php if ( empty( $moon_items ) ) : ?>
		<p class="moon-products__note"><?php esc_html_e( 'هنوز محصولی منتشر نشده است.', 'moon-theme' ); ?></p>
	<?php else : ?>
		<div class="moon-products__track moon-noscroll" data-moon-carousel>
			<?php
			foreach ( $moon_items as $item ) {
				echo '<div class="moon-products__item">';
				get_template_part(
					'template-parts/product/card',
					null,
					array_merge(
						$item,
						array(
							'contain' => empty( $item['cover'] ),
							'add_id'  => isset( $item['add_id'] ) ? (int) $item['add_id'] : 0,
							'desc'    => ! empty( $item['excerpt'] ) ? $item['excerpt'] : '',
						)
					)
				);
				echo '</div>';
			}
			?>
		</div>
		<?php if ( moon_opt( 'products_note' ) ) : ?>
			<p class="moon-products__note"><?php echo esc_html( moon_opt( 'products_note' ) ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</section>
