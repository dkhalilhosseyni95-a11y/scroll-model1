<?php
/**
 * Floating glass header pill.
 *
 * @package Moon_Theme
 */

$moon_sticky = (int) moon_opt( 'header_sticky' );
?>
<header class="moon-header<?php echo $moon_sticky ? '' : ' is-static'; ?>" id="moon-header" data-moon-header>
	<div class="moon-header__pill moon-glass">
		<a class="moon-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php elseif ( function_exists( 'moon_core_logo' ) && moon_core_logo() ) : ?>
				<?php moon_core_logo(); ?>
			<?php else : ?>
				<span class="moon-brand__text"><?php echo esc_html( moon_brand_text() ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="moon-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'moon-theme' ); ?>">
			<?php if ( moon_has_primary_menu() ) : ?>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				) );
				?>
			<?php else : ?>
				<ul>
					<?php foreach ( moon_default_nav_items() as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</nav>

		<div class="moon-actions">
			<?php if ( moon_opt( 'header_search' ) ) : ?>
				<button type="button" class="moon-iconbtn moon-iconbtn--sm-hide" data-moon-open="search" aria-label="<?php esc_attr_e( 'جستجو', 'moon-theme' ); ?>" aria-expanded="false">
					<?php moon_the_icon( 'search', 17 ); ?>
				</button>
			<?php endif; ?>

			<?php if ( moon_opt( 'header_account' ) && moon_is_woo() ) : ?>
				<a class="moon-iconbtn moon-iconbtn--sm-hide" href="<?php echo esc_url( moon_account_url() ); ?>" aria-label="<?php esc_attr_e( 'حساب کاربری', 'moon-theme' ); ?>">
					<?php moon_the_icon( 'user', 17 ); ?>
				</a>
			<?php endif; ?>

			<?php if ( moon_opt( 'header_wishlist' ) ) : ?>
				<button type="button" class="moon-iconbtn moon-iconbtn--sm-hide" data-moon-open="wishlist" aria-label="<?php esc_attr_e( 'علاقه‌مندی‌ها', 'moon-theme' ); ?>" aria-expanded="false">
					<?php moon_the_icon( 'heart', 17 ); ?>
					<span class="moon-wish-count" data-moon-wish-count hidden></span>
				</button>
			<?php endif; ?>

			<?php if ( moon_is_woo() ) : ?>
				<a class="moon-iconbtn moon-cart-toggle" href="<?php echo esc_url( moon_cart_url() ); ?>" data-moon-open="cart" aria-controls="moon-cart" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( __( 'سبد خرید، %s کالا', 'moon-theme' ), number_format_i18n( moon_cart_count() ) ) ); ?>">
					<?php moon_the_icon( 'shopping-bag', 17 ); ?>
					<?php $moon_count = moon_cart_count(); ?>
					<span class="moon-cart-count<?php echo $moon_count > 0 ? ' has-count' : ''; ?>" data-count="<?php echo esc_attr( (string) $moon_count ); ?>"><?php echo $moon_count > 0 ? esc_html( moon_fa_digits( (string) $moon_count ) ) : ''; ?></span>
				</a>
			<?php endif; ?>

			<?php if ( moon_opt( 'header_theme_toggle' ) ) : ?>
				<button type="button" class="moon-switch" data-moon-theme aria-pressed="false" aria-label="<?php esc_attr_e( 'تغییر حالت روشن و تاریک', 'moon-theme' ); ?>">
					<span class="moon-switch__knob">
						<?php moon_the_icon( 'sun', 13, 'moon-icon--sun' ); ?>
						<?php moon_the_icon( 'moon', 13, 'moon-icon--moon' ); ?>
					</span>
				</button>
			<?php endif; ?>

			<button type="button" class="moon-iconbtn moon-iconbtn--lg-hide" data-moon-open="menu" aria-label="<?php esc_attr_e( 'منو', 'moon-theme' ); ?>" aria-expanded="false">
				<?php moon_the_icon( 'menu', 18 ); ?>
			</button>
		</div>
	</div>
</header>
