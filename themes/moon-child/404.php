<?php
/**
 * 404 template.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="moon-container moon-404">
	<p class="moon-eyebrow"><?php esc_html_e( 'Error 404', 'moon-child' ); ?></p>
	<h1 class="moon-404__title"><?php esc_html_e( 'This page has left the wardrobe.', 'moon-child' ); ?></h1>
	<p class="moon-404__text"><?php esc_html_e( 'The page you are looking for was moved or never existed. Discover the current collection instead.', 'moon-child' ); ?></p>
	<div class="moon-404__actions">
		<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
			<a class="moon-btn moon-btn--solid" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse the shop', 'moon-child' ); ?></a>
		<?php endif; ?>
		<a class="moon-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'moon-child' ); ?></a>
	</div>
</div>
<?php
get_footer();
