<?php
/**
 * Wishlist panel (saved locally in the visitor's browser).
 *
 * @package Moon_Theme
 */

?>
<div class="moon-wishlist" id="moon-wishlist" data-moon-panel="wishlist" aria-hidden="true" aria-label="<?php esc_attr_e( 'علاقه‌مندی‌ها', 'moon-theme' ); ?>">
	<div class="moon-wishlist__head">
		<p class="moon-eyebrow"><span dir="ltr">Wishlist</span></p>
		<button type="button" class="moon-iconbtn" data-moon-close aria-label="<?php esc_attr_e( 'بستن', 'moon-theme' ); ?>">
			<?php moon_the_icon( 'x', 18 ); ?>
		</button>
	</div>

	<ul class="moon-wishlist__list" data-moon-wishlist-list>
		<li class="moon-wishlist__empty"><?php esc_html_e( 'هنوز محصولی را نشان نکرده‌اید.', 'moon-theme' ); ?></li>
	</ul>

	<p class="moon-wishlist__empty" style="margin-top:1.5rem;font-size:.75rem;">
		<?php esc_html_e( 'این فهرست در همین مرورگر ذخیره می‌شود.', 'moon-theme' ); ?>
	</p>
</div>
