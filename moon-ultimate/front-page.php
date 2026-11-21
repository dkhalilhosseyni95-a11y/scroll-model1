<?php
/**
 * Front page: homepage sections in the order chosen in the MOON builder.
 *
 * Sections come from `Moon_Sections`, which owns ordering, draft/publish and
 * each section's own options. Older sites keep the rows they already had.
 *
 * @package Moon_Theme
 */

get_header();

$moon_rows = class_exists( 'Moon_Sections' ) ? Moon_Sections::render_rows() : array();

if ( ! empty( $moon_rows ) ) {
	foreach ( $moon_rows as $moon_row ) {
		Moon_Sections::render( $moon_row );
	}
} elseif ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		?>
		<div class="moon-page-body moon-page-body--wide">
			<div class="moon-content" style="padding-top:8rem">
				<?php the_content(); ?>
			</div>
		</div>
		<?php
	}
}

get_footer();
