<?php
/**
 * MOON theme bootstrap.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'MOON_THEME_VERSION', '1.0.1' );
define( 'MOON_THEME_DIR', get_template_directory() );
define( 'MOON_THEME_URI', get_template_directory_uri() );

require_once MOON_THEME_DIR . '/inc/helpers.php';
require_once MOON_THEME_DIR . '/inc/setup.php';
require_once MOON_THEME_DIR . '/inc/enqueue.php';
require_once MOON_THEME_DIR . '/inc/template-functions.php';
require_once MOON_THEME_DIR . '/inc/customizer.php';
require_once MOON_THEME_DIR . '/inc/newsletter.php';
require_once MOON_THEME_DIR . '/inc/navigation.php';
require_once MOON_THEME_DIR . '/inc/integrations/woocommerce.php';
