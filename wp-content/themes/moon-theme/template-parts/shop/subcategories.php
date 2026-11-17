<?php
/**
 * Sub-category strip above the product grid.
 *
 * On a category page it lists that category's children (or its siblings when
 * the category is a leaf), on the shop page the top-level categories — the same
 * mental model as the mega menu, so a shopper can drill down without opening a
 * dropdown.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! moon_is_woo() || ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

/*
 * Only on a category archive: on the shop page the same top-level categories
 * are already listed in the tab strip right above, and the two strips repeated
 * each other.
 */
if ( ! is_product_category() ) {
	return;
}

if ( class_exists( 'Moon_Settings' ) && ! Moon_Settings::get( 'category_strip', '1' ) ) {
	return;
}

$moon_show_images = ! class_exists( 'Moon_Settings' ) || Moon_Settings::get( 'category_strip_images', '1' );
$moon_current     = is_product_category() ? get_queried_object() : null;
$moon_parent      = $moon_current instanceof WP_Term ? (int) $moon_current->term_id : 0;

$moon_terms = get_terms( array(
	'taxonomy'   => 'product_cat',
	'parent'     => $moon_parent,
	'hide_empty' => true,
) );

if ( is_wp_error( $moon_terms ) || ! $moon_terms ) {
	if ( $moon_current instanceof WP_Term && $moon_current->parent ) {
		$moon_terms = get_terms( array(
			'taxonomy'   => 'product_cat',
			'parent'     => (int) $moon_current->parent,
			'hide_empty' => true,
		) );
	}
}

if ( is_wp_error( $moon_terms ) || ! $moon_terms ) {
	return;
}
?>
<nav class="moon-subcats" aria-label="<?php esc_attr_e( 'زیردسته‌ها', 'moon-theme' ); ?>">
	<?php
	foreach ( $moon_terms as $moon_term ) :
		$moon_link = get_term_link( $moon_term );

		if ( is_wp_error( $moon_link ) ) {
			continue;
		}

		$moon_thumb = (int) get_term_meta( $moon_term->term_id, 'thumbnail_id', true );
		$moon_class = 'moon-subcat';

		if ( $moon_current instanceof WP_Term && (int) $moon_current->term_id === (int) $moon_term->term_id ) {
			$moon_class .= ' is-active';
		}
		?>
		<a class="<?php echo esc_attr( $moon_class ); ?>" href="<?php echo esc_url( $moon_link ); ?>">
			<?php if ( $moon_show_images ) : ?>
				<span class="moon-subcat__media">
					<?php if ( $moon_thumb ) : ?>
						<?php echo wp_get_attachment_image( $moon_thumb, 'moon-card', false, array( 'loading' => 'lazy', 'alt' => $moon_term->name ) ); ?>
					<?php else : ?>
						<span class="moon-subcat__initial" aria-hidden="true"><?php echo esc_html( mb_substr( $moon_term->name, 0, 1 ) ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>

			<span class="moon-subcat__body">
				<span class="moon-subcat__name"><?php echo esc_html( $moon_term->name ); ?></span>
				<span class="moon-subcat__count">
					<?php
					echo esc_html( sprintf(
						/* translators: %s: number of products. */
						__( '%s محصول', 'moon-theme' ),
						moon_fa_digits( number_format_i18n( (int) $moon_term->count ) )
					) );
					?>
				</span>
			</span>
		</a>
	<?php endforeach; ?>
</nav>
