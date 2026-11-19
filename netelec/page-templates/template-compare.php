<?php
/**
 * Template Name: مقایسه محصولات
 *
 * @package Netelec
 */

get_header();

$netelec_ids      = netelec_compare_ids();
$netelec_products = array();

foreach ( $netelec_ids as $netelec_id ) {
	$netelec_product = wc_get_product( $netelec_id );
	if ( $netelec_product ) {
		$netelec_products[ $netelec_id ] = $netelec_product;
	}
}

// Collect every attribute used by the compared products.
$netelec_attributes = array();
foreach ( $netelec_products as $netelec_product ) {
	foreach ( $netelec_product->get_attributes() as $netelec_attribute ) {
		$netelec_name = $netelec_attribute->get_name();
		if ( ! isset( $netelec_attributes[ $netelec_name ] ) ) {
			$netelec_attributes[ $netelec_name ] = wc_attribute_label( $netelec_name );
		}
	}
}
?>
<?php netelec_page_header( get_the_title(), 'حداکثر ۴ محصول را کنار هم بررسی کنید' ); ?>

<div class="container compare-page">
	<?php if ( ! $netelec_products ) : ?>
		<div class="shop-empty">
			<?php netelec_icon( 'compare' ); ?>
			<h2>هنوز محصولی برای مقایسه انتخاب نکرده‌اید</h2>
			<p>از کارت محصولات، دکمه مقایسه را انتخاب کنید.</p>
			<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده محصولات</a>
		</div>
	<?php else : ?>
		<div class="compare-scroll">
			<table class="compare-table">
				<thead>
					<tr>
						<th scope="col">مشخصات</th>
						<?php foreach ( $netelec_products as $netelec_product ) : ?>
							<th scope="col">
								<div class="compare-product">
									<button type="button" class="compare-product__remove" data-compare-toggle="<?php echo esc_attr( $netelec_product->get_id() ); ?>" aria-label="حذف از مقایسه">
										<?php netelec_icon( 'close', 'nc-icon--sm' ); ?>
									</button>
									<a class="compare-product__media" href="<?php echo esc_url( get_permalink( $netelec_product->get_id() ) ); ?>">
										<?php echo $netelec_product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
									<a class="compare-product__title" href="<?php echo esc_url( get_permalink( $netelec_product->get_id() ) ); ?>">
										<?php echo esc_html( $netelec_product->get_name() ); ?>
									</a>
								</div>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">قیمت</th>
						<?php foreach ( $netelec_products as $netelec_product ) : ?>
							<td><?php echo wp_kses_post( $netelec_product->get_price_html() ); ?></td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<th scope="row">وضعیت</th>
						<?php foreach ( $netelec_products as $netelec_product ) : ?>
							<td>
								<?php if ( $netelec_product->is_in_stock() ) : ?>
									<span class="pc-stock pc-stock--in">موجود</span>
								<?php else : ?>
									<span class="pc-stock pc-stock--out">ناموجود</span>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<th scope="row">امتیاز</th>
						<?php foreach ( $netelec_products as $netelec_product ) : ?>
							<td>
								<?php if ( $netelec_product->get_rating_count() ) : ?>
									<?php netelec_stars( (float) $netelec_product->get_average_rating() ); ?>
									<small>(<?php echo esc_html( number_format_i18n( $netelec_product->get_review_count() ) ); ?>)</small>
								<?php else : ?>
									<span class="muted">بدون امتیاز</span>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<th scope="row">شناسه کالا</th>
						<?php foreach ( $netelec_products as $netelec_product ) : ?>
							<td><?php echo $netelec_product->get_sku() ? esc_html( $netelec_product->get_sku() ) : '—'; ?></td>
						<?php endforeach; ?>
					</tr>
					<?php foreach ( $netelec_attributes as $netelec_key => $netelec_label ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $netelec_label ); ?></th>
							<?php foreach ( $netelec_products as $netelec_product ) : ?>
								<td><?php echo esc_html( $netelec_product->get_attribute( $netelec_key ) ? $netelec_product->get_attribute( $netelec_key ) : '—' ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row">خرید</th>
						<?php foreach ( $netelec_products as $netelec_product ) : ?>
							<td>
								<a class="btn btn--primary btn--sm" href="<?php echo esc_url( get_permalink( $netelec_product->get_id() ) ); ?>">
									مشاهده محصول
								</a>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
