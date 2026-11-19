<?php
/**
 * Home category cards.
 *
 * @package Netelec
 */

if ( ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

$netelec_count = (int) netelec_opt( 'categories_count' );
$netelec_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'number'     => $netelec_count,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);

if ( is_wp_error( $netelec_terms ) || ! $netelec_terms ) {
	return;
}
?>
<section class="section section--categories">
	<div class="container">
		<?php netelec_section_head( netelec_opt( 'categories_title' ), get_permalink( wc_get_page_id( 'shop' ) ) ); ?>

		<ul class="category-grid">
			<?php foreach ( $netelec_terms as $netelec_term ) : ?>
				<?php
				$netelec_thumb_id = get_term_meta( $netelec_term->term_id, 'thumbnail_id', true );
				?>
				<li class="category-card">
					<a class="category-card__link" href="<?php echo esc_url( get_term_link( $netelec_term ) ); ?>">
						<span class="category-card__media">
							<?php if ( $netelec_thumb_id ) : ?>
								<?php echo wp_get_attachment_image( $netelec_thumb_id, 'medium', false, array( 'alt' => $netelec_term->name, 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<?php netelec_icon( 'package' ); ?>
							<?php endif; ?>
						</span>
						<span class="category-card__body">
							<strong><?php echo esc_html( $netelec_term->name ); ?></strong>
							<small><?php echo esc_html( number_format_i18n( $netelec_term->count ) ); ?> محصول</small>
						</span>
						<span class="category-card__arrow"><?php netelec_icon( 'arrow-left', 'nc-icon--sm' ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
