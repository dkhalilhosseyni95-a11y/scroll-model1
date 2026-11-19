<?php
/**
 * Shop sidebar filters.
 *
 * @package Netelec
 */

if ( ! function_exists( 'wc_get_page_permalink' ) ) {
	return;
}

$netelec_filters = netelec_current_filters();
$netelec_action  = is_product_taxonomy() ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' );
if ( is_wp_error( $netelec_action ) ) {
	$netelec_action = wc_get_page_permalink( 'shop' );
}

$netelec_current_cat = is_product_taxonomy() ? get_queried_object_id() : 0;
?>
<form class="shop-filters" method="get" action="<?php echo esc_url( $netelec_action ); ?>">
	<div class="filter-block">
		<h2 class="filter-block__title">دسته‌بندی‌ها</h2>
		<ul class="filter-cats">
			<li>
				<a class="<?php echo is_shop() ? 'is-active' : ''; ?>" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
					همه محصولات
				</a>
			</li>
			<?php
			$netelec_cats = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => 0,
				)
			);
			if ( ! is_wp_error( $netelec_cats ) ) :
				foreach ( $netelec_cats as $netelec_cat ) :
					?>
					<li>
						<a class="<?php echo (int) $netelec_current_cat === (int) $netelec_cat->term_id ? 'is-active' : ''; ?>"
							href="<?php echo esc_url( get_term_link( $netelec_cat ) ); ?>">
							<?php echo esc_html( $netelec_cat->name ); ?>
							<span><?php echo esc_html( number_format_i18n( $netelec_cat->count ) ); ?></span>
						</a>
					</li>
					<?php
				endforeach;
			endif;
			?>
		</ul>
	</div>

	<div class="filter-block">
		<h2 class="filter-block__title">محدوده قیمت</h2>
		<div class="filter-price">
			<label>
				<span>از</span>
				<input type="number" name="min_price" min="0" step="1000" placeholder="۰" value="<?php echo esc_attr( $netelec_filters['min_price'] ); ?>">
			</label>
			<label>
				<span>تا</span>
				<input type="number" name="max_price" min="0" step="1000" placeholder="بی‌نهایت" value="<?php echo esc_attr( $netelec_filters['max_price'] ); ?>">
			</label>
		</div>
	</div>

	<?php
	$netelec_attributes = wc_get_attribute_taxonomies();
	if ( $netelec_attributes ) :
		foreach ( $netelec_attributes as $netelec_attribute ) :
			$netelec_taxonomy = wc_attribute_taxonomy_name( $netelec_attribute->attribute_name );
			if ( ! taxonomy_exists( $netelec_taxonomy ) ) {
				continue;
			}
			$netelec_terms = get_terms(
				array(
					'taxonomy'   => $netelec_taxonomy,
					'hide_empty' => true,
					'number'     => 12,
				)
			);
			if ( is_wp_error( $netelec_terms ) || ! $netelec_terms ) {
				continue;
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$netelec_raw      = isset( $_GET[ 'filter_' . $netelec_taxonomy ] ) ? wp_unslash( $_GET[ 'filter_' . $netelec_taxonomy ] ) : array();
			$netelec_selected = is_array( $netelec_raw ) ? array_map( 'sanitize_title', $netelec_raw ) : array_map( 'sanitize_title', explode( ',', (string) $netelec_raw ) );
			?>
			<div class="filter-block">
				<h2 class="filter-block__title"><?php echo esc_html( $netelec_attribute->attribute_label ); ?></h2>
				<ul class="filter-checks">
					<?php foreach ( $netelec_terms as $netelec_term ) : ?>
						<li>
							<label>
								<input type="checkbox" name="filter_<?php echo esc_attr( $netelec_taxonomy ); ?>[]" value="<?php echo esc_attr( $netelec_term->slug ); ?>" <?php checked( in_array( $netelec_term->slug, $netelec_selected, true ) ); ?>>
								<span><?php echo esc_html( $netelec_term->name ); ?></span>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		endforeach;
	endif;
	?>

	<div class="filter-block">
		<h2 class="filter-block__title">سایر فیلترها</h2>
		<ul class="filter-checks">
			<li>
				<label>
					<input type="checkbox" name="in_stock" value="1" <?php checked( $netelec_filters['in_stock'] ); ?>>
					<span>فقط کالاهای موجود</span>
				</label>
			</li>
			<li>
				<label>
					<input type="checkbox" name="on_sale" value="1" <?php checked( $netelec_filters['on_sale'] ); ?>>
					<span>فقط محصولات تخفیف‌دار</span>
				</label>
			</li>
		</ul>
	</div>

	<div class="filter-actions">
		<button type="submit" class="btn btn--primary btn--sm">اعمال فیلتر</button>
		<a class="filter-reset" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">حذف فیلترها</a>
	</div>
</form>
