<?php
/**
 * Primary navigation items, used when no menu is assigned.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fallback navigation matching the source design.
 *
 * @return array
 */
function moon_default_nav_items() {
	$items = array(
		array(
			'label' => __( 'زنانه', 'moon-theme' ),
			'url'   => moon_product_cat_url( 'women' ),
		),
		array(
			'label' => __( 'مردانه', 'moon-theme' ),
			'url'   => moon_product_cat_url( 'men' ),
		),
		array(
			'label' => __( 'کالکشن جدید', 'moon-theme' ),
			'url'   => moon_shop_url(),
		),
		array(
			'label' => __( 'استودیو', 'moon-theme' ),
			'url'   => is_front_page() ? '#studio' : home_url( '/#studio' ),
		),
		array(
			'label' => __( 'داستان ما', 'moon-theme' ),
			'url'   => is_front_page() ? '#story' : home_url( '/#story' ),
		),
	);

	return $items;
}

/**
 * Whether a navigation menu is assigned to the primary location.
 *
 * @return bool
 */
function moon_has_primary_menu() {
	return has_nav_menu( 'primary' );
}
