<?php
/**
 * Search form.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="moon-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="moon-search-field"><?php esc_html_e( 'Search', 'moon-child' ); ?></label>
	<input id="moon-search-field" type="search" class="moon-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search the collection', 'moon-child' ); ?>">
	<button type="submit" class="moon-search__submit"><?php esc_html_e( 'Search', 'moon-child' ); ?></button>
</form>
