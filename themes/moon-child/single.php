<?php
/**
 * Single post (journal story).
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="moon-container moon-single">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="moon-single__head">
				<p class="moon-eyebrow"><?php echo esc_html( get_the_date() ); ?> &middot; <?php echo esc_html( moon_child_reading_time() ); ?></p>
				<h1 class="moon-single__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="moon-single__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="moon-single__media"><?php the_post_thumbnail( 'moon-editorial' ); ?></figure>
			<?php endif; ?>
			<div class="moon-prose"><?php the_content(); ?></div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
		<?php
	endwhile;
	?>
</div>
<?php
get_footer();
