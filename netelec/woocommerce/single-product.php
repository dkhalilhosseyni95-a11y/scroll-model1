<?php
/**
 * Single product template.
 *
 * @package Netelec
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="container product-page">
		<?php netelec_breadcrumb(); ?>
		<?php wc_get_template_part( 'content', 'single-product' ); ?>
	</div>
	<?php
endwhile;

get_footer();
