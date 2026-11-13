<?php
/**
 * Minimal default header used only when Moon Core is inactive and no Elementor
 * header template exists. The full editable header is the MOON Header widget.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="moon-header moon-header--fallback">
	<div class="moon-container moon-header__inner">
		<div class="moon-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="moon-brand__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>
		<nav class="moon-nav" aria-label="<?php esc_attr_e( 'Primary', 'moon-child' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_class'     => 'moon-nav__list',
					'container'      => false,
					'fallback_cb'    => 'moon_child_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>
	</div>
</header>
