<?php
/**
 * Site header wrapper. The chrome itself comes from Elementor (Pro Theme
 * Builder), the Moon Core Header widget or the default template part.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#moon-content"><?php esc_html_e( 'Skip to content', 'moon-child' ); ?></a>
<?php moon_child_render_header(); ?>
<main id="moon-content" class="moon-main">
