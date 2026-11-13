<?php
/**
 * MOON Child theme bootstrap.
 *
 * The theme stays intentionally small: it provides design tokens, typography,
 * the site chrome (header/footer) and WooCommerce styling. Everything that
 * renders editable sections lives in the Moon Core plugin so content survives a
 * theme switch.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_CHILD_VERSION', '1.0.1' );
define( 'MOON_CHILD_DIR', get_stylesheet_directory() );
define( 'MOON_CHILD_URL', get_stylesheet_directory_uri() );

require_once MOON_CHILD_DIR . '/inc/setup.php';
require_once MOON_CHILD_DIR . '/inc/helpers.php';
require_once MOON_CHILD_DIR . '/inc/enqueue.php';
require_once MOON_CHILD_DIR . '/inc/woocommerce.php';
