<?php
/**
 * Minimal default footer used only when Moon Core is inactive and no Elementor
 * footer template exists.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="moon-footer moon-footer--fallback">
	<div class="moon-container">
		<nav class="moon-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'moon-child' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'menu_class'     => 'moon-footer__list',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<p class="moon-footer__note">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>
</footer>
