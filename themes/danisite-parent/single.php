<?php
/**
 * Single post.
 *
 * @package DaniSite
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<section class="dani-page-hero">
			<div class="dani-container dani-page-hero__inner">
				<?php dani_breadcrumbs(); ?>
				<h1 class="dani-page-hero__title"><?php the_title(); ?></h1>
				<div class="dani-article__meta" style="margin-top:1.25rem">
					<span><?php echo esc_html( dani_date() ); ?></span>
					<span aria-hidden="true">·</span>
					<span><?php echo esc_html( dani_reading_time() ); ?></span>
					<?php $dani_cats = get_the_category(); ?>
					<?php if ( ! empty( $dani_cats ) ) : ?>
						<span aria-hidden="true">·</span>
						<a href="<?php echo esc_url( get_category_link( $dani_cats[0] ) ); ?>" style="color:var(--primary);font-weight:600"><?php echo esc_html( $dani_cats[0]->name ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="dani-section">
			<div class="dani-container">
				<div class="dani-article">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="dani-article__thumb">
							<?php the_post_thumbnail( 'dani-wide', array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
						</figure>
					<?php endif; ?>

					<div class="dani-prose" style="margin-top:2.5rem">
						<?php the_content(); ?>
						<?php
						wp_link_pages( array(
							'before' => '<nav class="dani-pagination">',
							'after'  => '</nav>',
						) );
						?>
					</div>

					<?php
					$dani_tags = get_the_tags();
					if ( ! empty( $dani_tags ) ) :
						?>
						<div class="dani-tags">
							<?php foreach ( $dani_tags as $dani_tag ) : ?>
								<a class="dani-tag" href="<?php echo esc_url( get_tag_link( $dani_tag ) ); ?>">#<?php echo esc_html( $dani_tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<nav class="dani-post-nav" aria-label="<?php esc_attr_e( 'نوشته‌های دیگر', 'danisite' ); ?>" style="margin-top:3rem;display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap">
						<?php
						$dani_prev = get_previous_post();
						$dani_next = get_next_post();
						if ( $dani_prev ) :
							?>
							<a class="dani-btn dani-btn--ghost" href="<?php echo esc_url( get_permalink( $dani_prev ) ); ?>"><?php echo dani_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( wp_trim_words( get_the_title( $dani_prev ), 6 ) ); ?></span></a>
						<?php endif; ?>
						<?php if ( $dani_next ) : ?>
							<a class="dani-btn dani-btn--ghost" href="<?php echo esc_url( get_permalink( $dani_next ) ); ?>"><span><?php echo esc_html( wp_trim_words( get_the_title( $dani_next ), 6 ) ); ?></span><?php echo dani_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php endif; ?>
					</nav>
				</div>
			</div>
		</section>
	</article>

	<?php
	$dani_related = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( get_the_ID() ),
		'category__in'        => wp_get_post_categories( get_the_ID() ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $dani_related->have_posts() ) :
		?>
		<section class="dani-section dani-section--surface">
			<div class="dani-container">
				<?php dani_section_head( array(
					'eyebrow' => 'MORE READING',
					'title'   => 'مطالب مرتبط',
				) ); ?>
				<div class="dani-posts dani-posts--3" style="margin-top:3rem">
					<?php
					while ( $dani_related->have_posts() ) :
						$dani_related->the_post();
						get_template_part( 'template-parts/card-post' );
					endwhile;
					?>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	endif;

	if ( comments_open() || get_comments_number() ) {
		echo '<section class="dani-section"><div class="dani-container"><div class="dani-article">';
		comments_template();
		echo '</div></div></section>';
	}
endwhile;

get_footer();
