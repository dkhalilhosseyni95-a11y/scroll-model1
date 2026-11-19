<?php
/**
 * Single post.
 *
 * @package Netelec
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'single-post' ); ?>>
		<div class="container single-post__head">
			<?php netelec_breadcrumb(); ?>
			<h1 class="single-post__title"><?php the_title(); ?></h1>
			<?php netelec_post_meta(); ?>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="container single-post__media">
				<?php the_post_thumbnail( 'full', array( 'alt' => get_the_title() ) ); ?>
			</div>
		<?php endif; ?>

		<div class="container single-post__body">
			<div class="single-post__content entry-content">
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

			<?php if ( get_the_tags() ) : ?>
				<div class="single-post__tags">
					<?php the_tags( '', '' ); ?>
				</div>
			<?php endif; ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>

	<?php
	if ( netelec_opt( 'blog_related' ) ) {
		$netelec_related = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post__not_in'   => array( get_the_ID() ),
				'category__in'   => wp_get_post_categories( get_the_ID() ),
				'no_found_rows'  => true,
			)
		);

		if ( $netelec_related ) {
			?>
			<section class="section section--related-posts">
				<div class="container">
					<?php netelec_section_head( 'مقالات مرتبط' ); ?>
					<div class="post-grid" style="--nc-cols:3">
						<?php
						foreach ( $netelec_related as $netelec_post ) {
							$GLOBALS['post'] = $netelec_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $netelec_post );
							get_template_part( 'template-parts/content-card' );
						}
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
			<?php
		}
	}
endwhile;

get_footer();
