<?php
/**
 * Search results.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="moon-container moon-index">
	<header class="moon-page-head">
		<p class="moon-eyebrow"><?php esc_html_e( 'Search', 'moon-child' ); ?></p>
		<h1 class="moon-page-title">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Results for “%s”', 'moon-child' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<?php get_search_form(); ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="moon-index__grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', get_post_type() );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p class="moon-empty"><?php esc_html_e( 'No results. Try a different search.', 'moon-child' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
