<?php
/**
 * Static page.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'moon-page' ); ?>>
		<?php moon_page_header(); ?>

		<div class="moon-page-body<?php echo moon_is_woo() && ( is_cart() || is_checkout() || is_account_page() ) ? ' moon-page-body--wide' : ''; ?>">
			<div class="moon-content"><?php the_content(); ?></div>
			<?php
			wp_link_pages( array(
				'before' => '<nav class="moon-pagination">',
				'after'  => '</nav>',
			) );
			?>
		</div>
	</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		moon_comments();
	}
endwhile;

get_footer();
