<?php
/**
 * Quick view dialog shell. Content is filled by cards.js from card data attributes.
 *
 * @package Moon_Theme
 */

?>
<div class="moon-qv" id="moon-quick-view" data-moon-quickview aria-hidden="true">
	<div class="moon-qv__panel" role="dialog" aria-modal="true" aria-labelledby="moon-qv-title">
		<div class="moon-qv__media">
			<img src="" alt="" data-moon-qv-image>
		</div>
		<div class="moon-qv__body">
			<p class="moon-eyebrow moon-qv__eyebrow"><span dir="ltr" data-moon-qv-en></span></p>
			<h2 class="moon-qv__title" id="moon-qv-title" data-moon-qv-title></h2>
			<p class="moon-qv__price" data-moon-qv-price></p>
			<p class="moon-qv__desc" data-moon-qv-desc></p>
			<div class="moon-qv__sizes" data-moon-qv-sizes></div>
			<div class="moon-qv__cta" data-moon-qv-cta></div>
			<button type="button" class="moon-qv__close" data-moon-close><?php esc_html_e( 'بستن', 'moon-theme' ); ?></button>
		</div>
	</div>
</div>
