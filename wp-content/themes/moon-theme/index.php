<?php
/**
 * Blog index / archive fallback.
 *
 * @package Moon_Theme
 */

get_header();

moon_page_header();
?>
<div class="moon-posts">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content/card' );
		endwhile;
		?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>

<?php moon_pagination(); ?>
<?php get_footer(); ?>
