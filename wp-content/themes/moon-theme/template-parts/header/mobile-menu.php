<?php
/**
 * Full-screen mobile navigation.
 *
 * @package Moon_Theme
 */

?>
<div class="moon-drawer" id="moon-menu" data-moon-panel="menu" aria-hidden="true">
	<div class="moon-drawer__top">
		<span class="moon-brand__text"><?php echo esc_html( moon_brand_text() ); ?></span>
		<button type="button" class="moon-iconbtn" data-moon-close aria-label="<?php esc_attr_e( 'بستن', 'moon-theme' ); ?>">
			<?php moon_the_icon( 'x', 20 ); ?>
		</button>
	</div>

	<nav aria-label="<?php esc_attr_e( 'منوی موبایل', 'moon-theme' ); ?>">
		<?php if ( moon_has_primary_menu() ) : ?>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'moon-drawer__nav',
				'depth'          => 1,
				'fallback_cb'    => false,
			) );
			?>
		<?php else : ?>
			<ul class="moon-drawer__nav">
				<?php foreach ( moon_default_nav_items() as $item ) : ?>
					<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</nav>

	<p class="moon-eyebrow moon-drawer__season"><span dir="ltr">Autumn / Winter 2026</span></p>
</div>
