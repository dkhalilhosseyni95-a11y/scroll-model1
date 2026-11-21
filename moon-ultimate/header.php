<?php
/**
 * Document head and site header.
 *
 * The colour mode chosen in the panel (dark, light or the visitor's operating
 * system) is printed on `<html>` so the first paint already has the right
 * palette; the inline script below re-applies a visitor's own choice before the
 * stylesheet paints, which keeps the switch flash-free.
 *
 * @package Moon_Theme
 */

$moon_theme_mode = class_exists( 'Moon_Settings' ) ? (string) Moon_Settings::get( 'theme_mode', 'dark' ) : 'dark';

if ( ! in_array( $moon_theme_mode, array( 'dark', 'light', 'system' ), true ) ) {
	$moon_theme_mode = 'dark';
}
?><!doctype html>
<html <?php language_attributes(); ?> class="<?php echo 'light' === $moon_theme_mode ? 'moon-light' : 'moon-dark'; ?>" data-moon-theme-mode="<?php echo esc_attr( $moon_theme_mode ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<script>
		(function () {
			var root = document.documentElement;
			var mode = root.getAttribute('data-moon-theme-mode') || 'dark';
			var saved = null;

			try {
				saved = window.localStorage.getItem('moon-theme');
			} catch (error) {
				/* storage unavailable */
			}

			var dark;

			if (saved === 'light' || saved === 'dark') {
				dark = saved === 'dark';
			} else if (mode === 'system') {
				dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
			} else {
				dark = mode !== 'light';
			}

			root.classList.toggle('moon-dark', dark);
			root.classList.toggle('moon-light', !dark);
		})();
	</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="moon-skip" href="#main"><?php esc_html_e( 'رفتن به محتوا', 'moon-theme' ); ?></a>

<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
<?php get_template_part( 'template-parts/header/site-header' ); ?>
<?php endif; ?>
<?php get_template_part( 'template-parts/header/mobile-menu' ); ?>
<?php get_template_part( 'template-parts/header/search-overlay' ); ?>
<?php get_template_part( 'template-parts/header/wishlist-panel' ); ?>
<?php get_template_part( 'template-parts/header/cart-drawer' ); ?>

<main id="main" class="moon-main">
