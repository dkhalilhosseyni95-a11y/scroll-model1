<?php
/**
 * Latest blog posts on the homepage.
 *
 * @package Netelec
 */

$netelec_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => (int) netelec_opt( 'blog_count' ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $netelec_posts->have_posts() ) {
	return;
}
?>
<section class="section section--blog">
	<div class="container">
		<?php netelec_section_head( netelec_opt( 'blog_title' ), get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>

		<div class="post-grid" style="--nc-cols:3">
			<?php
			while ( $netelec_posts->have_posts() ) :
				$netelec_posts->the_post();
				get_template_part( 'template-parts/content-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
