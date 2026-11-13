<?php
/**
 * Default page template. Elementor pages render through the canvas templates;
 * this file covers standard WordPress pages.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="moon-container moon-page">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'moon-page__article' ); ?>>
			<?php if ( ! is_front_page() ) : ?>
				<header class="moon-page-head">
					<h1 class="moon-page-title"><?php the_title(); ?></h1>
				</header>
			<?php endif; ?>
			<div class="moon-prose">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<nav class="moon-page-links">', 'after' => '</nav>' ) );
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>
<?php
get_footer();
