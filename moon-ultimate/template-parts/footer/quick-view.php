<?php
/**
 * Quick view dialog shell. The panel itself is loaded by AJAX (shop.js) so the
 * full product summary, colour swatches and variation form come along.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

if ( ! moon_is_woo() ) {
	return;
}
?>
<div class="moon-qv" id="moon-quick-view" aria-hidden="true">
	<div class="moon-qv__dialog" data-moon-qv-dialog></div>
</div>

<div class="moon-sizeguide-modal" aria-hidden="true">
	<?php
	if ( class_exists( 'Moon_Shop' ) ) {
		echo Moon_Shop::size_guide_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with escaped values.
	}
	?>
</div>

<div class="moon-toast" data-moon-toast role="status" aria-live="polite"></div>
