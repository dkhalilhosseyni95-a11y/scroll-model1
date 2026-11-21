<?php
/**
 * Category tabs on the shop page: all / women / men / kids / scarves.
 *
 * Real links to the category archives so each tab is crawlable and shareable.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! moon_is_woo() ) {
	return;
}

$moon_current = is_product_category() ? get_queried_object_id() : 0;
$moon_tabs    = array();

foreach ( moon_shop_categories() as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( $term && ! is_wp_error( $term ) ) {
		$moon_tabs[] = array(
			'url'    => get_term_link( $term ),
			'label'  => $term->name,
			'active' => $moon_current === (int) $term->term_id,
		);
	}
}

if ( ! $moon_tabs ) {
	return;
}
?>
<nav class="moon-cattabs" aria-label="<?php esc_attr_e( 'دسته‌بندی محصولات', 'moon-theme' ); ?>">
	<a class="moon-cattabs__tab<?php echo $moon_current ? '' : ' is-active'; ?>" href="<?php echo esc_url( moon_shop_url() ); ?>"<?php echo $moon_current ? '' : ' aria-current="page"'; ?>><?php esc_html_e( 'همه', 'moon-theme' ); ?></a>
	<?php foreach ( $moon_tabs as $tab ) : ?>
		<a class="moon-cattabs__tab<?php echo $tab['active'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $tab['url'] ); ?>"<?php echo $tab['active'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $tab['label'] ); ?></a>
	<?php endforeach; ?>
</nav>
