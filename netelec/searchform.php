<?php
/**
 * Search form.
 *
 * @package Netelec
 */

$netelec_search_id = wp_unique_id( 'search-form-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $netelec_search_id ); ?>">جستجو</label>
	<input type="search" id="<?php echo esc_attr( $netelec_search_id ); ?>" class="search-field" value="<?php echo get_search_query(); ?>" name="s" placeholder="جستجو…">
	<button type="submit" class="search-submit" aria-label="جستجو"><?php netelec_icon( 'search' ); ?></button>
</form>
