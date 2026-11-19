<?php
/**
 * Recently viewed products section.
 *
 * @package Netelec
 */

$netelec_recent = netelec_recent_ids();
if ( ! $netelec_recent ) {
	return;
}
?>
<section class="section section--recent">
	<div class="container">
		<?php netelec_section_head( 'اخیراً مشاهده کرده‌اید' ); ?>
		<?php netelec_render_products_by_ids( $netelec_recent ); ?>
	</div>
</section>
