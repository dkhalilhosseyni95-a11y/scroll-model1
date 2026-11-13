<?php
/**
 * Front page: homepage sections in the order chosen in the MOON panel.
 *
 * @package Moon_Theme
 */

get_header();

$moon_sections = moon_home_sections();

if ( ! empty( $moon_sections ) ) {
	foreach ( array_keys( $moon_sections ) as $moon_section ) {
		moon_render_home_section( $moon_section );
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
