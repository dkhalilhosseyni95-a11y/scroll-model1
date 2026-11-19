<?php
/**
 * NETELEC theme bootstrap.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NETELEC_VERSION', '2.0.0' );
define( 'NETELEC_DIR', get_template_directory() );
define( 'NETELEC_URI', get_template_directory_uri() );

require_once NETELEC_DIR . '/inc/setup.php';
require_once NETELEC_DIR . '/inc/options-schema.php';
require_once NETELEC_DIR . '/inc/options.php';
require_once NETELEC_DIR . '/inc/assets.php';
require_once NETELEC_DIR . '/inc/template-tags.php';
require_once NETELEC_DIR . '/inc/customizer.php';
require_once NETELEC_DIR . '/inc/ajax.php';
require_once NETELEC_DIR . '/inc/wishlist.php';
require_once NETELEC_DIR . '/inc/compare.php';
require_once NETELEC_DIR . '/inc/recently-viewed.php';
require_once NETELEC_DIR . '/inc/quick-view.php';
require_once NETELEC_DIR . '/inc/live-search.php';
require_once NETELEC_DIR . '/inc/contact-form.php';
require_once NETELEC_DIR . '/inc/sms/otp.php';
require_once NETELEC_DIR . '/inc/theme-pages.php';
require_once NETELEC_DIR . '/inc/demo-import.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once NETELEC_DIR . '/inc/woocommerce/setup.php';
	require_once NETELEC_DIR . '/inc/woocommerce/hooks.php';
	require_once NETELEC_DIR . '/inc/woocommerce/account.php';
	require_once NETELEC_DIR . '/inc/woocommerce/filters.php';
}
