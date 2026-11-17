<?php
/**
 * Template Name: حریم خصوصی — MOON
 *
 * Designed legal layout with a section index. Editing the page replaces the
 * shipped copy.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/pages/legal', null, array( 'kind' => 'privacy' ) );
endwhile;

get_footer();
