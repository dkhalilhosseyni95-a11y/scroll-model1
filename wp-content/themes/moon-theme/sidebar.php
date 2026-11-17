<?php
/**
 * Blog sidebar.
 *
 * @package Moon_Theme
 */

if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
	return;
}
?>
<aside class="moon-sidebar" aria-label="<?php esc_attr_e( 'نوار کناری', 'moon-theme' ); ?>">
	<?php dynamic_sidebar( 'blog-sidebar' ); ?>
</aside>
