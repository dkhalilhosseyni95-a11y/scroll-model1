<?php
/**
 * Search overlay.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

?>
<div class="moon-search" id="moon-search" data-moon-panel="search" aria-hidden="true">
	<button type="button" class="moon-search__close" data-moon-close aria-label="<?php esc_attr_e( 'بستن جستجو', 'moon-theme' ); ?>">
		<?php moon_the_icon( 'x', 24 ); ?>
	</button>

	<form role="search" method="get" class="moon-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="moon-sr-only" for="moon-search-field"><?php esc_html_e( 'جستجو', 'moon-theme' ); ?></label>
		<input type="search" id="moon-search-field" class="moon-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجوی محصولات…', 'moon-theme' ); ?>">
		<?php if ( moon_is_woo() ) : ?>
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>
		<button type="submit" class="moon-iconbtn" aria-label="<?php esc_attr_e( 'جستجو', 'moon-theme' ); ?>">
			<?php moon_the_icon( 'search', 20 ); ?>
		</button>
	</form>
</div>
