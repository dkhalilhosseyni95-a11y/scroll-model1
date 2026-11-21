<?php
/**
 * Single post.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'moon-single' ); ?>>
		<header class="moon-entry-header">
			<?php moon_breadcrumb(); ?>
			<h1 class="moon-entry-header__title"><?php the_title(); ?></h1>
			<div class="moon-entry-header__meta">
				<?php moon_posted_on(); ?>
				<span><?php echo esc_html( get_the_author() ); ?></span>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="moon-entry-hero"><?php the_post_thumbnail( 'moon-wide' ); ?></figure>
		<?php endif; ?>

		<div class="moon-page-body">
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
	moon_comments();
endwhile;

get_footer();
