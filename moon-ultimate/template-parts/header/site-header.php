<?php
/**
 * Site header.
 *
 * The markup is produced by the header builder (`Moon_Header`), which applies
 * the layout variant, the ordered elements and the announcement bar chosen in
 * the MOON panel. The wrapper stays here so a child theme can override it.
 *
 * @package Moon_Theme
 */

if ( class_exists( 'Moon_Header' ) ) {
	Moon_Header::render();
	return;
}

$moon_sticky = (int) moon_opt( 'header_sticky' );
?>
<header class="moon-header<?php echo $moon_sticky ? '' : ' is-static'; ?>" id="moon-header" data-moon-header>
	<div class="moon-header__pill moon-glass">
		<a class="moon-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="moon-brand__text<?php echo moon_is_arabic_script( moon_brand_text() ) ? ' is-fa' : ''; ?>"><?php echo esc_html( moon_brand_text() ); ?></span>
		</a>

		<nav class="moon-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'moon-theme' ); ?>">
			<?php if ( function_exists( 'moon_has_primary_menu' ) && moon_has_primary_menu() ) : ?>
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 2, 'fallback_cb' => false ) ); ?>
			<?php endif; ?>
		</nav>
	</div>
</header>
