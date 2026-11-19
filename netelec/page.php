<?php
/**
 * Static page.
 *
 * @package Netelec
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'page-content' ); ?>>
		<?php netelec_page_header( get_the_title() ); ?>

		<div class="container page-content__body">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="page-content__media"><?php the_post_thumbnail( 'full' ); ?></div>
			<?php endif; ?>

			<div class="entry-content">
				<?php the_content(); ?>
				<?php
				wp_link_pages(
					array(
						'before' => '<div class="page-links">',
						'after'  => '</div>',
					)
				);
				?>
			</div>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
