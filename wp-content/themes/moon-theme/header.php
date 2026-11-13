<?php
/**
 * Document head and site header.
 *
 * @package Moon_Theme
 */

?><!doctype html>
<html <?php language_attributes(); ?> class="moon-dark">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="moon-skip" href="#main"><?php esc_html_e( 'رفتن به محتوا', 'moon-theme' ); ?></a>

<?php get_template_part( 'template-parts/header/site-header' ); ?>
<?php get_template_part( 'template-parts/header/mobile-menu' ); ?>
<?php get_template_part( 'template-parts/header/search-overlay' ); ?>
<?php get_template_part( 'template-parts/header/wishlist-panel' ); ?>
<?php get_template_part( 'template-parts/header/cart-drawer' ); ?>

<main id="main" class="moon-main">
